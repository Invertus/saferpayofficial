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

namespace Invertus\SaferPay\Provider;

use Invertus\SaferPay\Service\PaymentRestrictionValidation\ApplePayPaymentRestrictionValidation;
use Invertus\SaferPay\Service\PaymentRestrictionValidation\BasePaymentRestrictionValidation;
use Invertus\SaferPay\Service\PaymentRestrictionValidation\KlarnaPaymentRestrictionValidation;

class PaymentRestrictionProvider
{
    /**
     * @var ApplePayPaymentRestrictionValidation
     */
    private $applePayPaymentRestrictionValidation;

    /**
     * @var BasePaymentRestrictionValidation
     */
    private $basePaymentRestrictionValidation;

    /**
     * @var KlarnaPaymentRestrictionValidation
     */
    private $klarnaPaymentRestrictionValidation;

    public function __construct
    (
        ApplePayPaymentRestrictionValidation $applePayPaymentRestrictionValidation,
        BasePaymentRestrictionValidation $basePaymentRestrictionValidation,
        KlarnaPaymentRestrictionValidation $klarnaPaymentRestrictionValidation
    )
    {
        $this->applePayPaymentRestrictionValidation = $applePayPaymentRestrictionValidation;
        $this->basePaymentRestrictionValidation = $basePaymentRestrictionValidation;
        $this->klarnaPaymentRestrictionValidation = $klarnaPaymentRestrictionValidation;
    }

    /**
     * @return  array<object>
     */
    public function getPaymentValidators(): array
    {
        $paymentRestrictions = [];
        $paymentRestrictions[] = $this->applePayPaymentRestrictionValidation;
        $paymentRestrictions[] = $this->basePaymentRestrictionValidation;
        $paymentRestrictions[] = $this->klarnaPaymentRestrictionValidation;
        return $paymentRestrictions;
    }
}
