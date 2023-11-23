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

namespace Invertus\SaferPay\DTO\Request\Refund;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Payment;
use Invertus\SaferPay\DTO\Request\PendingNotification;
use Invertus\SaferPay\DTO\Request\RequestHeader;
use Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundRequest
{

    /**
     * @var RequestHeader
     */
    private $requestHeader;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var PendingNotification
     */
    private $pendingNotification;

    public function __construct(
        RequestHeader $requestHeader,
        Payment $payment,
        $transactionId,
        $pendingNotification = null
    ) {
        $this->requestHeader = $requestHeader;
        $this->transactionId = $transactionId;
        $this->payment = $payment;
        $this->pendingNotification = $pendingNotification;
    }

    public function getAsArray()
    {
        $return = [
            'RequestHeader' => [
                'SpecVersion' => (string) Configuration::get(RequestHeader::SPEC_REFUND_VERSION),
                'CustomerId' => $this->requestHeader->getCustomerId(),
                'RequestId' => $this->requestHeader->getRequestId(),
                'RetryIndicator' => $this->requestHeader->getRetryIndicator(),
                'ClientInfo' => $this->requestHeader->getClientInfo(),
            ],
            'Refund' => [
                'Amount' => [
                    'Value' => $this->payment->getValue(),
                    'CurrencyCode' => $this->payment->getCurrencyCode(),
                ],
                'OrderId' => $this->payment->getOrderReference(), //for delay testing: NotifyRefund_DelayedResponse60
                'RestrictRefundAmountToCapturedAmount' =>
                    (bool) Configuration::get(SaferPayConfig::RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT),
            ],
            'CaptureReference' => [
                'CaptureId' => $this->transactionId,
            ],
        ];

        if ($this->pendingNotification) {
            $return['PendingNotification'] = [
                'MerchantEmails' => $this->pendingNotification->getMerchantEmails(),
                'NotifyUrl' => $this->pendingNotification->getNotifyUrl(),
            ];
        }

        return $return;
    }
}
