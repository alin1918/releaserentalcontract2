define([
    'ko',
    'uiComponent',
    'signaturepad',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/renderer-list'

], function (ko, Component, SignaturePad, $, quote, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'sagepaysuiteserver',
            component: 'SalesIgniter_RentalContract/js/model/salesigniter-sagepay'
        }
    );
    var checkoutConfig = window.checkoutConfig;
    var self;
    return Component.extend({
        initialize: function () {
            self = this;
            this._super();
            this.subscribeToPaymentMethod();
        },
        rentalContract: checkoutConfig.rentalContract,
        dateNow: checkoutConfig.dateNow,
        showRentalContract: checkoutConfig.showRentalContract,
        defaults: {
            template: 'SalesIgniter_RentalContract/contract-block'
        },

        subscribeToPaymentMethod: function () {
            quote.paymentMethod.subscribe(function (data) {
                //setTimeout(function () {
                //  self.resizeCanvas();
                //}, 1000);
            });
        },

        createSignature: function () {

            var firstRentalContract = $('#checkout-payment-method-load').find('.rentalContract').first();
            $('#checkout-payment-method-load').find('.rentalContract').not(':eq(0)').remove();
            firstRentalContract.insertBefore($('#checkout-payment-method-load').find('.payment-method._active').find('.checkout-agreements-block'));
            var checkoutButton = $('#checkout-payment-method-load').find('.payment-method._active').find('button[type=submit].checkout');
            var clearButton = $('#checkout-payment-method-load').find('.payment-method._active').find('[data-action=clear]')[0];
            var canvas = $('#checkout-payment-method-load').find('.payment-method._active').find('canvas')[0];
            var signaturePad;
            // Adjust canvas coordinate space taking into account pixel ratio,
            // to make it look crisp on mobile devices.
            // This also causes canvas to be cleared.
            function resizeCanvas() {
                // When zoomed out to less than 100%, for some very strange reason,
                // some browsers report devicePixelRatio as less than 1
                // and only part of the canvas is cleared then.
                var canvas = document.getElementsByClassName('canvas-signature');
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                for (var x in canvas) {
                    var currentCanvas = canvas[x];
                    if (typeof currentCanvas === 'object') {
                        if (currentCanvas.offsetWidth != 0 && currentCanvas.offsetHeight != 0) {
                            currentCanvas.width = currentCanvas.offsetWidth * ratio;
                            currentCanvas.height = currentCanvas.offsetHeight * ratio;
                            currentCanvas.getContext("2d").scale(ratio, ratio);
                        }
                    }
                }
            }

            window.onresize = resizeCanvas;
            resizeCanvas();

            signaturePad = new SignaturePad(canvas);

            clearButton.addEventListener("click", function (event) {
                signaturePad.clear();
            });

            // when stroke is done being drawn, update hidden signature text field with signature image data
            signaturePad.onEnd = function () {
                $('#signature_image').val(signaturePad.toDataURL());
            };
            checkoutButton.on("mousedown", function (event) {
                if (signaturePad.isEmpty()) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    event.stopPropagation();
                    alert($.mage.__('Please provide signature first.'));
                    return false;
                } else if ($("#signature_name").val() == '') {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    event.stopPropagation();
                    alert($.mage.__('Please type in your name below the signature field to confirm agreement.'));
                    return false;
                }
            });
        },

        applySignature: function (element) {
            var self = this;
            if (this.showRentalContract) {
                if ($('input[name="payment[method]"]').length > 1) {
                    $('input[name="payment[method]"]').on('change', function () {
                        self.createSignature();
                    });
                } else {
                    self.createSignature();
                }
            }
        },


    })
        ;
});
