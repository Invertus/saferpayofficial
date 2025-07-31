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

use Invertus\SaferPay\Config\SaferPayConfig;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CardPaymentGroupingService
{
    /** @var SaferPayOfficial */
    public $module;

    public function __construct(SaferPayOfficial $module)
    {
        $this->module = $module;
    }

    /**
     * @param array $paymentMethods Raw payment methods from API
     * @param array $allCurrencies List of all supported currencies (for CARD method)
     *
     * @return array Filtered/grouped payment methods
     */
    public function group(array $paymentMethods, array $allCurrencies): array
    {
        $result = [];
        $cardMethods = [];

        foreach ($paymentMethods as $method) {
            if (in_array($method['paymentMethod'], SaferPayConfig::CARD_BRANDS, true)) {
                $cardMethods[] = $method;
            } else {
                $result[] = $method;
            }
        }

        $logoUrl = _PS_BASE_URL_SSL_ . $this->module->getPathUri() . 'views/img/' . SaferPayConfig::PAYMENT_CARDS . '.png';

        if (!empty($cardMethods)) {
            $result[] = [
                'paymentMethod' => SaferPayConfig::PAYMENT_CARDS,
                'logoUrl' => $logoUrl,
                'currencies' => $allCurrencies,
            ];
        }

        return $result;
    }
}
