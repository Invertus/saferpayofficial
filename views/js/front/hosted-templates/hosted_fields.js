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

$(document).ready(function () {
    var savedCardMethod = $('input[name="saved_card_method"]');

    if (!savedCardMethod.length) {
        return;
    }

    $('body').on('submit', '[id^=pay-with-][id$=-form] form', function (event) {
        event.preventDefault();

        var isSaferPayMethodSelected = $('[data-module-name*="saferpayofficial"]:checked').length;

        if (!isSaferPayMethodSelected) {
            event.target.submit();
        }

        var selectedCardMethod = $(this).find("[name=saved_card_method]").val();
        var selectedCard = $(this).find("[name=selectedCreditCard_" + selectedCardMethod + "]").val();

        //NOTE: not saved card chosen, continuing with normal procedures.
        if (selectedCard <= 0) {
            event.target.submit();

            return;
        }

        $.ajax(saferpay_official_ajax_url, {
            method: 'POST',
            data: {
                action: 'submitHostedFields',
                paymentMethod: selectedCardMethod,
                selectedCard: selectedCard,
                isBusinessLicence: 1,
                ajax: 1
            },
            success: function (response) {
                var data = jQuery.parseJSON(response);

                window.location = data.url;
            },
        });
    });
});