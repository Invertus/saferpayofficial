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
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Service\PendingPaymentPageProvider;
use Invertus\SaferPay\Service\SaferPayExceptionService;
use Invertus\SaferPay\Controller\Front\CheckoutController;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialValidationModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'validation';

    /** @var SaferPayOfficial */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $paymentMethod = Tools::getValue('saved_card_method');
        $customerId = (int) $this->context->customer->id;

        // A double click on "Pay" submits the checkout twice. Run those requests one after another, so
        // the second one sees the order and payment page the first one created.
        if ($customerId) {
            $this->waitForLock(sprintf('validation-%s', $customerId));
        }

        $cart = $this->context->cart;

        $redirectLink = $this->context->link->getPageLink(
            'order',
            true,
            null,
            [
                'step' => 1,
            ]
        );

        $existingOrderId = $cart->id ? (int) Order::getIdByCartId((int) $cart->id) : 0;

        // PrestaShop swaps an already converted cart for an empty one, so a repeated submit arrives either
        // with the converted cart or with no cart at all.
        if (!Validate::isLoadedObject($cart) || $existingOrderId) {
            /** @var PendingPaymentPageProvider $pendingPaymentPageProvider */
            $pendingPaymentPageProvider = $this->module->getService(PendingPaymentPageProvider::class);
            $paymentPageUrl = $pendingPaymentPageProvider->getUrl(
                (int) $cart->id,
                $customerId,
                (int) $this->context->shop->id
            );

            if ($paymentPageUrl) {
                $logger->debug(sprintf('%s - Repeated submit sent to the open payment page', self::FILE_NAME), [
                    'context' => [
                        'id_cart' => (int) $cart->id,
                        'id_customer' => $customerId,
                    ],
                ]);

                Tools::redirect($paymentPageUrl);
            }

            if ($existingOrderId) {
                $this->errors[] = $this->module->l('Order already exists.', self::FILE_NAME);
                $this->redirectWithNotifications($redirectLink);
            }

            Tools::redirect($redirectLink);
        }

        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active
        ) {
            Tools::redirect($redirectLink);
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === $this->module->name) {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            $this->errors[] = $this->module->l('This payment method is not available.', self::FILE_NAME);
            $this->redirectWithNotifications($redirectLink);
        }

        try {
            /** @var CheckoutController $checkoutController */
            $checkoutController = $this->module->getService(CheckoutController::class);
            // refactor it to create checkout data from validator request
            $checkoutData = CheckoutData::create(
                (int) $this->context->cart->id,
                $paymentMethod,
                (int) Tools::getValue(SaferPayConfig::IS_BUSINESS_LICENCE),
                -1,
                null,
                null,
                false
            );

            $redirectLink = $checkoutController->execute($checkoutData);

            $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

            Tools::redirect($redirectLink);
        } catch (\Exception $exception) {
            /** @var SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getService(SaferPayExceptionService::class);
            $this->errors[] = $exceptionService->getErrorMessageForException($exception, $exceptionService->getErrorMessages());

            if (method_exists('Order', 'getIdByCartId')) {
                $orderId = Order::getIdByCartId($this->context->cart->id);
            } else {
                // For PrestaShop 1.6 use the alternative method
                $orderId = Order::getIdByCartId($this->context->cart->id);
            }

            $redirectLink = $this->context->link->getModuleLink(
                $this->module->name,
                'fail',
                [
                    'cartId' => $this->context->cart->id,
                    'orderId' => $orderId,
                    'secureKey' => $this->context->cart->secure_key,
                    'moduleId' => $this->module->id,
                ],
                true
            );
            $this->redirectWithNotifications($redirectLink);
        }
    }
}
