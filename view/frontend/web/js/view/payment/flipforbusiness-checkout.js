
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'flipforbusiness_checkout_seamless',
                component: 'FlipForBusiness_Checkout/js/view/payment/method-renderer/checkout-seamless'
            }
        );
        return Component.extend({});
    }
);
