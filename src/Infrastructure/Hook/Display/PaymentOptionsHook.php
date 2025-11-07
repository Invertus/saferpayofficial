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

namespace Invertus\SaferPay\Infrastructure\Hook\Display;

use Configuration;
use Context;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Provider\CurrencyProvider;
use Invertus\SaferPay\Provider\PaymentRedirectionProvider;
use Invertus\SaferPay\Provider\PaymentTypeProvider;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Service\CardPaymentGroupingService;
use Invertus\SaferPay\Service\LegacyTranslator;
use Invertus\SaferPay\Service\SaferPayCartService;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\PaymentRestrictionValidation;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookPaymentOptions
 * Displays available SaferPay payment methods on checkout page
 */
class PaymentOptionsHook implements HookInterface
{
    /**
     * @var SaferPayOfficial
     */
    private $module;

    /**
     * @var SaferPayCartService
     */
    private $cartService;

    /**
     * @var PaymentTypeProvider
     */
    private $paymentTypeProvider;

    /**
     * @var SaferPayObtainPaymentMethods
     */
    private $obtainPaymentMethods;

    /**
     * @var SaferPayPaymentRepository
     */
    private $paymentRepository;

    /**
     * @var PaymentRestrictionValidation
     */
    private $paymentRestrictionValidation;

    /**
     * @var CurrencyProvider
     */
    private $currencyProvider;

    /**
     * @var CardPaymentGroupingService
     */
    private $cardGroupingService;

    /**
     * @var SaferPayCardAliasRepository
     */
    private $cardAliasRepository;

    /**
     * @var PaymentRedirectionProvider
     */
    private $paymentRedirectionProvider;

