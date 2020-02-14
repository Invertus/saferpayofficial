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

use SaferPayOfficial;

abstract class AbstractInstaller
{
    /**
     * @var SaferPayOfficial
     */
    protected $module;

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
                'ParentClassName' => 'AdminParentPayment',
                'visible' => false,
            ],
            [
                'name' => $this->module->l('Settings'),
                'class_name' => SaferPayOfficial::ADMIN_SETTINGS_CONTROLLER,
                'ParentClassName' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
            ],
            [
                'name' => $this->module->l('Payments'),
                'class_name' => SaferPayOfficial::ADMIN_PAYMENTS_CONTROLLER,
                'ParentClassName' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
            ],
            [
                'name' => $this->module->l('Order'),
                'class_name' => SaferPayOfficial::ADMIN_ORDER_CONTROLLER,
                'ParentClassName' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
                'visible' => false,
            ],
            [
                'name' => $this->module->l('Logs'),
                'class_name' => SaferPayOfficial::ADMIN_LOGS_CONTROLLER,
                'ParentClassName' => SaferPayOfficial::ADMIN_SAFERPAY_MODULE_CONTROLLER,
                'module_tab' => true,
            ],
        ];
    }
}
