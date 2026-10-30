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

namespace Invertus\SaferPay\Service\TransactionFlow;

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Response\Amount;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayAuthorizedAmountGuard
{
    /**
     * @param Amount $authorized
     * @param float $total
     * @param string $currencyIsoCode
     *
     * @return bool
     */
    public function matchesTotal(Amount $authorized, $total, $currencyIsoCode): bool
    {
        if ((string) $authorized->getCurrencyCode() !== (string) $currencyIsoCode) {
            return false;
        }

        // Same conversion as InitializeRequestObjectCreator uses for the amount sent to Saferpay.
        return (int) $authorized->getValue() === (int) round($total * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API);
    }

    /**
     * @param Amount $authorized
     *
     * @return float
     */
    public function toMajorUnits(Amount $authorized)
    {
        return (float) $authorized->getValue() / SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API;
    }
}
