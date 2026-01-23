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
    // #region agent log
    console.log('[DEBUG] saferpay_settings.js loaded');
    // #endregion
    
    $("input[name='SAFERPAY_CONFIGURATION_NAME']").keypress(function (e) {
        var txt = String.fromCharCode(e.which);
        if (!txt.match(/[A-Za-z0-9&. ]/)) {
            return false;
        }
        if (e.keyCode === 32) {
            return false;
        }
    });

    var terminalFetchTimeout = null;

    $('.saferpay-terminal-selector').each(function() {
        // #region agent log
        console.log('[DEBUG] Found terminal selector, count:', $('.saferpay-terminal-selector').length);
        // #endregion
        
        var $container = $(this);
        var environment = $container.data('environment');
        var $select = $container.find('.saferpay-terminal-select');
        var $helpBlock = $container.find('.help-block');
        var $manualInput = $container.find('.saferpay-terminal-manual-input');

        var getFieldValue = function(fieldName) {
            var $field = $('input[name="' + fieldName + '"], select[name="' + fieldName + '"]');
            // #region agent log
            console.log('[DEBUG] getFieldValue:', fieldName, 'found:', $field.length > 0, 'value:', $field.length ? ($field.attr('type') === 'password' ? '***' : $field.val()) : 'not found');
            // #endregion
            if ($field.length) {
                if ($field.attr('type') === 'password') {
                    return $field.val() || '';
                }
                return $field.val() || '';
            }
            return '';
        };

        var getFieldName = function(baseName) {
            return environment === 'test' ? baseName + '_TEST' : baseName;
        };

        var lastCredentials = {
            customerId: getFieldValue(getFieldName('SAFERPAY_CUSTOMER_ID')),
            username: getFieldValue(getFieldName('SAFERPAY_USERNAME')),
            password: getFieldValue(getFieldName('SAFERPAY_PASSWORD'))
        };

        var fetchTerminals = function(force) {
            var customerId = getFieldValue(getFieldName('SAFERPAY_CUSTOMER_ID'));
            var username = getFieldValue(getFieldName('SAFERPAY_USERNAME'));
            var password = getFieldValue(getFieldName('SAFERPAY_PASSWORD'));

            var credentialsChanged = 
                customerId !== lastCredentials.customerId ||
                username !== lastCredentials.username ||
                password !== lastCredentials.password;

            if (!force && !credentialsChanged && $select.find('option').length > 1) {
                return;
            }

            // Update last credentials
            lastCredentials = {
                customerId: customerId,
                username: username,
                password: password
            };

            if (!customerId || !username || !password) {
                $select.prop('disabled', true).html('<option value="">-- Select Terminal --</option>');
                $helpBlock.text($helpBlock.data('empty-text') || 'Please configure Customer ID, Username, and Password to load terminals');
                if ($manualInput.length) {
                    $manualInput.show();
                }
                return;
            }

            $select.prop('disabled', true);
            $select.html('<option value="">Loading terminals...</option>');
            $helpBlock.html('<i class="icon-spinner icon-spin"></i> Loading terminals...');

            var ajaxUrl = window.location.href;

            // #region agent log
            console.log('[DEBUG] AJAX URL:', ajaxUrl, 'Window location:', window.location.href);
            // #endregion
            
            var params = {
                ajax: 1,
                action: 'getTerminals',
                environment: environment,
                customer_id: customerId,
                username: username,
                password: password
            };

            // #region agent log
            console.log('[DEBUG] Making AJAX request with params:', Object.assign({}, params, {password: '***'}));
            // #endregion

            alert('Customer ID before AJAX: ' + customerId); // Temporary debug alert

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: params,
                dataType: 'json',
                success: function(response) {
                    // #region agent log
                    console.log('[DEBUG] AJAX success response:', {success: response.success, terminalsCount: response.terminals ? response.terminals.length : 0, error: response.error});
                    // #endregion
                    if (response.success && response.terminals && response.terminals.length > 0) {
                        var options = '<option value="">-- Select Terminal --</option>';
                        var currentValue = $select.data('current-value') || '';

                        $.each(response.terminals, function(index, terminal) {
                            var selected = terminal.TerminalId === currentValue ? ' selected' : '';
                            var label = terminal.TerminalId + (terminal.Description ? ' - ' + terminal.Description : '');
                            options += '<option value="' + terminal.TerminalId + '"' + selected + '>' + label + '</option>';
                        });

                        $select.html(options).prop('disabled', false);

                        if (response.count === 1 && !$select.val()) {
                            $select.val(response.terminals[0].TerminalId).trigger('change');
                        }

                        $helpBlock.text('Select a terminal from the list');
                        if ($manualInput.length) {
                            $manualInput.hide();
                            $manualInput.val('');
                        }
                    } else {
                        $select.html('<option value="">-- Select Terminal --</option>').prop('disabled', false);
                        var errorMsg = response.error || 'No terminals found. Please check your credentials.';
                        $helpBlock.html('<span class="text-danger">' + errorMsg + '</span>');
                        if ($manualInput.length) {
                            $manualInput.show();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // #region agent log
                    console.error('[DEBUG] AJAX Error:', {status: xhr.status, statusText: xhr.statusText, responseText: xhr.responseText ? xhr.responseText.substring(0, 200) : 'empty', error: error});
                    // #endregion
                    $select.html('<option value="">-- Select Terminal --</option>').prop('disabled', false);
                    $helpBlock.html('<span class="text-danger">Failed to load terminals. You can enter the Terminal ID manually.</span>');
                    if ($manualInput.length) {
                        $manualInput.show();
                    }
                }
            });
        };

        var debouncedFetch = function() {
            clearTimeout(terminalFetchTimeout);
            terminalFetchTimeout = setTimeout(fetchTerminals, 500);
        };

        var fieldNames = [
            getFieldName('SAFERPAY_CUSTOMER_ID'),
            getFieldName('SAFERPAY_USERNAME'),
            getFieldName('SAFERPAY_PASSWORD')
        ];

        fieldNames.forEach(function(fieldName) {
            $('input[name="' + fieldName + '"], select[name="' + fieldName + '"]').on('input change blur', debouncedFetch);
        });

        $select.on('change', function() {
            if ($(this).val()) {
                $manualInput.val('');
            }
        });

        $manualInput.on('input', function() {
            if ($(this).val()) {
                $select.val('');
            }
        });

        $manualInput.on('blur', function() {
            if ($(this).val() && !$select.val()) {
                var manualValue = $(this).val();
                if (!$select.find('option[value="' + manualValue + '"]').length) {
                    $select.append('<option value="' + manualValue + '" selected>' + manualValue + '</option>');
                } else {
                    $select.val(manualValue);
                }
                $(this).val('');
            }
        });

        $container.find('.saferpay-refresh-terminals').on('click', function() {
            fetchTerminals(true);
        });

        $('form').on('submit', function() {
            if ($manualInput.val() && !$select.val()) {
                var manualValue = $(this).val();
                if (!$select.find('option[value="' + manualValue + '"]').length) {
                    $select.append('<option value="' + manualValue + '" selected>' + manualValue + '</option>');
                } else {
                    $select.val(manualValue);
                }
            }
        });

        if (getFieldValue(getFieldName('SAFERPAY_CUSTOMER_ID')) && 
            getFieldValue(getFieldName('SAFERPAY_USERNAME')) && 
            getFieldValue(getFieldName('SAFERPAY_PASSWORD'))) {
            if ($select.find('option').length <= 1) {
                fetchTerminals();
            }
        }
    });
});
