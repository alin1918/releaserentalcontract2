/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (placeOrderAction) {
        /** Override default place order action and add signature image and name to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, redirectOnSuccess, messageContainer) {
            var signatureImage = $('#signature_image'),
                signatureName = $('#signature_name');

            paymentData.extension_attributes = {
                signature_image: signatureImage.val(),
                signature_name: signatureName.val()
            };
            return originalAction(paymentData, redirectOnSuccess, messageContainer);
        });
    };
});
