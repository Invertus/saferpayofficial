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

use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\CartDuplicationService;

class SaferPayOfficialFailValidationModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'failValidation';

    public function postProcess()
    {
        $cartId = Tools::getValue('cartId');
        $orderId = Tools::getValue('orderId');
        $secureKey = Tools::getValue('secureKey');

        $cart = new Cart($cartId);
        if ($cart->secure_key !== $secureKey) {
            $redirectLink = $this->context->link->getPageLink(
                'order',
                true,
                null,
                [
                    'step' => 1,
                ]
            );

            Tools::redirect($redirectLink);
        }
        $order = new Order($orderId);
        $order->setCurrentState(_SAFERPAY_PAYMENT_AUTHORIZATION_FAILED_);
        /** @var SaferPayOrderRepository $orderRepo */
        /** @var CartDuplicationService $cartDuplicationService */
        $orderRepo = $this->module->getService('saferpay.order.repository');
        $cartDuplicationService = $this->module->getService(CartDuplicationService::class);

        $saferPayOrderId = $orderRepo->getIdByOrderId($orderId);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $saferPayOrder->canceled = 1;
        $saferPayOrder->update();

        $cartDuplicationService->restoreCart($cartId);
        $isBusinessLicence = Tools::getValue(\Invertus\SaferPay\Config\SaferPayConfig::IS_BUSINESS_LICENCE);
        $controller = $isBusinessLicence ? 'failIFrame' : 'fail';

        $failUrl = $this->context->link->getModuleLink(
            $this->module->name,
            $controller,
            [
                'cartId' => $cartId,
                'secureKey' => $secureKey,
                'orderId' => $orderId,
                'moduleId' => $this->module->id,
            ],
            true
        );

        Tools::redirect($failUrl);
    }
}
