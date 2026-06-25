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

use Cart;
use CartRule;
use Context;
use Db;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartDuplicationService
{
    public function restoreCart($cartId)
    {
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $duplication = $cart->duplicate();
        if ($duplication['success']) {
            /** @var Cart $duplicatedCart */
            $duplicatedCart = $duplication['cart'];
            foreach ($cart->getOrderedCartRulesIds() as $cartRuleId) {
                $duplicatedCart->addCartRule($cartRuleId['id_cart_rule']);
                $this->restoreCartRuleQuantity($cartId, $cartRuleId['id_cart_rule']);
            }
            
            $context->cart = $duplicatedCart;
            CartRule::autoAddToCart($context);
            $context->cookie->write();
        }
    }

    private function restoreCartRuleQuantity($cartId, $cartRuleId)
    {
        $cartRule = new CartRule($cartRuleId);
        $cartRule->quantity++;
        $cartRule->update();

        $orderId = Order::getIdByCartId($cartId);

        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'order_cart_rule`
                    WHERE id_order = ' . (int) $orderId . '
                        AND id_cart_rule = ' . (int) $cartRuleId;
        DB::getInstance()->execute($sql);
    }
}
