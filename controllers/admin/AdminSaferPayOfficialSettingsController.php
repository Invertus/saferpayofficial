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
use Invertus\SaferPay\Service\SaferPayGenerateFieldAccessToken;
use Invertus\SaferPay\Service\SaferPayGetLicense;
use Invertus\SaferPay\Service\SaferPayGetTerminals;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayObtainPaymentMethods;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayPaymentNotation;
use Invertus\SaferPay\Service\SaferPayRefreshPaymentsService;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Exception\Restriction\RestrictionException;
use Invertus\SaferPay\Logger\LoggerInterface;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialSettingsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminSaferPayOfficialSettingsController';
    const PASSWORD_PLACEHOLDER = '********';

    const ALLOWED_AJAX_ACTIONS = [
        'saveCredentials',
        'savePaymentProcessing',
        'saveEmailSettings',
        'saveGeneralSettings',
        'savePaymentMethods',
        'getTerminals',
        'generateFieldAccessToken',
        'refreshData',
    ];

    /**
     * AJAX actions that change state and therefore require 'edit' permission.
     */
    const STATE_CHANGING_AJAX_ACTIONS = [
        'saveCredentials',
        'savePaymentProcessing',
        'saveEmailSettings',
        'saveGeneralSettings',
        'savePaymentMethods',
        'generateFieldAccessToken',
    ];

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

        if (!$this->validateAjaxToken()) {
            $this->ajaxResponse(false, $this->module->l('Invalid security token', self::FILE_NAME));
            return;
        }

        $action = Tools::getValue('action');
        if (!$action || !in_array($action, self::ALLOWED_AJAX_ACTIONS)) {
            $this->ajaxResponse(false, $this->module->l('Invalid action', self::FILE_NAME));
            return;
        }

        // Bypassing parent::postProcess() skips PrestaShop's native permission checks,
        // so state-changing actions must explicitly require 'edit' permission.
        if (in_array($action, self::STATE_CHANGING_AJAX_ACTIONS) && !$this->access('edit')) {
            $this->ajaxResponse(false, $this->module->l('You do not have permission to edit these settings.', self::FILE_NAME));
            return;
        }

        $methodName = 'ajaxProcess' . ucfirst($action);
        $this->{$methodName}();
    }

    /**
     * Check if current request is AJAX
     */
    private function isAjax()
    {
        return (int) Tools::getValue('ajax') === 1;
    }

    /**
     * Validate AJAX requests come from authenticated admin
     */
    private function validateAjaxToken()
    {
        // In PS9, the routing layer already validates the admin token in the URL
        // before the controller is reached. We just verify the employee is logged in.
        return $this->context->employee && $this->context->employee->id;
    }

    /**
     * AJAX: Save API credentials
     */
    public function ajaxProcessSaveCredentials()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, $this->module->l('Invalid request data', self::FILE_NAME));
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        // Resolve active credentials for validation before saving
        $isTestMode = !empty($data['testMode']);
        $activeUsername = $isTestMode ? $this->getStringValue($data, 'testUsername') : $this->getStringValue($data, 'liveUsername');
        $activePassword = $isTestMode ? $this->getStringValue($data, 'testPassword') : $this->getStringValue($data, 'livePassword');
        $activeCustomerId = $this->parseCustomerIdFromUsername($activeUsername);

        if ($activePassword === self::PASSWORD_PLACEHOLDER) {
            $passwordSuffix = $isTestMode ? SaferPayConfig::TEST_SUFFIX : '';
            $activePassword = (string) $configuration->get(SaferPayConfig::PASSWORD . $passwordSuffix);
        }

        // Validate credentials against Saferpay API before saving
        if (!empty($activeUsername) && !empty($activePassword)) {
            if (empty($activeCustomerId)) {
                $this->ajaxResponse(false, $this->module->l('Invalid API username. Please check your credentials and try again.', self::FILE_NAME));
                return;
            }

            try {
                /** @var SaferPayGetTerminals $getTerminals */
                $getTerminals = $this->module->getService(SaferPayGetTerminals::class);
                $getTerminals->fetchTerminalsWithCredentials($activeUsername, $activePassword, $activeCustomerId, $isTestMode);
            } catch (\Exception $e) {
                $this->ajaxResponse(false, $this->parseApiErrorMessage($e->getMessage()));
                return;
            }
        }

        $testMerchantEmails = $this->getStringValue($data, 'testMerchantEmails');
        $liveMerchantEmails = $this->getStringValue($data, 'liveMerchantEmails');
        $invalidEmail = $this->findInvalidEmail($testMerchantEmails) ?: $this->findInvalidEmail($liveMerchantEmails);
        if ($invalidEmail !== null) {
            $this->ajaxResponse(false, sprintf(
                $this->module->l('Invalid merchant email address: %s', self::FILE_NAME),
                $invalidEmail
            ));
            return;
        }

        // Credentials validated — now save
        $configuration->set(SaferPayConfig::TEST_MODE, $isTestMode ? 1 : 0);

        // Test credentials
        $testUsername = $this->getStringValue($data, 'testUsername');
        $configuration->set(SaferPayConfig::USERNAME . SaferPayConfig::TEST_SUFFIX, $testUsername);
        $testPassword = $this->getStringValue($data, 'testPassword');
        if ($testPassword && $testPassword !== self::PASSWORD_PLACEHOLDER) {
            $configuration->set(SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX, $testPassword);
        }
        $configuration->set(SaferPayConfig::CUSTOMER_ID . SaferPayConfig::TEST_SUFFIX, $this->parseCustomerIdFromUsername($testUsername));
        $configuration->set(SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testTerminalId'));
        $configuration->set(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testMerchantEmails'));
        $configuration->set(SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testFieldAccessToken'));
        $configuration->set(SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX, $this->getStringValue($data, 'testFieldJsUrl'));

        // Live credentials
        $liveUsername = $this->getStringValue($data, 'liveUsername');
        $configuration->set(SaferPayConfig::USERNAME, $liveUsername);
        $livePassword = $this->getStringValue($data, 'livePassword');
        if ($livePassword && $livePassword !== self::PASSWORD_PLACEHOLDER) {
            $configuration->set(SaferPayConfig::PASSWORD, $livePassword);
        }
        $configuration->set(SaferPayConfig::CUSTOMER_ID, $this->parseCustomerIdFromUsername($liveUsername));
        $configuration->set(SaferPayConfig::TERMINAL_ID, $this->getStringValue($data, 'liveTerminalId'));
        $configuration->set(SaferPayConfig::MERCHANT_EMAILS, $this->getStringValue($data, 'liveMerchantEmails'));
        $configuration->set(SaferPayConfig::FIELDS_ACCESS_TOKEN, $this->getStringValue($data, 'liveFieldAccessToken'));
        $configuration->set(SaferPayConfig::FIELDS_LIBRARY, $this->getStringValue($data, 'liveFieldJsUrl'));

        // Auto-detect license features from Saferpay Management API
        $suffix = $isTestMode ? SaferPayConfig::TEST_SUFFIX : '';
        $hasBusinessLicense = false;
        $licenseFetchFailed = false;

        if (!empty($activeUsername) && !empty($activePassword) && !empty($activeCustomerId)) {
            try {
                /** @var SaferPayGetLicense $getLicense */
                $getLicense = $this->module->getService(SaferPayGetLicense::class);
                $licenseInfo = $getLicense->fetchLicenseWithCredentials(
                    $activeUsername,
                    $activePassword,
                    $activeCustomerId,
                    $isTestMode
                );

                $hasBusinessLicense = $licenseInfo['hasBusinessLicense'];
                $configuration->set(SaferPayConfig::BUSINESS_LICENSE . $suffix, $hasBusinessLicense ? 1 : 0);
            } catch (\Exception $e) {
                $configuration->set(SaferPayConfig::BUSINESS_LICENSE . $suffix, 0);
                $licenseFetchFailed = true;

                /** @var LoggerInterface $logger */
                $logger = $this->module->getService(LoggerInterface::class);
                $logger->error('License fetch failed on credentials save: ' . $e->getMessage(), [
                    'context' => ['exception_class' => get_class($e)],
                ]);
            }
        } else {
            $configuration->set(SaferPayConfig::BUSINESS_LICENSE . $suffix, 0);
        }

        $message = $licenseFetchFailed
            ? $this->module->l('Settings saved, but Saferpay Fields availability could not be confirmed. Please try again later or check the module Logs for details.', self::FILE_NAME)
            : $this->module->l('Settings saved successfully.', self::FILE_NAME);

        $this->ajaxResponse(
            true,
            $message,
            [
                'testHasBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX),
                'liveHasBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE),
                'warning' => $licenseFetchFailed,
            ]
        );
    }

    /**
     * AJAX: Save payment processing settings
     */
    public function ajaxProcessSavePaymentProcessing()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, $this->module->l('Invalid request data', self::FILE_NAME));
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::PAYMENT_BEHAVIOR, $this->getIntValue($data, 'paymentBehavior'));
        $configuration->set(SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D, $this->getIntValue($data, 'paymentBehaviorWithout3D'));
        $configuration->set(SaferPayConfig::RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT, $this->getIntValue($data, 'restrictRefund'));
        $configuration->set(SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION, $this->getIntValue($data, 'orderCreationAfterAuth'));
        $configuration->set(SaferPayConfig::SAFERPAY_GROUP_CARDS, !empty($data['groupCards']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_GROUP_CARDS_LOGO, !empty($data['groupCardsLogo']) ? 1 : 0);
        $configuration->set(SaferPayConfig::CREDIT_CARD_SAVE, $this->getIntValue($data, 'creditCardSave'));

        // If credit card save disabled, clean up saved cards
        if (empty($data['creditCardSave']) || (int) $data['creditCardSave'] === 0) {
            /** @var SaferPaySavedCreditCardRepository $cardRepo */
            $cardRepo = $this->module->getService(SaferPaySavedCreditCardRepository::class);
            $cardRepo->deleteAllSavedCreditCards();
        }

        $this->ajaxResponse(true, $this->module->l('Payment Processing saved successfully', self::FILE_NAME));
    }

    /**
     * AJAX: Save email settings
     */
    public function ajaxProcessSaveEmailSettings()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, $this->module->l('Invalid request data', self::FILE_NAME));
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL, !empty($data['allowSaferpayMail']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL, !empty($data['sendNewOrderMail']) ? 1 : 0);
        $configuration->set(SaferPayConfig::SAFERPAY_SEND_ORDER_CONF_MAIL, !empty($data['sendOrderConfMail']) ? 1 : 0);

        $this->ajaxResponse(true, $this->module->l('Email settings saved successfully', self::FILE_NAME));
    }

    /**
     * AJAX: Save general settings
     */
    public function ajaxProcessSaveGeneralSettings()
    {
        $data = $this->getJsonInput();
        if (!$data) {
            $this->ajaxResponse(false, $this->module->l('Invalid request data', self::FILE_NAME));
            return;
        }

        /** @var SaferPayConfiguration $configuration */
        $configuration = $this->module->getService(SaferPayConfiguration::class);

        $configuration->set(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT, $this->getIntValue($data, 'orderStateAwaitingPayment'));
        $configuration->set(SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION, $this->getStringValue($data, 'paymentDescription'));

        $configurationName = $this->getStringValue($data, 'configurationName');
        if ($configurationName !== '' && (strlen($configurationName) > 20 || !preg_match('/^[A-Za-z0-9.:\-_]+$/', $configurationName))) {
            $this->ajaxResponse(false, $this->module->l('Only letters, numbers, dots, colons, hyphens, and underscores are allowed. Max 20 characters.', self::FILE_NAME));
            return;
        }
        $configuration->set(SaferPayConfig::CONFIGURATION_NAME, $configurationName);
        $hostedFieldsTemplate = $this->getIntValue($data, 'hostedFieldsTemplate');
        if ($hostedFieldsTemplate < 1 || $hostedFieldsTemplate > 2) {
            $hostedFieldsTemplate = SaferPayConfig::HOSTED_FIELDS_TEMPLATE_DEFAULT;
        }
        $configuration->set(SaferPayConfig::HOSTED_FIELDS_TEMPLATE, $hostedFieldsTemplate);
        $configuration->set(SaferPayConfig::SAFERPAY_ORDER_ID_OPTION, $this->getIntValue($data, 'orderIdOption'));
        $configuration->set(SaferPayConfig::SAFERPAY_DEBUG_MODE, !empty($data['debugMode']) ? 1 : 0);

        $this->ajaxResponse(true, $this->module->l('General settings saved successfully', self::FILE_NAME));
    }

    /**
     * AJAX: Save payment methods
     */
    public function ajaxProcessSavePaymentMethods()
    {
        $data = $this->getJsonInput();
        if (!$data || !isset($data['paymentMethods'])) {
            $this->ajaxResponse(false, $this->module->l('Invalid request data', self::FILE_NAME));
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
            if (!isset($method['name']) || !is_string($method['name'])) {
                continue;
            }

            $paymentName = $method['name'];
            $success = $paymentCreation->updatePayment($paymentName, !empty($method['enabled'])) && $success;
            $success = $logoCreation->updateLogo($paymentName, !empty($method['showLogos'])) && $success;
            $success = $fieldCreation->updateField($paymentName, !empty($method['showCustomForm'])) && $success;

            try {
                $countries = isset($method['countries']) ? $method['countries'] : [];
                $currencies = isset($method['currencies']) ? $method['currencies'] : [];

                if (empty($countries)) {
                    $countries = [SaferPayRestrictionCreator::RESTRICTION_ALL];
                }
                if (empty($currencies)) {
                    $currencies = [SaferPayRestrictionCreator::RESTRICTION_ALL];
                }

                $success = $restrictionCreator->updateRestriction(
                    $paymentName,
                    SaferPayRestrictionCreator::RESTRICTION_COUNTRY,
                    $countries
                ) && $success;
                $success = $restrictionCreator->updateRestriction(
                    $paymentName,
                    SaferPayRestrictionCreator::RESTRICTION_CURRENCY,
                    $currencies
                ) && $success;
            } catch (RestrictionException $e) {
                $this->ajaxResponse(false, $this->module->l('Wrong restriction type', self::FILE_NAME));
                return;
            }
        }

        if (!$success) {
            $this->ajaxResponse(false, $this->module->l('Failed to update payment methods', self::FILE_NAME));
            return;
        }

        $this->ajaxResponse(true, $this->module->l('Payment methods saved successfully', self::FILE_NAME));
    }

    /**
     * AJAX: Get terminals
     */
    public function ajaxProcessGetTerminals()
    {
        $data = $this->getJsonInput();
        $isTestMode = isset($data['env']) && $data['env'] === 'test';
        $suffix = $isTestMode ? SaferPayConfig::TEST_SUFFIX : '';

        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $customerId = $this->parseCustomerIdFromUsername($username);

        if ($password === self::PASSWORD_PLACEHOLDER) {
            /** @var SaferPayConfiguration $configuration */
            $configuration = $this->module->getService(SaferPayConfiguration::class);
            $password = (string) $configuration->get(SaferPayConfig::PASSWORD . $suffix);
        }

        if (empty($username) || empty($password) || empty($customerId)) {
            $this->ajaxResponse(false, $this->module->l('Invalid credentials. Please check your username and password.', self::FILE_NAME));
            return;
        }

        try {
            /** @var SaferPayGetTerminals $getTerminals */
            $getTerminals = $this->module->getService(SaferPayGetTerminals::class);
            $terminals = $getTerminals->fetchTerminalsWithCredentials($username, $password, $customerId, $isTestMode);

            $this->sendJsonResponse([
                'success' => true,
                'terminals' => $terminals,
            ]);
        } catch (\Exception $e) {
            $this->ajaxResponse(false, $this->module->l('Invalid credentials. Please check your username and password.', self::FILE_NAME));
        }
    }

    /**
     * AJAX: Generate Saferpay Fields access token
     */
    public function ajaxProcessGenerateFieldAccessToken()
    {
        $data = $this->getJsonInput();
        $isTestMode = isset($data['env']) && $data['env'] === 'test';
        $suffix = $isTestMode ? SaferPayConfig::TEST_SUFFIX : '';

        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $terminalId = isset($data['terminalId']) ? trim($data['terminalId']) : '';
        $customerId = $this->parseCustomerIdFromUsername($username);

        if ($password === self::PASSWORD_PLACEHOLDER) {
            /** @var SaferPayConfiguration $configuration */
            $configuration = $this->module->getService(SaferPayConfiguration::class);
            $password = (string) $configuration->get(SaferPayConfig::PASSWORD . $suffix);
        }

        if (empty($username) || empty($password) || empty($customerId) || empty($terminalId)) {
            $this->ajaxResponse(false, $this->module->l('Please enter valid credentials and select a terminal first.', self::FILE_NAME));
            return;
        }

        try {
            /** @var SaferPayGenerateFieldAccessToken $tokenGenerator */
            $tokenGenerator = $this->module->getService(SaferPayGenerateFieldAccessToken::class);
            $shopUrl = $this->context->link->getBaseLink();
            $token = $tokenGenerator->generateWithCredentials($username, $password, $customerId, $terminalId, $isTestMode, $shopUrl);

            /** @var SaferPayConfiguration $configuration */
            $configuration = $this->module->getService(SaferPayConfiguration::class);
            $configuration->set(SaferPayConfig::FIELDS_ACCESS_TOKEN . $suffix, $token);

            $this->sendJsonResponse([
                'success' => true,
                'message' => $this->module->l('Access token generated successfully.', self::FILE_NAME),
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog(
                'SaferPay: Failed to generate field access token - ' . $e->getMessage(),
                3,
                null,
                null,
                null,
                true
            );

            $this->ajaxResponse(false, $this->module->l('Failed to generate access token.', self::FILE_NAME));
        }
    }

    /**
     * AJAX: Refresh all data
     */
    public function ajaxProcessRefreshData()
    {
        $settingsData = $this->collectSettingsData();
        $this->sendJsonResponse([
            'success' => true,
            'data' => $settingsData,
        ]);
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
            'testTerminalId' => (string) $configuration->get(SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX),
            'testMerchantEmails' => (string) $configuration->get(SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX) ?: (string) \Configuration::get('PS_SHOP_EMAIL'),
            'testFieldAccessToken' => (string) $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX),
            'testFieldJsUrl' => (string) $configuration->get(SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX),

            // Live credentials
            'liveUsername' => (string) $configuration->get(SaferPayConfig::USERNAME),
            'livePassword' => $configuration->get(SaferPayConfig::PASSWORD) ? self::PASSWORD_PLACEHOLDER : '',
            'liveTerminalId' => (string) $configuration->get(SaferPayConfig::TERMINAL_ID),
            'liveMerchantEmails' => (string) $configuration->get(SaferPayConfig::MERCHANT_EMAILS) ?: (string) \Configuration::get('PS_SHOP_EMAIL'),
            'liveFieldAccessToken' => (string) $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN),
            'liveFieldJsUrl' => (string) $configuration->get(SaferPayConfig::FIELDS_LIBRARY),

            // License (auto-detected, per environment)
            'testHasBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX),
            'liveHasBusinessLicense' => (bool) $configuration->get(SaferPayConfig::BUSINESS_LICENSE),

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
            'hostedFieldsTemplate' => $this->getHostedFieldsTemplateForDisplay($configuration),
            'modulePath' => $this->module->getPathUri(),
            'orderIdOption' => (int) $configuration->get(SaferPayConfig::SAFERPAY_ORDER_ID_OPTION),
            'debugMode' => (bool) $configuration->get(SaferPayConfig::SAFERPAY_DEBUG_MODE),

            // Reference data
            'orderStates' => $this->getOrderStates(),
            'countries' => $this->getCountries(),
            'currencies' => $this->getCurrencies(),
            'paymentMethods' => $this->getPaymentMethodsData(),

            // Endpoints
            'ajaxUrl' => $this->context->link->getAdminLink('AdminSaferPayOfficialSettings'),
            'adminToken' => Tools::getAdminTokenLite('AdminSaferPayOfficialSettings'),

            // Translations
            'translations' => $this->getSettingsTranslations(),
        ];

        return $data;
    }

    /**
     * Hosted-field style "3" (Inline Layout with Card) has been removed; clamp any
     * legacy stored value to a still-supported style so the settings UI stays valid.
     *
     * @param SaferPayConfiguration $configuration
     * @return int
     */
    private function getHostedFieldsTemplateForDisplay($configuration)
    {
        $template = (int) $configuration->get(SaferPayConfig::HOSTED_FIELDS_TEMPLATE);

        if ($template < 1 || $template > 2) {
            return SaferPayConfig::HOSTED_FIELDS_TEMPLATE_DEFAULT;
        }

        return $template;
    }

    /**
     * Get all translatable strings for the React frontend
     */
    private function getSettingsTranslations()
    {
        /** @var \Invertus\SaferPay\Service\SettingsTranslationService $translationService */
        $translationService = $this->module->getService(\Invertus\SaferPay\Service\SettingsTranslationService::class);

        return $translationService->getAll();
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
        $result[] = ['id' => 0, 'name' => $this->module->l('All', self::FILE_NAME)];
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
        $result[] = ['id' => 0, 'iso_code' => $this->module->l('All', self::FILE_NAME)];
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
        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->module->getService(SaferPayPaymentRepository::class);

        try {
            // Re-read the account and reconcile the stored list when the Payment Methods
            // settings open, so methods added/removed on the Saferpay account are reflected
            // (and persisted for the front office) without requiring a Save click. Enabled
            // flags are preserved by the refresh; newly added methods default to disabled.
            /** @var SaferPayRefreshPaymentsService $refreshPaymentsService */
            $refreshPaymentsService = $this->module->getService(SaferPayRefreshPaymentsService::class);
            $refreshPaymentsService->refreshPayments();

            // The refresh persists the account's methods, so read them back from storage
            // instead of calling the API a second time.
            $paymentMethods = array_column($paymentRepository->getAllPaymentMethodsNames(), 'name');

            // refreshPayments() is a no-op when nothing is active yet (e.g. a fresh setup),
            // so fall back to the live account list to still surface newly available methods.
            if (empty($paymentMethods)) {
                /** @var SaferPayObtainPaymentMethods $obtainMethods */
                $obtainMethods = $this->module->getService(SaferPayObtainPaymentMethods::class);
                $paymentMethods = $obtainMethods->obtainPaymentMethodsNamesAsArray();
            }
        } catch (SaferPayApiException $exception) {
            // Account unreachable (bad credentials / offline): keep the last-known stored
            // list rather than wiping the page. Credential validity is surfaced separately
            // on the Credentials tab. Never clears stored configuration.
            $paymentMethods = array_column($paymentRepository->getAllPaymentMethodsNames(), 'name');
        }

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
    private function ajaxResponse($success, $message = '', $extraData = [])
    {
        $this->sendJsonResponse(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extraData));
    }

    /**
     * Send JSON response and terminate (PS9 compatible)
     */
    private function sendJsonResponse(array $data)
    {
        header('Content-Type: application/json');
        ob_end_clean();
        die(json_encode($data));
    }

    /**
     * Parse customer ID from API username (format: PREFIX_CUSTOMERID_SUFFIX)
     */
    private function parseCustomerIdFromUsername($username)
    {
        $parts = explode('_', (string) $username);

        return isset($parts[1]) ? $parts[1] : '';
    }

    /**
     * Parse Saferpay API error message into user-friendly text
     */
    private function parseApiErrorMessage($rawMessage)
    {
        $jsonStart = strpos($rawMessage, '{');
        if ($jsonStart !== false) {
            $jsonString = substr($rawMessage, $jsonStart);
            $decoded = json_decode($jsonString, true);
            if (is_array($decoded) && !empty($decoded['ErrorName'])) {
                $errorName = $decoded['ErrorName'];
                if ($errorName === 'AUTHENTICATION_FAILED') {
                    return $this->module->l('Invalid API credentials. Please verify your username and password.', self::FILE_NAME);
                }

                $message = $this->module->l('API validation failed:', self::FILE_NAME) . ' ' . $errorName;
                if (!empty($decoded['ErrorMessage'])) {
                    $message .= ' — ' . $decoded['ErrorMessage'];
                }

                return $message;
            }
        }

        return $this->module->l('API validation failed. Please check your credentials and try again.', self::FILE_NAME);
    }

    /**
     * Get string value from data array
     */
    private function getStringValue($data, $key)
    {
        return isset($data[$key]) ? (string) $data[$key] : '';
    }

    /**
     * Get int value from data array
     */
    private function getIntValue($data, $key)
    {
        return isset($data[$key]) ? (int) $data[$key] : 0;
    }

    /**
     * Returns the first invalid email in a comma-separated list, or null if all are valid.
     */
    private function findInvalidEmail($emails)
    {
        if ($emails === '') {
            return null;
        }
        foreach (explode(',', $emails) as $email) {
            $email = trim($email);
            if ($email === '') {
                continue;
            }
            if (!\Validate::isEmail($email)) {
                return $email;
            }
        }
        return null;
    }
}
