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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayPaymentNotation
{
    const PAYMENTS = [
        'AMEX' => 'AmericanExpress',
        'DINERS' => 'DinersClub',
        'BONUS' => 'BonusCard',
        'DIRECTDEBIT' => 'Lastschrift',
        'POSTFINANCE' => 'PostEFinance',
        'MAESTRO' => 'Maestro-Intl.',
    ];

    /**
     * @param string $payment
     * @return string
     */
    public function getForDisplay($payment): string
    {
        if (array_key_exists($payment, self::PAYMENTS)) {
            return self::PAYMENTS[$payment];
        }

        $notation = strtolower($payment);
        $notation = ucfirst($notation);

        return $notation;
    }

    /**
     * @param string $payment
     * @return string
     */
    public function getShortName($payment): string
    {
        $paymentNotation = str_replace(' ', '', $payment);

        $map = array_flip(self::PAYMENTS);
        $fixedPaymentNotation = strtoupper($paymentNotation);

        if (isset($map[$paymentNotation])) {
            return $map[$paymentNotation];
        }

        return $fixedPaymentNotation;
    }
}
