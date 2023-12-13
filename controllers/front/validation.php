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
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Service\SaferPayExceptionService;
use Invertus\SaferPay\Service\SaferPayInitialize;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialValidationModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'validation';

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $paymentMethod = Tools::getValue('saved_card_method');
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
                $this->module->l('This payment method is not available.', self::FILENAME);
            $this->redirectWithNotifications($redirectLink);
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($redirectLink);
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal();

        if (!(int) Configuration::get(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION)) {
            $this->module->validateOrder(
                $cart->id,
                Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
                $total,
                $paymentMethod,
                null,
                [],
                (int) $currency->id,
                false,
                $customer->secure_key
            );
        }

        /** @var SaferPayInitialize $initializeService */
        $initializeService = $this->module->getService(SaferPayInitialize::class);
        try {
            $isBusinessLicence = Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
            $initializeBody = $initializeService->initialize($paymentMethod, $isBusinessLicence);
        } catch (SaferPayApiException $e) {
            /** @var SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getService(SaferPayExceptionService::class);
            $this->errors[] = $exceptionService->getErrorMessageForException($e, $exceptionService->getErrorMessages());
            $redirectLink = $this->context->link->getModuleLink(
                $this->module->name,
                'fail',
                [
                    'cartId' => $this->context->cart->id,
                    'orderId' => Order::getOrderByCartId($this->context->cart->id),
                    'secureKey' => $this->context->cart->secure_key,
                    'moduleId' => $this->module->id,
                ],
                true
            );
            $this->redirectWithNotifications($redirectLink);
        }
        if (!(int) Configuration::get(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION)) {
            /** @var Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder $saferPayOrderBuilder */
            $saferPayOrderBuilder = $this->module->getService(\Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder::class);

            $saferPayOrderBuilder->create(
                $initializeBody,
                $this->context->cart,
                $this->context->customer,
                false,
                $isBusinessLicence
            );
        }

        Tools::redirect($initializeBody->RedirectUrl);
    }
}
