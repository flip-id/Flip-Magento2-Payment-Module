<?php

namespace FlipForBusiness\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CheckoutModeOptions
 *
 * Provides options for the checkout mode configuration in the admin panel.
 * Currently only supports 'Redirect' mode for the Flip payment method.
 */
class CheckoutModeOptions implements OptionSourceInterface
{
    /**
     * Retrieve options array
     *
     * @return array Array of options with keys 'value' and 'label'
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 1, 'label' => __('Redirect')],
        ];
    }

    /**
     * Retrieve options as a key-value pair
     *
     * @return array Array with option values as keys and labels as values
     */
    public function toArray(): array
    {
        return [
            1 => __('Redirect'),
        ];
    }
}
