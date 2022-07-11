define([
    'underscore',
    'jquery',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'stripemx'
], function (_, $, globalMessageList, Component, redirectOnSuccessAction, $t, quote, urlBuilder) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'GDW_Stripemx/payment/form',
                selectedCardType: null,
                creditCardType: '',
                creditCardNumber: '',
                creditCardSsIssue: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardSsStartYear: '',
                creditCardSsStartMonth: '',
                creditCardVerificationNumber: ''
            },
            initialize: function () {
                this._super();

                stripemx.general.publicKey = window.checkoutConfig.payment.gdw_stripemx.PublicKey;

                var fromLoaded = setInterval(function(){
                    if($(stripemx.selectors.code).length){stripemx.createFormCard(); clearInterval(fromLoaded);}
                }, 300);
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardNumber',
                        'selectedCardType',
                        'creditCardExpYear',
                        'creditCardSsIssue',
                        'creditCardExpMonth',
                        'creditCardSsStartYear',
                        'creditCardSsStartMonth',
                        'creditCardVerificationNumber',
                        'showConfirmButton'
                    ]);
                return this;
            },
            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment.gdw_stripemx.availableTypes[this.getCode()];
            },
            getCcMonths: function () {
                return window.checkoutConfig.payment.gdw_stripemx.months[this.getCode()];
            },
            getCcYears: function () {
                return window.checkoutConfig.payment.gdw_stripemx.years[this.getCode()];
            },
            hasVerification: function () {
                return window.checkoutConfig.payment.gdw_stripemx.hasVerification[this.getCode()];
            },
            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonthsValues: function () {
                this.getCcMonths();
                return _.map(this.getCcMonths(), function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isActive: function () {
                return true;
            },
            isDebug: function () {
                return window.checkoutConfig.payment.gdw_stripemx.isDebug == 1 ? true : false; 
            },
            getCode: function () {
                return 'gdw_stripemx';
            },
            getData: function () {
                var _card = JSON.stringify(stripemx.general.card);
                return {
                    'method': this.item.method,
                    'additional_data': {
                        card: _card,
                        selected_plan: $(stripemx.selectors.selectedplan).val(),
                        payment_intent_id: $(stripemx.selectors.intentid).val()
                    }
                };
            },
            note: function(){
                return window.checkoutConfig.payment.gdw_stripemx.note;
            },
            notemsi: function () {
                return window.checkoutConfig.payment.gdw_stripemx.notemsi;
            },
            checkMSI: function(){
                var params = { totals: quote.totals._latestValue,
                url: urlBuilder.build(window.checkoutConfig.payment.gdw_stripemx.urlcheck),
                data: { email: this.getEmail(), name: $(stripemx.selectors.holdername).val()}}
                stripemx.checkMSI(params);
            },
            cleanCard: function(){
                $(stripemx.selectors.intentid).val('');
                $(stripemx.selectors.holdername).val('');
                $(stripemx.selectors.plans).slideUp();
                $(stripemx.selectors.toolbar).slideUp();
                $(stripemx.selectors.nextbotton).slideDown();
            },
            getEmail: function () {
                return quote.guestEmail ? quote.guestEmail : window.checkoutConfig.customerData.email;
            },
            validate: function(){
                return true;
            },
            placeOrder: function (data, event) {
                var self = this;
                if (event) {event.preventDefault();}
                if (this.validate()){
                    this.getPlaceOrderDeferredObject()
                        .fail(function () {
                            console.log(stripemx.general.card.paymentMethod.card);
                        }).done(function () {
                            self.afterPlaceOrder();
                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        });
                  return true;
                }
              return false;
            }
        })
});