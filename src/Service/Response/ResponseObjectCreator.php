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

namespace Invertus\SaferPay\Service\Response;

use Invertus\SaferPay\DTO\Response\Amount;
use Invertus\SaferPay\DTO\Response\Brand;
use Invertus\SaferPay\DTO\Response\Card;
use Invertus\SaferPay\DTO\Response\Dcc;
use Invertus\SaferPay\DTO\Response\DeliveryAddress;
use Invertus\SaferPay\DTO\Response\Liability;
use Invertus\SaferPay\DTO\Response\Payer;
use Invertus\SaferPay\DTO\Response\PaymentMeans;
use Invertus\SaferPay\DTO\Response\RegistrationResult;
use Invertus\SaferPay\DTO\Response\ResponseHeader;
use Invertus\SaferPay\DTO\Response\ThreeDs;
use Invertus\SaferPay\DTO\Response\Transaction;

class ResponseObjectCreator
{
    protected function createResponseHeader($responseHeader)
    {
        return new ResponseHeader($responseHeader->SpecVersion, $responseHeader->RequestId);
    }

    protected function createTransaction($transaction)
    {
        $amount = new Amount($transaction->Amount->Value, $transaction->Amount->CurrencyCode);

        $transactionObj = new Transaction();
        $transactionObj->setType($transaction->Type);
        $transactionObj->setStatus($transaction->Status);
        $transactionObj->setId($transaction->Id);
        $transactionObj->setDate($transaction->Date);
        $transactionObj->setAmount($amount);
        $transactionObj->setAcquirerName($transaction->AcquirerName);
        if (isset($transaction->AcquirerReference)) {
            $transactionObj->setAcquirerReference($transaction->AcquirerReference);
        }
        if (isset($transaction->ApprovalCode)) {
            $transactionObj->setApprovalCode($transaction->ApprovalCode);
        }

        return $transactionObj;
    }

    protected function createPaymentMeans($paymentMeans)
    {
        $brandObj = new Brand($paymentMeans->Brand->PaymentMethod, $paymentMeans->Brand->Name);

        $paymentMeansObj = new PaymentMeans();
        if (property_exists($paymentMeans, 'Card')) {
            $card = $paymentMeans->Card;
            $cardObj = new Card();
            $cardObj->setMaskedNumber($card->MaskedNumber);
            $cardObj->setExpYear($card->ExpYear);
            $cardObj->setExpMonth($card->ExpMonth);
            if (isset($card->HolderName)) {
                $cardObj->setHolderName($card->HolderName);
            }
            if (isset($card->CountryCode)) {
                $cardObj->setCountryCode($card->CountryCode);
            }
            $paymentMeansObj->setCard($cardObj);
        }
        $paymentMeansObj->setBrand($brandObj);
        $paymentMeansObj->setDisplayText($paymentMeans->DisplayText);

        return $paymentMeansObj;
    }

    protected function createPayer($payer)
    {
        $deliveryAddress = $this->createDeliveryAddress($payer->DeliveryAddress);

        $payerObj = new Payer();
        $payerObj->setDeliveryAddress($deliveryAddress);

        return $payerObj;
    }

    protected function createDeliveryAddress($deliveryAddress)
    {
        $deliveryAddressObj = new DeliveryAddress();

        if (isset($deliveryAddress->FirstName)) {
            $deliveryAddressObj->setFirstName($deliveryAddress->FirstName);
        }
        if (isset($deliveryAddress->LastName)) {
            $deliveryAddressObj->setLastName($deliveryAddress->LastName);
        }
        if (isset($deliveryAddress->Company)) {
            $deliveryAddressObj->setCompany($deliveryAddress->Company);
        }
        if (isset($deliveryAddress->Gender)) {
            $deliveryAddressObj->setGender($deliveryAddress->Gender);
        }
        if (isset($deliveryAddress->Street)) {
            $deliveryAddressObj->setStreet($deliveryAddress->Street);
        }
        if (isset($deliveryAddress->Zip)) {
            $deliveryAddressObj->setZip($deliveryAddress->Zip);
        }
        if (isset($deliveryAddress->City)) {
            $deliveryAddressObj->setCity($deliveryAddress->City);
        }
        if (isset($deliveryAddress->CountryCode)) {
            $deliveryAddressObj->setCountryCode($deliveryAddress->CountryCode);
        }
        if (isset($deliveryAddress->Phone)) {
            $deliveryAddressObj->setPhone($deliveryAddress->Phone);
        }
        if (isset($deliveryAddress->Email)) {
            $deliveryAddressObj->setEmail($deliveryAddress->Email);
        }

        return $deliveryAddressObj;
    }

    protected function createThreeDs($threeDs)
    {
        $threeDsObj = new ThreeDs();
        $threeDsObj->setAuthenticated($threeDs->Authenticated);
        $threeDsObj->setLiabilityShift($threeDs->LiabilityShift);
        $threeDsObj->setXid($threeDs->Xid);
        $threeDsObj->setVerificationValue($threeDs->VerificationValue);

        return $threeDsObj;
    }

    protected function createLiability($liability)
    {
        $liabilityObj = new Liability();
        if (isset($liability->ThreeDs)) {
            $threeDs = $liability->ThreeDs;
            $threeDsObj = new ThreeDs();
            $threeDsObj->setAuthenticated($threeDs->Authenticated);
            $threeDsObj->setLiabilityShift($threeDs->LiabilityShift);
            $threeDsObj->setXid($threeDs->Xid);
            if (isset($threeDs->VerificationValue)) {
                $threeDsObj->setVerificationValue($threeDs->VerificationValue);
            }
            $liabilityObj->setThreeDs($threeDsObj);
        }

        $liabilityObj->setLiabilityShift($liability->LiabilityShift);
        $liabilityObj->setLiableEntity($liability->LiableEntity);

        return $liabilityObj;
    }

    protected function createDcc($dcc)
    {
        $amountObj = new Amount();
        $amountObj->setValue($dcc->PayerAmount->Value);
        $amountObj->setCurrencyCode($dcc->PayerAmount->CurrencyCode);

        $dccObj = new Dcc();
        $dccObj->setAmount($amountObj);

        return $dccObj;
    }

    protected function createRegistrationResult($regResult)
    {
        $amountObj = new RegistrationResult();
        $amountObj->setSuccess($regResult->Success);
        $amountObj->setAliasId($regResult->Alias->Id);
        $amountObj->setLifetime($regResult->Alias->Lifetime);

        return $amountObj;
    }
}
