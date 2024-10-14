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

var selectedCard = null;

$(document).on('change', 'input[name^="saved_card_"]', function () {
    var method = $('[data-module-name*="saferpayofficial"]:checked').closest('div').find('.h6').text().toUpperCase();
    updateCheckedCardValue();
    $("input[name='selectedCreditCard_" + method + "']").val(selectedCard);
});

$(document).ready(function () {
    let savedCardMethod = $('input[name="saved_card_method"]');

    if (!savedCardMethod.length) {
        return;
    }
});

$('body').on('submit', 'form.payment-form', function (e) {
    handleSubmit(e);
});

function handleSubmit(event) {
    event.preventDefault();

    let selectedCardMethod = $('[data-module-name*="saferpayofficial"]:checked').closest('div').find('.h6').text().toUpperCase();
    let form = $(document).find("[name=selectedCreditCard_" + selectedCardMethod + "]").closest('form');
    let hiddenInput = form.find("input[name='selectedCreditCard_" + selectedCardMethod + "']");

    /*
     *  NOTE:
     *  when user just press payment method
     *  but not touched what to do with card
     */
    if (selectedCard === null) {
        selectedCard = hiddenInput.val();
    }

    hiddenInput.val(selectedCard);

    /*
     * NOTE:
     * not saved card chosen, continuing with normal procedures.
     */
    if (parseInt(selectedCard) <= 0 || selectedCard === null || selectedCard === undefined) {
        event.target.submit();

        return;
    }

    $.ajax(saferpay_official_ajax_url, {
        method: 'POST',
        data: {
            action: 'submitHostedFields',
            paymentMethod: selectedCardMethod,
            selectedCard: parseInt(selectedCard),
            isBusinessLicence: 1,
            ajax: 1
        },
        success: function (response) {
            var data = jQuery.parseJSON(response);

            window.location = data.url;
        },
    });
}

function updateCheckedCardValue() {
    $('input[name^="saved_card_"]:checked').each(function() {
        if ($(this).is(':visible')) {
            selectedCard = $(this).val();
        }
    });
}