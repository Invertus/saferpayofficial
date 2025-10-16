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

namespace Invertus\SaferPay\Presenter;

use Invertus\SaferPay\Config\SaferPayConfig;
use SaferPayAssert;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AssertPresenter
{
    const FILE_NAME = 'AssertPresenter';

    /**
     * @var SaferPayOfficial
     */
    private $saferPay;

    public function __construct(SaferPayOfficial $saferPay)
    {
        $this->saferPay = $saferPay;
    }

    /**
     * @param SaferPayAssert $assert
     * @return array
     */
    public function present(SaferPayAssert $assert): array
    {
        $paymentMethod = $assert->payment_method;

        return [
            'authAmount' => $assert->amount,
            'transactionAuth' => $assert->authorized ?
                $this->saferPay->l('Yes', self::FILE_NAME) :
                $this->saferPay->l('No', self::FILE_NAME),
            'cardExpiryDate' =>
                $assert->exp_year
                . '/'
                . $assert->exp_month,
            'currency' => $assert->currency_code,
            'transactionUncertain' => $assert->uncertain,
            'brand' => $assert->brand,
            'paymentMethod' => $paymentMethod,
            'transactionPaid' => $assert->status,
            'merchantReference' => $assert->merchant_reference,
            'paymentId' => $assert->payment_id,
            'supportsOrderCapture' => SaferPayConfig::supportsOrderCapture($paymentMethod),
            'supportsOrderCancel' => SaferPayConfig::supportsOrderCancel($paymentMethod),
            'acceptance' => '????',
            'liability_entity' => $assert->liability_entity,
            'cardNumber' => $assert->card_number,
            'refund_amount' => $assert->refunded_amount,
            'pending_refund_amount' => $assert->pending_refund_amount,
            'liability_shift' => $assert->liability_shift,
            'dcc_value' => $assert->dcc_value,
            'dcc_currency_code' => $assert->dcc_currency_code,
        ];
    }
}
