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
use Invertus\SaferPay\Controller\Front\CheckoutController;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialAjaxModuleFrontController extends ModuleFrontController
{
    const FILE_NAME = 'ajax';

    /** @var SaferPayOfficial */
    public $module;

    public function postProcess()
    {
        switch (Tools::getValue('action')) {
            case 'submitHostedFields':
                $this->submitHostedFields();
                break;
            case 'getStatus':
                $this->processGetStatus();
                break;
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function processGetStatus()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->module->getService(SaferPayOrderRepository::class);
        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');
        $moduleId = $this->module->id;
        $selectedCard = Tools::getValue('selectedCard');
        $saferPayOrderId = $saferPayOrderRepository->getIdByCartId($cartId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        if (!$saferPayOrder->id || $saferPayOrder->canceled) {
            $this->ajaxDie(json_encode([
                'isFinished' => true,
                'href' => $this->getFailControllerLink($cartId, $secureKey, $moduleId)
            ]));
        }

        $this->ajaxDie(json_encode([
            'saferpayOrder' => json_encode($saferPayOrder),
            'isFinished' => true,
            'href' => $this->context->link->getModuleLink(
                $this->module->name,
                $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                [
                    'cartId' => $cartId,
                    'orderId' => $saferPayOrder->id_order,
                    'moduleId' => $moduleId,
                    'secureKey' => $secureKey,
                    'selectedCard' => $selectedCard ?? '-1',
                ]
            )
        ]));
    }

    private function getFailControllerLink($cartId, $secureKey, $moduleId)
    {
        return $this->context->link->getModuleLink(
            $this->module->name,
            ControllerName::FAIL,
            [
                'cartId' => $cartId,
                'secureKey' => $secureKey,
                'moduleId' => $moduleId,
            ],
            true
        );
    }

    private function getSuccessControllerName($isBusinessLicence, $fieldToken)
    {
        $successController = ControllerName::SUCCESS;

        if ($isBusinessLicence) {
            $successController = ControllerName::SUCCESS_IFRAME;
        }

        if ($fieldToken) {
            $successController = ControllerName::SUCCESS_HOSTED;
        }

        return $successController;
    }

    private function submitHostedFields()
    {
        try {
            if (Order::getOrderByCartId($this->context->cart->id)) {
                $this->ajaxDie(json_encode([
                    'error' => true,
                    'message' => $this->module->l('Order already exists', self::FILE_NAME),
                    'url' => $this->getRedirectionToControllerUrl('fail'),
                ]));
            }

            // refactor it to create checkout data from validator request
            $checkoutData = CheckoutData::create(
                (int) $this->context->cart->id,
                Tools::getValue('paymentMethod'),
                (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE),
                Tools::getValue('selectedCard'),
                Tools::getValue('fieldToken'),
                ControllerName::SUCCESS_HOSTED,
                true
            );

            /** @var CheckoutController $checkoutController */
            $checkoutController = $this->module->getService(CheckoutController::class);
            $redirectUrl = $checkoutController->execute($checkoutData);

            if (empty($redirectUrl) || Tools::getValue('action') === 'submitHostedFields') {
                $redirectUrl = $this->getRedirectionToControllerUrl('successHosted');
            }

            $this->ajaxDie(json_encode([
                'error' => false,
                'url' => $redirectUrl,
            ]));
        } catch (Exception $e) {
            $this->ajaxDie(json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'url' => $this->getRedirectionToControllerUrl('fail'),
            ]));
        }
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
}
