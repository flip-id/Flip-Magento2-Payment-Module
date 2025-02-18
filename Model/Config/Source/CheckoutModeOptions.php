<?php

namespace Flip\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CheckoutModeOptions implements OptionSourceInterface
{
    /**
     * Retrieve options array
     *
     * @return array
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            1 => __('Redirect'),
        ];
    }
}
