<?php

namespace FlipForBusiness\Checkout\Block\Payment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\Order;

/**
 * Class Finish
 *
 * Block class for handling the payment finish page display
 *
 * @api
 */
class Finish extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * Get the order object
     *
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        // First check if order is set in data array (from controller)
        if ($order = $this->getData('order')) {
            return $order;
        }
        
        return null;
    }

    /**
     * Set the order object
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order): self
    {
        $this->setData('order', $order);
        return $this;
    }

    /**
     * Get customer session
     *
     * @return CustomerSession
     */
    public function getCustomerSession(): CustomerSession
    {
        return $this->customerSession;
    }

    /**
     * Get order view URL
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl(int $orderId): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
