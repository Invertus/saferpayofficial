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

namespace Invertus\SaferPay\Service\PaymentRestrictionValidation;

use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Config\SaferPayConfig;
use Tools;

class WlcryptopaymentsPaymentRestrictionValidation implements PaymentRestrictionValidationInterface
{
    private $context;

    public function __construct(LegacyContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function isValid($paymentName)
    {
        if (!$this->isContextCurrencySupported()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function supports($paymentName)
    {
        return Tools::strtoupper($paymentName) == SaferPayConfig::PAYMENT_WLCRYPTOPAYMENTS;
    }

    /**
     * @return bool
     */
    private function isContextCurrencySupported()
    {
        if (!$this->context->getCurrencyIsoCode()) {
            return false;
        }

        return in_array($this->context->getCurrencyIsoCode(), SaferPayConfig::WLCRYPTOPAYMENTS_SUPPORTED_CURRENCIES);
    }
}
