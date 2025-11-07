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

namespace Invertus\SaferPay\Infrastructure\Hook\Display;

use Context;
use Currency;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Presenter\AdminOrderPagePresenter;
use Invertus\SaferPay\Presenter\AssertPresenter;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Utility\VersionUtility;
use Order;
use SaferPayAssert;
use SaferPayOfficial;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookDisplayAdminOrder and hookDisplayAdminOrderTabContent
 * Displays SaferPay order details in admin order page
 */
class AdminOrderHook implements HookInterface
{
    /**
     * @var SaferPayOfficial
     */
    private $module;

    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        SaferPayOfficial $module,
        SaferPayOrderRepository $orderRepository,
        Context $context
    ) {
        $this->module = $module;
        $this->orderRepository = $orderRepository;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $params)
    {
        $orderId = $params['id_order'];
        $order = new Order($orderId);

        $saferPayOrderId = $this->orderRepository->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        if ($order->module !== $this->module->name) {
            return '';
        }

        if (!$saferPayOrder->authorized && !$saferPayOrder->captured) {
            return '';
        }

        $action = $this->buildActionUrl($orderId);
        $assertData = $this->prepareAssertData($saferPayOrderId);
        $orderPageData = $this->prepareOrderPageData($saferPayOrder, $order, $action);

        $this->context->smarty->assign($assertData);
        $this->context->smarty->assign($orderPageData);

        return $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/hook/admin/saferpay_order.tpl'
        );
    }

    /**
     * Build action URL for admin order controller
     *
     * @param int $orderId
     * @return string
     */
    private function buildActionUrl($orderId)
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.7.0')) {
            return $this->context->link->getAdminLink(
                SaferPayOfficial::ADMIN_ORDER_CONTROLLER,
                true,
                [],
                ['orderId' => $orderId]
            );
        }

        return $this->context->link->getAdminLink(
            SaferPayOfficial::ADMIN_ORDER_CONTROLLER
        ) . '&id_order=' . (int) $orderId;
    }

    /**
     * Prepare assert data for template
     *
     * @param int $saferPayOrderId
     * @return array
     */
    private function prepareAssertData($saferPayOrderId)
    {
        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrderId);
        $assertData = new SaferPayAssert($assertId);
        $assertPresenter = new AssertPresenter($this->module);
        $assertData = $assertPresenter->present($assertData);
        $supported3DsPaymentMethods = SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS;

        // If payment method does not support 3DS, set liability_shift to true
        // to hide 'failed security check' message
        if ($assertData['liability_shift'] === "0"
            && !in_array($assertData['paymentMethod'], $supported3DsPaymentMethods)) {
            $assertData['liability_shift'] = true;
        }

        return $assertData;
    }

    /**
     * Prepare order page data for template
     *
     * @param SaferPayOrder $saferPayOrder
     * @param Order $order
     * @param string $action
     * @return array
     */
    private function prepareOrderPageData(SaferPayOrder $saferPayOrder, Order $order, $action)
    {
        $currency = new Currency($order->id_currency);
        $adminOrderPagePresenter = new AdminOrderPagePresenter();

        return $adminOrderPagePresenter->present(
            $saferPayOrder,
            $action,
            SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API,
            $currency->sign
        );
    }
}
