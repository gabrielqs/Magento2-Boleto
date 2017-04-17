/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (
        $,
        Component
        ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Gabrielqs_Boleto/payment/boleto_boleto'
            },

            redirectAfterPlaceOrder: true,

            getBoletoImage: function() {
                return window.checkoutConfig.payment.boleto_boleto.checkout_image;
            }

        });
    }
);
