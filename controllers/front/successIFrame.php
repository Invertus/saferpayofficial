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

use Invertus\SaferPay\Api\Request\AuthorizationService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\AuthorizationRequestObjectCreator;
use Invertus\SaferPay\Service\SaferPay3DSecureService;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;

class SaferPayOfficialSuccessIFrameModuleFrontController extends AbstractSaferPayController
{
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
        $selectedCard = Tools::getValue('selectedCard');
        $isDirectPayment = Tools::getValue('directPayment');
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

        /** @var AuthorizationRequestObjectCreator $authRequestCreator */
        $authRequestCreator = $this->module->getContainer()->get(AuthorizationRequestObjectCreator::class);

        /** @var SaferPayOrderRepository $orderRepo */
        $orderRepo = $this->module->getContainer()->get('saferpay.order.repository');

        $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $saveCard = (int) $selectedCard === SaferPayConfig::CREDIT_CARD_OPTION_SAVE;
        $authRequest = $authRequestCreator->create($saferPayOrder->token, $this->module->version, $saveCard);

        /** @var AuthorizationService $authorizeService */
        $authorizeService = $this->module->getContainer()->get(AuthorizationService::class);
        $order = new Order($orderId);
        if (!$isDirectPayment) {
            try {
                $response = $authorizeService->authorize(
                    $authRequest
                );
                $authResponse = $authorizeService->createObjectsFromAuthorizationResponse(
                    $response,
                    $saferPayOrderId,
                    $this->context->customer->id,
                    $selectedCard
                );
            } catch (SaferPayApiException $e) {
                $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.');
                $failUrl = $this->context->link->getModuleLink(
                    $this->module->name,
                    'failValidation',
                    [
                        'cartId' => $cartId,
                        'secureKey' => $secureKey,
                        'orderId' => $orderId,
                        SaferPayOfficial::IS_BUSINESS_LICENCE => true,
                    ],
                    true
                );
                $this->redirectWithNotifications($failUrl);
            }
            $saferPayOrder->transaction_id = $authResponse->getTransaction()->getId();
            if ($authResponse->getTransaction()->getStatus() === 'AUTHORIZED') {
                $saferPayOrder->authorized = 1;
            }
            if ($authResponse->getTransaction()->getStatus() === 'CAPTURED') {
                $saferPayOrder->authorized = 1;
                $saferPayOrder->captured = 1;
                $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
            }
            $saferPayOrder->update();

            $newOrderStatus = _SAFERPAY_PAYMENT_AUTHORIZED_;
            $order->setCurrentState($newOrderStatus);

            if ($authResponse->getLiability()->getThreeDs() && !$authResponse->getLiability()->getLiabilityShift()) {
                /** @var SaferPay3DSecureService $secureService */
                $secureService = $this->module->getContainer()->get(SaferPay3DSecureService::class);
                $secureService->processNotSecuredPayment($order);
                $isOrderCanceled = $secureService->isSaferPayOrderCanceled($orderId);
                if ($isOrderCanceled) {
                    $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.');
                    $failUrl = $this->context->link->getModuleLink(
                        $this->module->name,
                        'failIFrame',
                        [
                            'cartId' => $cartId,
                            'secureKey' => $secureKey,
                            'orderId' => $orderId,
                            'moduleId' => $moduleId,
                        ],
                        true
                    );
                    $this->redirectWithNotifications($failUrl);
                }

                return;
            }
        }

        if ($authResponse->getLiability()->getThreeDs()) {
            $defaultBehavior = Configuration::get(SaferPayConfig::PAYMENT_BEHAVIOR);
            if ((int) $defaultBehavior === SaferPayConfig::DEFAULT_PAYMENT_BEHAVIOR_CAPTURE) {
                /** @var SaferPayOrderStatusService $orderStatusService */
                $orderStatusService = $this->module->getContainer()->get(SaferPayOrderStatusService::class);
                $orderStatusService->capture($order);
            }
        }

        $isDirectPayment = Tools::getValue('directPayment');
        if ($isDirectPayment) {
            $orderLink = $this->context->link->getPageLink(
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
            if ($isDirectPayment) {
                Tools::redirect($orderLink);
            }
        }
    }

    public function initContent()
    {
        parent::initContent();
        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $orderLink = $this->context->link->getPageLink(
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

        $this->addCSS("{$this->module->getPathUri()}views/css/front/loading.css");

        Media::addJsDef([
            'redirectUrl' => $orderLink,
        ]);
        if (SaferPayConfig::isVersion17()) {
            $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/loading.tpl');
            return;
        }
        $this->context->smarty->assign([
            'cssUrl' => "{$this->module->getPathUri()}views/css/front/loading.css",
            'jsUrl' => "{$this->module->getPathUri()}views/js/front/saferpay_iframe.js",
            'redirectUrl' => $orderLink,
        ]);
        $this->setTemplate('loading_16.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $orderLink = $this->context->link->getPageLink(
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

        $this->addCSS("{$this->module->getPathUri()}views/css/front/loading.css");

        Media::addJsDef([
            'redirectUrl' => $orderLink,
        ]);

        if (SaferPayConfig::isVersion17()) {
            $this->context->controller->registerJavascript(
                'saferpayIFrame',
                '/modules/saferpayofficial/views/js/front/saferpay_iframe.js'
            );
        }
    }
}
