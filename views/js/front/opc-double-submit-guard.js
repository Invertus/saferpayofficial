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

// A one-page checkout can drop its own double-submit guard as soon as it starts the
// native form submit, so its pay button goes live again while the browser is still
// navigating to our validation controller. A second click then submits against a cart
// validateOrder() has already converted, which strands the order in awaiting payment.
(function () {
    'use strict';

    var PAY_BUTTON = '#opc-pay-button';
    var SUBMIT_STARTED_EVENT = 'opcFinalSubmitStarted';

    function isSaferPaySelected() {
        return document.querySelector('[data-module-name*="saferpayofficial"]:checked') !== null;
    }

    function swallowPayClick(event) {
        if (event.target instanceof Element && event.target.closest(PAY_BUTTON)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }

    function lock(button) {
        button.disabled = true;
        document.addEventListener('click', swallowPayClick, true);

        // The checkout re-enables the button from its own validation pass, so hold it down.
        new MutationObserver(function () {
            if (!button.disabled) {
                button.disabled = true;
            }
        }).observe(button, { attributes: true, attributeFilter: ['disabled'] });
    }

    function onSubmitStarted() {
        var button = document.querySelector(PAY_BUTTON);

        if (!button || !isSaferPaySelected()) {
            return;
        }

        lock(button);
    }

    function subscribe() {
        if (typeof prestashop === 'undefined' || typeof prestashop.on !== 'function') {
            return;
        }

        prestashop.on(SUBMIT_STARTED_EVENT, onSubmitStarted);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', subscribe);
    } else {
        subscribe();
    }
})();
