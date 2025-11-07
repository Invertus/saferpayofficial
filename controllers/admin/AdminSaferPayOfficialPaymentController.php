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

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Exception\Restriction\RestrictionException;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayLogoRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\SaferPayExceptionService;
use Invertus\SaferPay\Service\SaferPayFieldCreator;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayPaymentNotation;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayRefreshPaymentsService;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;

class AdminSaferPayOfficialPaymentController extends ModuleAdminController
{
    /** @var \SaferPayOfficial */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS('modules/' . $this->module->name . '/views/css/admin/payment_method.css');
        $this->addJS('modules/' . $this->module->name . '/views/js/admin/chosen_countries.js');
        $this->addJS('modules/' . $this->module->name . '/views/js/admin/payment_method_all.js');
    }

    /**
     * Custom form processing
     */
    public function postProcess()
    {
        // Refresh payments.
        /** @var SaferPayRefreshPaymentsService $refreshPaymentsService */
        $refreshPaymentsService = $this->module->getService(SaferPayRefreshPaymentsService::class);
        try {
            $refreshPaymentsService->refreshPayments();
        } catch (SaferPayApiException $exception) {
            $this->errors[] = $this->module->l($exception->getMessage());
        }

        if (!Tools::isSubmit('submitAddconfiguration')) {
            return parent::postProcess();
        }

        /** @var SaferPayPaymentCreator $paymentCreation */
        $paymentCreation = $this->module->getService(SaferPayPaymentCreator::class);

        /** @var SaferPayLogoCreator $logoCreation */
        $logoCreation = $this->module->getService(SaferPayLogoCreator::class);

        /** @var SaferPayFieldCreator $fieldCreation */
        $fieldCreation = $this->module->getService(SaferPayFieldCreator::class);

        /** @var SaferPayRestrictionCreator $restrictionCreator */
        $restrictionCreator = $this->module->getService(SaferPayRestrictionCreator::class);

        $paymentMethods = $this->getPaymentMethods();
        if (is_null($paymentMethods)) {
            return;
        }

        $success = true;
        foreach ($paymentMethods as $paymentMethod) {
            $isActive = Tools::getValue($paymentMethod . '_enable');
            $success &= $paymentCreation->updatePayment($paymentMethod, $isActive);

            $isActive = Tools::getValue($paymentMethod . '_logo');
            $success &= $logoCreation->updateLogo($paymentMethod, $isActive);

            $isActive = Tools::getValue($paymentMethod . '_field');
            $success &= $fieldCreation->updateField($paymentMethod, $isActive);

            try {
                $success &= $restrictionCreator->updateRestriction(
                    $paymentMethod,
                    SaferPayRestrictionCreator::RESTRICTION_COUNTRY,
                    Tools::getValue($paymentMethod . SaferPayRestrictionCreator::COUNTRY_SUFFIX)
                );
                $success &= $restrictionCreator->updateRestriction(
                    $paymentMethod,
                    SaferPayRestrictionCreator::RESTRICTION_CURRENCY,
                    Tools::getValue($paymentMethod . SaferPayRestrictionCreator::CURRENCY_SUFFIX)
                );
            } catch (RestrictionException $e) {
                $this->errors[] = $this->module->l('Wrong restriction type');
                $success = false;
            }
        }

        if (!$success) {
            $this->errors[] = $this->module->l('Failed update');
        } else {
            $this->confirmations[] = $this->module->l('Successful update');
        }

        return true;
    }

    public function initContent()
    {
        parent::initContent();

        $this->content .= $this->renderShoppingPointOptions();
        $this->context->smarty->assign('content', $this->content);
    }

    protected function renderShoppingPointOptions()
    {
        $referralOptionsForm = new HelperForm();

        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->module->getService(SaferPayPaymentRepository::class);

        /** @var SaferPayLogoRepository $logoRepository */
        $logoRepository = $this->module->getService(SaferPayLogoRepository::class);

        /** @var SaferPayFieldRepository $fieldRepository */
        $fieldRepository = $this->module->getService(SaferPayFieldRepository::class);

        /** @var SaferPayRestrictionRepository $restrictionRepository */
        $restrictionRepository = $this->module->getService(SaferPayRestrictionRepository::class);

        $paymentMethods = $this->getPaymentMethods();
        if (is_null($paymentMethods)) {
            return;
        }

        $this->initForm();
        $fieldsForm = [];
        $fieldsForm[0]['form'] = $this->fields_form;

        /** @var SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
        $saferPayObtainPaymentMethods = $this->module->getService(SaferPayObtainPaymentMethods::class);

        $paymentMethodsList = $saferPayObtainPaymentMethods->obtainPaymentMethods();

        foreach ($paymentMethods as $paymentMethod) {
            $isActive = $paymentRepository->isActiveByName($paymentMethod);
            $isLogoActive = $logoRepository->isActiveByName($paymentMethod);
            $isFieldActive = $fieldRepository->isActiveByName($paymentMethod);
            $selectedCountries = $restrictionRepository->getSelectedIdsByName(
                $paymentMethod,
                SaferPayRestrictionCreator::RESTRICTION_COUNTRY
            );
            $selectedCurrencies = $restrictionRepository->getSelectedIdsByName(
                $paymentMethod,
                SaferPayRestrictionCreator::RESTRICTION_CURRENCY
            );

            $this->context->smarty->assign(
                [
                    'is_active' => $isActive,
                    'is_logo_active' => $isLogoActive,
                    'paymentMethod' => $paymentMethod,
                    'countryOptions' => $this->getActiveCountriesList(),
                    'countrySelect' => $selectedCountries,
                    'currencyOptions' => $this->getActiveCurrenciesList($paymentMethod, $paymentMethodsList),
                    'currencySelect' => $selectedCurrencies,
                    'is_field_active' => $isFieldActive,
                    'supported_field_payments' => SaferPayConfig::FIELD_SUPPORTED_PAYMENT_METHODS,
                ]
            );
            $referralOptionsForm->fields_value[$paymentMethod] =
                $this->context->smarty->fetch(
                    $this->module->getLocalPath() . 'views/templates/admin/payment_method.tpl'
                );
        }
        $referralOptionsForm->fields_value['all'] =
            $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/payment_method_all.tpl'
            );
        $referralOptionsForm->fields_value['payment_method_label'] =
            $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/payment_method_label.tpl'
            );
        $this->content .= $referralOptionsForm->generateForm($fieldsForm);
    }

    public function getActiveCountriesList($onlyActive = true)
    {
        $langId = $this->context->language->id;
        $countries = Country::getCountries($langId, $onlyActive);
        $countriesWithNames = [];
        $countriesWithNames[0] = $this->module->l('All');
        foreach ($countries as $key => $country) {
            $countriesWithNames[$key] = $country['name'];
        }

        return $countriesWithNames;
    }

    public function getActiveCurrenciesList($paymentMethod, $paymentMethods)
    {
        $currencyOptions[0] = $this->module->l('All');

        if (!isset($paymentMethods[$paymentMethod]['currencies']) && in_array($paymentMethod, SaferPayConfig::WALLET_PAYMENT_METHODS)) {
            foreach (Currency::getCurrencies() as $currency) {
                $currencyOptions[$currency['id_currency']] = $currency['iso_code'];
            }

            return $currencyOptions;
        }

        foreach ($paymentMethods[$paymentMethod]['currencies'] as $currencyIso) {
            if (Currency::getIdByIsoCode($currencyIso)) {
                $currencyOptions[Currency::getIdByIsoCode($currencyIso)] = $currencyIso;
            }
        }

        return $currencyOptions;
    }

    protected function initForm()
    {
        $fields = [];
        $fields[] = [
            'type' => 'free',
            'label' => '',
            'name' => 'payment_method_label',
        ];
        $fields[] = [
            'type' => 'free',
            'label' => $this->module->l('All payments'),
            'name' => 'all',
            'form_group_class' => 'saferpay-group all-payments',
        ];

        try {
            /** @var SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
            $saferPayObtainPaymentMethods = $this->module->getService(SaferPayObtainPaymentMethods::class);
            $paymentMethods = $saferPayObtainPaymentMethods->obtainPaymentMethodsNamesAsArray();
        } catch (SaferPayApiException $exception) {
            /** @var SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getService(SaferPayExceptionService::class);
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            $saferPayErrors[] = $exceptionService->getErrorMessageForException(
                $exception,
                $exceptionService->getErrorMessages()
            );
            $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);

            $this->errors[] = $this->module->l('Please connect to SaferPay system to allowed payment methods.');

            return;
        }
        /** @var SaferPayPaymentNotation $saferPayPaymentNotation */
        $saferPayPaymentNotation = $this->module->getService(SaferPayPaymentNotation::class);

        foreach ($paymentMethods as $paymentMethod) {
            $fields[] = [
                'type' => 'free',
                'label' => $saferPayPaymentNotation->getForDisplay($paymentMethod),
                'name' => $paymentMethod,
                'form_group_class' => 'saferpay-group',
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Payments'),
            ],
            'input' =>
                $fields,
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];
    }

    private function getPaymentMethods()
    {
        try {
            /** @var SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
            $saferPayObtainPaymentMethods = $this->module->getService(SaferPayObtainPaymentMethods::class);

            return $saferPayObtainPaymentMethods->obtainPaymentMethodsNamesAsArray();
        } catch (SaferPayApiException $exception) {
            /** @var SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getService(SaferPayExceptionService::class);
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            $saferPayErrors[] = $exceptionService->getErrorMessageForException(
                $exception,
                $exceptionService->getErrorMessages()
            );
            $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);

            $this->errors[] = $this->module->l('To see available payment methods, you must connect to your SaferPay account.');

            return null;
        }
    }
}
