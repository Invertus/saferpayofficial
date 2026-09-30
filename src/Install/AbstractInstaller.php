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

use Language;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class AbstractInstaller
{
    /**
     * @var SaferPayOfficial
     */
    protected $module;

    /**
     * @var array|null
     */
    private $dictionaries;

    public function __construct(SaferPayOfficial $module)
    {
        $this->module = $module;
    }

    public function tabs()
    {
        return [
            [
                'name' => $this->module->displayName,
                'class_name' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'parent_class_name' => 'AdminParentPayment',
                'visible' => false,
            ],
            [
                'name' => $this->tabName('Settings'),
                'class_name' => SaferPayOfficial::ADMIN_SETTINGS_CONTROLLER,
                'parent_class_name' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
            ],
            [
                'name' => $this->tabName('Payments'),
                'class_name' => SaferPayOfficial::ADMIN_PAYMENTS_CONTROLLER,
                'parent_class_name' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
                'visible' => false,
            ],
            [
                'name' => $this->tabName('Order'),
                'class_name' => SaferPayOfficial::ADMIN_ORDER_CONTROLLER,
                'parent_class_name' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
                'visible' => false,
            ],
            [
                'name' => $this->tabName('Logs'),
                'class_name' => SaferPayOfficial::ADMIN_LOGS_CONTROLLER,
                'parent_class_name' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
            ],
        ];
    }

    /**
     * A tab name is stored once per language, so every language has to be resolved while the tab
     * is created instead of once in the language of the employee installing the module.
     *
     * Module::l() cannot do that here. getModuleTranslation() merges every dictionary it loads
     * into one global $_MODULES, and the keys are identical in all languages, so the file loaded
     * last wins. A language the module ships no wording for would then take the previous
     * language's value instead of falling back to English. Reading the dictionaries keeps every
     * language independent of that load order.
     *
     * English stays first in the returned map because ModuleTabRegister::getTabNames() falls back
     * to the first entry for any language missing from it.
     *
     * @param string $source
     *
     * @return array
     */
    protected function tabName($source)
    {
        $names = ['en' => $source];
        $key = '<{' . $this->module->name . '}prestashop>' . $this->module->name . '_' . md5($source);

        foreach ($this->getDictionaries() as $iso => $dictionary) {
            if (empty($dictionary[$key])) {
                continue;
            }

            $names[$iso] = $dictionary[$key];
        }

        return $names;
    }

    /**
     * @return array
     */
    private function getDictionaries()
    {
        if ($this->dictionaries !== null) {
            return $this->dictionaries;
        }

        $this->dictionaries = [];
        $current = isset($GLOBALS['_MODULE']) ? $GLOBALS['_MODULE'] : [];

        foreach (Language::getLanguages(false) as $language) {
            $file = _PS_MODULE_DIR_ . $this->module->name . '/translations/' . $language['iso_code'] . '.php';

            if (!is_readable($file)) {
                continue;
            }

            $GLOBALS['_MODULE'] = [];
            include $file;
            $this->dictionaries[$language['iso_code']] = $GLOBALS['_MODULE'];
        }

        $GLOBALS['_MODULE'] = $current;

        return $this->dictionaries;
    }
}
