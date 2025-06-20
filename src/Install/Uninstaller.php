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
use Invertus\SaferPay\Config\SaferPayConfig;
use Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Uninstaller extends AbstractInstaller
{
    private $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public function uninstall()
    {
        foreach (SaferPayConfig::getUninstallConfiguration() as $configuration) {
            if (!Configuration::deleteByName($configuration)) {
                $this->errors[] = $this->module->l('Failed to uninstall configuration', __CLASS__);
                return false;
            }
        }

        foreach ($this->getCommands() as $tableName => $command) {
            if (false === \Db::getInstance()->execute($command)) {
                $this->errors[] = sprintf($this->module->l('Failed to uninstall database table [%s]', __CLASS__), $tableName);
                return false;
            }
        }

        return true;
    }

    private function getCommands()
    {
        return [
            \SaferPayPayment::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayPayment::$definition['table']) . '`;',
            \SaferPayLogo::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayLogo::$definition['table']) . '`;',
            \SaferPayCountry::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayCountry::$definition['table']) . '`;',
            \SaferPayCurrency::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayCurrency::$definition['table']) . '`;',
            \SaferPayOrder::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayOrder::$definition['table']) . '`;',
            \SaferPayAssert::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayAssert::$definition['table']) . '`;',
            \SaferPayCardAlias::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayCardAlias::$definition['table']) . '`;',
            \SaferPayLog::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayLog::$definition['table']) . '`;',
            \SaferPayField::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayField::$definition['table']) . '`;',
            \SaferPayOrderRefund::$definition['table'] => 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . bqSQL(\SaferPayOrderRefund::$definition['table']) . '`;',
        ];
    }

    private function uninstallTabs()
    {
        $tabs = $this->tabs();

        foreach ($tabs as $tab) {
            $idTab = Tab::getIdFromClassName($tab['class_name']);

            if (!$idTab) {
                continue;
            }

            $tab = new Tab($idTab);
            if (!$tab->delete()) {
                return false;
            }
        }

        return true;
    }
}