    /**
     * @var LegacyTranslator
     */
    private $translator;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        SaferPayOfficial $module,
        SaferPayCartService $cartService,
        PaymentTypeProvider $paymentTypeProvider,
        SaferPayObtainPaymentMethods $obtainPaymentMethods,
        SaferPayPaymentRepository $paymentRepository,
        PaymentRestrictionValidation $paymentRestrictionValidation,
        CurrencyProvider $currencyProvider,
        CardPaymentGroupingService $cardGroupingService,
        SaferPayCardAliasRepository $cardAliasRepository,
        PaymentRedirectionProvider $paymentRedirectionProvider,
        LegacyTranslator $translator,
        Context $context
    ) {
        $this->module = $module;
        $this->cartService = $cartService;
        $this->paymentTypeProvider = $paymentTypeProvider;
        $this->obtainPaymentMethods = $obtainPaymentMethods;
        $this->paymentRepository = $paymentRepository;
        $this->paymentRestrictionValidation = $paymentRestrictionValidation;
        $this->currencyProvider = $currencyProvider;
        $this->cardGroupingService = $cardGroupingService;
        $this->cardAliasRepository = $cardAliasRepository;
        $this->paymentRedirectionProvider = $paymentRedirectionProvider;
        $this->translator = $translator;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $params)
    {
        if (!$this->cartService->isCurrencyAvailable($params['cart'])) {
            return [];
        }

        try {
            $paymentMethods = $this->obtainPaymentMethods->obtainPaymentMethods();
        } catch (SaferPayApiException $exception) {
            return [];
        }

        $paymentOptions = [];
        $logosEnabled = $this->getEnabledLogos();
        $allCurrencies = $this->currencyProvider->getAllCurrenciesInArray();

        if (Configuration::get(SaferPayConfig::SAFERPAY_GROUP_CARDS)) {
            $paymentMethods = $this->cardGroupingService->group($paymentMethods, $allCurrencies);
        }

        $isBusinessLicenseEnabled = Configuration::get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix());
        $isCreditCardSavingEnabled = Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);

        foreach ($paymentMethods as $paymentMethod) {
            $paymentOption = $this->buildPaymentOption(
                $paymentMethod,
                $logosEnabled,
                $allCurrencies,
                $isBusinessLicenseEnabled,
                $isCreditCardSavingEnabled
            );

            if ($paymentOption) {
                $paymentOptions[] = $paymentOption;
            }
        }

        return $paymentOptions;
    }

    /**
     * Build a single payment option
     *
     * @param array $paymentMethod
     * @param array $logosEnabled
     * @param array $allCurrencies
     * @param bool $isBusinessLicenseEnabled
     * @param bool $isCreditCardSavingEnabled
     * @return PaymentOption|null
     */
    private function buildPaymentOption(
        array $paymentMethod,
        array $logosEnabled,
        array $allCurrencies,
        $isBusinessLicenseEnabled,
        $isCreditCardSavingEnabled
    ) {
        $paymentMethod['paymentMethod'] = str_replace(' ', '', $paymentMethod['paymentMethod']);

        if (in_array($paymentMethod['paymentMethod'], SaferPayConfig::WALLET_PAYMENT_METHODS)) {
            $paymentMethod['currencies'] = $allCurrencies;
        }

        if (!in_array($this->context->currency->iso_code, $paymentMethod['currencies'])
            && !in_array($paymentMethod['paymentMethod'], SaferPayConfig::WALLET_PAYMENT_METHODS)) {
            return null;
        }

        if (!$this->paymentRestrictionValidation->isPaymentMethodValid($paymentMethod['paymentMethod'])) {
            return null;
        }

        $imageUrl = (in_array($paymentMethod['paymentMethod'], $logosEnabled))
            ? $paymentMethod['logoUrl'] : '';

        $isCreditCard = in_array(
            $paymentMethod['paymentMethod'],
            SaferPayConfig::TRANSACTION_METHODS
        );

        $selectedCard = 0;
        $isCreditCardSavingEnabledForUser = $isCreditCardSavingEnabled;

        if ($this->context->customer->is_guest) {
            $isCreditCardSavingEnabledForUser = false;
            $selectedCard = -1;
        }

        $newOption = new PaymentOption();
        $paymentMethodName = $this->translator->translate($paymentMethod['paymentMethod']);

        $inputs = [
            'saved_card_method' => [
                'name' => 'saved_card_method',
                'type' => 'hidden',
                'value' => $paymentMethod['paymentMethod'],
            ],
            'selectedCreditCard' => [
                'name' => "selectedCreditCard_{$paymentMethod['paymentMethod']}",
                'type' => 'hidden',
                'value' => $selectedCard,
            ],
        ];

        if ($isCreditCardSavingEnabledForUser && $isCreditCard && $isBusinessLicenseEnabled) {
            $currentDate = date('Y-m-d h:i:s');

            $savedCards = $this->cardAliasRepository->getSavedValidCardsByUserIdAndPaymentMethod(
                $this->context->customer->id,
                $paymentMethod['paymentMethod'],
                $currentDate
            );

            $this->module->smarty->assign(
                [
                    'savedCards' => $savedCards,
                    'paymentMethod' => $paymentMethod['paymentMethod'],
                ]
            );

            if ($savedCards) {
                $inputs['selectedCreditCard'] = [
                    'name' => "selectedCreditCard_{$paymentMethod['paymentMethod']}",
                    'type' => 'hidden',
                    'value' => $savedCards[0]['id_saferpay_card_alias'],
                ];
            }

            $newOption->setAdditionalInformation(
                $this->module->display($this->module->getPathUri(), 'front/saferpay_additional_info.tpl')
            );
        }

        $inputs['type'] = [
            'name' => 'saferpayPaymentType',
            'type' => 'hidden',
            'value' => $this->paymentTypeProvider->get($paymentMethod['paymentMethod']),
        ];

        $newOption->setModuleName($this->module->name)
            ->setCallToActionText($this->translator->translate($paymentMethodName))
            ->setAction($this->paymentRedirectionProvider->provideRedirectionLinkByPaymentMethod($paymentMethod['paymentMethod']))
            ->setLogo($imageUrl)
            ->setInputs($inputs);

        return $newOption;
    }

    /**
     * Get enabled logos
     *
     * @return array
     */
    private function getEnabledLogos()
    {
        $logosEnabled = $this->paymentRepository->getAllActiveLogosNames();
        $logosEnabled = array_column($logosEnabled, 'name');

        if (Configuration::get(SaferPayConfig::SAFERPAY_GROUP_CARDS_LOGO)) {
            $logosEnabled[] = SaferPayConfig::PAYMENT_CARDS;
        }

        return $logosEnabled;
    }
}
