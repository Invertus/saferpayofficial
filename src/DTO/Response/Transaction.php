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

namespace Invertus\SaferPay\DTO\Response;

class Transaction
{
    private $type;
    private $status;
    private $id;
    private $date;
    private $amount;
    private $orderId;
    private $acquirerName;
    private $acquirerReference;
    private $approvalCode;

    /**
     * Transaction constructor.
     * @param string $type
     * @param string $status
     * @param string $id
     * @param string $date
     * @param Amount|null $amount
     * @param string $orderId
     * @param string $acquirerName
     * @param string $acquirerReference
     * @param string $approvalCode
     */
    public function __construct(
        $type = null,
        $status = null,
        $id = null,
        $date = null,
        Amount $amount = null,
        $orderId = null,
        $acquirerName = null,
        $acquirerReference = null,
        $approvalCode = null
    ) {
        $this->type = $type;
        $this->status = $status;
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->orderId = $orderId;
        $this->acquirerName = $acquirerName;
        $this->acquirerReference = $acquirerReference;
        $this->approvalCode = $approvalCode;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getAcquirerName()
    {
        return $this->acquirerName;
    }

    /**
     * @param mixed $acquirerName
     */
    public function setAcquirerName($acquirerName)
    {
        $this->acquirerName = $acquirerName;
    }

    /**
     * @return mixed
     */
    public function getAcquirerReference()
    {
        return $this->acquirerReference;
    }

    /**
     * @param mixed $acquirerReference
     */
    public function setAcquirerReference($acquirerReference)
    {
        $this->acquirerReference = $acquirerReference;
    }

    /**
     * @return mixed
     */
    public function getApprovalCode()
    {
        return $this->approvalCode;
    }

    /**
     * @param mixed $approvalCode
     */
    public function setApprovalCode($approvalCode)
    {
        $this->approvalCode = $approvalCode;
    }
}
