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
    $('.log-modal-overlay').on('click', function (event) {
        $('.modal.open').removeClass('open');
        event.preventDefault();
    });

    $('.js-log-button').on('click', function (event) {
        var logId = $(this).data('log-id');
        var informationType = $(this).data('information-type');

        // NOTE: opening modal
        $('#' + $(this).data('target')).addClass('open');

        // NOTE: if information has been set already we don't need to call ajax again.
        if (!$('#log-modal-' + logId + '-' + informationType + ' .log-modal-content-data').hasClass('hidden')) {
            return;
        }

        $('.log-modal-content-spinner').removeClass('hidden');

        $.ajax({
            type: 'POST',
            url: saferpayofficial.logsUrl,
            data: {
                ajax: true,
                action: 'getLog',
                log_id: logId
            }
        })
            .then(response => jQuery.parseJSON(response))
            .then(data => {
                $('.log-modal-content-spinner').addClass('hidden')

                $('#log-modal-' + logId + '-request .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.request));
                $('#log-modal-' + logId + '-response .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.response));
                $('#log-modal-' + logId + '-context .log-modal-content-data').removeClass('hidden').html(prettyJson(data.log.context));
            })
    });
});

function prettyJson(json) {
    return JSON.stringify(JSON.parse(json), null, 2)
}
