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

use Invertus\SaferPay\DTO\Response\Initialize\InitializeBody;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InitializeResponseObjectCreator extends ResponseObjectCreator
{
    public function createInitializeObject($responseBody)
    {
        $initializeBody = new InitializeBody();

        if (isset($responseBody->ResponseHeader)) {
            $responseHeader = $this->createResponseHeader($responseBody->ResponseHeader);
            $initializeBody->setResponseHeader($responseHeader);
        }

        if (isset($responseBody->Transaction)) {
            $transaction = $this->createTransaction($responseBody->Transaction);
            $initializeBody->setTransaction($transaction);
        }

        if (isset($responseBody->PaymentMeans)) {
            $paymentMeans = $this->createPaymentMeans($responseBody->PaymentMeans);
            $initializeBody->setPaymentMeans($paymentMeans);
        }

        if (isset($responseBody->Payer)) {
            $payer = $this->createPayer($responseBody->Payer);
            $initializeBody->setPayer($payer);
        }

        if (isset($responseBody->ThreeDs)) {
            $threeDs = $this->createThreeDs($responseBody->ThreeDs);
            $initializeBody->setThreeDs($threeDs);
        }

        if (isset($responseBody->Dcc)) {
            $dcc = $this->createDcc($responseBody->Dcc);
            $initializeBody->setDcc($dcc);
        }

        return $initializeBody;
    }
}
