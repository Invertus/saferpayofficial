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
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Logger\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialFailIFrameModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'failIFrame';

    protected $display_header = false;
    protected $display_footer = false;

    public function init()
    {
        if (SaferPayConfig::isVersion17()) {
            $this->display_header = true;
        }
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $cart = new \Cart(Tools::getValue('cartId'));

        /**
         * Note: deleting cart prevents
         * from further failing when creating order with same cart
         */
        $cart->delete();

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $orderLink = $this->context->link->getPageLink(
            'order',
            true,
            null
        );

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        if (SaferPayConfig::isVersion17()) {
            $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/loading.tpl');
            return;
        }

        $this->context->smarty->assign([
            'cssUrl' => "{$this->module->getPathUri()}views/css/front/loading.css",
            'jsUrl' => "{$this->module->getPathUri()}views/js/front/saferpay_iframe.js",
            'redirectUrl' => $orderLink,
        ]);
        $this->setTemplate('loading_16.tpl');

    }

    public function setMedia()
    {
        parent::setMedia();

        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $failUrl = $this->context->link->getModuleLink(
            $this->module->name,
            ControllerName::FAIL,
            [
                'cartId' => $cartId,
                'secureKey' => $secureKey,
                'orderId' => $orderId,
                'moduleId' => $moduleId,
            ],
            true
        );

        $this->addCSS("{$this->module->getPathUri()}views/css/front/loading.css");

        Media::addJsDef([
            'redirectUrl' => $failUrl,
        ]);

        if (SaferPayConfig::isVersion17()) {
            $this->context->controller->registerJavascript(
                'saferpayIFrame',
                '/modules/saferpayofficial/views/js/front/saferpay_iframe.js'
            );
        }
    }
}
