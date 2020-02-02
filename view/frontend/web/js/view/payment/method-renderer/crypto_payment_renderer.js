define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'jquery'
    ],
    function (ko, Component, url, quote, jquery) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'CryptoCore_CryptoPayment/payment/form_crypto_payment',
                default_crypto: window.checkoutConfig.payment.crypto_payment.default_crypto
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'cryptocorrency'
                    ]);
                return this;
            },

            afterPlaceOrder: function () {
                jquery('body').loader('show');
                this.selectPaymentMethod();
                jquery.mage.redirect(url.build(window.checkoutConfig.payment.crypto_payment.redirectUrl));
                return false;
            },

            getCode: function () {
                return 'crypto_payment';
            },

            getBillingAddress: function () {
                if (quote.billingAddress() == null) {
                    return null;
                }

                if (typeof quote.billingAddress().street === 'undefined' || typeof quote.billingAddress().street[0] === 'undefined') {
                    return null;
                }

                return quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().postcode;
            },

            getLogo: function () {
                return window.checkoutConfig.payment.crypto_payment.logo;
            },

            getCryptoCurencies: function () {
                var list = [];
                for (var i = 0; i < window.checkoutConfig.payment.crypto_payment.cryptocurrencies.length; i++) {
                   var value = window.checkoutConfig.payment.crypto_payment.cryptocurrencies[i];
                   list.push(
                        {
                            'value': value.value,
                            'label': value.text
                        }
                    );
                }
                return list;
            }
        });
    }
);