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
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BasePaymentRestrictionValidation implements PaymentRestrictionValidationInterface
{
    /**
     * @var SaferPayPaymentRepository
     */
    private $paymentRepository;

    /**
     * @var SaferPayRestrictionRepository
     */
    private $restrictionRepository;

    /**
     * @var LegacyContext
     */
    private $legacyContext;

    /**
     * @var SaferPayObtainPaymentMethods
     */
    private $obtainPaymentMethods;

    public function __construct(
        LegacyContext $legacyContext,
        SaferPayPaymentRepository $paymentRepository,
        SaferPayRestrictionRepository $restrictionRepository,
        SaferPayObtainPaymentMethods $obtainPaymentMethods
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->restrictionRepository = $restrictionRepository;
        $this->legacyContext = $legacyContext;
        $this->obtainPaymentMethods = $obtainPaymentMethods;
    }

    /**
     * @inheritDoc
     * @param string $paymentName
     * @return bool
     */
    public function isValid($paymentName): bool
    {
        if ($paymentName === SaferPayConfig::PAYMENT_CARDS) {
            return true;
        }

        if (!$this->isPaymentMethodEnabled($paymentName)) {
            return false;
        }

        if (!$this->isCountrySupportedByPaymentName($paymentName)) {
            return false;
        }

        if (!$this->isCurrencySupportedByPaymentName($paymentName)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @param string $paymentName
     * @return bool
     */
    public function supports($paymentName): bool
    {
        return true;
    }

    /**
     * @param string $paymentName
     *
     * @return array|false
     */
    private function getEnabledCountriesByPaymentName($paymentName)
    {
        return $this->restrictionRepository->getSelectedIdsByName(
            $paymentName,
            SaferPayRestrictionCreator::RESTRICTION_COUNTRY
        );
    }

    /**
     * @param $paymentName
     *
     * @return array|false
     */
    private function getEnabledCurrenciesByPaymentName($paymentName)
    {
        return $this->restrictionRepository->getSelectedIdsByName(
            $paymentName,
            SaferPayRestrictionCreator::RESTRICTION_CURRENCY
        );
    }

    /**
     * @param string $paymentName
     *
     * @return bool
     */
    private function isCountrySupportedByPaymentName($paymentName)
    {
        $enabledCountries = $this->getEnabledCountriesByPaymentName($paymentName);

        $isAllCountries = in_array('0', $enabledCountries, false);
        $isCountryInList = in_array($this->legacyContext->getCountryId(), $enabledCountries, false);

        return $isCountryInList || $isAllCountries;
    }

    /**
     * @param string $paymentName
     *
     * @return bool
     */
    private function isPaymentMethodEnabled($paymentName)
    {
        return $this->paymentRepository->isActiveByName($paymentName);
    }

    /**
     * @param string $paymentName
     *
     * @return bool
     */
    private function isCurrencySupportedByPaymentName($paymentName)
    {
        $enabledCurrencies = $this->getEnabledCurrenciesByPaymentName($paymentName);

        if (!$enabledCurrencies) {
            return false;
        }

        $isAllCurrencies = in_array('0', $enabledCurrencies, false);
        $isCurrencyInList = in_array($this->legacyContext->getCurrencyId(), $enabledCurrencies, false);

        return $isCurrencyInList || $isAllCurrencies;
    }
}
