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

namespace Invertus\SaferPay\Install;

use Configuration;
use Context;
use Db;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Language;
use OrderState;
use Tab;

/**
 * Class SaferPayInstaller
 */
class Installer extends AbstractInstaller
{
    private $errors = [];


    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Install controllers, hooks, database & etc.
     *
     * @return bool
     */
    public function install()
    {
        $this->registerHooks();

        if (!$this->installConfiguration()) {
            $this->errors[] = $this->module->l('Failed to install configuration', __CLASS__);
            return false;
        }

        if (!$this->processDatabase()) {
            $this->errors[] = $this->module->l('Failed to install database', __CLASS__);
            return false;
        }

        if (!$this->createAllOrderStatus()) {
            $this->errors[] = $this->module->l('Failed to create order status', __CLASS__);
            return false;
        }

        if (!SaferPayConfig::isVersion17()) {
            if (!$this->installTabs()) {
                $this->errors[] = $this->module->l('Failed to install tabs', __CLASS__);
                return false;
            }
        }

        return true;
    }

    private function registerHooks()
    {
        $this->module->registerHook('paymentOptions');
        $this->module->registerHook('displayPayment');
        $this->module->registerHook('displayAdminOrder');
        $this->module->registerHook('actionFrontControllerSetMedia');
        $this->module->registerHook('displayCustomerAccount');
        $this->module->registerHook('displayPayment');
        $this->module->registerHook('paymentReturn');
        $this->module->registerHook('actionEmailSendBefore');
        $this->module->registerHook('displayAdminOrderTabContent');
        $this->module->registerHook('actionAdminControllerSetMedia');
    }

    private function installConfiguration()
    {
        $configuration = SaferPayConfig::getDefaultConfiguration();

        foreach ($configuration as $name => $value) {
            if (!Configuration::updateValue($name, $value, false, 0, 0)) {
                return false;
            }
        }

        return true;
    }

    private function installTabs()
    {
        $tabs = $this->tabs();

        foreach ($tabs as $tab) {
            if (Tab::getIdFromClassName($tab['class_name'])) {
                continue;
            }

            if (!$this->installTab($tab['class_name'], $tab['ParentClassName'], $tab['name'])) {
                return false;
            }
        }

        return true;
    }

    public function installTab($className, $parent, $name)
    {
        $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

        $moduleTab = new Tab();
        $moduleTab->class_name = $className;
        $moduleTab->id_parent = $idParent;
        $moduleTab->module = $this->module->name;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $moduleTab->name[$language['id_lang']] = $name;
        }

        if (!$moduleTab->save()) {
            return false;
        }

