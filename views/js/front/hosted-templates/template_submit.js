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
    document.getElementById('submit').onclick = function () {
        if (!areAllFieldsValid) {
            return;
        }

        SaferpayFields.submit({
            onSuccess: function (evt) {

                $.ajax(saferpay_official_ajax_url, {
                        method: 'POST',
                        data: {
                            action: 'submitHostedFields',
                            paymentMethod: saved_card_method,
                            fieldToken: evt.token,
                            isBusinessLicence: isBusinessLicence,
                            ajax: 1
                        },
                        success: function (response) {
                            if (isJsonString(response)) {
                                window.location = $.parseJSON(response).url;
                            } else {
                                $('.internal-error').show();
                            }
                        }
                });

            },
            onError: function (evt) {
                showSubmissionError(evt.message);
            }
        });
    };
});

function areAllFieldsValid() {
    let invalidFields = fields_to_validate.filter(function (item) {
        return $('.error-' + item).is(':visible')
    })

    return invalidFields.length === 0;
}

function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }

    return true;
}

function showSubmissionError(message) {
    $('.submission-error-message').text(message);
    $('.submission-error').show();
}