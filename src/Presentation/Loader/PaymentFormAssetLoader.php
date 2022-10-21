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

use FrontController;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Enum\PaymentType;
use Media;
use OrderControllerCore;
use SaferPayOfficial;
use Context;

class PaymentFormAssetLoader
{
    /** @var SaferPayOfficial */
    private $module;
    /** @var Context */
    private $context;

    public function __construct(SaferPayOfficial $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    public function register($controller)
    {
        if (!$controller instanceof OrderControllerCore) {
            return;
        }

        Media::addJsDef([
            'saferpay_official_ajax_url' => $this->context->link->getModuleLink('saferpayofficial', ControllerName::AJAX),
            'saferpay_payment_types' => [
                'hosted_iframe' => PaymentType::HOSTED_IFRAME,
                'iframe' => PaymentType::IFRAME,
                'basic' => PaymentType::BASIC,
            ]
        ]);

        if (method_exists($controller, 'registerJavascript')) {
            if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
                $controller->registerJavascript(
                    'saved_card_hosted_fields',
                    "modules/saferpayofficial/views/js/front/hosted-templates/hosted_fields.js"
                );
            } else {
                $controller->registerJavascript(
                    'saved_card_hosted_fields',
                    "modules/saferpayofficial/views/js/front/hosted-templates/hosted_fields_16.js"
                );
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
}