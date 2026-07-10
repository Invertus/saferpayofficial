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

/**
 * Renders the Saferpay Fields card form inline in the default PrestaShop checkout.
 *
 * The Saferpay Fields SDK binds to four fixed element IDs (fields-holder-name,
 * fields-card-number, fields-expiration, fields-cvc) and offers no way to scope to a
 * container, no re-init and no teardown. Therefore exactly ONE Fields form may exist on
 * the page. We render one shared form, initialise it once, and reveal it under whichever
 * Saferpay card option is currently selected. Only the submitted payment-method name
 * differs between card options; the card inputs themselves are brand-agnostic.
 */
(function () {
    if (typeof saferpay_field_access_token === 'undefined' || !saferpay_field_access_token) {
        return;
    }

    var SLOT_ID = 'saferpay-inline-fields';
    var initialised = false;
    var safeHolderName = typeof holder_name !== 'undefined' ? holder_name : 'Holder name';

    function fieldsFormMarkup() {
        return '' +
            '<div id="' + SLOT_ID + '" class="saferpay-inline-fields" style="display:none">' +
            '  <div style="display:none" class="alert alert-danger initialize-error" role="alert" aria-live="assertive">' +
            '    <span class="initialize-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger submission-error" role="alert" aria-live="assertive">' +
            '    <span class="submission-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger internal-error" role="alert" aria-live="assertive">' +
            '    ' + (typeof saferpay_internal_error !== 'undefined' ? saferpay_internal_error : 'An error occurred, please try again.') +
            '  </div>' +
            '  <div class="form-group">' +
            '    <label for="fields-holder-name" class="sr-only">' + safeHolderName + '</label>' +
            '    <input class="form-control" id="fields-holder-name" readonly placeholder="' + safeHolderName + '" aria-label="' + safeHolderName + '">' +
            '  </div>' +
            '  <div class="form-group">' +
            '    <label for="fields-card-number" class="sr-only">Card number</label>' +
            '    <input class="form-control" id="fields-card-number" readonly placeholder="0000 0000 0000 0000" aria-label="Card number">' +
            '  </div>' +
            '  <div class="row">' +
            '    <div class="col-sm-6 col-xs-12 form-group">' +
            '      <label for="fields-expiration" class="sr-only">Expiration date</label>' +
            '      <input class="form-control" id="fields-expiration" readonly placeholder="MM/YYYY" aria-label="Expiration date">' +
            '    </div>' +
            '    <div class="col-sm-6 col-xs-12 form-group">' +
            '      <label for="fields-cvc" class="sr-only">CVC code</label>' +
            '      <input class="form-control" id="fields-cvc" readonly placeholder="000" aria-label="CVC code">' +
            '    </div>' +
            '  </div>' +
            '  <input id="token" readonly type="hidden" />' +
            '</div>';
    }

    // A Saferpay card option that renders inline Fields (Custom Form ON). These carry the
    // hidden saferpayPaymentType input set to the hosted_iframe value.
    function isInlineFieldsOption($form) {
        var type = $form.find('[name="saferpayPaymentType"]').val();
        return typeof saferpay_payment_types !== 'undefined'
            && type === saferpay_payment_types.hosted_iframe;
    }

    function selectedCardValue($form) {
        var method = $form.find('[name="saved_card_method"]').val();
        return parseInt($form.find('[name="selectedCreditCard_' + method + '"]').val(), 10) || 0;
    }

    function ensureInitialised() {
        if (initialised) {
            return;
        }
        initialised = true;

        SaferpayFields.init({
            accessToken: saferpay_field_access_token,
            url: saferpay_field_url,
            placeholders: {
                holdername: safeHolderName,
                cardnumber: '0000 0000 0000 0000',
                expiration: 'MM/YYYY',
                cvc: '000'
            },
            onError: function (evt) {
                $('#' + SLOT_ID + ' .initialize-error-message').text(evt.message);
                $('#' + SLOT_ID + ' .initialize-error').show();
            },
            onValidated: function () { /* validation styling handled by field CSS */ }
        });
    }

    // Reveal the shared form. It is placed ONCE in a stable location (right below the
    // payment-options list) and only shown/hidden afterwards. It must never be moved in the
    // DOM after init: reparenting an iframe reloads it, which would tear down the Saferpay
    // Fields SDK (which offers no re-init). It therefore sits just beneath whichever card
    // option is selected/expanded, serving them all.
    function revealUnder() {
        var $slot = $('#' + SLOT_ID);
        if (!$slot.data('placed')) {
            var $anchor = $('.payment-options').first();
            if ($anchor.length) {
                $slot.insertAfter($anchor);
            }
            $slot.data('placed', true);
        }
        $slot.show();
        ensureInitialised();
    }

    function hide() {
        $('#' + SLOT_ID).hide();
    }

    function submitFields($form) {
        SaferpayFields.submit({
            onSuccess: function (evt) {
                $.ajax(saferpay_official_ajax_url, {
                    method: 'POST',
                    data: {
                        action: 'submitHostedFields',
                        paymentMethod: $form.find('[name="saved_card_method"]').val(),
                        selectedCard: 0,
                        fieldToken: evt.token,
                        isBusinessLicence: 1,
                        ajax: 1
                    },
                    success: function (response) {
                        try {
                            // jQuery may already have parsed a JSON response into an object.
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data && data.url) {
                                window.location = data.url;
                            } else {
                                $('#' + SLOT_ID + ' .internal-error').show();
                            }
                        } catch (e) {
                            $('#' + SLOT_ID + ' .internal-error').show();
                        }
                    }
                });
            },
            onError: function (evt) {
                $('#' + SLOT_ID + ' .submission-error-message').text(evt.message);
                $('#' + SLOT_ID + ' .submission-error').show();
            }
        });
    }

    $(document).ready(function () {
        if (!$('[name="saferpayPaymentType"]').length) {
            return;
        }

        $('body').append(fieldsFormMarkup());

        // Reveal / hide the shared form as payment options are selected.
        $('body').on('change', 'input[name="payment-option"]', function () {
            var $option = $('#pay-with-' + $(this).attr('id') + '-form');
            var $form = $option.find('form').first();

            if ($form.length && isInlineFieldsOption($form) && selectedCardValue($form) <= 0) {
                revealUnder();
            } else {
                hide();
            }
        });

        // Handle a payment option that is already selected on load (e.g. single option or
        // themes that pre-select) — the change event would not fire on its own.
        $('input[name="payment-option"]:checked').trigger('change');

        // Intercept the place-order submit for inline Fields (new card) options.
        $('body').on('submit', '[id^=pay-with-][id$=-form] form', function (event) {
            var $form = $(this);

            if (!isInlineFieldsOption($form) || selectedCardValue($form) > 0) {
                return; // saved-card & non-Fields flows are handled elsewhere
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            submitFields($form);
        });
    });
})();
