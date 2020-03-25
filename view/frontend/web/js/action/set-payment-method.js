/**
 * Copyright © 2017 SeQura Engineering. All rights reserved.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, quote, urlBuilder, storage, errorProcessor, fullScreenLoader, setPaymentInformation) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl,
                placeOrder = function (product_code) {
                    if (typeof window.SequraFormInstance === 'undefined') {
                        setTimeout(placeOrder, 100);
                        return;
                    }
                    window.SequraFormInstance.setCloseCallback(function () {
                        fullScreenLoader.stopLoader();
                        window.SequraFormInstance.defaultCloseCallback();
                    });
                    window.SequraFormInstance.setElement("sq-identification-" + product_code);
                    window.SequraFormInstance.show();
                };

            return setPaymentInformation(messageContainer, quote.paymentMethod()).done(
                function () {
                    serviceUrl = urlBuilder.createUrl('/sequra_core/Submission', {});
                    storage.get(serviceUrl).done(
                        function (response) {
                            $('[id^="sq-identification"]').remove();
                            $('body').append(response);
                            var product_code = window.checkoutConfig.payment[quote.paymentMethod().method].product
                            placeOrder(product_code);
                        }
                    ).fail(
                        function (response) {
                            errorProcessor.process(response, messageContainer);
                            fullScreenLoader.stopLoader();
                        }
                    );
                });
        };
    }
);