<?php

namespace Flip\Checkout\Controller\Payment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

/**
 * Callback class to handle Flip payment callback notifications.
 *
 * This class processes incoming callback requests from the Flip for Business API.
 * It validates the request, decodes the callback data, and updates the associated order
 * based on the payment status.
 *
 * @package Flip\Checkout\Controller\Payment
 */
class Callback extends AbstractAction implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * Create a CSRF validation exception.
     *
     * @param RequestInterface $request The incoming request.
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate the request for CSRF.
     *
     * @param RequestInterface $request The incoming request.
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute the callback action.
     *
     * @return ResultInterface JSON response containing the status of the callback processing.
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->_resultJsonFactory->create();
        try {
            $this->logger->logCallback(
                "API Call Details:\n" .
                "========================================= REQUEST From Flip =========================================\n" .
                "Request URL: ". $this->getRequest()->getOriginalPathInfo() . "\n" .
                "Request Body: " . json_encode($this->getCallbackData(), JSON_PRETTY_PRINT) . "\n" .
                "======================================================================================================="
            );

            // Validate request method.
            if (!$this->getRequest()->isPost()) {
                throw new LocalizedException(__('Invalid request method'));
            }

            // Validate token.
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
                "======================================= RESPONSE from Magento ========================================\n" .
                "API Response: " . print_r($response, true) . "\n" .
                "======================================================================================================="
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
                "======================================= RESPONSE from Magento ========================================\n" .
                "API Response: " . print_r($errorResponse, true) . "\n" .
                "Error: " . print_r($e->getMessage(), true) . "\n" .
                "======================================================================================================="
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
                "======================================= RESPONSE from Magento ========================================\n" .
                "API Response: " . print_r($errorResponse, true) . "\n" .
                "Error: " . print_r($e->getMessage(), true) . "\n" .
                "======================================================================================================="
            );
            return $resultJson->setData($errorResponse);
        }
    }

    /**
     * Validate the callback token.
     *
     * @param string|null $token The token provided in the callback request.
     * @return bool True if the token is valid, otherwise false.
     */
    private function validateToken(?string $token): bool
    {
        return $token === $this->flipModuleConfig->getValidationKey();
    }

    /**
     * Retrieve and decode callback data.
     *
     * @return array Decoded callback data.
     * @throws LocalizedException If the callback data is invalid or empty.
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
     * Validate the callback data for required fields.
     *
     * @param array $data The callback data to validate.
     * @throws LocalizedException If any required fields are missing.
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
     * Process the payment and update the order.
     *
     * @param array $data The callback data.
     * @throws LocalizedException If the payment processing fails.
     */
    private function processPayment(array $data): void
    {
        $order = $this->orderRepository->getOrderByLinkId($data['bill_link_id']);
        if ($order->getId()) {
            if ($data['status'] === 'SUCCESSFUL') {
                $paymentMethod = strtoupper($data['sender_bank_type']) . '-' . strtoupper($data['sender_bank']);
                $this->orderRepository->setStateAndStatus(
                    $order,
                    Order::STATE_PROCESSING,
                    "<strong style='color: green;'>Payment Successfully!</strong><br>" .
                    "- Payment Transaction Id: {$data['id']}<br>- Payment Status: {$data['status']}<br>- Payment Method: {$paymentMethod}",
                    true
                );
                $this->orderRepository->setAdditionalPaymentInfo($order, 'flip_trx_id', $data['id']);
                $this->orderRepository->saveOrder($order);

                $this->invoiceRepository->createInvoice($order, $data);
            } elseif (in_array($data['status'], ['CANCELLED', 'FAILED'])) {
                $statusTitle = $data['status'] === 'CANCELLED' ? 'Expired' : 'Failed';
                $this->orderRepository->setStateAndStatus(
                    $order,
                    Order::STATE_CANCELED,
                    "<strong style='color: red;'>Flip Bill {$statusTitle}!</strong><br>" .
                    "- Payment Transaction Id: {$data['id']}<br>- Payment Status: {$data['status']}",
                    true
                );
                $this->orderRepository->setAdditionalPaymentInfo($order, 'flip_trx_id', $data['id']);
                $this->orderRepository->saveOrder($order);
            } else {
                throw new LocalizedException(__('Payment was not successful'));
            }
        } else {
            throw new LocalizedException(__('Order not found'));
        }
    }
}
