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

class SaferPayOfficial extends PaymentModule
{
    const ADMIN_SAFERPAY_MODULE_CONTROLLER = 'AdminSaferPayOfficialModule';
    const ADMIN_SETTINGS_CONTROLLER = 'AdminSaferPayOfficialSettings';
    const ADMIN_PAYMENTS_CONTROLLER = 'AdminSaferPayOfficialPayment';
    const ADMIN_ORDER_CONTROLLER = 'AdminSaferPayOfficialOrder';
    const ADMIN_LOGS_CONTROLLER = 'AdminSaferPayOfficialLogs';

    const DISABLE_CACHE = true;

    const IS_BUSINESS_LICENCE = 'isBusinessLicence';

    /**
     * Symfony DI Container
     **/
    private $moduleContainer;

    public function __construct($name = null)
    {
        $this->name = 'saferpayofficial';
        $this->author = 'Invertus';
        $this->version = '1.0.2';
        $this->module_key = '3d3506c3e184a1fe63b936b82bda1bdf';
        $this->displayName = 'SaferpayOfficial';
        $this->description = 'Saferpay Payment module';
        $this->tab = 'payments_gateways';

        parent::__construct($name);

        $this->autoload();
        $this->loadConfig();
        $this->compile();
    }

    public function getContent()
    {
        if (Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::USERNAME)) {
            Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_PAYMENTS_CONTROLLER));
        }
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_SETTINGS_CONTROLLER));
    }

    public function install()
    {
        $installer = new \Invertus\SaferPay\Install\Installer($this);

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
        $uninstaller = new \Invertus\SaferPay\Install\Uninstaller($this);
        if (!$uninstaller->uninstall()) {
            $this->_errors += $uninstaller->getErrors();
            return false;
        }
        return parent::uninstall();
    }

    public function getTabs()
    {
        $installer = new \Invertus\SaferPay\Install\Installer($this);

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

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->moduleContainer;
    }

    private function compile()
    {
        $containerCache = $this->getLocalPath() . 'var/cache/container.php';
        $containerConfigCache = new \Symfony\Component\Config\ConfigCache($containerCache, self::DISABLE_CACHE);
        $containerClass = get_class($this) . 'Container';
        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
            $locator = new \Symfony\Component\Config\FileLocator($this->getLocalPath() . 'config');
            $loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($containerBuilder, $locator);
            $loader->load('config.yml');
            $containerBuilder->compile();
            $dumper = new \Symfony\Component\DependencyInjection\Dumper\PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => $containerClass]),
                $containerBuilder->getResources()
            );
        }
        require_once $containerCache;
        $this->moduleContainer = new $containerClass();
    }

    public function hookPaymentOptions($params)
    {
        /** @var SaferPayCartService $assertService */
        $cartService = $this->getContainer()->get(\Invertus\SaferPay\Service\SaferPayCartService::class);
        if (!$cartService->isCurrencyAvailable($params['cart'])) {
            return;
        }

        $paymentOptions = [];

        /** @var SaferPayPaymentRepository $paymentRepository */
        /** @var PaymentRestrictionValidation $paymentRestrictionValidation */
        $paymentRepository = $this->getContainer()->get(\Invertus\SaferPay\Repository\SaferPayPaymentRepository::class);
        $paymentRestrictionValidation = $this->getContainer()->get(
            \Invertus\SaferPay\Service\PaymentRestrictionValidation::class
        );
        $countryId = $this->context->country->id;
        $currencyId = $this->context->currency->id;
        foreach (\Invertus\SaferPay\Config\SaferPayConfig::PAYMENT_METHODS as $paymentMethod) {
            $isValid = $paymentRestrictionValidation->isPaymentMethodValid($paymentMethod, $countryId, $currencyId);
            if (!$isValid) {
                continue;
            }
            $imageUrl = '';
            if ($paymentRepository->isLogoEnabledByName($paymentMethod)) {
                $imageUrl = "{$this->getPathUri()}views/img/{$paymentMethod}.png";
            }
            $isCreditCard = false;
            if (in_array($paymentMethod, \Invertus\SaferPay\Config\SaferPayConfig::TRANSACTION_METHODS)) {
                $isCreditCard = true;
            }
            $isBusinessLicenseEnabled =
                Configuration::get(
                    \Invertus\SaferPay\Config\SaferPayConfig::BUSINESS_LICENSE
                    . \Invertus\SaferPay\Config\SaferPayConfig::getConfigSuffix()
                );

            $redirect = $this->context->link->getModuleLink(
                $this->name,
                'validation',
                ['saved_card_method' => $paymentMethod, self::IS_BUSINESS_LICENCE => false],
                true
            );
            if ($isBusinessLicenseEnabled) {
                if ($isCreditCard) {
                    $redirect = $this->context->link->getModuleLink(
                        $this->name,
                        'iframe',
                        ['saved_card_method' => $paymentMethod, self::IS_BUSINESS_LICENCE => true],
                        true
                    );
                }
            }
            /** @var \Invertus\SaferPay\Repository\SaferPayCardAliasRepository $cardAliasRep */
            $cardAliasRep = $this->getContainer()->get(
                \Invertus\SaferPay\Repository\SaferPayCardAliasRepository::class
            );
            $isCreditCardSavingEnabled = Configuration::get(
                \Invertus\SaferPay\Config\SaferPayConfig::CREDIT_CARD_SAVE
            );
            $selectedCard = 0;
            if ($this->context->customer->is_guest) {
                $isCreditCardSavingEnabled = false;
                $selectedCard = -1;
            }

            $newOption = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans($paymentMethod, [], 'Modules.saferpay.Shop'))
                ->setAction($redirect)
                ->setLogo($imageUrl)
                ->setInputs(
                    [
                        'saved_card_method' => [
                            'name' => 'saved_card_method',
                            'type' => 'hidden',
                            'value' => $paymentMethod,
                        ],
                        'selectedCreditCard' => [
                            'name' => "selectedCreditCard_{$paymentMethod}",
                            'type' => 'hidden',
                            'value' => $selectedCard,
                        ],
                    ]
                );
            $currentDate = date('Y-m-d h:i:s');
            if ($isCreditCardSavingEnabled && $isCreditCard && $isBusinessLicenseEnabled) {
                $savedCards = $cardAliasRep->getSavedValidCardsByUserIdAndPaymentMethod(
                    $this->context->customer->id,
                    $paymentMethod,
                    $currentDate
                );

                $this->smarty->assign(
                    [
                        'savedCards' => $savedCards,
                        'paymentMethod' => $paymentMethod,
                    ]
                );

                if ($savedCards) {
                    /** Select first card if any are saved **/
                    $newOption->setInputs(
                        [
                            'selectedCreditCard' => [
                                'name' => "selectedCreditCard_{$paymentMethod}",
                                'type' => 'hidden',
                                'value' => $savedCards[0]['id_saferpay_card_alias'],
                            ],
                        ]
                    );
                }

                $newOption->setAdditionalInformation(
                    $this->display(__FILE__, 'front/saferpay_additional_info.tpl')
                );
            }
            $paymentOptions[] = $newOption;
        }

        return $paymentOptions;
    }

    public function hookDisplayAdminOrder(array $params)
    {
        $this->context->controller->addCSS("{$this->getPathUri()}views/css/admin/saferpay_admin_order.css");
        $orderId = $params['id_order'];
        $order = new Order($orderId);

        if (isset($this->context->cookie->saferPayErrors)) {
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            if (isset($saferPayErrors[$orderId])) {
                $this->context->controller->errors[] = $saferPayErrors[$orderId];
                unset($saferPayErrors[$orderId]);
                $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);
            }
        }

        /** @var \Invertus\SaferPay\Repository\SaferPayOrderRepository $orderRepo */
        $orderRepo = $this->getContainer()->get(\Invertus\SaferPay\Repository\SaferPayOrderRepository::class);
        $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        if ($order->module !== $this->name) {
            return;
        }
        if (!$saferPayOrder->authorized) {
            return;
        }
        if ($this->context->cookie->canceled) {
            $this->context->controller->confirmations[] = $this->l('Saferpay payment was canceled successfully');
            $this->context->cookie->canceled = false;
        }
        if ($this->context->cookie->captured) {
            $this->context->controller->confirmations[] = $this->l('Saferpay payment was captured successfully');
            $this->context->cookie->captured = false;
        }
        if ($this->context->cookie->refunded) {
            if ($saferPayOrder->refunded) {
                $this->context->controller->confirmations[] = $this->l('Saferpay full refund was made successfully!');
            } else {
                $this->context->controller->confirmations[] = $this->l('Saferpay partial refund was made successfully!');
            }
            $this->context->cookie->refunded = false;
        }
        $action = $this->context->link->getAdminLink(self::ADMIN_ORDER_CONTROLLER) . '&id_order=' . (int) $orderId;

        $assertId = $orderRepo->getAssertIdBySaferPayOrderId($saferPayOrderId);
        $assertData = new SaferPayAssert($assertId);
        $assertPresenter = new \Invertus\SaferPay\Presenter\AssertPresenter($this);
        $assertData = $assertPresenter->present($assertData);
        $this->context->smarty->assign($assertData);

        $adminOrderPagePresenter = new \Invertus\SaferPay\Presenter\AdminOrderPagePresenter();
        $orderPageData = $adminOrderPagePresenter->present(
            $saferPayOrder,
            $action,
            \Invertus\SaferPay\Config\SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API,
            $this->context->currency->sign
        );
        $this->context->smarty->assign($orderPageData);

        return $this->context->smarty->fetch(
            $this->getLocalPath() . 'views/templates/hook/admin/saferpay_order.tpl'
        );
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof OrderController) {
            if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
                $this->context->controller->addJS("{$this->getPathUri()}views/js/front/saferpay_saved_card.js");
                $this->context->controller->addCSS("{$this->getPathUri()}views/css/front/saferpay_checkout.css");
            } else {
                $this->context->controller->addCSS("{$this->getPathUri()}views/css/front/saferpay_checkout_16.css");
                $this->context->controller->addJS("{$this->getPathUri()}views/js/front/saferpay_saved_card_16.js");
            }
            /** @var \Invertus\SaferPay\Service\SaferPayErrorDisplayService $errorDisplayService */
            $errorDisplayService = $this->getContainer()->get(\Invertus\SaferPay\Service\SaferPayErrorDisplayService::class);
            $errorDisplayService->showCookieError('saferpay_payment_canceled_error');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        $isCreditCardSaveEnabled =
            Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::CREDIT_CARD_SAVE);
        if (!$isCreditCardSaveEnabled) {
            return;
        }
        if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
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
        if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
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

    public function hookDisplayPayment($params)
    {
        if (!$this->active) {
            return;
        }

        /** @var SaferPayCartService $assertService */
        $cartService = $this->getContainer()->get(\Invertus\SaferPay\Service\SaferPayCartService::class);
        if (!$cartService->isCurrencyAvailable($params['cart'])) {
            return;
        }

        $paymentOptions = [];

        /** @var \Invertus\SaferPay\Repository\SaferPayPaymentRepository: $paymentRepository */
        /** @var \Invertus\SaferPay\Service\PaymentRestrictionValidation $paymentRestrictionValidation */
        $paymentRepository = $this->getContainer()->get(\Invertus\SaferPay\Repository\SaferPayPaymentRepository::class);
        $paymentRestrictionValidation = $this->getContainer()->get(
            \Invertus\SaferPay\Service\PaymentRestrictionValidation::class
        );
        $countryId = $this->context->country->id;
        $currencyId = $this->context->currency->id;
        foreach (\Invertus\SaferPay\Config\SaferPayConfig::PAYMENT_METHODS as $paymentMethod) {
            $isValid = $paymentRestrictionValidation->isPaymentMethodValid($paymentMethod, $countryId, $currencyId);
            if (!$isValid) {
                continue;
            }
            $imageUrl = '';
            if ($paymentRepository->isLogoEnabledByName($paymentMethod)) {
                $imageUrl = "{$this->getPathUri()}views/img/{$paymentMethod}.png";
            }
            $isCreditCard = false;
            if (in_array($paymentMethod, \Invertus\SaferPay\Config\SaferPayConfig::TRANSACTION_METHODS)) {
                $isCreditCard = true;
            }
            $isBusinessLicenseEnabled =
                Configuration::get(
                    \Invertus\SaferPay\Config\SaferPayConfig::BUSINESS_LICENSE
                    . \Invertus\SaferPay\Config\SaferPayConfig::getConfigSuffix()
                );

            $redirect = $this->context->link->getModuleLink(
                $this->name,
                'validation',
                ['saved_card_method' => $paymentMethod, self::IS_BUSINESS_LICENCE => false],
                true
            );
            $displayTpl = 'front/payment.tpl';
            $isCardSaveEnabled = Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::CREDIT_CARD_SAVE);
            if ($isBusinessLicenseEnabled) {
                if ($isCreditCard) {
                    $redirect = $this->context->link->getModuleLink(
                        $this->name,
                        'iframe',
                        ['saved_card_method' => $paymentMethod, self::IS_BUSINESS_LICENCE => true],
                        true
                    );
                    if ($isCardSaveEnabled) {
                        /** @var \Invertus\SaferPay\Repository\SaferPayCardAliasRepository $cardAliasRep */
                        $cardAliasRep = $this->getContainer()->get(
                            \Invertus\SaferPay\Repository\SaferPayCardAliasRepository::class
                        );
                        $currentDate = date('Y-m-d h:i:s');

                        $savedCards = $cardAliasRep->getSavedValidCardsByUserIdAndPaymentMethod(
                            $this->context->customer->id,
                            $paymentMethod,
                            $currentDate
                        );

                        $this->smarty->assign(
                            [
                                'savedCards' => $savedCards,
                                'paymentMethod' => $paymentMethod,
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
                }
            }

            $this->smarty->assign(
                [
                    'redirect' => $redirect,
                    'imgUrl' => $imageUrl,
                    'method' => $paymentMethod,
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
        if (\Invertus\SaferPay\Config\SaferPayConfig::isVersion17()) {
            return;
        }
        /**
         * @var \Invertus\SaferPay\Builder\OrderConfirmationMessageTemplate $OrderConfirmationMessageTemplate
         */
        $OrderConfirmationMessageTemplate = $this->getContainer()->get(
            \Invertus\SaferPay\Builder\OrderConfirmationMessageTemplate::class
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
        if (Order::getByCartId($cart->id)->module !== $this->name) {
            return true;
        }

        if ($params['template'] === 'order_conf') {
            if (Configuration::get(\Invertus\SaferPay\Config\SaferPayConfig::SAFERPAY_SEND_ORDER_CONFIRMATION)) {
                return true;
            }
            return false;
        }
    }
}
