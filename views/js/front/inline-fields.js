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

    // Each card field is a fieldset whose legend sits in a notch on the top border —
    // the Saferpay payment-page outlined style. The legend/label is our own element
    // (outside the cross-origin iframe), so it can be styled freely; the SDK replaces
    // the placeholder inside with its iframe. The placeholder is a bare div (not a
    // readonly input) so the theme's input styling and browser password-manager icons
    // cannot flash while the SDK loads; its CSS height matches the iframe that replaces
    // it, so the form does not shift when the fields initialise.
    function fieldMarkup(elementId, label, extraClass) {
        return '' +
            '<fieldset class="form-group saferpay-field' + (extraClass ? ' ' + extraClass : '') + '">' +
            '  <legend>' + label + '</legend>' +
            '  <div class="saferpay-field-placeholder" id="' + elementId + '" title="' + label + '"></div>' +
            '</fieldset>';
    }

    // The slot starts in the "loading" state: the field iframes are kept invisible until
    // the SDK reports successful initialisation, because each iframe first paints with the
    // SDK's default input styling and only then applies our injected stylesheet — showing
    // it earlier flashes an unstyled square input inside the outlined field.
    function fieldsFormMarkup() {
        return '' +
            '<div id="' + SLOT_ID + '" class="saferpay-inline-fields saferpay-fields-loading">' +
            '  <div style="display:none" class="alert alert-danger initialize-error" role="alert" aria-live="assertive">' +
            '    <span class="initialize-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger submission-error" role="alert" aria-live="assertive">' +
            '    <span class="submission-error-message"></span>' +
            '  </div>' +
            '  <div style="display:none" class="alert alert-danger internal-error" role="alert" aria-live="assertive">' +
            '    ' + safeInternalError +
            '  </div>' +
            fieldMarkup('fields-holder-name', safeHolderName) +
            fieldMarkup('fields-card-number', fieldLabel('cardnumber')) +
            '  <div class="row">' +
            '    <div class="col-sm-6 col-xs-12">' +
            fieldMarkup('fields-expiration', fieldLabel('expiration')) +
            '    </div>' +
            '    <div class="col-sm-6 col-xs-12">' +
            fieldMarkup('fields-cvc', fieldLabel('cvc')) +
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
            // Visible labels sit in the field border notch (see fieldMarkup), so the inputs
            // themselves stay placeholder-free like Saferpay's own payment page.
            placeholders: {
                holdername: ' ',
                cardnumber: ' ',
                expiration: ' ',
                cvc: ' '
            },
            // The card inputs render inside cross-origin iframes; module CSS cannot reach
            // them, only these rules do. They are passed inline (not via cssUrl) on purpose:
            // a cssUrl stylesheet is fetched through Saferpay's server after the iframes
            // render, briefly flashing the SDK's default input styling; the style object
            // travels with the init config, so the default look never paints.
            //
            // The visible field outline and label are drawn OUTSIDE the iframe, on the
            // fieldset/legend wrapping it (see saferpay_checkout.css), so the inner input
            // stays borderless and transparent, with no horizontal padding (the fieldset
            // provides it). The input sizes to its content via top/bottom padding — an
            // explicit height/line-height fights the SDK's own iframe sizing and pushes the
            // text off-centre. The :focus rule clears the SDK's default focus border/outline
            // so the field does not shrink or gain a stray outline when active — focus
            // feedback is shown on the fieldset outline instead.
            style: {
                '.form-control': 'box-sizing: border-box; width: 100%; margin: 0; padding: 4px 0 12px; border: none; outline: none; background: transparent; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 17px; line-height: normal; color: #1f2426; caret-color: rgb(39, 119, 119);',
                '.form-control:focus': 'border: none; outline: none; box-shadow: none;'
            },
            // Reveal the field iframes only once the SDK reports them fully loaded (inner
            // stylesheet included) — see the loading-state note on fieldsFormMarkup.
            onSuccess: function () {
                $('#' + SLOT_ID).removeClass('saferpay-fields-loading');
            },
            onError: function (evt) {
                $('#' + SLOT_ID).removeClass('saferpay-fields-loading');
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
