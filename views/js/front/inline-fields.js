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
 * fields-card-number, fields-expiration, fields-cvc), offers no way to scope to a
 * container and no teardown. Moving an initialised field iframe in the DOM reloads it and
 * breaks the binding. To show the form directly under whichever Saferpay card option is
 * selected, we therefore render a fresh form (plain readonly-input placeholders) INTO the
 * selected option's container and (re-)initialise the SDK on it. Selecting a different card
 * option rebuilds the form there and re-initialises — re-init is supported as long as the
 * placeholders are fresh DIV/SPAN/readonly-input elements, not the iframes of a prior init.
 */
(function () {
    if (typeof saferpay_field_access_token === 'undefined' || !saferpay_field_access_token) {
        return;
    }

    var SLOT_ID = 'saferpay-inline-fields';
    var safeHolderName = typeof holder_name !== 'undefined' ? holder_name : 'Holder name';
    var safeInternalError = typeof saferpay_internal_error !== 'undefined'
        ? saferpay_internal_error
        : 'An error occurred, please try again.';

    // The container id the form is currently rendered into, to avoid a redundant
    // rebuild+re-init when the same option fires a spurious change event.
    var renderedContainerId = null;

    // The customer-entered card inputs live in cross-origin Saferpay iframes, so their
    // validity is only known through the SDK's onValidated callback. It fires when a field
    // loses focus; an untouched field never fires it. We therefore default every required
    // field to invalid so an all-empty form is correctly blocked on submit.
    var REQUIRED_FIELDS = ['holdername', 'cardnumber', 'expiration', 'cvc'];
    var fieldValidity = {};

    // The Saferpay Fields SDK binds to fixed element IDs. Map each field type to its element
    // so we can highlight the matching form-group on validation.
    var FIELD_ELEMENT_IDS = {
        holdername: 'fields-holder-name',
        cardnumber: 'fields-card-number',
        expiration: 'fields-expiration',
        cvc: 'fields-cvc'
    };

    function resetValidity() {
        fieldValidity = {};
        REQUIRED_FIELDS.forEach(function (fieldType) {
            fieldValidity[fieldType] = false;
        });
    }

    // Toggle a state class on the form-group wrapping a given field's iframe.
    function toggleFieldClass(fieldType, className, on) {
        var elementId = FIELD_ELEMENT_IDS[fieldType];
        if (elementId) {
            $('#' + elementId).closest('.form-group').toggleClass(className, on);
        }
    }

    function fieldLabel(fieldType) {
        var labels = {
            holdername: safeHolderName,
            cardnumber: typeof saferpay_field_label_cardnumber !== 'undefined' ? saferpay_field_label_cardnumber : 'Card number',
            expiration: typeof saferpay_field_label_expiration !== 'undefined' ? saferpay_field_label_expiration : 'Expiry date',
            cvc: typeof saferpay_field_label_cvc !== 'undefined' ? saferpay_field_label_cvc : 'CVC'
        };
        return labels[fieldType] || fieldType;
    }

    function fieldsFormMarkup() {
        return '' +
            '<div id="' + SLOT_ID + '" class="saferpay-inline-fields">' +
            '  <div style="display:none" class="alert alert-danger initialize-error" role="alert" aria-live="assertive">' +
            '    <span class="initialize-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger submission-error" role="alert" aria-live="assertive">' +
            '    <span class="submission-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger internal-error" role="alert" aria-live="assertive">' +
            '    ' + safeInternalError +
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

    function removeSlot() {
        $('#' + SLOT_ID).remove();
        renderedContainerId = null;
    }

    // Render a fresh Fields form into the selected option's container and initialise the SDK
    // on it. Rebuilding fresh readonly-input placeholders each time keeps re-initialisation
    // valid when the customer switches between card options.
    function renderInto($container) {
        var containerId = $container.attr('id');
        if (renderedContainerId === containerId && $('#' + SLOT_ID).length) {
            return;
        }

        removeSlot();
        resetValidity();

        $container.append(fieldsFormMarkup());
        renderedContainerId = containerId;

        SaferpayFields.init({
            accessToken: saferpay_field_access_token,
            url: saferpay_field_url,
            placeholders: {
                holdername: safeHolderName,
                cardnumber: '0000 0000 0000 0000',
                expiration: 'MM/YYYY',
                cvc: '000'
            },
            // The card inputs render inside cross-origin iframes; module CSS cannot reach
            // them. Drop the SDK's default per-field input border (the single visible border
            // is drawn on the iframe by saferpay_checkout.css), normalise the height so all
            // four fields match, and vertically centre the text via line-height. The :focus
            // rule repeats the sizing and clears the SDK's focus border/outline so the field
            // does not shrink or gain a stray outline when active — focus feedback is instead
            // shown on the iframe border via the onFocus/onBlur handlers below.
            // Let the input size to its content and centre the text via equal top/bottom
            // padding (native single-line centering). Forcing an explicit height/line-height
            // fought the SDK's own iframe sizing and pushed the text off-centre.
            style: {
                '.form-control': 'box-sizing: border-box; width: 100%; margin: 0; padding: 8px 12px; line-height: normal; font-size: 14px; border: none; background: transparent;',
                '.form-control:focus': 'border: none; outline: none; box-shadow: none;'
            },
            onError: function (evt) {
                $('#' + SLOT_ID + ' .initialize-error-message').text(evt.message);
                $('#' + SLOT_ID + ' .initialize-error').show();
            },
            onValidated: function (evt) {
                if (!evt || typeof evt.fieldType === 'undefined') {
                    return;
                }
                if (fieldValidity.hasOwnProperty(evt.fieldType)) {
                    fieldValidity[evt.fieldType] = !!evt.isValid;
                }
                toggleFieldClass(evt.fieldType, 'has-error', !evt.isValid);
            },
            onFocus: function (evt) {
                if (evt) {
                    toggleFieldClass(evt.fieldType, 'is-focused', true);
                }
            },
            onBlur: function (evt) {
                if (evt) {
                    toggleFieldClass(evt.fieldType, 'is-focused', false);
                }
            }
        });
    }

    function showSubmissionError(message) {
        $('#' + SLOT_ID + ' .submission-error-message').text(message);
        $('#' + SLOT_ID + ' .submission-error').show();
    }

    function hideSubmissionError() {
        $('#' + SLOT_ID + ' .submission-error').hide();
    }

    // PrestaShop's theme adds a "disabled" class to the place-order button when it is
    // clicked (to guard against double submits). When we abort the submit — because a field
    // is invalid or the SDK rejects it — nothing re-enables the button, so the customer is
    // stuck. Restore it here so they can retry after fixing the fields.
    function reEnablePlaceOrder() {
        $('#payment-confirmation button[type="submit"]').removeClass('disabled').removeAttr('disabled');
    }

    // Returns the list of required fields that are not currently valid, marking each as
    // invalid so the user sees which ones need attention.
    function invalidFieldLabels() {
        var labels = [];
        REQUIRED_FIELDS.forEach(function (fieldType) {
            if (!fieldValidity[fieldType]) {
                labels.push(fieldLabel(fieldType));
                toggleFieldClass(fieldType, 'has-error', true);
            }
        });
        return labels;
    }

    function incompleteFieldsMessage(labels) {
        var prefix = typeof saferpay_fields_incomplete_error !== 'undefined'
            ? saferpay_fields_incomplete_error
            : 'Please check the following:';
        return prefix + ' ' + labels.join(', ');
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
                                reEnablePlaceOrder();
                            }
                        } catch (e) {
                            $('#' + SLOT_ID + ' .internal-error').show();
                            reEnablePlaceOrder();
                        }
                    },
                    error: function () {
                        $('#' + SLOT_ID + ' .internal-error').show();
                        reEnablePlaceOrder();
                    }
                });
            },
            onError: function (evt) {
                // The SDK's raw message (e.g. "cannot store data, because fields contains
                // invalid or missing data") is not actionable. Surface the same clear,
                // field-specific message we use for pre-submit gating instead.
                if (evt && evt.message) {
                    console.warn('Saferpay Fields submit error: ' + evt.message);
                }
                var labels = invalidFieldLabels();
                showSubmissionError(labels.length ? incompleteFieldsMessage(labels) : safeInternalError);
                reEnablePlaceOrder();
            }
        });
    }

    $(document).ready(function () {
        if (!$('[name="saferpayPaymentType"]').length) {
            return;
        }

        // Render / remove the form as payment options are selected. Rendering into the
        // selected option's own container places the fields directly under it.
        $('body').on('change', 'input[name="payment-option"]', function () {
            var $option = $('#pay-with-' + $(this).attr('id') + '-form');
            var $form = $option.find('form').first();

            if ($form.length && isInlineFieldsOption($form) && selectedCardValue($form) <= 0) {
                renderInto($option);
            } else {
                removeSlot();
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

            hideSubmissionError();

            // Block submission while any required card field is empty/invalid and tell the
            // customer exactly which ones, instead of letting the SDK fail with a cryptic
            // "cannot store data" message.
            var labels = invalidFieldLabels();
            if (labels.length) {
                showSubmissionError(incompleteFieldsMessage(labels));
                reEnablePlaceOrder();
                return;
            }

            submitFields($form);
        });
    });
})();
