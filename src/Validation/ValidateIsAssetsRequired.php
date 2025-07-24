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

namespace Invertus\SaferPay\Validation;

use Invertus\SaferPay\Provider\OpcModulesProvider;
use FrontController;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ValidateIsAssetsRequired
{
    private $opcModulesProvider;

    public function __construct(OpcModulesProvider $opcModulesProvider)
    {
        $this->opcModulesProvider = $opcModulesProvider;
    }

    /**
     * It returns true if it's an OPC controller or an OrderController with products in the cart. Otherwise, it returns false.
     */
    public function run(FrontController $controller)
    {
        $isOrderController = $controller instanceof \OrderControllerCore
            || $controller instanceof \ModuleFrontController && isset($controller->php_self) && $controller->php_self === 'order';

        if (!empty($this->opcModulesProvider->get())) {
            return $isOrderController && !empty(\Context::getContext()->cart->getProducts());
        }

        return true;
    }
}
