/*browser:true*/
/*global define*/
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
                type: 'crypto_payment',
                component: 'CryptoCore_CryptoPayment/js/view/payment/method-renderer/crypto_payment_renderer'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
