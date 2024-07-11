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

use Invertus\SaferPay\Builder\OrderConfirmationMessageTemplate;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Core\Order\Verification\CanSendOrderConfirmationEmail;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Install\Installer;
use Invertus\SaferPay\Install\Uninstaller;
use Invertus\SaferPay\Presentation\Loader\PaymentFormAssetLoader;
use Invertus\SaferPay\Presenter\AdminOrderPagePresenter;
use Invertus\SaferPay\Presenter\AssertPresenter;
use Invertus\SaferPay\Provider\PaymentRedirectionProvider;
use Invertus\SaferPay\Provider\PaymentTypeProvider;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Service\LegacyTranslator;
use Invertus\SaferPay\Service\PaymentRestrictionValidation;
use Invertus\SaferPay\Service\SaferPayCartService;
use Invertus\SaferPay\Service\SaferPayErrorDisplayService;
use Invertus\SaferPay\Service\SaferPayMailService;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayPaymentNotation;
use Invertus\SaferPay\ServiceProvider\LeagueServiceContainerProvider;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficial extends PaymentModule
{
    const ADMIN_SAFERPAY_MODULE_CONTROLLER = 'AdminSaferPayOfficialModule';
    const ADMIN_SETTINGS_CONTROLLER = 'AdminSaferPayOfficialSettings';
    const ADMIN_PAYMENTS_CONTROLLER = 'AdminSaferPayOfficialPayment';
    const ADMIN_FIELDS_CONTROLLER = 'AdminSaferPayOfficialFields';
    const ADMIN_ORDER_CONTROLLER = 'AdminSaferPayOfficialOrder';
    const ADMIN_LOGS_CONTROLLER = 'AdminSaferPayOfficialLogs';

    const DISABLE_CACHE = true;

    public function __construct($name = null)
    {
        $this->name = 'saferpayofficial';
        $this->author = 'Invertus';
        $this->version = '1.2.3';
        $this->module_key = '3d3506c3e184a1fe63b936b82bda1bdf';
        $this->displayName = 'SaferpayOfficial';
        $this->description = 'Saferpay Payment module';
        $this->tab = 'payments_gateways';
        $this->ps_versions_compliancy = [
            'min' => '1.6.1.0',
            'max' => _PS_VERSION_,
        ];
        parent::__construct($name);

        $this->autoload();
        $this->loadConfig();
    }

    public function getContent()
    {
        if (Configuration::get(SaferPayConfig::USERNAME)) {
            Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_PAYMENTS_CONTROLLER));
        }
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_SETTINGS_CONTROLLER));
    }

    public function install()
    {
        $installer = new Installer($this);

        if (!parent::install()) {
            return false;
        }

        if (!$installer->install()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $uninstaller = new Uninstaller($this);
        if (!$uninstaller->uninstall()) {
            $this->_errors += $uninstaller->getErrors();
            return false;
        }
        return parent::uninstall();
    }

    public function getTabs()
    {
        $installer = new Installer($this);

        return $installer->tabs();
    }

    /**
     * Init autoload.
     */
    private function autoload()
    {
        require_once dirname(__FILE__) . '/vendor/autoload.php';
    }

    private function loadConfig()
    {
        require $this->getLocalPath() . 'saferpay.config.php';
    }
    public function getService($service)
    {
        $containerProvider = new LeagueServiceContainerProvider();

        return $containerProvider->getService($service);
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (empty($params['order'])) {
            return '';
        }

        /** @var Order $psOrder */
        $psOrder = $params['order'];

        /** @var SaferPayOrderRepository $repository */
        $repository = $this->getService(SaferPayOrderRepository::class);

        $sfOrder = $repository->getByOrderId((int) $psOrder->id);
        if (!$sfOrder->pending) {
            return '';
        }

        return $this->l('Your payment is still being processed by your bank. This can take up to 5 days (120 hours). Once we receive the final status, we will notify you immediately.
Thank you for your patience!');
    }

    public function hookActionObjectOrderPaymentAddAfter($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var OrderPayment $orderPayment */
        $orderPayment = $params['object'];

        if (!Validate::isLoadedObject($orderPayment)) {
            return;
        }

        /** @var SaferPayOrderRepository $saferPayOrderRepository */
        $saferPayOrderRepository = $this->getService(SaferPayOrderRepository::class);

        $orders = Order::getByReference($orderPayment->order_reference);

        /** @var Order|bool $order */
        $order = $orders->getFirst();

        if (!Validate::isLoadedObject($order) || !$order) {
            return;
        }

        $saferPayOrderId = (int) $saferPayOrderRepository->getIdByOrderId($order->id);

        if (!$saferPayOrderId) {
            return;
        }

        $brand = $saferPayOrderRepository->getPaymentBrandBySaferpayOrderId($saferPayOrderId);

        if (!$brand) {
            return;
        }

        $orderPayment->payment_method = 'Saferpay - ' . $brand;
        $orderPayment->update();
    }

    public function hookPaymentOptions($params)
    {
        /** @var Invertus\SaferPay\Service\SaferPayCartService $assertService */
        $cartService = $this->getService(SaferPayCartService::class);
        if (!$cartService->isCurrencyAvailable($params['cart'])) {
            return;
        }

        /** @var PaymentTypeProvider $paymentTypeProvider */
        $paymentTypeProvider = $this->getService(PaymentTypeProvider::class);

        /** @var SaferPayObtainPaymentMethods $obtainPaymentMethods */
        $obtainPaymentMethods = $this->getService(SaferPayObtainPaymentMethods::class);
        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->getService(SaferPayPaymentRepository::class);

        try {
            $paymentMethods = $obtainPaymentMethods->obtainPaymentMethods();
        } catch (SaferPayApiException $exception) {
            return [];
        }

        $paymentOptions = [];

        /** @var PaymentRestrictionValidation $paymentRestrictionValidation */
        $paymentRestrictionValidation = $this->getService(
            PaymentRestrictionValidation::class
        );

        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethod['paymentMethod'] = str_replace(' ', '', $paymentMethod['paymentMethod']);

            if (!$paymentRestrictionValidation->isPaymentMethodValid($paymentMethod['paymentMethod'])) {
                continue;
            }

            $imageUrl = ($paymentRepository->isLogoEnabledByName($paymentMethod['paymentMethod']))
                ? $paymentMethod['logoUrl'] : '';

            $isCreditCard = in_array(
                $paymentMethod['paymentMethod'],
                SaferPayConfig::TRANSACTION_METHODS
            );
            $isBusinessLicenseEnabled =
                Configuration::get(
                    SaferPayConfig::BUSINESS_LICENSE
                    . SaferPayConfig::getConfigSuffix()
                );

            /** @var SaferPayCardAliasRepository $cardAliasRep */
            $cardAliasRep = $this->getService(
                SaferPayCardAliasRepository::class
            );
            $isCreditCardSavingEnabled = Configuration::get(
                SaferPayConfig::CREDIT_CARD_SAVE
            );
            $selectedCard = 0;
            if ($this->context->customer->is_guest) {
                $isCreditCardSavingEnabled = false;
                $selectedCard = -1;
            }

            /** @var PaymentRedirectionProvider $paymentRedirectionProvider */
            $paymentRedirectionProvider = $this->getService(PaymentRedirectionProvider::class);

            $newOption = new PaymentOption();
            $translator = $this->getService(
                LegacyTranslator::class
            );
            /** @var SaferPayPaymentNotation $saferPayPaymentNotation */
            $saferPayPaymentNotation = $this->getService(SaferPayPaymentNotation::class);
            $paymentMethodName = $saferPayPaymentNotation->getForDisplay($paymentMethod['paymentMethod']);

            $inputs = [
                'saved_card_method' => [
                    'name' => 'saved_card_method',
                    'type' => 'hidden',
                    'value' => $paymentMethod['paymentMethod'],
                ],
                'selectedCreditCard' => [
                    'name' => "selectedCreditCard_{$paymentMethod['paymentMethod']}",
                    'type' => 'hidden',
                    'value' => $selectedCard,
                ],
            ];
            
            if ($isCreditCardSavingEnabled && $isCreditCard && $isBusinessLicenseEnabled) {
                $currentDate = date('Y-m-d h:i:s');

                $savedCards = $cardAliasRep->getSavedValidCardsByUserIdAndPaymentMethod(
                    $this->context->customer->id,
                    $paymentMethod['paymentMethod'],
                    $currentDate
                );

                $this->smarty->assign(
                    [
                        'savedCards' => $savedCards,
                        'paymentMethod' => $paymentMethod['paymentMethod'],
                    ]
                );

                if ($savedCards) {
                    /** Select first card if any are saved **/

                    $inputs['selectedCreditCard'] = [
                        'name' => "selectedCreditCard_{$paymentMethod['paymentMethod']}",
                        'type' => 'hidden',
                        'value' => $savedCards[0]['id_saferpay_card_alias'],
                    ];
                }

                $newOption->setAdditionalInformation(
                    $this->display(__FILE__, 'front/saferpay_additional_info.tpl')
                );
            }

            $inputs['type'] = [
                'name' => 'saferpayPaymentType',
                'type' => 'hidden',
                'value' => $paymentTypeProvider->get($paymentMethod['paymentMethod']),
            ];

            $newOption->setModuleName($this->name)
                ->setCallToActionText($translator->translate($paymentMethodName))
                ->setAction($paymentRedirectionProvider->provideRedirectionLinkByPaymentMethod($paymentMethod['paymentMethod']))
                ->setLogo($imageUrl)
                ->setInputs($inputs);

            $paymentOptions[] = $newOption;
        }

        return $paymentOptions;
    }

    public function hookDisplayAdminOrderTabContent(array $params)
    {
        $isVersionAbove177 = SaferPayConfig::isVersionAbove177();

        return !$isVersionAbove177 ? false : $this->displayInAdminOrderPage($params);
    }


    public function hookDisplayAdminOrder(array $params)
    {
        $isVersionAbove177 = SaferPayConfig::isVersionAbove177();

        return $isVersionAbove177 ? false : $this->displayInAdminOrderPage($params);
    }

    public function hookActionFrontControllerSetMedia()
    {
        /** @var PaymentFormAssetLoader $paymentFormAssetsLoader */
        $paymentFormAssetsLoader = $this->getService(PaymentFormAssetLoader::class);

        $paymentFormAssetsLoader->register($this->context->controller);

        if ($this->context->controller instanceof OrderController) {
            if (SaferPayConfig::isVersion17()) {
                $this->context->controller->registerJavascript(
                    'saved-card',
                    'modules/' . $this->name . '/views/js/front/saferpay_saved_card.js'
                );

                $this->context->controller->addCSS("{$this->getPathUri()}views/css/front/saferpay_checkout.css");
            } else {
                $this->context->controller->addCSS("{$this->getPathUri()}views/css/front/saferpay_checkout_16.css");
                $this->context->controller->addJS("{$this->getPathUri()}views/js/front/saferpay_saved_card_16.js");
                $fieldsLibrary = SaferPayConfig::FIELDS_LIBRARY;
                $configSuffix = SaferPayConfig::getConfigSuffix();
                $this->context->controller->addJs(Configuration::get($fieldsLibrary . $configSuffix));
            }

            /** @var SaferPayErrorDisplayService $errorDisplayService */
            $errorDisplayService = $this->getService(SaferPayErrorDisplayService::class);
            $errorDisplayService->showCookieError('saferpay_payment_canceled_error');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        $isCreditCardSaveEnabled =
            Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);
        if (!$isCreditCardSaveEnabled) {
            return;
        }
        if (SaferPayConfig::isVersion17()) {
            return $this->context->smarty->fetch(
                $this->getLocalPath() . 'views/templates/hook/front/MyAccount.tpl'
            );
        }

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/front/MyAccount_16.tpl'
        );
    }

    public function displayNavigationTop()
    {
        if (SaferPayConfig::isVersion17()) {
            return;
        }

        $menu_tabs = [];

        $menu_tabs[self::ADMIN_SETTINGS_CONTROLLER] = [
            'short' => 'Settings',
            'desc' => $this->l('Settings', $this->name),
            'href' => $this->context->link->getAdminLink(self::ADMIN_SETTINGS_CONTROLLER),
            'active' => false,
            'imgclass' => 'icon-list',
        ];

        $menu_tabs[self::ADMIN_PAYMENTS_CONTROLLER] = [
            'short' => 'Related',
            'desc' => $this->l('Payments', $this->name),
            'href' => $this->context->link->getAdminLink(self::ADMIN_PAYMENTS_CONTROLLER),
            'active' => false,
            'imgclass' => 'icon-cogs',
        ];

        $menu_tabs[self::ADMIN_LOGS_CONTROLLER] = [
            'short' => 'Related',
            'desc' => $this->l('Logs', $this->name),
            'href' => $this->context->link->getAdminLink(self::ADMIN_LOGS_CONTROLLER),
            'active' => false,
            'imgclass' => 'icon-bug',
        ];

        $menu_tabs[self::ADMIN_FIELDS_CONTROLLER] = [
            'short' => 'Fields',
            'desc' => $this->l('Fields', $this->name),
            'href' => $this->context->link->getAdminLink(self::ADMIN_FIELDS_CONTROLLER),
            'active' => false,
            'imgclass' => 'icon-eye',
        ];

        $current_controller = Tools::getValue('controllerUri', 'AdminSaferPaySettings');

        $menu_tabs[$current_controller]['active'] = true;

        $this->context->smarty->assign([
            'menu_tabs' => $menu_tabs,
            'lists_configuration_link' => $this->context->link->getAdminLink($current_controller),
        ]);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/admin/display_nav.tpl'
        );
    }

    /**
     * PS 1.6 display payment options.
     *
     * @param array $params
     *
     * @return string|void
     */
    public function hookDisplayPayment($params)
    {
        if (!$this->active) {
            return;
        }

        /** @var SaferPayCartService $assertService */
        $cartService = $this->getService(SaferPayCartService::class);
        if (!$cartService->isCurrencyAvailable($params['cart'])) {
            return;
        }

        /** @var PaymentRestrictionValidation $paymentRestrictionValidation */
        $paymentRepository = $this->getService(SaferPayPaymentRepository::class);

        /** @var SaferPayObtainPaymentMethods $obtainPaymentMethods */
        $obtainPaymentMethods = $this->getService(SaferPayObtainPaymentMethods::class);
        try {
            $paymentMethods = $obtainPaymentMethods->obtainPaymentMethods();
        } catch (SaferPayApiException $exception) {
            return '';
        }

        $paymentOptions = [];

        $paymentRestrictionValidation = $this->getService(
            PaymentRestrictionValidation::class
        );

        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethod['paymentMethod'] = str_replace(' ', '', $paymentMethod['paymentMethod']);

            if (!$paymentRestrictionValidation->isPaymentMethodValid($paymentMethod['paymentMethod'])) {
                continue;
            }

            $imageUrl = ($paymentRepository->isLogoEnabledByName($paymentMethod['paymentMethod']))
                ? $paymentMethod['logoUrl'] : '';
            $isCreditCard = in_array($paymentMethod['paymentMethod'], SaferPayConfig::TRANSACTION_METHODS);
            $isBusinessLicenseEnabled =
                Configuration::get(
                    SaferPayConfig::BUSINESS_LICENSE
                    . SaferPayConfig::getConfigSuffix()
                );

            /** @var SaferPayCardAliasRepository $cardAliasRep */
            $cardAliasRep = $this->getService(
                SaferPayCardAliasRepository::class
            );

            $displayTpl = 'front/payment.tpl';
            $isCardSaveEnabled = Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);
            $currentDate = date('Y-m-d h:i:s');

            if ($isBusinessLicenseEnabled && $isCreditCard && $isCardSaveEnabled) {
                $savedCards = $cardAliasRep->getSavedValidCardsByUserIdAndPaymentMethod(
                    $this->context->customer->id,
                    $paymentMethod['paymentMethod'],
                    $currentDate
                );

                $this->smarty->assign(
                    [
                        'savedCards' => $savedCards,
                        'paymentMethod' => $paymentMethod['paymentMethod'],
                    ]
                );

                $additionalInfo = $this->display(__FILE__, 'front/saferpay_additional_info.tpl');

                $this->smarty->assign(
                    [
                        'additional_info' => $additionalInfo,
                    ]
                );
                $displayTpl = 'front/payment_with_cards.tpl';
            }

            /** @var PaymentRedirectionProvider $paymentRedirectionProvider */
            $paymentRedirectionProvider = $this->getService(PaymentRedirectionProvider::class);

            /** @var PaymentTypeProvider $paymentTypeProvider */
            $paymentTypeProvider = $this->getService(PaymentTypeProvider::class);

            $this->smarty->assign(
                [
                    'redirect' => $paymentRedirectionProvider->provideRedirectionLinkByPaymentMethod($paymentMethod['paymentMethod']),
                    'imgUrl' => $imageUrl,
                    'method' => $paymentMethod['paymentMethod'],
                    'saferpayPaymentType' => $paymentTypeProvider->get($paymentMethod['paymentMethod']),
                ]
            );

            $paymentOptions[] = $this->display(__FILE__, $displayTpl);
        }

        $this->smarty->assign(
            [
                'payments' => $paymentOptions,
            ]
        );

        return $this->display(__FILE__, 'front/payments.tpl');
    }

    public function hookPaymentReturn()
    {
        if (SaferPayConfig::isVersion17()) {
            return;
        }

        /** @var OrderConfirmationMessageTemplate $OrderConfirmationMessageTemplate */
        $OrderConfirmationMessageTemplate = $this->getService(
            OrderConfirmationMessageTemplate::class
        );
        $OrderConfirmationMessageTemplate->setSmarty($this->context->smarty);

        $orderId = Order::getOrderByCartId(Tools::getValue('id_cart'));
        $order = new Order($orderId);

        if (Tools::getIsset('cancel')) {
            $OrderConfirmationMessageTemplate->setOrderMessageTemplateClass('alert-danger');
            $OrderConfirmationMessageTemplate->setOrderMessageText(
                sprintf($this->l('Your order with reference %s is canceled'), $order->reference)
            );

            return $OrderConfirmationMessageTemplate->getHtml();
        }

        $OrderConfirmationMessageTemplate->setOrderMessageTemplateClass('alert-success');
        $OrderConfirmationMessageTemplate->setOrderMessageText(
            sprintf($this->l('Your order with reference %s has been confirmed'), $order->reference)
        );

        return $OrderConfirmationMessageTemplate->getHtml();
    }

    public function hookActionEmailSendBefore($params)
    {
        if (!isset($params['cart']->id)) {
            return true;
        }
        $cart = new Cart($params['cart']->id);

        /** @var Order $order */
        $order = Order::getByCartId($cart->id);

        if (!$order) {
            return true;
        }

        if ($order->module !== $this->name) {
            return true;
        }

        /** @var CanSendOrderConfirmationEmail $canSendOrderConfirmationEmail */
        $canSendOrderConfirmationEmail = $this->getService(CanSendOrderConfirmationEmail::class);

        if ($params['template'] === 'order_conf') {
            return $canSendOrderConfirmationEmail->verify((int) $order->current_state);
        }

        if ($params['template'] === 'new_order') {
            if ((int) Configuration::get(SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL)) {
                return true;
            }

            return false;
        }
    }

    public function hookActionOrderHistoryAddAfter($params = [])
    {
        /** @var OrderHistory $orderHistory */
        $orderHistory = $params['order_history'];

        if (!$orderHistory instanceof OrderHistory) {
            return;
        }

        $idOrder = (int) $orderHistory->id_order;

        $internalOrder = new Order($idOrder);

        if (!Validate::isLoadedObject($internalOrder)) {
            return;
        }

        $order = new Order($idOrder);

        $orderStatus = new OrderState((int) $order->current_state);

        if (!Validate::isLoadedObject($orderStatus)) {
            return;
        }

        /** @var SaferPayMailService $mailService */
        $mailService = $this->getService(SaferPayMailService::class);

        /** @var CanSendOrderConfirmationEmail $canSendOrderConfirmationEmail */
        $canSendOrderConfirmationEmail = $this->getService(CanSendOrderConfirmationEmail::class);

        if ($canSendOrderConfirmationEmail->verify((int) $orderStatus->id)) {
            try {
                $mailService->sendNewOrderMail($order, (int) $orderStatus->id);
            } catch (Exception $e) {
                // emailalert module sometimes throws error which leads into failed payment issue
            }

            if ((int) Configuration::get(SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED) === (int) $orderStatus->id) {
               $mailService->sendOrderConfMail($order, (int) $orderStatus->id);
            }
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ('AdminOrders' === Tools::getValue('controller') &&
            (Tools::isSubmit('vieworder') || Tools::getValue('action') === 'vieworder')
        ) {
            $this->context->controller->addCSS("{$this->getPathUri()}views/css/admin/saferpay_admin_order.css");

            $orderId = Tools::getValue('id_order');
            $order = new Order($orderId);

            /** @var SaferPayOrderRepository $orderRepo */
            $orderRepo = $this->getService(SaferPayOrderRepository::class);
            $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);
            $saferPayOrder = new SaferPayOrder($saferPayOrderId);

            if ($order->module !== $this->name) {
                return;
            }

            if (!$saferPayOrder->authorized) {
                return;
            }

            if (isset($this->context->cookie->saferPayErrors)) {
                $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
                if (isset($saferPayErrors[$orderId])) {
                    $this->addFlash($saferPayErrors[$orderId], 'error');
                    unset($saferPayErrors[$orderId]);
                    $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);
                }
            }

            if ($this->context->cookie->canceled) {
                $this->addFlash($this->l('Saferpay payment was canceled successfully'), 'success');
                $this->context->cookie->canceled = false;
            }
            if ($this->context->cookie->captured) {
                $this->addFlash($this->l('Saferpay payment was captured successfully'), 'success');
                $this->context->cookie->captured = false;
            }

            if ($this->context->cookie->refunded) {
                if ($saferPayOrder->refunded) {
                    $this->addFlash($this->l('Saferpay full refund was made successfully!'), 'success');
                } else {
                    $this->addFlash($this->l('Saferpay partial refund was made successfully!'), 'success');
                }
                $this->context->cookie->refunded = false;
            }
        }
    }

    private function displayInAdminOrderPage(array $params)
    {
        $orderId = $params['id_order'];
        $order = new Order($orderId);
        /** @var SaferPayOrderRepository $orderRepo */
        $orderRepo = $this->getService(SaferPayOrderRepository::class);
        $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        if ($order->module !== $this->name) {
            return '';
        }

        if (!$saferPayOrder->authorized && !$saferPayOrder->captured) {
            return '';
        }

        if (SaferPayConfig::isVersionAbove177()) {
            $action = $this->context->link->getAdminLink(
                self::ADMIN_ORDER_CONTROLLER,
                true,
                [],
                ['orderId' => $orderId]
            );
        } else {
            $action = $this->context->link->getAdminLink(
                    self::ADMIN_ORDER_CONTROLLER
                ) . '&id_order=' . (int) $orderId;
        }

        $assertId = $orderRepo->getAssertIdBySaferPayOrderId($saferPayOrderId);
        $assertData = new SaferPayAssert($assertId);
        $assertPresenter = new AssertPresenter($this);
        $assertData = $assertPresenter->present($assertData);
        $supported3DsPaymentMethods = SaferPayConfig::SUPPORTED_3DS_PAYMENT_METHODS;

        // Note: This condition check or Payment method supports 3DS.
        // If payment method does not supports 3DS , when we change 'liability_shift'
        // to true , to hide 'failed security check ' message.
        if ($assertData['liability_shift'] === "0"
            && !in_array($assertData['paymentMethod'], $supported3DsPaymentMethods)) {
            $assertData['liability_shift'] = true;
        }

        $this->context->smarty->assign($assertData);

        $currency = new Currency($order->id_currency);
        $adminOrderPagePresenter = new AdminOrderPagePresenter();
        $orderPageData = $adminOrderPagePresenter->present(
            $saferPayOrder,
            $action,
            SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API,
            $currency->sign
        );

        $this->context->smarty->assign($orderPageData);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/admin/saferpay_order.tpl'
        );
    }

    public function addFlash($msg, $type)
    {
        if (SaferPayConfig::isVersionAbove177()) {
            return $this->get('session')->getFlashBag()->add($type, $msg);
        }

        switch ($type) {
            case 'success':
                return $this->context->controller->confirmations[] = $msg;
            case 'error':
                return $this->context->controller->errors[] = $msg;
        }

        return true;
    }
}
