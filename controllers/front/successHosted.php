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
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialSuccessHostedModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'successHosted';

    protected $display_header = false;
    protected $display_footer = false;

    public function init()
    {
        if (SaferPayConfig::isVersion17()) {
            $this->display_header = true;
        }
        parent::init();
    }

    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');
        $moduleId = Tools::getValue('moduleId');
        $selectedCard = Tools::getValue('selectedCard');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            Tools::redirect($this->getOrderLink());
        }

        try {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getService(SaferPayOrderStatusService::class);

            $order = new Order($orderId);

            /** @var SaferPayTransactionAuthorization $saferPayTransactionAuthorization */
            $saferPayTransactionAuthorization = $this->module->getService(SaferPayTransactionAuthorization::class);

            $authResponseBody = $saferPayTransactionAuthorization->authorize(
                $orderId,
                (int) $selectedCard === SaferPayConfig::CREDIT_CARD_OPTION_SAVE,
                $selectedCard
            );

            $paymentBehaviourWithout3DS = (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D);

            if (
                (!$authResponseBody->getLiability()->getLiabilityShift() &&
                in_array($order->payment, SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS) &&
                $paymentBehaviourWithout3DS === SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL) ||
                $authResponseBody->getTransaction()->getStatus() === SaferPayConfig::TRANSACTION_STATUS_CANCELED
            ) {
                $orderStatusService->cancel($order);

                $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.', self::FILENAME);

                $this->redirectWithNotifications($this->context->link->getModuleLink(
                    $this->module->name,
                    ControllerName::FAIL,
                    [
                        'cartId' => $cartId,
                        'secureKey' => $secureKey,
                        'orderId' => $orderId,
                        'moduleId' => $moduleId,
                    ],
                    true
                ));
            }

            $orderStatusService->authorize($order);

            $paymentBehaviour = (int) Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR);

            if ($paymentBehaviour === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE && $authResponseBody->getTransaction()->getStatus() !== TransactionStatus::CAPTURED) {
                $orderStatusService->capture($order);
                Tools::redirect($this->getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey));
            }

            Tools::redirect($this->getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey));
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '%s has caught an error: %s',
                    __CLASS__,
                    $e->getMessage()
                ),
                1,
                null,
                null,
                null,
                true
            );

            Tools::redirect(
                $this->context->link->getModuleLink(
                    $this->module->name,
                    ControllerName::FAIL,
                    [
                        'cartId' => $cartId,
                        'secureKey' => $secureKey,
                        'orderId' => $orderId,
                        \Invertus\SaferPay\Config\SaferPayConfig::IS_BUSINESS_LICENCE => true,
                    ],
                    true
                )
            );
        }
    }

    public function initContent()
    {
        parent::initContent();
        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        Tools::redirect($this->getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey));
    }

    /**
     * @param int $cartId
     * @param int $moduleId
     * @param int $orderId
     * @param string $secureKey
     *
     * @return string
     */
    private function getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey)
    {
        return $this->context->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => $cartId,
                'id_module' => $moduleId,
                'id_order' => $orderId,
                'key' => $secureKey,
            ]
        );
    }

    private function getOrderLink()
    {
        return $this->context->link->getPageLink(
            'order',
            true,
            null,
            [
                'step' => 1,
            ]
        );
    }
}
