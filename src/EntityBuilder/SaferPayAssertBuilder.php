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

namespace Invertus\SaferPay\EntityBuilder;

use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use SaferPayAssert;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayAssertBuilder
{
    /**
     * @param AssertBody $assertBody
     * @param int $saferPayOrderId
     *
     * @return SaferPayAssert
     * @throws \Exception
     */
    public function createAssert(AssertBody $assertBody, int $saferPayOrderId): SaferPayAssert
    {
        $assert = new SaferPayAssert();

        $assert->id_saferpay_order = $saferPayOrderId;
        $assert->amount = (int) $assertBody->getTransaction()->getAmount()->getValue();
        $assert->status = $assertBody->getTransaction()->getStatus();
        $assert->authorized = 1; //If creating assert, it must be authorized.

        $assert->currency_code = $assertBody->getTransaction()->getAmount()->getCurrencyCode();
        $assert->uncertain = 0;
        $assert->brand = $assertBody->getPaymentMeans()->getBrand()->getName();
        $assert->payment_method = $assertBody->getPaymentMeans()->getBrand()->getPaymentMethod();
        $assert->transaction_paid = 0;
        $assert->merchant_reference = $assertBody->getPaymentMeans()->getBrand()->getPaymentMethod();
        $assert->payment_id = $assertBody->getTransaction()->getId();
        $assert->acceptance = 0;
        $assert->is_test = 0;
        $assert->pending_refund_amount = 0;
        $assert->card_number = $assertBody->getPaymentMeans()->getDisplayText();
        if ($assertBody->getPaymentMeans()->getCard() !== null) {
            $assert->exp_year = $assertBody->getPaymentMeans()->getCard()->getExpYear();
            $assert->exp_month = $assertBody->getPaymentMeans()->getCard()->getExpMonth();
        }
        if ($assertBody->getLiability() !== null) {
            $assert->liability_shift = $assertBody->getLiability()->getLiabilityShift();
            $assert->liability_entity = $assertBody->getLiability()->getLiableEntity();
        }
        if ($assertBody->getDcc() !== null) {
            $assert->dcc_value = $assertBody->getDcc()->getAmount()->getValue();
            $assert->dcc_currency_code = $assertBody->getDcc()->getAmount()->getCurrencyCode();
        }
        $assert->add();

        return $assert;
    }
}
