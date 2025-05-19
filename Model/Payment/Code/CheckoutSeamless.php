<?php

namespace FlipForBusiness\Checkout\Model\Payment\Code;

use FlipForBusiness\Checkout\Model\Payment\AbstractPayment;

/**
 * Class CheckoutSeamless
 * 
 * Represents the seamless checkout payment method for Flip.
 * This class handles the payment integration for seamless checkout via Flip,
 * providing a streamlined payment experience for customers.
 *
 * @package FlipForBusiness\Checkout\Model\Payment\Code
 * @api
 */
class CheckoutSeamless extends AbstractPayment
{
    /**
     * Payment method code for the seamless checkout payment
     *
     * @var string
     */
    public const CODE = 'flipforbusiness_checkout_seamless';

    /**
     * Payment method code
     *
     * @var string
     */
    public string $code = self::CODE;
}
