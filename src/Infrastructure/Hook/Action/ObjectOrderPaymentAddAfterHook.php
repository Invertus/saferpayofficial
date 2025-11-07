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

namespace Invertus\SaferPay\Infrastructure\Hook\Action;

use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Order;
use OrderPayment;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookActionObjectOrderPaymentAddAfter
 * Updates payment method name with SaferPay brand
 */
class ObjectOrderPaymentAddAfterHook implements HookInterface
{
    /**
     * @var SaferPayOrderRepository
     */
    private $saferPayOrderRepository;

    public function __construct(SaferPayOrderRepository $saferPayOrderRepository)
    {
        $this->saferPayOrderRepository = $saferPayOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $params)
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var OrderPayment $orderPayment */
        $orderPayment = $params['object'];

        if (!Validate::isLoadedObject($orderPayment)) {
            return;
        }

        $orders = Order::getByReference($orderPayment->order_reference);

        /** @var Order|bool $order */
        $order = $orders->getFirst();

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $saferPayOrderId = (int) $this->saferPayOrderRepository->getIdByOrderId($order->id);

        if (!$saferPayOrderId) {
            return;
        }

        $brand = $this->saferPayOrderRepository->getPaymentBrandBySaferpayOrderId($saferPayOrderId);

        if (!$brand) {
            return;
        }

        $orderPayment->payment_method = 'Saferpay - ' . $brand;
        $orderPayment->update();
    }
}
