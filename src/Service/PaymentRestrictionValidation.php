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

namespace Invertus\SaferPay\Service;

use Invertus\SaferPay\Provider\PaymentRestrictionProvider;
use Invertus\SaferPay\Service\PaymentRestrictionValidation\PaymentRestrictionValidationInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentRestrictionValidation
{
    /**
     * @var PaymentRestrictionProvider
     */
    private $paymentRestrictionProvider;

    public function __construct(PaymentRestrictionProvider $paymentRestrictionProvider)
    {
        $this->paymentRestrictionProvider = $paymentRestrictionProvider;
    }

    /**
     * Atleast one payment restriction validator is present at all times (BasePaymentRestrictionValidation)
     *
     * @param string $paymentMethod
     *
     * @return bool
     */
    public function isPaymentMethodValid($paymentMethod)
    {
        $success = false;
        $paymentValidators = $this->paymentRestrictionProvider->getPaymentValidators();
        /**
         * @var PaymentRestrictionValidationInterface $paymentRestrictionValidator
         */
        foreach ($paymentValidators as $paymentRestrictionValidator) {
            if ($paymentRestrictionValidator->supports($paymentMethod)) {
                $success = $paymentRestrictionValidator->isValid($paymentMethod);

                if (!$success) {
                    return false;
                }
            }
        }
        return $success;
    }
}
