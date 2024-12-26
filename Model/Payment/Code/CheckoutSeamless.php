<?php

namespace Flip\Checkout\Model\Payment\Code;

use Flip\Checkout\Model\Payment\AbstractPayment;

/**
 * Class CheckoutSeamless
 * Represents the seamless checkout payment method for Flip.
 *
 * This class is used for handling the payment integration for seamless checkout via Flip.
 * It extends the `AbstractPayment` class and defines the payment method code.
 *
 * @package Flip\Checkout\Model\Payment\Code
 */
class CheckoutSeamless extends AbstractPayment
{
    /**
     * Payment method code for the seamless checkout payment.
     */
    const CODE = 'flip_checkout_seamless';

    /**
     * @var string The payment method code.
     */
    public string $code = self::CODE;
}
