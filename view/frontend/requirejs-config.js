/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            signaturepad: 'SalesIgniter_RentalContract/js/signaturepad/signature_pad'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'SalesIgniter_RentalContract/js/model/place-order-mixin': true
            },
            'Magento_Paypal/js/action/set-payment-method': {
                'SalesIgniter_RentalContract/js/model/place-order-mixin-paypal': true
            }
        }
    }
};
