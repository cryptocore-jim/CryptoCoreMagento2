define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/totals',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'jquery'
    ],
    function (ko, Component, totals, url, quote, jquery) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'CryptoCore_CryptoPayment/payment/form_crypto_payment',
                selectedCrypto: window.checkoutConfig.payment.crypto_payment.default_crypto
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'selectedCrypto'
                    ]);
                return this;
            },

            ccoreGetPureValue: function () {
                if (totals.totals()) {
                    return parseFloat(totals.getSegment('grand_total').value);
                }

                return 0;
            },

            afterPlaceOrder: function () {
                jquery('body').loader('show');
                this.selectPaymentMethod();
                jquery.mage.redirect(url.build(window.checkoutConfig.payment.crypto_payment.redirectUrl));
                return false;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'selected_crypto': this.selectedCrypto()
                    }
                };
            },

            getCode: function () {
                return 'crypto_payment';
            },

            isSelectCurrency: function () {
                if (window.checkoutConfig.payment.crypto_payment.select_currency == 1) {
                    return true
                }
                return false;
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
                    var rate;
                    if (value.volatility == 0) {
                        rate = parseFloat((value.rate * this.ccoreGetPureValue()).toFixed(value.decimals_amount))
                    } else {
                        rate = parseFloat((value.rate * this.ccoreGetPureValue() + (value.rate * this.ccoreGetPureValue() * value.volatility / 100)).toFixed(value.decimals_amount));
                    }
                    list.push(
                        {
                            'value': value.value,
                            'label': value.text,
                            'rate':rate,
                            'logo': value.logo
                        }
                    );
                }
                return list;
            }
        });
    }
);