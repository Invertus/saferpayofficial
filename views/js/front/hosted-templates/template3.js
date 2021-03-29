/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */

var fields_to_validate = [
    "cardnumber",
    "expiration",
    "cvc"
];

$(document).ready(function () {
    SaferpayFields.init({
        apiKey: saferpay_field_access_token,
        url: saferpay_field_url,
        placeholders: {
            cardnumber: '0000 0000 0000 0000',
            expiration: 'MM/YY',
            cvc: '000'
        },
        onError: function (evt) {
            $('.initialize-error-message').text(evt.message);
            $('.initialize-error').show();
        },
        onSuccess: function () {
            var element = document.getElementById("submit");
            element.removeAttribute("disabled");
        },
        onFocus: function (e) {
            var imageContainer = $('.image-container');
            var creditCardContainer = $('.credit-card-container');
            var creditCard = $('#credit-card');
            var frontElements = [
                'cardnumber',
                'expiration'
            ];

            if (frontElements.includes(e.fieldType)) {
                if (imageContainer.hasClass('rotate-to-back')) {
                    imageContainer.removeClass('rotate-to-back');

                    setTimeout(function () {
                        creditCardContainer.removeClass('rotate-element');
                        creditCard.removeClass('cardnumber expiration cvc');
                        creditCard.addClass(e.fieldType);
                    }, 300);
                } else {
                    creditCardContainer.removeClass('rotate-element');
                    creditCard.removeClass('cardnumber expiration cvc');
                    creditCard.addClass(e.fieldType);
                }
            } else {
                imageContainer.addClass('rotate-to-back');

                setTimeout(function () {
                    creditCardContainer.addClass('rotate-element');
                    creditCard.removeClass('cardnumber expiration cvc');
                    creditCard.addClass(e.fieldType);
                }, 300);
            }
        },
        onValidated: function (e) {
            $('.validation-error').show();

            if (e.isValid) {
                $('.error-' + e.fieldType).hide();
            } else {
                $('.error-' + e.fieldType).show();
            }

            var invalidFields = fields_to_validate.filter(function (item) {
                return $('.error-' + item).is(':visible')
            })

            if (invalidFields.length === 0) {
                $('.validation-error').hide();
            }
        }
    });
});