        return true;
    }

    public function processDatabase()
    {
        return $this->installSaferPayPaymentTable() &&
            $this->installSaferPayLogoTable() &&
            $this->installSaferPayCountryTable() &&
            $this->installSaferPayCurrencyTable() &&
            $this->installSaferPayOrderTable() &&
            $this->installSaferPayAssertTable() &&
            $this->installSaferPayCardAlias() &&
            $this->installSaferPayLog() &&
            $this->installSaferPayFieldTable() &&
            $this->installOrderRefundTable()
            ;
    }

    private function installSaferPayPaymentTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_payment' . '(
            `id_saferpay_payment` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(64) NOT NULL,
            `active` tinyint(1) DEFAULT 0,
            UNIQUE (`name`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayLogoTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_logo' . '(
            `id_saferpay_logo` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(64) NOT NULL,
            `active` tinyint(1) DEFAULT 0,
            UNIQUE (`name`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayCountryTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_country' . '(
            `id_saferpay_country` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `payment_name` VARCHAR(64) NOT NULL,
            `id_country` int(16) DEFAULT 0,
            `all_countries` tinyint(1) DEFAULT 0
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayFieldTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_field' . '(
            `id_saferpay_field` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(64) NOT NULL,
            `active` tinyint(1) DEFAULT 0,
            UNIQUE (`name`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayCurrencyTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_currency' . '(
            `id_saferpay_currency` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `payment_name` VARCHAR(64) NOT NULL,
            `id_currency` int(16) DEFAULT 0,
            `all_currencies` tinyint(1) DEFAULT 0
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayOrderTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_order' . '(
            `id_saferpay_order` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_order` INTEGER(10) DEFAULT 0,
            `id_customer` INTEGER(10) DEFAULT 0,
            `transaction_id` VARCHAR(64) DEFAULT NULL,
            `refund_id` VARCHAR(64) DEFAULT NULL,
            `token` VARCHAR(64) NOT NULL,
            `redirect_url` VARCHAR(128) NOT NULL,
            `is_transaction` tinyint(1) NOT NULL,
            `captured` tinyint(1) DEFAULT 0,
            `refunded` tinyint(1) DEFAULT 0,
            `canceled` tinyint(1) DEFAULT 0,
            `authorized` tinyint(1) DEFAULT 0
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayAssertTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_assert' . '(
            `id_saferpay_assert` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_saferpay_order` INTEGER(10) DEFAULT 0,
            `amount` INTEGER(10) DEFAULT 0,
            `refunded_amount` INTEGER(10) DEFAULT 0,
            `pending_refund_amount` INTEGER(10) DEFAULT 0,
            `status` VARCHAR(64) NOT NULL,
            `exp_year` INTEGER(10) NOT NULL,
            `exp_month` INTEGER(10) NOT NULL,
            `currency_code` VARCHAR(64) NOT NULL,
            `uncertain` tinyint(1) DEFAULT 0,
            `brand` VARCHAR(64) NOT NULL,
            `payment_method` VARCHAR(64) NOT NULL,
            `transaction_paid` VARCHAR(64) NOT NULL,
            `merchant_reference` VARCHAR(64) NOT NULL,
            `payment_id` VARCHAR(64) NOT NULL,
            `acceptance` VARCHAR(64) NOT NULL,
            `is_test` VARCHAR(64) NOT NULL,
            `liability_shift` tinyint(1) DEFAULT 0,
            `liability_entity` VARCHAR(64) NOT NULL,
            `card_number` VARCHAR(64) NOT NULL,
            `dcc_value` INTEGER(32) DEFAULT NULL,
            `dcc_currency_code` VARCHAR(64) DEFAULT NULL,
            `authorized` tinyint(1) DEFAULT 0
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayCardAlias()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_card_alias' . '(
            `id_saferpay_card_alias` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_customer` INTEGER(10) DEFAULT 0,
            `success`  tinyint(1) DEFAULT 0,
            `alias_id` VARCHAR(64) NOT NULL,
            `lifetime` INTEGER(10) DEFAULT 0,
            `card_number` VARCHAR(64) NOT NULL,
            `payment_method` VARCHAR(64) NOT NULL,
            `valid_till` datetime NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installSaferPayLog()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_log' . '(
            `id_saferpay_log` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `message` TEXT NOT NULL,
            `payload` TEXT NOT NULL,
            `date_add` datetime NOT NULL
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    private function installOrderRefundTable()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'saferpay_order_refund' . '(
            `id_saferpay_order_refund` INTEGER(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_saferpay_order` INTEGER(10) DEFAULT 0,
            `id_order` INTEGER(10) DEFAULT 0,
            `transaction_id` VARCHAR(64) NOT NULL,
            `amount` INTEGER(20) NOT NULL,
            `currency` VARCHAR(64) NOT NULL,
            `status` VARCHAR(64) NOT NULL
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    public function createAllOrderStatus()
    {
        $success = true;
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_COMPLETED,
            'Payment completed by Saferpay',
            '#32cd31',
            true,
            true,
            true,
            true,
            false,
            'payment'
        );
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZED,
            'Payment authorized by Saferpay',
            '#4069e1',
            false,
            true,
            true
        );
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_REJECTED,
            'Payment rejected by Saferpay',
            '#8f0821',
            false,
            false,
            false,
            true,
            false,
            'payment_error'
        );
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_AWAITING,
            'Awaiting Saferpay payment',
            '#4069e1'
        );
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_REFUNDED,
            'Order Refunded by Saferpay',
            '#dc143c',
            false,
            false,
            true,
            true,
            false,
            'refund'
        );

        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_PARTLY_REFUNDED,
            'Order Partly Refunded by Saferpay',
            '#FFDD99'
        );

        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_PENDING_REFUND,
            'Order Pending Refund by Saferpay',
            '#ec730a'
        );

        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_CANCELED,
            'Order Canceled by Saferpay',
            '#8f0821',
            false,
            false,
            false,
            true,
            false,
            'order_canceled'
        );
        $success &= $this->createOrderStatus(
            SaferPayConfig::SAFERPAY_PAYMENT_AUTHORIZATION_FAILED,
            'Order authorization failed by Saferpay',
            '#8f0821',
            false,
            false,
            false,
            true,
            false,
            'payment_error'
        );

        return $success;
    }

    private function createOrderStatus(
        $configName,
        $name,
        $color,
        $paid = false,
        $logable = false,
        $invoice = false,
        $sendEmail = false,
        $hidden = false,
        $template = ''
    ) {
        $stateExists = false;
        $langId = Context::getContext()->language->id;
        $states = OrderState::getOrderStates((int) $langId);
        foreach ($states as $state) {
            if ($this->module->l($name) === $state['name']) {
                Configuration::updateValue($configName, (int) $state[OrderState::$definition['primary']]);
                $stateExists = true;
                break;
            }
        }
        if (!$stateExists) {
            $orderState = new OrderState();
            $orderState->send_email = $sendEmail;
            $orderState->color = $color;
            $orderState->hidden = $hidden;
            $orderState->delivery = false;
            $orderState->logable = $logable;
            $orderState->invoice = $invoice;
            $orderState->paid = $paid;
            $orderState->module_name = $this->module->name;
            $orderState->name = [];
            $orderState->template = $template;
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $orderState->name[$language['id_lang']] = $this->module->l($name);
            }
            if (!$orderState->add()) {
                return false;
            }
            Configuration::updateValue($configName, (int) $orderState->id);

            $imagePath = "{$this->module->getLocalPath()}views/img/state/{$configName}.gif";
            $destination = _PS_ORDER_STATE_IMG_DIR_ . $orderState->id . '.gif';
            \Tools::copy($imagePath, $destination);
        }

        return true;
    }
}
