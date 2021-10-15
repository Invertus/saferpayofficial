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

namespace Invertus\SaferPay\DTO\Response\AssertRefund;

use Invertus\SaferPay\DTO\Response\Dcc;
use Invertus\SaferPay\DTO\Response\Liability;
use Invertus\SaferPay\DTO\Response\Payer;
use Invertus\SaferPay\DTO\Response\PaymentMeans;
use Invertus\SaferPay\DTO\Response\RegistrationResult;
use Invertus\SaferPay\DTO\Response\ResponseHeader;
use Invertus\SaferPay\DTO\Response\ThreeDs;
use Invertus\SaferPay\DTO\Response\Transaction;

class AssertRefundBody
{
    /**
     * @var ResponseHeader
     */
    private $responseHeader;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $orderId;

    private $date;

    public function __construct(
        ResponseHeader $responseHeader = null,
        $transactionId = null,
        $status = null,
        $date = null,
        $orderId = null
    ) {
        $this->responseHeader = $responseHeader;
        $this->transactionId = $transactionId;
        $this->status = $status;
        $this->date = $date;
        $this->orderId = $orderId;
    }

    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * @param ResponseHeader $responseHeader
     * @return AssertRefundBody
     */
    public function setResponseHeader($responseHeader)
    {
        $this->responseHeader = $responseHeader;

        return $this;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return AssertRefundBody
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return AssertRefundBody
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return AssertRefundBody
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed|null $date
     * @return AssertRefundBody
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }
}
