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

use Invertus\SaferPay\DTO\Response\Authorization\AuthorizationBody;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AuthorizationResponseObjectCreator extends ResponseObjectCreator
{
    public function createAuthorizationObject($responseBody)
    {
        $authBody = new AuthorizationBody();

        if (isset($responseBody->ResponseHeader)) {
            $responseHeader = $this->createResponseHeader($responseBody->ResponseHeader);
            $authBody->setResponseHeader($responseHeader);
        }

        if (isset($responseBody->Transaction)) {
            $transaction = $this->createTransaction($responseBody->Transaction);
            $authBody->setTransaction($transaction);
        }

        if (isset($responseBody->PaymentMeans)) {
            $paymentMeans = $this->createPaymentMeans($responseBody->PaymentMeans);
            $authBody->setPaymentMeans($paymentMeans);
        }

        if (isset($responseBody->Liability)) {
            $liability = $this->createLiability($responseBody->Liability);
            $authBody->setLiability($liability);
        }

        if (isset($responseBody->ThreeDs)) {
            $threeDs = $this->createThreeDs($responseBody->ThreeDs);
            $authBody->setThreeDs($threeDs);
        }

        return $authBody;
    }
}
