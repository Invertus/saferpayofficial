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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Logger\LoggerInterface;
use PrestaShop\PrestaShop\Core\Checkout\TermsAndConditions;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialHostedIframeModuleFrontController extends ModuleFrontController
{
    const FILE_NAME = 'hostedIframe';

    public function initContent()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        parent::initContent();

        $paymentMethod = Tools::getValue('saved_card_method');
        $selectedCard = Tools::getValue("selectedCreditCard_{$paymentMethod}");

        $this->context->smarty->assign([
            'credit_card_front_url' => $this->module->getPathUri() .'views/img/example-card/credit-card-front.png',
            'credit_card_back_url' => $this->module->getPathUri() . 'views/img/example-card/credit-card-back.png',
            'tos_cms' => $this->getDefaultTermsAndConditions(),
            'saferpay_selected_card' => $selectedCard,
        ]);

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->setTemplate(SaferPayConfig::SAFERPAY_HOSTED_TEMPLATE_LOCATION . 'template' . Configuration::get(SaferPayConfig::HOSTED_FIELDS_TEMPLATE) . '.tpl');

    }

    public function setMedia()
    {
        parent::setMedia();

        Media::addJsDef([
            'saferpay_field_access_token' => SaferPayConfig::getFieldAccessToken(),
            'saferpay_field_url' => SaferPayConfig::getFieldUrl(),
            'holder_name' => $this->module->l('Holder name', self::FILE_NAME),
            'saferpay_official_ajax_url' => $this->context->link->getModuleLink('saferpayofficial', 'ajax'),
            'saved_card_method' => Tools::getValue('saved_card_method'),
            'isBusinessLicence' => Tools::getValue('isBusinessLicence'),
        ]);

        $this->context->controller->registerJavascript(
            'remote-saferpay-fields-js-lib',
            Configuration::get(SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::getConfigSuffix()),
            [
                'server' => 'remote',
                'position' => 'bottom',
                'priority' => 20,
            ]
        );

        $this->context->controller->registerJavascript(
            'hosted-template-js-init',
            'modules/' . $this->module->name . '/views/js/front/hosted-templates/template' . Configuration::get(SaferPayConfig::HOSTED_FIELDS_TEMPLATE) . '.js'
        );
        $this->context->controller->registerJavascript(
            'hosted-template-js-submit',
            'modules/' . $this->module->name . '/views/js/front/hosted-templates/template_submit.js'
        );

        $this->context->controller->registerStylesheet(
            'theme-css',
            'modules/' . $this->module->name . '/views/css/front/hosted-templates/template' . Configuration::get(SaferPayConfig::HOSTED_FIELDS_TEMPLATE) . '.css'
        );
    }

    protected function getDefaultTermsAndConditions()
    {
        if (!SaferPayConfig::isVersionAbove177()) {
            return null;
        }

        $cms = new CMS(
            (int) Configuration::get('PS_CONDITIONS_CMS_ID'),
            $this->context->language->id
        );

        if (!Validate::isLoadedObject($cms)) {
            return false;
        }

        $link = $this->context->link->getCMSLink(
            $cms,
            $cms->link_rewrite,
            (bool) Configuration::get('PS_SSL_ENABLED')
        );

        $termsAndConditions = new TermsAndConditions();
        $termsAndConditions->setText(
            '[' . $cms->meta_title . ']',
            $link
        )
        ->setIdentifier('terms-and-conditions-footer');

        return $termsAndConditions->format();
    }
}
