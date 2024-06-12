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

namespace Invertus\SaferPay\EntityBuilder;

use Cart;
use Customer;
use Order;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOrderBuilder
{
    //TODO to pass $body as InitializeBody.
    public function create($body, $cartId, $customerId, $isTransaction, $status = null)
    {
        if (method_exists('Order', 'getIdByCartId')) {
            $orderId = Order::getIdByCartId($cartId);
        } else {
            // For PrestaShop 1.6 use the alternative method
            $orderId = Order::getOrderByCartId($cartId);
        }

        $saferPayOrder = new SaferPayOrder();
        $saferPayOrder->token = $body->Token;
        $saferPayOrder->id_order = $orderId ?: null;
        $saferPayOrder->id_cart = $cartId;
        $saferPayOrder->id_customer = $customerId;
        $saferPayOrder->redirect_url = $this->getRedirectionUrl($body);
        $saferPayOrder->is_transaction = $isTransaction;

        $saferPayOrder->add();

        return $saferPayOrder;
    }

    public function createDirectOrder($body, Cart $cart, Customer $customer, $isTransaction)
    {
        $orderId = Order::getOrderByCartId($cart->id);
        $saferPayOrder = new SaferPayOrder();
        $saferPayOrder->transaction_id = $body->Transaction->Id;
        $saferPayOrder->id_order = $orderId;
        $saferPayOrder->id_customer = $customer->id;
        $saferPayOrder->is_transaction = $isTransaction;
        $saferPayOrder->authorized = 1;
        $saferPayOrder->add();

        return $saferPayOrder;
    }

    /**
     * @param object $initializeBody
     *
     * @return string
     */
    private function getRedirectionUrl($initializeBody)
    {
        if (isset($initializeBody->RedirectUrl)) {
            return $initializeBody->RedirectUrl;
        }

        if (isset($initializeBody->Redirect->RedirectUrl)) {
            return $initializeBody->Redirect->RedirectUrl;
        }

        return '';
    }
}
