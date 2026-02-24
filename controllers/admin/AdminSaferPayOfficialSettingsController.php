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
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayLogoRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use Invertus\SaferPay\Repository\SaferPaySavedCreditCardRepository;
use Invertus\SaferPay\Adapter\Configuration as SaferPayConfiguration;
use Invertus\SaferPay\Service\SaferPayFieldCreator;
use Invertus\SaferPay\Service\SaferPayGetTerminals;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayPaymentNotation;
use Invertus\SaferPay\Service\SaferPayRefreshPaymentsService;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Exception\Restriction\RestrictionException;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialSettingsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminSaferPayOfficialSettingsController';
    const PASSWORD_PLACEHOLDER = '********';

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

        $distPath = 'modules/' . $this->module->name . '/views/js/admin/dist/';
        $this->addJS($distPath . 'saferpay-settings.js');
        $this->addCSS($distPath . 'saferpay-settings.css');
    }

    public function initContent()
    {
        parent::initContent();

        $settingsData = $this->collectSettingsData();

        $this->context->smarty->assign([
            'settingsDataJson' => json_encode($settingsData),
        ]);

        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/settings_react.tpl'
        );
        $this->context->smarty->assign('content', $this->content);
    }

    public function postProcess()
    {
        if (!$this->isAjax()) {
            return parent::postProcess();
        }

        $action = Tools::getValue('action');
        if ($action) {
            $methodName = 'ajaxProcess' . ucfirst($action);
            if (method_exists($this, $methodName)) {
                $this->{$methodName}();
            }
        }
    }

    /**
     * Check if current request is AJAX
     */
    private function isAjax()
    {
        return Tools::getValue('ajax') == 1;
    }

    /**
     * AJAX: Save API credentials
     */
    public function ajaxProcessSaveCredentials()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        // Test mode
        $configuration->set(SaferPayConfig::TEST_MODE, !empty($data['testMode']) ? 1 : 0);

        // Test credentials
        $configuration->set(SaferPayConfig::USERNAME . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testUsername'));
        $testPassword = $this->getStringValue($data, 'testPassword');
        if ($testPassword && $testPassword !== self::PASSWORD_PLACEHOLDER) {
            $configuration->set(SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX, $testPassword);
        }
        $configuration->set(SaferPayConfig::CUSTOMER_ID . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testCustomerId'));
        $configuration->set(SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testTerminalId'));
        $configuration->set(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testMerchantEmails'));
        $configuration->set(SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testFieldAccessToken'));
        $configuration->set(SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testFieldJsUrl'));
        $configuration->set(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX, !empty($data['testBusinessLicense']) ? 1 : 0);

        // Live credentials
        $configuration->set(SaferPayConfig::USERNAME, $this->getStringValue($data, 'liveUsername'));
        $livePassword = $this->getStringValue($data, 'livePassword');
        if ($livePassword && $livePassword !== self::PASSWORD_PLACEHOLDER) {
            $configuration->set(SaferPayConfig::PASSWORD, $livePassword);
        }
        $configuration->set(SaferPayConfig::CUSTOMER_ID, $this->getStringValue($data, 'liveCustomerId'));
        $configuration->set(SaferPayConfig::TERMINAL_ID, $this->getStringValue($data, 'liveTerminalId'));
        $configuration->set(SaferPayConfig::MERCHANT_EMAILS, $this->getStringValue($data, 'liveMerchantEmails'));
        $configuration->set(SaferPayConfig::FIELDS_ACCESS_TOKEN, $this->getStringValue($data, 'liveFieldAccessToken'));
        $configuration->set(SaferPayConfig::FIELDS_LIBRARY, $this->getStringValue($data, 'liveFieldJsUrl'));
        $configuration->set(SaferPayConfig::BUSINESS_LICENSE, !empty($data['liveBusinessLicense']) ? 1 : 0);

        // Validate: business license requires field access token
        $suffix = SaferPayConfig::getConfigSuffix();
        $haveFieldToken = $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN . $suffix);
        $haveBusinessLicense = $configuration->get(SaferPayConfig::BUSINESS_LICENSE . $suffix);

        if (!$haveFieldToken && $haveBusinessLicense) {
            $configuration->set(SaferPayConfig::BUSINESS_LICENSE . $suffix, 0);
            $this->ajaxResponse(true, 'Saved, but Field Access Token is required to use business license. Business license was disabled.');
            return;
        }

        $this->ajaxResponse(true, 'API Credentials saved successfully');
    }

    /**
     * AJAX: Save payment processing settings
     */
    public function ajaxProcessSavePaymentProcessing()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::PAYMENT_BEHAVIOR, (int) $this->getIntValue($data, 'paymentBehavior'));
        $configuration->set(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D, (int) $this->getIntValue($data, 'paymentBehaviorWithout3D'));
        $configuration->set(SaferPayConfig::RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT, (int) $this->getIntValue($data, 'restrictRefund'));
        $configuration->set(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION, (int) $this->getIntValue($data, 'orderCreationAfterAuth'));
        $configuration->set(SaferPayConfig::SAFERPAY_GROUP_CARDS, !empty($data['groupCards']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_GROUP_CARDS_LOGO, !empty($data['groupCardsLogo']) ? 1 : 0);
        $configuration->set(SaferPayConfig::CREDIT_CARD_SAVE, (int) $this->getIntValue($data, 'creditCardSave'));

        // If credit card save disabled, clean up saved cards
        if (empty($data['creditCardSave']) || (int) $data['creditCardSave'] === 0) {
            /** @var SaferPaySavedCreditCardRepository $cardRepo */
            $cardRepo = $this->module->getService(SaferPaySavedCreditCardRepository::class);
            $cardRepo->deleteAllSavedCreditCards();
        }

        $this->ajaxResponse(true, 'Payment Processing saved successfully');
    }

    /**
     * AJAX: Save email settings
     */
    public function ajaxProcessSaveEmailSettings()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL, !empty($data['allowSaferpayMail']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL, !empty($data['sendNewOrderMail']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_SEND_ORDER_CONF_MAIL, !empty($data['sendOrderConfMail']) ? 1 : 0);

        $this->ajaxResponse(true, 'Email settings saved successfully');
    }

    /**
     * AJAX: Save general settings
     */
    public function ajaxProcessSaveGeneralSettings()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT, (int) $this->getIntValue($data, 'orderStateAwaitingPayment'));
        $configuration->set(SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION, $this->getStringValue($data, 'paymentDescription'));
        $configuration->set(SaferPayConfig::CONFIGURATION_NAME, $this->getStringValue($data, 'configurationName'));
        $configuration->set(SaferPayConfig::SAFERPAY_DEBUG_MODE, !empty($data['debugMode']) ? 1 : 0);

        $this->ajaxResponse(true, 'General settings saved successfully');
    }

    /**
     * AJAX: Save payment methods
     */
    public function ajaxProcessSavePaymentMethods()
    {
        $data = $this->getJsonInput();
        if (!$data || !isset($data['paymentMethods'])) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        // Refresh payments first
        /** @var SaferPayRefreshPaymentsService $refreshPaymentsService */
        $refreshPaymentsService = $this->module->getService(SaferPayRefreshPaymentsService::class);
        try {
            $refreshPaymentsService->refreshPayments();
        } catch (SaferPayApiException $exception) {
            $this->ajaxResponse(false, $exception->getMessage());
            return;
        }

        /** @var SaferPayPaymentCreator $paymentCreation */
        $paymentCreation = $this->module->getService(SaferPayPaymentCreator::class);

        /** @var SaferPayLogoCreator $logoCreation */
        $logoCreation = $this->module->getService(SaferPayLogoCreator::class);

        /** @var SaferPayFieldCreator $fieldCreation */
        $fieldCreation = $this->module->getService(SaferPayFieldCreator::class);

        /** @var SaferPayRestrictionCreator $restrictionCreator */
        $restrictionCreator = $this->module->getService(SaferPayRestrictionCreator::class);

        $success = true;
        foreach ($data['paymentMethods'] as $method) {
            $paymentName = $method['name'];
            $success &= $paymentCreation->updatePayment($paymentName, !empty($method['enabled']));
            $success &= $logoCreation->updateLogo($paymentName, !empty($method['showLogos']));
            $success &= $fieldCreation->updateField($paymentName, !empty($method['showCustomForm']));

            try {
                $countries = isset($method['countries']) ? $method['countries'] : [];
                $currencies = isset($method['currencies']) ? $method['currencies'] : [];

                $success &= $restrictionCreator->updateRestriction(
                    $paymentName,
                    SaferPayRestrictionCreator::RESTRICTION_COUNTRY,
                    $countries
                );
                $success &= $restrictionCreator->updateRestriction(
                    $paymentName,
                    SaferPayRestrictionCreator::RESTRICTION_CURRENCY,
                    $currencies
                );
            } catch (RestrictionException $e) {
                $this->ajaxResponse(false, 'Wrong restriction type');
                return;
            }
        }

        if (!$success) {
            $this->ajaxResponse(false, 'Failed to update payment methods');
            return;
        }

        $this->ajaxResponse(true, 'Payment methods saved successfully');
    }

    /**
     * AJAX: Get terminals
     */
    public function ajaxProcessGetTerminals()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, 'Invalid request data');
            return;
        }

        $username = isset($data['username']) ? $data['username'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $customerId = isset($data['customerId']) ? $data['customerId'] : '';
        $isTestMode = isset($data['env']) && $data['env'] === 'test';

        if ($password === self::PASSWORD_PLACEHOLDER) {
            $suffix = $isTestMode ? SaferPayConfig::TEST_SUFFIX : '';
            /** @var SaferPayConfiguration $configuration */
            $configuration = $this->module->getService(SaferPayConfiguration::class);
            $password = (string) $configuration->get(SaferPayConfig::PASSWORD . $suffix);
        }

        if (empty($username) || empty($password) || empty($customerId)) {
            $this->ajaxResponse(false, 'Username, password and customer ID are required');
            return;
        }

        try {
            /** @var SaferPayGetTerminals $getTerminals */
            $getTerminals = $this->module->getService(SaferPayGetTerminals::class);
            $terminals = $getTerminals->fetchTerminalsWithCredentials($username, $password, $customerId, $isTestMode);

            $this->ajaxDie(json_encode([
                'success' => true,
                'terminals' => $terminals,
            ]));
        } catch (\Exception $e) {
            $this->ajaxResponse(false, 'Failed to fetch terminals: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Refresh all data
     */
    public function ajaxProcessRefreshData()
    {
        $settingsData = $this->collectSettingsData();
        $this->ajaxDie(json_encode([
            'success' => true,
            'data' => $settingsData,
        ]));
    }

    /**
     * Collect all settings data to pass to the React app
     */
    private function collectSettingsData()
    {
        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $data = [
            // Environment
            'testMode' => (bool) $configuration->get(SaferPayConfig::TEST_MODE),

            // Test credentials
            'testUsername' => (string) $configuration->get(SaferPayConfig::USERNAME . SaferPayConfig::TEST_SUFFIX),
            'testPassword' => $configuration->get(SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX) ? self::PASSWORD_PLACEHOLDER : '',
            'testCustomerId' => (string) $configuration->get(SaferPayConfig::CUSTOMER_ID . SaferPayConfig::TEST_SUFFIX),
            'testTerminalId' => (string) $configuration->get(SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX),
            'testMerchantEmails' => (string) $configuration->get(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX),
            'testFieldAccessToken' => (string) $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX),
            'testFieldJsUrl' => (string) $configuration->get(SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX),
            'testBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX),

            // Live credentials
            'liveUsername' => (string) $configuration->get(SaferPayConfig::USERNAME),
            'livePassword' => $configuration->get(SaferPayConfig::PASSWORD) ? self::PASSWORD_PLACEHOLDER : '',
            'liveCustomerId' => (string) $configuration->get(SaferPayConfig::CUSTOMER_ID),
            'liveTerminalId' => (string) $configuration->get(SaferPayConfig::TERMINAL_ID),
            'liveMerchantEmails' => (string) $configuration->get(SaferPayConfig::MERCHANT_EMAILS),
            'liveFieldAccessToken' => (string) $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN),
            'liveFieldJsUrl' => (string) $configuration->get(SaferPayConfig::FIELDS_LIBRARY),
            'liveBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE),

            // Payment Processing
            'paymentBehavior' => (int) $configuration->get(SaferPayConfig::PAYMENT_BEHAVIOR),
            'paymentBehaviorWithout3D' => (int) $configuration->get(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D),
            'restrictRefund' => (int) $configuration->get(SaferPayConfig::RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT),
            'orderCreationAfterAuth' => (int) $configuration->get(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION),
            'groupCards' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_GROUP_CARDS),
            'groupCardsLogo' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_GROUP_CARDS_LOGO),
            'creditCardSave' => (int) $configuration->get(SaferPayConfig::CREDIT_CARD_SAVE),

            // Email
            'allowSaferpayMail' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL),
            'sendNewOrderMail' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL),
            'sendOrderConfMail' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_SEND_ORDER_CONF_MAIL),

            // General
            'orderStateAwaitingPayment' => (int) $configuration->get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            'paymentDescription' => (string) $configuration->get(SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION),
            'configurationName' => (string) $configuration->get(SaferPayConfig::CONFIGURATION_NAME),
            'debugMode' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_DEBUG_MODE),

            // Reference data
            'orderStates' => $this->getOrderStates(),
            'countries' => $this->getCountries(),
            'currencies' => $this->getCurrencies(),
            'paymentMethods' => $this->getPaymentMethodsData(),

            // Endpoints
            'ajaxUrl' => $this->context->link->getAdminLink('AdminSaferPayOfficialSettings'),
            'adminToken' => Tools::getAdminTokenLite('AdminSaferPayOfficialSettings'),
        ];

        return $data;
    }

    /**
     * Get order states for dropdown
     */
    private function getOrderStates()
    {
        $states = OrderState::getOrderStates($this->context->language->id);
        $result = [];
        foreach ($states as $state) {
            $result[] = [
                'id' => (int) $state['id_order_state'],
                'name' => $state['name'],
            ];
        }
        return $result;
    }

    /**
     * Get active countries
     */
    private function getCountries()
    {
        $countries = Country::getCountries($this->context->language->id, true);
        $result = [];
        $result[] = ['id' => 0, 'name' => 'All'];
        foreach ($countries as $key => $country) {
            $result[] = [
                'id' => (int) $key,
                'name' => $country['name'],
            ];
        }
        return $result;
    }

    /**
     * Get active currencies
     */
    private function getCurrencies()
    {
        $currencies = Currency::getCurrencies();
        $result = [];
        $result[] = ['id' => 0, 'iso_code' => 'All'];
        foreach ($currencies as $currency) {
            $result[] = [
                'id' => (int) $currency['id_currency'],
                'iso_code' => $currency['iso_code'],
            ];
        }
        return $result;
    }

    /**
     * Get payment methods data with their current state
     */
    private function getPaymentMethodsData()
    {
        try {
            /** @var SaferPayObtainPaymentMethods $obtainMethods */
            $obtainMethods = $this->module->getService(SaferPayObtainPaymentMethods::class);
            $paymentMethods = $obtainMethods->obtainPaymentMethodsNamesAsArray();
        } catch (SaferPayApiException $exception) {
            return [];
        }

        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->module->getService(SaferPayPaymentRepository::class);

        /** @var SaferPayLogoRepository $logoRepository */
        $logoRepository = $this->module->getService(SaferPayLogoRepository::class);

        /** @var SaferPayFieldRepository $fieldRepository */
        $fieldRepository = $this->module->getService(SaferPayFieldRepository::class);

        /** @var SaferPayRestrictionRepository $restrictionRepository */
        $restrictionRepository = $this->module->getService(SaferPayRestrictionRepository::class);

        /** @var SaferPayPaymentNotation $saferPayPaymentNotation */
        $saferPayPaymentNotation = $this->module->getService(SaferPayPaymentNotation::class);

        $result = [];
        foreach ($paymentMethods as $paymentMethod) {
            $result[] = [
                'name' => $paymentMethod,
                'displayName' => $saferPayPaymentNotation->getForDisplay($paymentMethod),
                'enabled' => (bool) $paymentRepository->isActiveByName($paymentMethod),
                'showLogos' => (bool) $logoRepository->isActiveByName($paymentMethod),
                'showCustomForm' => (bool) $fieldRepository->isActiveByName($paymentMethod),
                'hasCustomForm' => in_array($paymentMethod, SaferPayConfig::FIELD_SUPPORTED_PAYMENT_METHODS),
                'countries' => $restrictionRepository->getSelectedIdsByName(
                    $paymentMethod,
                    SaferPayRestrictionCreator::RESTRICTION_COUNTRY
                ),
                'currencies' => $restrictionRepository->getSelectedIdsByName(
                    $paymentMethod,
                    SaferPayRestrictionCreator::RESTRICTION_CURRENCY
                ),
            ];
        }

        return $result;
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Send AJAX JSON response
     */
    private function ajaxResponse($success, $message = '')
    {
        $this->ajaxDie(json_encode([
            'success' => $success,
            'message' => $message,
        ]));
    }

    /**
     * Get string value from data array
     */
    private function getStringValue($data, $key)
    {
        return isset($data[$key]) ? pSQL((string) $data[$key]) : '';
    }

    /**
     * Get int value from data array
     */
    private function getIntValue($data, $key)
    {
        return isset($data[$key]) ? (int) $data[$key] : 0;
    }
}
