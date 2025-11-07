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

use Context;
use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Order;
use SaferPayOfficial;
use SaferPayOrder;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookActionAdminControllerSetMedia
 * Loads admin CSS and manages flash messages on order page
 */
class AdminControllerSetMediaHook implements HookInterface
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
        if ('AdminOrders' !== Tools::getValue('controller')
            || (!Tools::isSubmit('vieworder') && Tools::getValue('action') !== 'vieworder')
        ) {
            return;
        }

        $this->context->controller->addCSS(
            'modules/' . $this->module->name . '/views/css/admin/saferpay_admin_order.css'
        );

        $orderId = Tools::getValue('id_order');
        $order = new Order($orderId);

        if ($order->module !== $this->module->name) {
            return;
        }

        $saferPayOrderId = $this->orderRepository->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        if (!$saferPayOrder->authorized) {
            return;
        }

        $this->handleFlashMessages($orderId, $saferPayOrder);
    }

    /**
     * Handle flash messages from cookies
     *
     * @param int $orderId
     * @param SaferPayOrder $saferPayOrder
     */
    private function handleFlashMessages($orderId, SaferPayOrder $saferPayOrder)
    {
        // Handle error messages
        if (isset($this->context->cookie->saferPayErrors)) {
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            if (isset($saferPayErrors[$orderId])) {
                $this->module->addFlash($saferPayErrors[$orderId], 'error');
                unset($saferPayErrors[$orderId]);
                $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);
            }
        }

        // Handle canceled status
        if ($this->context->cookie->canceled) {
            $this->module->addFlash($this->module->l('Saferpay payment was canceled successfully'), 'success');
            $this->context->cookie->canceled = false;
        }

        // Handle captured status
        if ($this->context->cookie->captured) {
            $this->module->addFlash($this->module->l('Saferpay payment was captured successfully'), 'success');
            $this->context->cookie->captured = false;
        }

        // Handle refunded status
        if ($this->context->cookie->refunded) {
            if ($saferPayOrder->refunded) {
                $this->module->addFlash($this->module->l('Saferpay full refund was made successfully!'), 'success');
            } else {
                $this->module->addFlash($this->module->l('Saferpay partial refund was made successfully!'), 'success');
            }
            $this->context->cookie->refunded = false;
        }
    }
}
