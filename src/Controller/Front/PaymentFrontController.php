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

namespace Invertus\SaferPay\Controller\Front;

use Invertus\SaferPay\Processor\CheckoutProcessor;
use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Config\SaferPayConfig;

class PaymentFrontController
{
    /**
     * @var CheckoutProcessor
     */
    private $checkoutProcessor;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        CheckoutProcessor $checkoutProcessor,
        Configuration $configuration
    ) {
        $this->checkoutProcessor = $checkoutProcessor;
        $this->configuration = $configuration;
    }

    public function create(\Cart $cart, $paymentMethod, $isBusinessLicense, $selectedCard = -1, $fieldToken = null, $successController = null, $isTransaction = false)
    {
        if (!$this->configuration->getAsBoolean(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION)) {
            $this->checkoutProcessor->processCreateOrder(
                $cart,
                $paymentMethod
            );
        }

        $response = $this->checkoutProcessor->initializePayment(
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController
        );

        $this->checkoutProcessor->processCreateSaferPayOrder(
            $response,
            $cart->id,
            $cart->id_customer,
            $isTransaction
        );

        return $response;
    }

    /**
     * @param object $initializeBody
     *
     * @return string
     */
    public function getRedirectionUrl($initializeBody)
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
