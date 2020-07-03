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

use Invertus\SaferPay\Api\Request\AssertService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\AssertRequestObjectCreator;
use Invertus\SaferPay\Service\SaferPay3DSecureService;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;

class SaferPayOfficialNotifyModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'notify';

    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            die(400);
        }
        $order = new Order($orderId);
        $status = _SAFERPAY_PAYMENT_AUTHORIZED_;
        $order->setCurrentState($status);

        /** @var SaferPayOrderRepository $orderRepo */
        $orderRepo = $this->module->getContainer()->get(SaferPayOrderRepository::class);
        $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);

        /** @var AssertRequestObjectCreator $SaferPayObjectCreator */
        $SaferPayObjectCreator = $this->module->getContainer()->get(AssertRequestObjectCreator::class);
        $assertRequest = $SaferPayObjectCreator->create($orderId);

        /** @var AssertService $assertService */
        $assertService = $this->module->getContainer()->get(AssertService::class);

        try {
            $assertBody = $assertService->assert($assertRequest, $saferPayOrderId);
        } catch (Exception $e) {
            die(404);
        }
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $saferPayOrder->transaction_id = $assertBody->getTransaction()->getId();

        if ($assertBody->getTransaction()->getStatus() === 'AUTHORIZED') {
            $saferPayOrder->authorized = 1;
        }
        if ($assertBody->getTransaction()->getStatus() === 'CAPTURED') {
            $saferPayOrder->authorized = 1;
            $saferPayOrder->captured = 1;
            $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
        }

        $saferPayOrder->update();
        if (!$assertBody->getLiability()->getLiabilityShift()) {
            /** @var SaferPay3DSecureService $secureService */
            $secureService = $this->module->getContainer()->get(SaferPay3DSecureService::class);
            $secureService->processNotSecuredPayment($order);
            die();
        }
        $defaultBehavior = Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR);
        if ((int) $defaultBehavior === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE && !$saferPayOrder->captured) {
            /** @var SaferPayOrderStatusService $orderStatusService */
            $orderStatusService = $this->module->getContainer()->get(SaferPayOrderStatusService::class);
            $orderStatusService->capture($order);
        }

        die();
    }
}
