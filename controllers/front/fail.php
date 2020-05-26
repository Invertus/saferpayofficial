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
use PrestaShop\PrestaShop\Adapter\Order\OrderPresenter;

class SaferPayOfficialFailModuleFrontController extends ModuleFrontController
{

    /**
     * ID Order Variable Declaration.
     *
     * @var
     */
    private $id_order;

    /**
     * Security Key Variable Declaration.
     *
     * @var
     */
    private $secure_key;

    /**
     * ID Cart Variable Declaration.
     *
     * @var
     */
    private $id_cart;

    /**
     * Order Presenter Variable Declaration.
     *
     * @var
     */
    private $order_presenter;

    public function init()
    {
        if (!SaferPayConfig::isVersion17()) {
            return parent::init();
        }
        parent::init();

        $this->id_cart = (int) Tools::getValue('cartId', 0);

        $redirectLink = 'index.php?controller=history';

        $this->id_order = Order::getOrderByCartId((int) $this->id_cart);
        $this->secure_key = Tools::getValue('secureKey');
        $order = new Order((int) $this->id_order);

        if (!$this->id_order || !$this->module->id || !$this->secure_key || empty($this->secure_key)) {
            Tools::redirect($redirectLink . (Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }

        if ((string) $this->secure_key !== (string) $order->secure_key ||
            (int) $order->id_customer !== (int) $this->context->customer->id ||
            !Validate::isLoadedObject($order)
        ) {
            Tools::redirect($redirectLink);
        }

        if ($order->module !== $this->module->name) {
            Tools::redirect($redirectLink);
        }
        $this->order_presenter = new OrderPresenter();
    }

    public function initContent()
    {
        parent::initContent();

        $cartId = Tools::getValue('cartId');
        $moduleId = Tools::getValue('moduleId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $this->restoreCart($cartId);
        $orderLink = $this->context->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => $cartId,
                'id_module' => $moduleId,
                'id_order' => $orderId,
                'key' => $secureKey,
                'cancel' => 1,
            ]
        );
        if (!SaferPayConfig::isVersion17()) {
            Tools::redirect($orderLink);
        }

        $order = new Order($this->id_order);
        if ((bool) version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->smarty->assign([
                'order' => $this->order_presenter->present($order),
            ]);
        } else {
            $this->context->smarty->assign([
                'id_order' => $this->id_order,
                'email' => $this->context->customer->email,
            ]);
        }

        Tools::redirect($this->context->link->getPageLink('cart'));

        $this->setTemplate(
            sprintf('module:%s/views/templates/front/order_fail.tpl', $this->module->name)
        );
    }

    private function restoreCart($cartId)
    {
        $cart = new Cart($cartId);
        $duplication = $cart->duplicate();
        if ($duplication['success']) {
            $this->context->cookie->id_cart = $duplication['cart']->id;
            $context = $this->context;
            $context->cart = $duplication['cart'];
            CartRule::autoAddToCart($context);
            $this->context->cookie->write();
        }
    }
}
