<?php

namespace FlipForBusiness\Checkout\Block\Adminhtml\Config;

use FlipForBusiness\Checkout\Model\Config\Payment\ModuleConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class CallbackUrl
 *
 * Custom configuration field for the Flip Checkout module in the Magento admin panel.
 * This class extends Magento's core `Field` block and is used to display a fixed notification
 * endpoint URL for the Flip Checkout payment solution.
 *
 * The URL is intended to be used as a callback URL for payment notifications, and it is typically
 * configured in the Magento backend for payment-related operations.
 *
 */
class CallbackUrl extends Field
{
    /**
     * @var ModuleConfig
     */
    private ModuleConfig $moduleConfig;

    /**
     * Constructor for the CallbackUrl class.
     *
     * Initializes the class by passing the context and optional additional data
     * to the parent class constructor.
     *
     * @param Context $context The Magento backend context, providing access to session, request, etc.
     * @param array $data Optional additional data for initializing the block.
     */
    public function __construct(Context $context, ModuleConfig $moduleConfig, array $data = [])
    {
        parent::__construct($context, $data);
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Retrieve the Callback endpoint URL from module config
     *
     * This method returns the URL that is used for Flip Checkout's callback endpoint.
     * The URL points to a controller action that handles payment-related callback notifications
     * such as payment status updates or callbacks from the payment gateway.
     *
     * The URL is typically constructed dynamically, but in this case, a static URL is returned
     * as a placeholder. It is expected to be replaced with the actual base URL of the Magento store.
     *
     * @param AbstractElement $element The form element that this block is associated with.
     * @return string The fixed callback URL string for Flip Checkout.
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->moduleConfig->getCallbackUrl();
    }
}
