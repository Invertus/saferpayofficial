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

namespace Invertus\SaferPay\Service;

use Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Order;
use SaferPayOrder;

class SaferPay3DSecureService
{

    /**
     * @var SaferPayOrderStatusService
     */
    private $orderStatusService;
    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;
    /**
     * @var CartDuplicationService
     */
    private $cartDuplicationService;

    public function __construct(
        SaferPayOrderStatusService $orderStatusService,
        SaferPayOrderRepository $orderRepository,
        CartDuplicationService $cartDuplicationService
    ) {
        $this->orderStatusService = $orderStatusService;
        $this->orderRepository = $orderRepository;
        $this->cartDuplicationService = $cartDuplicationService;
    }

    /**
     * @param Order $order
     */
    public function processNotSecuredPayment(Order $order)
    {
        $defaultBehavior = Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D);
        if ($defaultBehavior) {
            return;
        }
        $this->cartDuplicationService->restoreCart($order->id_cart);
        $this->orderStatusService->cancel($order);
    }

    /**
     * @param $orderId
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function isSaferPayOrderCanceled($orderId)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        return $saferPayOrder->canceled;
    }
}
