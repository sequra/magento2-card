/**
 * Copyright © 2017 SeQura Engineering. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [   
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Sequra_Card/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage'
    ],
    function (ko, Component, setPaymentMethodAction, additionalValidators, quote, urlBuilder, storage) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Sequra_Card/payment/form'
            },

            card: ko.observable(false),
            card_icon: ko.observable(),
            card_brand: ko.observable(),
            card_last_four: ko.observable(),
            card_expiry_month_year: ko.observable(),

            initObservable: function () {
                this._super()
                    .observe([
                        'card',
                        'card_icon',
                        'card_brand',
                        'card_last_four',
                        'card_expiry_month_year'
                    ]);
                //Start credit car info query    
                storage.get(
                    urlBuilder.createUrl('/sequra_card/StartCardQuery', {})
                ).done(
                    function(){
                        this.parseResponseOrRetry()
                    }.bind(this)
                );

                return this;
            },

            getProduct: function () {
                return window.checkoutConfig.payment.sequra_card.product;
            },

            getAmount: function () {
                var totals = quote.getTotals()();
                if (totals) {
                    return Math.round(totals['base_grand_total']*100);
                }
                return Math.round(quote['base_grand_total']*100);
            },

            showLogo: function(){
                return window.checkoutConfig.payment.sequra_card.showlogo === "1";
            },

            showSequraForm: function () {
               if (additionalValidators.validate()) {
                   //update payment method information if additional was changed
                   this.selectPaymentMethod();
                   setPaymentMethodAction(this.messageContainer);
                   return false;
               }
            },

            parseResponseOrRetry: function() {
                storage.get(urlBuilder.createUrl('/sequra_card/GetCardQuery', {}))
                    .done(function (response) {
                        var data = JSON.parse(response);
                        if(data.result == 'done'){
                            this.populateCardDetails(data.card);
                        } else if (data.result == 'retry'){
                            setTimeout(this.parseResponseOrRetry.bind(this),2000);
                        }
                    }.bind(this));
            },

            populateCardDetails: function(card){
                if(card.icon){                    
                    this.card(true);
                    this.card_icon(card.icon);
                    this.card_brand(card.brand);
                    this.card_last_four(card.last_four);
                    this.card_expiry_month_year(card.expiry_month_year);                
                } else {
                    this.card(false);
                }
            }
        });
    }
);