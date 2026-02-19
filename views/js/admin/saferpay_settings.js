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

$(document).ready(function (e) {
    $("input[name='SAFERPAY_CONFIGURATION_NAME']").keypress(function (e) {
        //disable symbols
        var txt = String.fromCharCode(e.which);
        if (!txt.match(/[A-Za-z0-9&. ]/)) {
            return false;
        }
        // disable space
        if (e.keyCode === 32) {
            return false;
        }
    });

    var environments = {
        live: {
            username: 'SAFERPAY_USERNAME',
            password: 'SAFERPAY_PASSWORD',
            customerId: 'SAFERPAY_CUSTOMER_ID'
        },
        test: {
            username: 'SAFERPAY_USERNAME_TEST',
            password: 'SAFERPAY_PASSWORD_TEST',
            customerId: 'SAFERPAY_CUSTOMER_ID_TEST'
        }
    };

    var activeRequests = {};
    var lastFetchedValues = {};

    function fetchTerminals(environment) {
        var fields = environments[environment];
        if (!fields) {
            return;
        }

        var customerId = $('[name="' + fields.customerId + '"]').val();
        var username = $('[name="' + fields.username + '"]').val();
        var password = $('[name="' + fields.password + '"]').val();

        if (!customerId || !username || !password) {
            return;
        }

        var valuesKey = environment + ':' + customerId + ':' + username + ':' + password;
        if (lastFetchedValues[environment] === valuesKey) {
            return;
        }
        lastFetchedValues[environment] = valuesKey;

        var $container = $('.saferpay-terminal-selector[data-environment="' + environment + '"]');
        var $select = $container.find('.saferpay-terminal-select');
        var $loading = $container.find('.saferpay-terminal-loading');
        var currentValue = $select.val();

        if (activeRequests[environment]) {
            activeRequests[environment].abort();
        }

        $loading.show();

        activeRequests[environment] = $.ajax({
            url: saferpayofficial_settings.settingsUrl,
            type: 'POST',
            data: {
                ajax: true,
                action: 'getTerminals',
                environment: environment,
                customerId: customerId,
                username: username,
                password: password
            },
            success: function (response) {
                var data;
                try {
                    data = typeof response === 'string' ? JSON.parse(response) : response;
                } catch (parseError) {
                    $container.find('.saferpay-terminal-help').text('Invalid response from server.');
                    return;
                }

                if (data.error) {
                    $container.find('.saferpay-terminal-help').text(data.message || 'Failed to load terminals.');
                    return;
                }

                $select.find('option:not(:first)').remove();

                if (data.terminals && data.terminals.length > 0) {
                    $.each(data.terminals, function (i, terminal) {
                        var $option = $('<option></option>')
                            .val(terminal.TerminalId)
                            .text(terminal.TerminalId + ' - ' + terminal.Description);

                        if (terminal.TerminalId === currentValue) {
                            $option.prop('selected', true);
                        }

                        $select.append($option);
                    });

                    $select.prop('disabled', false);
                    $container.find('.saferpay-terminal-help').text('Select a terminal from the list');
                } else {
                    $select.prop('disabled', true);
                    $container.find('.saferpay-terminal-help').text('No terminals found for the given credentials.');
                }
            },
            error: function (jqXHR, textStatus) {
                if (textStatus !== 'abort') {
                    lastFetchedValues[environment] = null;
                    $container.find('.saferpay-terminal-help').text('Failed to load terminals. Please check your credentials.');
                }
            },
            complete: function () {
                activeRequests[environment] = null;
                $loading.hide();
            }
        });
    }

    $.each(environments, function (environment, fields) {
        $('[name="' + fields.username + '"], [name="' + fields.password + '"], [name="' + fields.customerId + '"]')
            .on('change', function () {
                fetchTerminals(environment);
            });
    });
});
