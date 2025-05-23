<?php

namespace FlipForBusiness\Checkout\Controller\Payment;

use FlipForBusiness\Checkout\Service\FlipService;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Model\Order;

/**
 * Class Callback
 *
 * Handles the callback from Flip payment gateway.
 * This controller is responsible for processing payment status notifications.
 *
 * @api
 */
class Callback extends AbstractAction implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var JsonSerializer
     */
    public JsonSerializer $jsonSerializer;

    /**
     * @var FlipService
     */
    public FlipService $flipService;

    /**
     * Create an exception for CSRF validation failure
     *
     * @param RequestInterface $request The incoming request
     * @return InvalidRequestException|null Null as CSRF validation is bypassed
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate the request for CSRF
     *
     * @param RequestInterface $request The incoming request
     * @return bool|null True if validation passed, false if failed, null for default validation
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute the callback action
     *
     * @return ResultInterface|Json JSON response containing the status of the callback processing
     * @throws LocalizedException If the request is invalid or processing fails
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->_resultJsonFactory->create();
        try {
            $this->logger->logCallback(
                "API Call Details:\n" .
                "========================================= REQUEST From Flip ===================================\n" .
                "Request URL: ". $this->getRequest()->getOriginalPathInfo() . "\n" .
                "Request Body: " . json_encode($this->getCallbackData(), JSON_PRETTY_PRINT) . "\n" .
                "=========================================================================================="
            );

            // Validate request method
            if (!$this->getRequest()->isPost()) {
                throw new LocalizedException(__('Invalid request method'));
            }

            // Validate token
            $token = $this->getRequest()->getPost('token');
            if (!$this->validateToken($token)) {
                throw new LocalizedException(__('Invalid token'));
            }

            $data = $this->getCallbackData();
            $this->validateCallbackData($data);
            $this->processPayment($data);

            $response = [
                'status_code' => 200,
                'status' => 'success',
                'message' => 'Payment processed successfully!'
            ];

            $this->logger->logCallback(
                "API Response Details:\n" .
                "======================================= RESPONSE from Magento =============================\n" .
                "API Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n" .
                "=========================================================================================="
            );

            return $resultJson->setData($response);
        } catch (LocalizedException $e) {
            $errorResponse = [
                'status_code' => 400,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $this->logger->logErrorException('Payment Callback Error: ' . $e->getMessage(), $e);
            $this->logger->logCallback(
                "API Response Details:\n" .
                "======================================= RESPONSE from Magento =============================\n" .
                "API Response: " . json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n" .
                "Error: " . $e->getMessage() . "\n" .
                "=========================================================================================="
            );
            return $resultJson->setData($errorResponse);
        } catch (\Exception $e) {
            $errorResponse = [
                'status_code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the payment'
            ];
            $this->logger->logErrorException('Callback->execute: ' . $e->getMessage(), $e);
            $this->logger->logCallback(
                "API Response Details:\n" .
                "======================================= RESPONSE from Magento =============================\n" .
                "API Response: " . json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n" .
                "Error: " . $e->getMessage() . "\n" .
                "=========================================================================================="
            );
            return $resultJson->setData($errorResponse);
        }
    }

    /**
     * Validate the callback token
     *
     * @param string|null $token The token provided in the callback request
     * @return bool True if the token is valid, otherwise false
     */
    private function validateToken(?string $token): bool
    {
        return $token === $this->flipModuleConfig->getValidationKey();
    }

    /**
     * Retrieve and decode callback data
     *
     * @return array<string, mixed> Decoded callback data
     * @throws LocalizedException If the callback data is invalid or empty
     */
    private function getCallbackData(): array
    {
        $data = $this->getRequest()->getPost('data');
        if (empty($data)) {
            throw new LocalizedException(__('Empty callback data'));
        }

        try {
            return $this->jsonSerializer->unserialize($data);
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('Invalid JSON data'));
        }
    }

    /**
     * Validate the callback data for required fields
     *
     * @param array $data The callback data to validate
     * @return void
     * @throws LocalizedException If any required fields are missing
     */
    private function validateCallbackData(array $data): void
    {
        $requiredFields = ['id', 'amount', 'status', 'bill_link_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new LocalizedException(__('Missing required field: %1', $field));
            }
        }
    }

    /**
     * Process the payment and update the order
     *
     * @param array $data The callback data
     * @return void
     * @throws LocalizedException If the payment processing fails
     */
    private function processPayment(array $data): void
    {
        $order = $this->orderRepository->getOrderByLinkId($data['bill_link_id']);
        if ($order->getId()) {
            try {
                // Verify transaction status with Flip API for additional security
                $isVerified = $this->flipService->verifyTransactionStatus($data);
                if (!$isVerified) {
                    $this->logger->logErrorException(
                        "Transaction verification failed for bill_link_id: {$data['bill_link_id']}",
                        new \Exception("Callback data doesn't match API response")
                    );
                    throw new LocalizedException(__(
                        'Transaction verification failed. Please contact support.'
                    ));
                }

                if ($data['status'] === 'SUCCESSFUL') {
                    $paymentMethod = strtoupper($data['sender_bank_type']) . '-' .
                        strtoupper($data['sender_bank']);
                    
                    $successMessage = "<strong style='color: green;'>Payment Successfully!</strong><br>" .
                        "- Payment Transaction Id: {$data['id']}<br>" .
                        "- Payment Status: {$data['status']}<br>" .
                        "- Payment Method: {$paymentMethod}";
                    
                    $this->orderRepository->setStateAndStatus(
                        $order,
                        Order::STATE_PROCESSING,
                        $successMessage,
                        true
                    );
                    $this->orderRepository->setAdditionalPaymentInfo($order, 'flip_trx_id', $data['id']);
                    $this->orderRepository->saveOrder($order);

                    $this->invoiceRepository->createInvoice($order, $data);
                } elseif (in_array($data['status'], ['CANCELLED', 'FAILED'])) {
                    $statusTitle = $data['status'] === 'CANCELLED' ? 'Expired' : 'Failed';
                    
                    $failureMessage = "<strong style='color: red;'>Flip Bill {$statusTitle}!</strong><br>" .
                        "- Payment Transaction Id: {$data['id']}<br>" .
                        "- Payment Status: {$data['status']}";
                    
                    $this->orderRepository->setStateAndStatus(
                        $order,
                        Order::STATE_CANCELED,
                        $failureMessage,
                        true
                    );
                    $this->orderRepository->setAdditionalPaymentInfo($order, 'flip_trx_id', $data['id']);
                    $this->orderRepository->saveOrder($order);
                } else {
                    throw new LocalizedException(__('Payment was not successful'));
                }
            } catch (\Exception $e) {
                $this->logger->logErrorException("Error processing payment: {$e->getMessage()}", $e);
                throw new LocalizedException(__($e->getMessage()));
            }
        } else {
            throw new LocalizedException(__('Order not found'));
        }
    }
}
