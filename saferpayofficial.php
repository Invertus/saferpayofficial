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
use Invertus\SaferPay\Infrastructure\Hook\Action\AdminControllerSetMediaHook;
use Invertus\SaferPay\Infrastructure\Hook\Action\EmailSendBeforeHook;
use Invertus\SaferPay\Infrastructure\Hook\Action\FrontControllerSetMediaHook;
use Invertus\SaferPay\Infrastructure\Hook\Action\ObjectOrderPaymentAddAfterHook;
use Invertus\SaferPay\Infrastructure\Hook\Display\AdminOrderHook;
use Invertus\SaferPay\Infrastructure\Hook\Display\CustomerAccountHook;
use Invertus\SaferPay\Infrastructure\Hook\Display\OrderConfirmationHook;
use Invertus\SaferPay\Infrastructure\Hook\Display\PaymentOptionsHook;
use Invertus\SaferPay\Install\Installer;
use Invertus\SaferPay\Install\Uninstaller;
use Invertus\SaferPay\ServiceProvider\LeagueServiceContainerProvider;
use Invertus\SaferPay\Utility\VersionUtility;

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

    /**
     * @var LeagueServiceContainerProvider|null
     */
    private $containerProvider;

    public function __construct($name = null)
    {
        $this->name = 'saferpayofficial';
        $this->author = 'Invertus';
        $this->version = '2.0.2';
        $this->module_key = '3d3506c3e184a1fe63b936b82bda1bdf';
        $this->displayName = 'SaferpayOfficial';
        $this->description = 'Saferpay Payment module';
        $this->tab = 'payments_gateways';
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.1',
            'max' => _PS_VERSION_,
        ];
        parent::__construct($name);

        $this->autoload();
        $this->loadConfig();
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_SETTINGS_CONTROLLER));
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installer = new Installer($this);

        return $installer->install();
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

    /**
     * Get a service from the container.
     *
     * @param string $service
     * @return mixed
     */
    public function getService($service)
    {
        if (null === $this->containerProvider) {
            $this->containerProvider = new LeagueServiceContainerProvider();
        }

        return $this->containerProvider->getService($service);
    }

    /**
     * Hook: Display order confirmation message
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayOrderConfirmation($params)
    {
        /** @var OrderConfirmationHook $hook */
        $hook = $this->getService(OrderConfirmationHook::class);

        return $hook->handle($params);
    }

    /**
     * Hook: Update payment method name after order payment is added
     *
     * @param array $params
     */
    public function hookActionObjectOrderPaymentAddAfter($params)
    {
        /** @var ObjectOrderPaymentAddAfterHook $hook */
        $hook = $this->getService(ObjectOrderPaymentAddAfterHook::class);

        $hook->handle($params);
    }

    /**
     * Hook: Provide payment options on checkout page
     *
     * @param array $params
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        /** @var PaymentOptionsHook $hook */
        $hook = $this->getService(PaymentOptionsHook::class);

        return $hook->handle($params);
    }

    /**
     * Hook: Display admin order tab content (PS 1.7.7+)
     *
     * @param array $params
     * @return string|bool
     */
    public function hookDisplayAdminOrderTabContent(array $params)
    {
        if (!SaferPayConfig::isVersionAbove177()) {
            return false;
        }

        /** @var AdminOrderHook $hook */
        $hook = $this->getService(AdminOrderHook::class);

        return $hook->handle($params);
    }

    /**
     * Hook: Display admin order (Pre-1.7.7)
     *
     * @param array $params
     * @return string|bool
     */
    public function hookDisplayAdminOrder(array $params)
    {
        if (SaferPayConfig::isVersionAbove177()) {
            return false;
        }

        /** @var AdminOrderHook $hook */
        $hook = $this->getService(AdminOrderHook::class);

        return $hook->handle($params);
    }

    /**
     * Hook: Register front controller media (JS/CSS)
     *
     * @param array $params
     */
    public function hookActionFrontControllerSetMedia()
    {
        /** @var FrontControllerSetMediaHook $hook */
        $hook = $this->getService(FrontControllerSetMediaHook::class);

        $hook->handle([]);
    }

    /**
     * Hook: Display saved credit cards link in customer account
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        /** @var CustomerAccountHook $hook */
        $hook = $this->getService(CustomerAccountHook::class);

        return $hook->handle([]);
    }

    /**
     * Hook: Control email sending logic
     *
     * @param array $params
     * @return bool
     */
    public function hookActionEmailSendBefore($params)
    {
        /** @var EmailSendBeforeHook $hook */
        $hook = $this->getService(EmailSendBeforeHook::class);

        return $hook->handle($params);
    }

    /**
     * Hook: Register admin controller media and handle flash messages
     *
     * @param array $params
     */
    public function hookActionAdminControllerSetMedia()
    {
        /** @var AdminControllerSetMediaHook $hook */
        $hook = $this->getService(AdminControllerSetMediaHook::class);

        $hook->handle([]);
    }

    /**
     * Add flash message (for backward compatibility)
     *
     * @param string $msg
     * @param string $type
     * @return bool
     */
    public function addFlash($msg, $type)
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.7.0')
            && VersionUtility::isPsVersionLessThan('9.0.0')
        ) {
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
