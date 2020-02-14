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

namespace Invertus\SaferPay\DTO\Response\Authorization;

use Invertus\SaferPay\DTO\Response\Liability;
use Invertus\SaferPay\DTO\Response\PaymentMeans;
use Invertus\SaferPay\DTO\Response\ResponseHeader;
use Invertus\SaferPay\DTO\Response\ThreeDs;
use Invertus\SaferPay\DTO\Response\Transaction;

class AuthorizationBody
{
    /**
     * @var ResponseHeader
     */
    private $responseHeader;
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * @var PaymentMeans
     */
    private $paymentMeans;
    /**
     * @var ThreeDs
     */
    private $threeDs;
    /**
     * @var Liability
     */
    private $liability;

    public function __construct(
        ResponseHeader $responseHeader = null,
        Transaction $transaction = null,
        PaymentMeans $paymentMeans = null,
        ThreeDs $threeDs = null,
        Liability $liability = null
    ) {
        $this->responseHeader = $responseHeader;
        $this->transaction = $transaction;
        $this->paymentMeans = $paymentMeans;
        $this->threeDs = $threeDs;
        $this->liability = $liability;
    }

    /**
     * @return Liability
     */
    public function getLiability()
    {
        return $this->liability;
    }

    /**
     * @param Liability $liability
     */
    public function setLiability($liability)
    {
        $this->liability = $liability;
    }

    /**
     * @return ResponseHeader
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * @param $responseHeader
     */
    public function setResponseHeader($responseHeader)
    {
        $this->responseHeader = $responseHeader;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return PaymentMeans
     */
    public function getPaymentMeans()
    {
        return $this->paymentMeans;
    }

    /**
     * @param PaymentMeans $paymentMeans
     */
    public function setPaymentMeans($paymentMeans)
    {
        $this->paymentMeans = $paymentMeans;
    }

    /**
     * @return ThreeDs
     */
    public function getThreeDs()
    {
        return $this->threeDs;
    }

    /**
     * @param ThreeDs $threeDs
     */
    public function setThreeDs($threeDs)
    {
        $this->threeDs = $threeDs;
    }
}
