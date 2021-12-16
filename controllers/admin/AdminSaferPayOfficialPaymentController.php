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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Exception\Restriction\RestrictionException;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayLogoRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Service\SaferPayFieldCreator;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;

class AdminSaferPayOfficialPaymentController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS("{$this->module->getPathUri()}views/css/admin/payment_method.css");
        $this->addJS("{$this->module->getPathUri()}views/js/admin/chosen_countries.js");
        $this->addJS("{$this->module->getPathUri()}views/js/admin/payment_method_all.js");
    }

    /**
     * Custom form processing
     */
    public function postProcess()
    {
        if (!Tools::isSubmit('submitAddconfiguration')) {
            return parent::postProcess();
        }

        /** @var SaferPayPaymentCreator $paymentCreation */
        $paymentCreation = $this->module->getModuleContainer()->get(SaferPayPaymentCreator::class);

        /** @var SaferPayLogoCreator $logoCreation */
        $logoCreation = $this->module->getModuleContainer()->get(SaferPayLogoCreator::class);

        /** @var SaferPayFieldCreator $fieldCreation */
        $fieldCreation = $this->module->getModuleContainer()->get(SaferPayFieldCreator::class);

        /** @var SaferPayRestrictionCreator $restrictionCreator */
        $restrictionCreator = $this->module->getModuleContainer()->get(SaferPayRestrictionCreator::class);

        /** @var \Invertus\SaferPay\Service\SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
        $saferPayObtainPaymentMethods = $this->module->getModuleContainer()->get(SaferPayObtainPaymentMethods::class);
        $paymentMethodsFromSaferPay = $saferPayObtainPaymentMethods->obtainPaymentMethodsNamesAsArray();

        $success = true;
        foreach ($paymentMethodsFromSaferPay as $paymentMethod) {
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
                $this->errors[] = $this->l('Wrong restriction type');
                $success = false;
            }
        }

        if (!$success) {
            $this->errors[] = $this->l('Failed update');
        } else {
            $this->confirmations[] = $this->l('Successful update');
        }
    }

    public function initContent()
    {
        if ($this->module instanceof SaferPayOfficial) {
            $this->content .= $this->module->displayNavigationTop();
        }
        parent::initContent();
        $this->content .= $this->renderShoppingPointOptions();
        $this->context->smarty->assign('content', $this->content);
    }

    protected function renderShoppingPointOptions()
    {
        $referralOptionsForm = new HelperForm();

        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->module->getModuleContainer()->get(SaferPayPaymentRepository::class);

        /** @var SaferPayLogoRepository $logoRepository */
        $logoRepository = $this->module->getModuleContainer()->get(SaferPayLogoRepository::class);

        /** @var SaferPayLogoRepository $fieldsRepository */
        $fieldRepository = $this->module->getModuleContainer()->get(SaferPayFieldRepository::class);

        /** @var SaferPayRestrictionRepository $restrictionRepository */
        $restrictionRepository = $this->module->getModuleContainer()->get(SaferPayRestrictionRepository::class);

        try {
            /** @var \Invertus\SaferPay\Service\SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
            $saferPayObtainPaymentMethods = $this->module->getModuleContainer()->get(SaferPayObtainPaymentMethods::class);
            $paymentMethodsFromSaferPay = $saferPayObtainPaymentMethods->obtainPaymentMethodsNamesAsArray();
        } catch (\Exception $exception) {
            /** @var \Invertus\SaferPay\Service\SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getModuleContainer()
                ->get(\Invertus\SaferPay\Service\SaferPayExceptionService::class);
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            $saferPayErrors[] = $exceptionService->getErrorMessageForException(
                $exception,
                $exceptionService->getErrorMessages()
            );
            $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);

            $this->errors[] = $this->l('Please connect to SaferPay system to allowed payment methods.');

            return;
        }

        $this->initForm();
        $fieldsForm = [];
        $fieldsForm[0]['form'] = $this->fields_form;

        foreach ($paymentMethodsFromSaferPay as $paymentMethod) {
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
                    'currencyOptions' => $this->getActiveCurrenciesList(),
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
        $countriesWithNames[0] = $this->l('All');
        foreach ($countries as $key => $country) {
            $countriesWithNames[$key] = $country['name'];
        }

        return $countriesWithNames;
    }

    public function getActiveCurrenciesList($onlyActive = true)
    {
        $langId = $this->context->language->id;
        $currencies = Currency::getCurrencies($langId, $onlyActive);
        $currenciesWithNames = [];
        $currenciesWithNames[0] = $this->l('All');
        foreach ($currencies as $currency) {
            $currenciesWithNames[$currency->id] = $currency->name;
        }

        return $currenciesWithNames;
    }

    protected function initForm()
    {
        $fields = [];
        $fields[] = [
            'type' => 'free',
            'name' => 'payment_method_label',
        ];
        $fields[] = [
            'type' => 'free',
            'label' => $this->l('All payments'),
            'name' => 'all',
            'form_group_class' => 'saferpay-group all-payments',
        ];

        try {
            /** @var \Invertus\SaferPay\Service\SaferPayObtainPaymentMethods $saferPayObtainPaymentMethods */
            $saferPayObtainPaymentMethods = $this->module->getModuleContainer()->get(SaferPayObtainPaymentMethods::class);
            $paymentMethodsFromSaferPay = $saferPayObtainPaymentMethods->obtainPaymentMethodsNamesAsArray();
        } catch (\Exception $exception) {
            /** @var \Invertus\SaferPay\Service\SaferPayExceptionService $exceptionService */
            $exceptionService = $this->module->getModuleContainer()
                ->get(\Invertus\SaferPay\Service\SaferPayExceptionService::class);
            $saferPayErrors = json_decode($this->context->cookie->saferPayErrors, true);
            $saferPayErrors[] = $exceptionService->getErrorMessageForException(
                $exception,
                $exceptionService->getErrorMessages()
            );
            $this->context->cookie->saferPayErrors = json_encode($saferPayErrors);

            $this->errors[] = $this->l('Please connect to SaferPay system to allowed payment methods.');

            return;
        }

        foreach ($paymentMethodsFromSaferPay as $paymentMethod) {
            $fields[] = [
                'type' => 'free',
                'label' => $this->l($paymentMethod),
                'name' => $paymentMethod,
                'form_group_class' => 'saferpay-group',
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Payments'),
            ],
            'input' =>
                $fields,
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }
}
