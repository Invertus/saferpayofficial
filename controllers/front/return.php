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

use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Processor\CheckoutProcessor;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialReturnModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'return';

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $secureKey = Tools::getValue('secureKey');
        $isBusinessLicence = (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE);
        $fieldToken = Tools::getValue('fieldToken');
        $moduleId = $this->module->id;
        $selectedCard = Tools::getValue('selectedCard');

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->secure_key !== $secureKey) {
            $this->ajaxDie(json_encode([
                'error_type' => 'unknown_error',
                'error_text' => $this->module->l('An unknown error error occurred. Please contact support', self::FILENAME),
            ]));
        }

        if ($cart->orderExists()) {
            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($cartId);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getOrderByCartId($cartId);
            }

            $order = new Order($orderId);

            $saferPayAuthorizedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
            $saferPayCapturedStatus = (int) Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

            if ((int) $order->current_state === $saferPayAuthorizedStatus || (int) $order->current_state === $saferPayCapturedStatus) {
                Tools::redirect($this->context->link->getModuleLink(
                    $this->module->name,
                    $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                    [
                        'cartId' => $cartId,
                        'orderId' => $orderId,
                        'moduleId' => $moduleId,
                        'secureKey' => $secureKey,
                        'selectedCard' => $selectedCard,
                    ]
                ));
            }
        } else {
            $this->context->smarty->assign(
                'checkStatusEndpoint',
                $this->context->link->getModuleLink(
                    $this->module->name,
                    'return',
                    [
                        'ajax' => 1,
                        'action' => 'getStatus',
                        'secureKey' => $secureKey,
                        'cartId' => $cartId,
                    ],
                    true
                )
            );
            parent::setTemplate('saferpay_wait.tpl');
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

        if ($saferPayOrder->canceled || !$saferPayOrder->id_order) {
            $href = $this->context->link->getModuleLink(
                $this->module->name,
                ControllerName::FAIL,
                [
                    'cartId' => $cartId,
                    'secureKey' => $secureKey,
                    'moduleId' => $moduleId,
                ],
                true
            );
        } else {
            $href = $this->context->link->getModuleLink(
                $this->module->name,
                $this->getSuccessControllerName($isBusinessLicence, $fieldToken),
                [
                    'cartId' => $cartId,
                    'orderId' => $saferPayOrder->id_order,
                    'moduleId' => $moduleId,
                    'secureKey' => $secureKey,
                    'selectedCard' => $selectedCard,
                ]
            );
        }

        // @todo: this place needs attention after a2a pr merged - need to check if pending status works well
        $this->ajaxDie(json_encode([
            'isFinished' => $saferPayOrder->authorized || $saferPayOrder->captured || $saferPayOrder->canceled || isset($saferPayOrder->pending),
            'href' => $href
        ]));
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
}
