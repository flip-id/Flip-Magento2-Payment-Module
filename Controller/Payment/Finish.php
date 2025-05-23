<?php

namespace FlipForBusiness\Checkout\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

/**
 * Class Finish
 *
 * Handles the finish step of the Flip Checkout payment process.
 * Displays a confirmation page with order details after the payment is processed successfully.
 *
 * @api
 */
class Finish extends AbstractAction implements HttpGetActionInterface, CsrfAwareActionInterface
{
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
     * @return bool|null Always returns true to skip CSRF validation
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Execute the controller action to display the payment confirmation page
     *
     * @return ResultInterface|Page|Redirect Result page or redirect response
     * @throws NotFoundException If the requested page or data is not found
     */
    public function execute(): ResultInterface
    {
        $orderId = $this->requestInterface->getParam('state');
        if (!$orderId) {
            $this->logger->logErrorException("Order ID missing in request.");
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        try {
            // Fetch order by ID
            $order = $this->orderRepository->getOrderById($orderId);
            if (!$order || !$order->getId()) {
                throw new NoSuchEntityException(__("Order not found for ID: %1", $orderId));
            }

            // Create result page
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Payment Order Confirmation'));

            // Find the layout block and set order data
            $finishBlock = $resultPage->getLayout()->getBlock('finish.page');
            if (!$finishBlock) {
                throw new LocalizedException(__("Block 'finish.page' not found in layout."));
            }

            $finishBlock->setData('order', $order);
            return $resultPage;

        } catch (NoSuchEntityException $e) {
            $this->logger->critical("Order not found: " . $e->getMessage());
            return $this->redirectFactory->create()->setPath('checkout/cart');
        } catch (LocalizedException $e) {
            $this->logger->critical("Layout error: " . $e->getMessage());
            return $this->redirectFactory->create()->setPath('checkout/cart');
        } catch (\Exception $e) {
            $this->logger->critical("Unexpected error in Finish Controller: " . $e->getMessage());
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }
    }
}
