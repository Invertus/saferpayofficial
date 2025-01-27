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
use Invertus\SaferPay\Controller\Front\CheckoutController;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Logger\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialIFrameModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'iframe';

    public $display_column_left = false;

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $cart = $this->context->cart;
        $redirectLink = $this->context->link->getPageLink(
            'order',
            true,
            null,
            [
                'step' => 1,
            ]
        );
        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active
        ) {
            Tools::redirect($redirectLink);
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === $this->module->name) {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            $this->errors[] =
                $this->module->l('This payment method is not available.', self::FILE_NAME);
            $this->redirectWithNotifications($redirectLink);
        }
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $logger->error(sprintf('%s - Customer not found', self::FILE_NAME), [
                'context' => [],
                'exceptions' => [],
            ]);

            Tools::redirect($redirectLink);
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    public function initContent()
    {
        parent::initContent();

        $paymentMethod = Tools::getValue('saved_card_method');
        $selectedCard = Tools::getValue("selectedCreditCard_{$paymentMethod}");

        if (!SaferPayConfig::isVersion17()) {
            $selectedCard = Tools::getValue("saved_card_{$paymentMethod}");
        }

        try {
            /** @var CheckoutController $checkoutController */
            $checkoutController = $this->module->getService(CheckoutController::class);

            // refactor it to create checkout data from validator request
            $checkoutData = CheckoutData::create(
                (int) $this->context->cart->id,
                $paymentMethod,
                (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE),
                $selectedCard,
                null,
                null,
                false,
                0
            );

            $redirectUrl = $checkoutController->execute($checkoutData);
        } catch (\Exception $exception) {
            $redirectUrl = $this->context->link->getModuleLink(
                $this->module->name,
                ControllerName::FAIL,
                [
                    'cartId' => $this->context->cart->id,
                    'orderId' => Order::getOrderByCartId($this->context->cart->id),
                    'secureKey' => $this->context->cart->secure_key,
                    'moduleId' => $this->module->id,
                ],
                true
            );
            $this->redirectWithNotifications($redirectUrl);
        }

        $this->context->smarty->assign([
            'redirect' => $redirectUrl,
        ]);

        if (SaferPayConfig::isVersion17()) {
            $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/saferpay_iframe.tpl');
            return;
        }

        $this->setTemplate('saferpay_iframe_16.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS("{$this->module->getPathUri()}views/css/front/saferpay_iframe.css");
    }
}
