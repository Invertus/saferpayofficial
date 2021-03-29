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
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPay3DSecureService;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAuthorization;

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

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            $redirectLink = $this->context->link->getPageLink(
                'order',
                true,
                null,
                [
                    'step' => 1,
                ]
            );
            Tools::redirect($redirectLink);
        }

        try {
            /** @var SaferPayOrderRepository $orderRepo */
            $orderRepo = $this->module->getModuleContainer()->get(SaferPayOrderRepository::class);
            $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);

            $saferPayOrder = new SaferPayOrder($saferPayOrderId);
            $order = new Order($orderId);
            $authResponseBody = $this->authorizeTransaction($cartId);

            if (!$authResponseBody->getLiability()->getLiabilityShift()) {
                /** @var SaferPay3DSecureService $secureService */
                $secureService = $this->module->getModuleContainer()->get(SaferPay3DSecureService::class);
                $secureService->processNotSecuredPayment($order);
                Tools::redirect($this->getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey));
            }

            $defaultBehavior = Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR);
            if ((int) $defaultBehavior === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE &&
                !$saferPayOrder->captured
            ) {
                /** @var SaferPayOrderStatusService $orderStatusService */
                $orderStatusService = $this->module->getModuleContainer()->get(SaferPayOrderStatusService::class);
                $orderStatusService->capture($order);
                Tools::redirect($this->getOrderConfirmationLink($cartId, $moduleId, $orderId, $secureKey));
            }
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
                    'fail',
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

    /**
     * @param int $cartId
     *
     * @return AssertBody
     * @throws Exception
     */
    private function authorizeTransaction($cartId)
    {
        /** @var SaferPayTransactionAuthorization $transactionAuth */
        $transactionAuth = $this->module->getModuleContainer()->get(SaferPayTransactionAuthorization::class);
        $authorizationResponse = $transactionAuth->authorize(
            Order::getOrderByCartId($cartId),
            0,
            -1
        );

        return $authorizationResponse;
    }
}
