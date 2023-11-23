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
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Service\CartDuplicationService;
use PrestaShop\PrestaShop\Adapter\Order\OrderPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialFailModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'fail';

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

        /** @var CartDuplicationService $cartDuplicationService */
        $cartDuplicationService = $this->module->getService(CartDuplicationService::class);
        $cartDuplicationService->restoreCart($this->id_cart);

        $this->order_presenter = new OrderPresenter();
    }

    public function initContent()
    {
        parent::initContent();

        $orderLink = $this->context->link->getPageLink(
            'order',
            true,
            null
        );
        $this->warning[] = $this->module->l('We couldn\'t authorize your payment. Please try again.', self::FILENAME);

        if (!SaferPayConfig::isVersion17()) {
            $this->redirectWithNotifications($orderLink);
        }

        $this->redirectWithNotifications(
            $this->context->link->getPageLink(
                'cart',
                null,
                $this->context->language->id,
                [
                    'action' => 'show',
                ],
                false,
                null,
                false
            )
        );
    }
}
