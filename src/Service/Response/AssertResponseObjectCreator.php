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

use Invertus\SaferPay\DTO\Response\Assert\AssertBody;

class AssertResponseObjectCreator extends ResponseObjectCreator
{
    public function createAssertObject($responseBody)
    {
        $assertBody = new AssertBody();

        if (isset($responseBody->ResponseHeader)) {
            $responseHeader = $this->createResponseHeader($responseBody->ResponseHeader);
            $assertBody->setResponseHeader($responseHeader);
        }

        if (isset($responseBody->Transaction)) {
            $transaction = $this->createTransaction($responseBody->Transaction);
            $assertBody->setTransaction($transaction);
        }

        if (isset($responseBody->PaymentMeans)) {
            $paymentMeans = $this->createPaymentMeans($responseBody->PaymentMeans);
            $assertBody->setPaymentMeans($paymentMeans);
        }

        if (isset($responseBody->Payer)) {
            $payer = $this->createPayer($responseBody->Payer);
            $assertBody->setPayer($payer);
        }

        if (isset($responseBody->Liability)) {
            $liability = $this->createLiability($responseBody->Liability);
            $assertBody->setLiability($liability);
        }

        if (isset($responseBody->ThreeDs)) {
            $threeDs = $this->createThreeDs($responseBody->ThreeDs);
            $assertBody->setThreeDs($threeDs);
        }

        if (isset($responseBody->Dcc)) {
            $dcc = $this->createDcc($responseBody->Dcc);
            $assertBody->setDcc($dcc);
        }

        if (isset($responseBody->RegistrationResult)) {
            $regResult = $this->createRegistrationResult($responseBody->RegistrationResult);
            $assertBody->setRegistrationResult($regResult);
        }

        return $assertBody;
    }
}
