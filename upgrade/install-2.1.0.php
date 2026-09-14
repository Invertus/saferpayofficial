<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_0()
{
    saferpayofficial_2_1_0_delete_removed_tabs();
    saferpayofficial_2_1_0_delete_removed_files();
    saferpayofficial_2_1_0_delete_removed_configuration();

    Tools::clearSmartyCache();

    return true;
}

function saferpayofficial_2_1_0_delete_removed_tabs()
{
    $removedTabs = ['AdminSaferPayOfficialPayment', 'AdminSaferPayOfficialFields'];

    foreach ($removedTabs as $className) {
        $tabId = Tab::getIdFromClassName($className);
        if (!$tabId) {
            continue;
        }

        $tab = new Tab($tabId);
        $tab->delete();
    }
}

/**
 * A ZIP upgrade overwrites files but never removes the ones a new version dropped,
 * so everything deleted in 2.1.0 stays on disk and stays reachable:
 * the leftover admin controller is re-registered as a tab by PrestaShop on the next
 * module reset, and the leftover iframe front controllers keep answering, one of them
 * still reaching the checkout processor with the payment method taken from the request.
 * Both fatal on removed class constants, so they can only be cleaned up from here.
 */
function saferpayofficial_2_1_0_delete_removed_files()
{
    $moduleDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

    $removedFiles = [
        'controllers/admin/AdminSaferPayOfficialFieldsController.php',
        'controllers/front/failIFrame.php',
        'controllers/front/hostedIframe.php',
        'controllers/front/iframe.php',
        'controllers/front/successIFrame.php',
        'src/Entity/index.php',
        'src/Service/SaferPayTerminalService.php',
        'views/css/admin/saferpay_fields.css',
        'views/css/front/hosted-templates/index.php',
        'views/css/front/hosted-templates/template1.css',
        'views/css/front/hosted-templates/template2.css',
        'views/css/front/hosted-templates/template3.css',
        'views/css/front/saferpay_iframe.css',
        'views/img/example-card/credit-card-back-cvc.png',
        'views/img/example-card/credit-card-back.png',
        'views/img/example-card/credit-card-front-card-number.png',
        'views/img/example-card/credit-card-front-expiration.png',
        'views/img/example-card/credit-card-front.png',
        'views/img/example-card/index.php',
        'views/img/hosted-templates/index.php',
        'views/img/hosted-templates/template1.jpg',
        'views/img/hosted-templates/template2.jpg',
        'views/img/hosted-templates/template3.jpg',
        'views/js/front/hosted-templates/template1.js',
        'views/js/front/hosted-templates/template2.js',
        'views/js/front/hosted-templates/template3.js',
        'views/js/front/hosted-templates/template_submit.js',
        'views/js/front/saferpay_iframe.js',
        'views/templates/admin/field-option-settings/helpers/index.php',
        'views/templates/admin/field-option-settings/helpers/options/index.php',
        'views/templates/admin/field-option-settings/helpers/options/options.tpl',
        'views/templates/admin/field-option-settings/index.php',
        'views/templates/admin/partials/field-hosted-field-template-desc.tpl',
        'views/templates/admin/partials/field-terminal-id.tpl',
        'views/templates/front/hosted-templates/index.php',
        'views/templates/front/hosted-templates/partials/all_errors.tpl',
        'views/templates/front/hosted-templates/partials/all_errors_16.tpl',
        'views/templates/front/hosted-templates/partials/index.php',
        'views/templates/front/hosted-templates/partials/initialize_error.tpl',
        'views/templates/front/hosted-templates/partials/internal_error.tpl',
        'views/templates/front/hosted-templates/partials/submission_error.tpl',
        'views/templates/front/hosted-templates/partials/validation_error.tpl',
        'views/templates/front/hosted-templates/template1.tpl',
        'views/templates/front/hosted-templates/template2.tpl',
        'views/templates/front/hosted-templates/template3.tpl',
        'views/templates/front/saferpay_iframe.tpl',
    ];

    $parentDirectories = [];

    foreach ($removedFiles as $removedFile) {
        $path = $moduleDir . str_replace('/', DIRECTORY_SEPARATOR, $removedFile);

        if (!is_file($path)) {
            continue;
        }

        @unlink($path);
        $parentDirectories[dirname($path)] = true;
    }

    $parentDirectories = array_keys($parentDirectories);

    usort($parentDirectories, function ($first, $second) {
        return strlen($second) - strlen($first);
    });

    foreach ($parentDirectories as $parentDirectory) {
        saferpayofficial_2_1_0_delete_empty_directory($parentDirectory, $moduleDir);
    }
}

function saferpayofficial_2_1_0_delete_empty_directory($directory, $moduleDir)
{
    while (strpos($directory, $moduleDir) === 0 && is_dir($directory)) {
        $entries = scandir($directory);

        if ($entries === false || count($entries) > 2) {
            return;
        }

        @rmdir($directory);
        $directory = dirname($directory);
    }
}

/**
 * The API spec version is no longer stored in configuration, it now comes straight from
 * SaferPayConfig::API_VERSION, so the HTTP header and the request body cannot disagree.
 * Upgraded shops still carry the old rows, and the refund one was never migrated past the
 * version it was first written at, so both are removed here to stop them being trusted.
 */
function saferpayofficial_2_1_0_delete_removed_configuration()
{
    Configuration::deleteByName('SAFERPAY_HOSTED_FIELDS_TEMPLATE');
    Configuration::deleteByName('SAFERPAY_SPEC_VERSION');
    Configuration::deleteByName('SAFERPAY_SPEC_REFUND_VERSION');
}
