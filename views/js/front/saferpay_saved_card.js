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

// $(document).ready(function () {
//         var $savedCards = $("input[name^='saved_card_']");
//         $savedCards.on('change', function () {
//             var $selectedCard = $(this);
//             var method = $selectedCard.closest('div.saved_cards').find('.saved_card_method').val();
//             $("input[name='selectedCreditCard_" + method + "']").val($selectedCard.val());
//         });
//     }
// );

$(document).ready(function () {
    $(document).on('change', 'input[name^="saved_card_"]', function () {
        var method = $('[data-module-name*="saferpayofficial"]:checked').closest('div').find('.h6').text().toUpperCase();
        $("input[name='selectedCreditCard_" + method + "']").val(getCheckedCardValue());
    })
});

function getCheckedCardValue() {
    var checkedValue = null;
    $('input[name^="saved_card_"]:checked').each(function() {
        if ($(this).is(':visible')) {
            checkedValue = $(this).val();
        }
    });
    return checkedValue;
}