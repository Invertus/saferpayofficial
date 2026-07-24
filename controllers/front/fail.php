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
use Invertus\SaferPay\Factory\OrderPresenterFactory;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\CartDuplicationService;
use Invertus\SaferPay\Logger\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialFailModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'fail';

    /** @var string */
    private $secure_key;

    /** @var int */
    private $id_cart;

    /** @var OrderPresenter|null */
    private $order_presenter;

    public function init()
    {
        parent::init();

        $this->id_cart = (int) Tools::getValue('cartId', 0);

        $redirectLink = 'index.php?controller=history';

        $this->secure_key = Tools::getValue('secureKey');

        $cart = new Cart($this->id_cart);

        if (!$this->module->id || empty($this->secure_key)) {
            Tools::redirect($redirectLink . (Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }

        if (
            (string) $this->secure_key !== (string) $cart->secure_key
            || (int) $cart->id_customer !== (int) $this->context->customer->id
            || !Validate::isLoadedObject($cart)
        ) {
            Tools::redirect($redirectLink);
        }

        /** @var CartDuplicationService $cartDuplicationService */
        $cartDuplicationService = $this->module->getService(CartDuplicationService::class);

        $cartDuplicationService->restoreCart($this->id_cart);

        $this->order_presenter = OrderPresenterFactory::getOrderPresenter();
    }

    public function initContent()
    {
        parent::initContent();

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $this->markOrderAsFailed($logger);

        $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.', self::FILE_NAME);

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->redirectWithNotifications(
            $this->context->link->getPageLink(
                'cart',
                null,
                $this->context->language->id,
                [
                    'action' => 'show',
                ],
                false,
                null,
                false
            )
        );
    }

    /**
     * When the "Order creation rule" is "Before authorization", the order row already exists by the time
     * the customer lands here after a rejected/failed authorization. Transition it to the failed state and
     * flag the Saferpay order as canceled so it does not stay stuck on "Awaiting Saferpay payment" forever
     * (and so the awaiting-status poller stops spinning). Mirrors the failure handling in notify.php.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    private function markOrderAsFailed($logger)
    {
        /** @var SaferPayOrderRepository $orderRepo */
        $orderRepo = $this->module->getService(SaferPayOrderRepository::class);

        $saferPayOrderId = (int) $orderRepo->getIdByCartId($this->id_cart);

        if (!$saferPayOrderId) {
            // "After authorization" mode: no order was created for a failed payment, nothing to update.
            return;
        }

        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        if (!Validate::isLoadedObject($saferPayOrder)) {
            return;
        }

        // Payment already succeeded through another path (e.g. the notify webhook); never override it.
        if ($saferPayOrder->authorized || $saferPayOrder->captured) {
            return;
        }

        $orderId = (int) Order::getIdByCartId($this->id_cart);
        $failedStatus = (int) _SAFERPAY_PAYMENT_AUTHORIZATION_FAILED_;

        if ($orderId && $failedStatus) {
            $order = new Order($orderId);

            if (Validate::isLoadedObject($order)) {
                $currentState = (int) $order->current_state;

                $authorizedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED);
                $capturedStatus = (int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED);

                // Do not override a success state, and avoid duplicate history entries if already failed.
                if ($currentState !== $authorizedStatus
                    && $currentState !== $capturedStatus
                    && $currentState !== $failedStatus
                ) {
                    $order->setCurrentState($failedStatus);

                    $logger->debug(sprintf('%s - Order transitioned to authorization failed', self::FILE_NAME), [
                        'context' => [
                            'id_order' => $orderId,
                            'id_cart' => $this->id_cart,
                        ],
                    ]);
                }
            }
        }

        if (!$saferPayOrder->canceled) {
            $saferPayOrder->authorized = false;
            $saferPayOrder->pending = false;
            $saferPayOrder->canceled = true;

            if ($orderId) {
                $saferPayOrder->id_order = $orderId;
            }

            $saferPayOrder->update();
        }
    }
}
