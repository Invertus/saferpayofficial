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
    var $configInput = $("input[name='SAFERPAY_CONFIGURATION_NAME']");

    $configInput.attr('maxlength', 20);

    $configInput.keypress(function (e) {
        var txt = String.fromCharCode(e.which);
        if (!txt.match(/[A-Za-z0-9.:\-_]/)) {
            return false;
        }
    });

    $configInput.on('paste', function (e) {
        var $input = $(this);
        setTimeout(function () {
            var cleaned = $input.val().replace(/[^A-Za-z0-9.:\-_]/g, '').substring(0, 20);
            $input.val(cleaned);
        }, 0);
    });
});