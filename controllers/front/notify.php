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
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionAssertion;
use Invertus\SaferPay\Service\TransactionFlow\SaferPayTransactionRefundAssertion;

class SaferPayOfficialNotifyModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'notify';

    /**
     * This code is being called by SaferPay by using NotifyUrl in InitializeRequest.
     * # WILL NOT work for local development, to AUTHORIZE payment this must be called manually. #
     * Example manual request: https://saferpay.demo.com/en/module/saferpayofficial/notify?success=1&cartId=12&orderId=12&secureKey=9366c61b59e918b2cd96ed0567c82e90
     */
    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            die($this->module->l('Error. Insecure cart', self::FILENAME));
        }
        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->module->getModuleContainer()->get(SaferPayOrderRepository::class);
        $saferPayOrderId = $saferPayOrderRepository->getIdByOrderId($orderId);

        try {
            $assertResponseBody = $this->assertTransaction($cartId);

            //TODO look into pipeline design pattern to use when object is modified in multiple places to avoid this issue.
            //NOTE must be left below assert action to get newest information.
            $order = new Order($orderId);

            if (
                in_array($order->payment, SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS) &&
                !$assertResponseBody->getLiability()->getLiabilityShift()
            ) {
                /** @var SaferPay3DSecureService $secureService */
                $secureService = $this->module->getModuleContainer()->get(SaferPay3DSecureService::class);
                $secureService->processNotSecuredPayment($order);
                die($this->module->l('Liability shift is false', self::FILENAME));
            }

            //NOTE to get latest information possible and not override new information.
            $order = new Order($orderId);

            $defaultBehavior = Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR);
            if ((int) $defaultBehavior === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE &&
                $assertResponseBody->getTransaction()->getStatus() !== 'CAPTURED'
            ) {
                /** @var SaferPayOrderStatusService $orderStatusService */
                $orderStatusService = $this->module->getModuleContainer()->get(SaferPayOrderStatusService::class);
                $orderStatusService->capture($order);
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
            die($this->module->l($e->getMessage(), self::FILENAME));
        }

        die($this->module->l('Success', self::FILENAME));
    }

    /**
     * @param int $cartId
     *
     * @return AssertBody
     * @throws Exception
     */
    private function assertTransaction($cartId)
    {
        /** @var SaferPayTransactionAssertion $transactionAssert */
        $transactionAssert = $this->module->getModuleContainer()->get(SaferPayTransactionAssertion::class);
        $assertionResponse = $transactionAssert->assert(Order::getOrderByCartId($cartId));

        return $assertionResponse;
    }

    protected function displayMaintenancePage()
    {
        return true;
    }
}
