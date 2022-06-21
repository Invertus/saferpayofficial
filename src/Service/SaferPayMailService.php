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

namespace Invertus\SaferPay\Service;

use Context;
use Invertus\SaferPay\Config\SaferPayConfig;
use Module;
use Order;
use OrderState;

class SaferPayMailService
{
    /** @var Context|null */
    private $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * @param Order $order
     * @param int $orderStateId
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function sendNewOrderMail(Order $order, $orderStateId)
    {
        if (!Module::isEnabled(SaferPayConfig::EMAIL_ALERTS_MODULE_NAME)) {
            return;
        }
        $customer = $order->getCustomer();

        /** @var \Ps_EmailAlerts $emailAlertsModule */
        $emailAlertsModule = Module::getInstanceByName(SaferPayConfig::EMAIL_ALERTS_MODULE_NAME);

        $emailAlertsModule->hookActionValidateOrder(
            [
                'currency' => $this->context->currency,
                'order' => $order,
                'customer' => $customer,
                'cart' => $this->context->cart,
                'orderStatus' => new OrderState($orderStateId),
            ]
        );
    }

}
