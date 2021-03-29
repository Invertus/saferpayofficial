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
use Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder;
use Invertus\SaferPay\Service\SaferPayInitialize;

class SaferPayOfficialAjaxModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        switch (Tools::getValue('action')) {
            case 'submitHostedFields':
                $this->submitHostedFields();
                break;
        }
    }

    private function submitHostedFields()
    {
        try {
            if (!Order::getOrderByCartId($this->context->cart->id)) {
                $this->validateOrder();
            }
            /** @var SaferPayInitialize $initializeService */
            $initializeService = $this->module->getModuleContainer()->get(SaferPayInitialize::class);
            $initializeBody = $initializeService->initialize(
                Tools::getValue('paymentMethod'),
                (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE),
                -1,
                null,
                Tools::getValue('fieldToken')
            );
            $this->createSaferPayOrder($initializeBody);
            $redirectUrl = $this->getRedirectionUrl($initializeBody);

            if (empty($redirectUrl)) {
                $redirectUrl = $this->getRedirectionToControllerUrl('successHosted');
            }

            $this->ajaxDie(json_encode([
                'status' => true,
                'url' => $redirectUrl,
            ]));
        } catch (Exception $e) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'url' => $this->getRedirectionToControllerUrl('fail'),
                'message' => $e->getMessage(),
            ]));
        }
    }

    /**
     * @param object $initializeBody
     *
     * @return string
     */
    private function getRedirectionUrl($initializeBody)
    {
        if (isset($initializeBody->RedirectUrl)) {
            return $initializeBody->RedirectUrl;
        }

        if (isset($initializeBody->Redirect->RedirectUrl)) {
            return $initializeBody->Redirect->RedirectUrl;
        }

        return '';
    }

    /**
     * @param object $initializeBody
     */
    private function createSaferPayOrder($initializeBody)
    {
        /** @var Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder $saferPayOrderBuilder */
        $saferPayOrderBuilder = $this->module->getModuleContainer()->get(SaferPayOrderBuilder::class);
        $saferPayOrderBuilder->create(
            $initializeBody,
            $this->context->cart,
            $this->context->customer,
            true,
            Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE)
        );
    }

    /**
     * @param string $controllerName
     *
     * @return string
     */
    private function getRedirectionToControllerUrl($controllerName)
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            $controllerName,
            [
                'cartId' => $this->context->cart->id,
                'orderId' => Order::getOrderByCartId($this->context->cart->id),
                'secureKey' => $this->context->cart->secure_key,
                'moduleId' => $this->module->id,
            ],
            true
        );
    }

    /**
     * @throws Exception
     */
    private function validateOrder()
    {
        $customer = new Customer($this->context->cart->id_customer);

        $this->module->validateOrder(
            $this->context->cart->id,
            Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            (float) $this->context->cart->getOrderTotal(),
            Tools::getValue('paymentMethod'),
            null,
            [],
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );
    }
}
