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

use Exception;
use Invertus\SaferPay\Api\Request\ObtainPaymentMethodsService;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Service\Request\ObtainPaymentMethodsObjectCreator;

class SaferPayObtainPaymentMethods
{
    private $obtainPaymentMethodsService;
    private $obtainPaymentMethodsObjectCreator;

    public function __construct(
        ObtainPaymentMethodsService $obtainPaymentMethodsService,
        ObtainPaymentMethodsObjectCreator $obtainPaymentMethodsObjectCreator
    ) {
        $this->obtainPaymentMethodsService = $obtainPaymentMethodsService;
        $this->obtainPaymentMethodsObjectCreator = $obtainPaymentMethodsObjectCreator;
    }

    public function obtainPaymentMethods()
    {
        $paymentMethods = [];

        try {
            $paymentMethodsObject = $this->obtainPaymentMethodsService->getPaymentMethods(
                $this->obtainPaymentMethodsObjectCreator->create()
            );
        } catch (Exception $e) {
            throw new SaferPayApiException('Initialize API failed', SaferPayApiException::INITIALIZE);
        }

        if (!empty($paymentMethodsObject->PaymentMethods)) {
            foreach ($paymentMethodsObject->PaymentMethods as $paymentMethodObject) {
                $paymentMethods[] = [
                    'paymentMethod' => $paymentMethodObject->PaymentMethod,
                    'logoUrl' => $paymentMethodObject->LogoUrl,
                ];
            }
        }

        if (!empty($paymentMethodsObject->Wallets)) {
            foreach ($paymentMethodsObject->Wallets as $wallet) {
                $paymentMethods[] = [
                    'paymentMethod' => $wallet->WalletName,
                    'logoUrl' => $wallet->LogoUrl,
                ];
            }
        }

        return $paymentMethods;
    }

    public function obtainPaymentMethodsNamesAsArray()
    {
        $paymentMethodsObject = $this->obtainPaymentMethods();
        $paymentMethodsArray = [];

        if (!empty($paymentMethodsObject)) {
            foreach ($paymentMethodsObject as $paymentMethod) {
                $paymentMethodsArray[] = str_replace(' ', '', $paymentMethod['paymentMethod']);
            }
        }

        return $paymentMethodsArray;
    }
}
