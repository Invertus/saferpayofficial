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

use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;

class PaymentRestrictionValidation
{

    /**
     * @var SaferPayPaymentRepository
     */
    private $paymentRepository;
    /**
     * @var SaferPayRestrictionRepository
     */
    private $restrictionRepository;

    public function __construct(
        SaferPayPaymentRepository $paymentRepository,
        SaferPayRestrictionRepository $restrictionRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->restrictionRepository = $restrictionRepository;
    }

    public function isPaymentMethodValid($paymentMethod, $countryId, $currencyId)
    {
        if (!$this->paymentRepository->isActiveByName($paymentMethod)) {
            return false;
        }

        $enabledCountries = $this->restrictionRepository->getSelectedIdsByName(
            $paymentMethod,
            SaferPayRestrictionCreator::COUNTRY_RESTRICTION
        );

        $isAllCountries = in_array('0', $enabledCountries, false);
        $isCountryInList = in_array($countryId, $enabledCountries, false);

        if (!$isCountryInList && !$isAllCountries) {
            return false;
        }

        $enabledCurrencies = $this->restrictionRepository->getSelectedIdsByName(
            $paymentMethod,
            SaferPayRestrictionCreator::CURRENCY_RESTRICTION
        );
        $isAllCurrencies = in_array('0', $enabledCurrencies);
        $isCurrencyInList = in_array($currencyId, $enabledCurrencies);
        if (!$isCurrencyInList && !$isAllCurrencies) {
            return false;
        }

        return true;
    }
}
