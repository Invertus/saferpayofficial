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

namespace Invertus\SaferPay\Processor;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder;
use Invertus\SaferPay\Service\SaferPayInitialize;

class CheckoutProcessor
{
    /** @var \SaferPayOfficial */
    private $module;
    /** @var SaferPayOrderBuilder */
    private $saferPayOrderBuilder;
    /** @var SaferPayInitialize */
    private $saferPayInitialize;

    public function __construct(
        \SaferPayOfficial $module,
        SaferPayOrderBuilder $saferPayOrderBuilder,
        SaferPayInitialize $saferPayInitialize
    ) {
        $this->module = $module;
        $this->saferPayOrderBuilder = $saferPayOrderBuilder;
        $this->saferPayInitialize = $saferPayInitialize;
    }

    public function processCreateOrder(\Cart $cart, $paymentMethod)
    {
        $customer = new \Customer($cart->id_customer);

        $this->module->validateOrder(
            $cart->id,
            \Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            (float) $cart->getOrderTotal(),
            $paymentMethod,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );
    }

    public function initializePayment(
        $paymentMethod,
        $isBusinessLicense,
        $selectedCard,
        $fieldToken,
        $successController
    ) {
        $request = $this->saferPayInitialize->buildRequest(
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController
        );

        return $this->saferPayInitialize->initialize($request, $isBusinessLicense);
    }

    public function processCreateSaferPayOrder($initializeBody, $cartId, $customerId, $isTransaction)
    {
        $this->saferPayOrderBuilder->create(
            $initializeBody,
            $cartId,
            $customerId,
            $isTransaction
        );
    }
}