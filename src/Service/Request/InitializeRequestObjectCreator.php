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

namespace Invertus\SaferPay\Service\Request;

use Cart;
use Configuration;
use Customer;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Initialize\InitializeRequest;
use Invertus\SaferPay\DTO\Request\Payer;

class InitializeRequestObjectCreator extends RequestObjectCreator
{
    public function create(
        Cart $cart,
        $customerEmail,
        $paymentMethod,
        $successUrl,
        $notifyUrl,
        $failUrl,
        $deliveryAddressId,
        $invoiceAddressId,
        $customerId,
        $alias = null
    ) {
        $requestHeader = $this->createRequestHeader();
        $terminalId = Configuration::get(SaferPayConfig::TERMINAL_ID . SaferPayConfig::getConfigSuffix());

        $cartDetails = $cart->getSummaryDetails();
        $totalPrice = (int) ($cartDetails['total_price'] * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API);
        $payment = $this->createPayment($cart, $totalPrice);
        $payer = new Payer();
        $returnUrls = $this->createReturnUrls($successUrl, $failUrl);
        $notification = $this->createNotification($customerEmail, $notifyUrl);
        $deliveryAddressForm = $this->createDeliveryAddressForm();
        $configSet = Configuration::get(SaferPayConfig::CONFIGURATION_NAME);
        $cssUrl = Configuration::get(SaferPayConfig::CSS_FILE);

        $customer = new Customer($customerId);
        $deliveryAddress = new \Address($deliveryAddressId);
        $deliveryAddress = $this->createAddressObject($deliveryAddress, $customer);

        $invoiceAddress = new \Address($invoiceAddressId);
        $invoiceAddress = $this->createAddressObject($invoiceAddress, $customer);

        return new InitializeRequest(
            $requestHeader,
            $terminalId,
            $paymentMethod,
            $payment,
            $payer,
            $returnUrls,
            $notification,
            $deliveryAddressForm,
            $configSet,
            $cssUrl,
            $deliveryAddress,
            $invoiceAddress,
            $alias
        );
    }
}
