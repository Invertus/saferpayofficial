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

namespace Invertus\SaferPay\Presentation\Loader;

use Configuration;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Order;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Enum\PaymentType;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Provider\OpcModulesProvider;
use Media;
use OrderControllerCore;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentFormAssetLoader
{
    /** @var SaferPayOfficial */
    private $module;
    /** @var LegacyContext */
    private $context;
    /** @var OpcModulesProvider $opcModuleProvider */
    private $opcModulesProvider;

    public function __construct(ModuleFactory $module, LegacyContext $context, OpcModulesProvider $opcModulesProvider)
    {
        $this->module = $module->getModule();
        $this->context = $context;
        $this->opcModulesProvider = $opcModulesProvider;
    }

    public function register($controller)
    {
        Media::addJsDef([
            'saferpay_official_ajax_url' => $this->context->getLink()->getModuleLink('saferpayofficial', ControllerName::AJAX),
            'saferpay_payment_types' => [
                'hosted_iframe' => PaymentType::HOSTED_IFRAME,
                'iframe' => PaymentType::IFRAME,
                'basic' => PaymentType::BASIC,
            ],
        ]);

        $opcModule = $this->opcModulesProvider->get();

        switch ($opcModule) {
            case SaferPayConfig::ONE_PAGE_CHECKOUT_MODULE:
                $this->registerOnePageCheckoutAssets($controller);
                break;
            case SaferPayConfig::THE_CHECKOUT_MODULE:
                $this->registerTheCheckoutAssets($controller);
                break;
            case SaferPayConfig::SUPER_CHECKOUT_MODULE:
                $this->registerSuperCheckoutAssets($controller);
                break;
            default:
                $this->registerDefaultCheckoutAssets($controller);
        }
    }

    private function registerOnePageCheckoutAssets($controller)
    {
        if (!$controller instanceof \OrderControllerCore) {
            return;
        }

        $controller->addCSS("{$this->module->getPathUri()}views/css/front/saferpay_checkout.css");

        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'saved_card_hosted_fields_opc',
                "modules/saferpayofficial/views/js/front/opc/onepagecheckoutps/hosted_fields.js"
            );
        } else {
            $controller->addJs(
                $this->module->getPathUri() . 'views/js/front/opc/onepagecheckoutps/hosted_fields.js',
                false
            );
        }
    }

    private function registerTheCheckoutAssets($controller)
    {
        if (!$controller instanceof \TheCheckoutModuleFrontController) {
            return;
        }

        $controller->addCSS("{$this->module->getPathUri()}views/css/front/saferpay_checkout.css");

        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'saved_card_hosted_fields_opc',
                "modules/saferpayofficial/views/js/front/opc/thecheckout/hosted_fields.js"
            );
        } else {
            $controller->addJs(
                $this->module->getPathUri() . 'views/js/front/opc/thecheckout/hosted_fields.js',
                false
            );
        }
    }

    private function registerSuperCheckoutAssets($controller)
    {
        if (!$controller instanceof \SupercheckoutSupercheckoutModuleFrontController) {
            return;
        }

        $controller->addCSS("{$this->module->getPathUri()}views/css/front/saferpay_checkout.css");

        if (method_exists($controller, 'registerJavascript')) {
            $controller->registerJavascript(
                'saved_card_hosted_fields_opc',
                "modules/saferpayofficial/views/js/front/opc/supercheckout/hosted_fields.js"
            );
        } else {
            $controller->addJs(
                $this->module->getPathUri() . 'views/js/front/opc/supercheckout/hosted_fields.js',
                false
            );
        }
    }

    private function registerDefaultCheckoutAssets($controller)
    {
        if (!$controller instanceof OrderControllerCore) {
            return;
        }

        if (method_exists($controller, 'registerJavascript')) {
            if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
                $controller->registerJavascript(
                    'saved_card_hosted_fields',
                    "modules/saferpayofficial/views/js/front/hosted-templates/hosted_fields.js"
                );

                $controller->registerJavascript(
                    'saved-card',
                    'modules/' . $this->module->name . '/views/js/front/saferpay_saved_card.js'
                );

                $controller->registerStylesheet("",
                    "{$this->module->getPathUri()}views/css/front/saferpay_checkout.css");
            } else {
                $controller->registerJavascript(
                    'saved_card_hosted_fields',
                    "modules/saferpayofficial/views/js/front/hosted-templates/hosted_fields_16.js"
                );

                $controller->addCSS("{$this->module->getPathUri()}views/css/front/saferpay_checkout_16.css");
                $controller->addJS("{$this->module->getPathUri()}views/js/front/saferpay_saved_card_16.js");
                $fieldsLibrary = \Invertus\SaferPay\Config\SaferPayConfig::FIELDS_LIBRARY;
                $configSuffix = \Invertus\SaferPay\Config\SaferPayConfig::getConfigSuffix();
                $controller->addJs(Configuration::get($fieldsLibrary . $configSuffix));
            }
        } else {
            if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
                $controller->addJs(
                    $this->module->getPathUri() . 'views/js/front/hosted-templates/hosted_fields.js',
                    false
                );
            } else {
                $controller->addJs(
                    $this->module->getPathUri() . 'views/js/front/hosted-templates/hosted_fields_16.js',
                    false
                );
            }
        }
    }

    public function registerErrorBags()
    {
        /** @var \Invertus\SaferPay\Service\SaferPayErrorDisplayService $errorDisplayService */
        $errorDisplayService = $this->module->getService(\Invertus\SaferPay\Service\SaferPayErrorDisplayService::class);

        $errorDisplayService->showCookieError('saferpay_payment_canceled_error');
    }
}
