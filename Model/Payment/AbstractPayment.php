<?php

namespace FlipForBusiness\Checkout\Model\Payment;

use FlipForBusiness\Checkout\Logger\FlipLogger;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class AbstractPayment
 *
 * Provides a base payment method implementation for Flip payment methods.
 * This class extends the Magento Payment Adapter and implements core payment functionality.
 *
 * @api
 */
class AbstractPayment extends Adapter
{
    /**
     * Payment method code
     *
     * @var string
     */
    public string $code;

    /**
     * Flag indicating whether the gateway is enabled
     *
     * @var bool
     */
    protected bool $isGateway = true;

    /**
     * Logger instance for payment-related events
     *
     * @var FlipLogger
     */
    private FlipLogger $logger;

    /**
     * AbstractPayment constructor.
     *
     * @param ManagerInterface $eventManager Event manager for handling Magento events
     * @param ValueHandlerPoolInterface $valueHandlerPool Pool of value handlers for payment gateway configuration
     * @param PaymentDataObjectFactory $paymentDataObjectFactory Factory for creating payment data objects
     * @param FlipLogger $logger Logger instance for logging events and errors
     * @param string $code Payment method code
     * @param string $formBlockType Block type for the payment method form
     * @param string $infoBlockType Block type for the payment method info
     * @param CommandPoolInterface|null $commandPool Command pool for gateway commands
     * @param ValidatorPoolInterface|null $validatorPool Validator pool for gateway command validation
     * @param CommandManagerInterface|null $commandExecutor Command executor for running commands
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        FlipLogger $logger,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        ?CommandPoolInterface $commandPool = null,
        ?ValidatorPoolInterface $validatorPool = null,
        ?CommandManagerInterface $commandExecutor = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->logger = $logger;
    }

    /**
     * Checks if the payment method is available.
     *
     * This method checks if the payment method is active in the configuration and
     * if it is available for the given cart (quote).
     *
     * @param CartInterface|null $quote The current cart/quote object
     * @return bool True if the payment method is available, false otherwise
     */
    public function isAvailable(?CartInterface $quote = null): bool
    {
        return $this->getConfigData('active') && parent::isAvailable($quote);
    }

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function canUseCheckout(?CartInterface $quote = null): bool
    {
        return $this->isAvailable($quote);
    }
}
