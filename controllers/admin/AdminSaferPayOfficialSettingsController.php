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
use Invertus\SaferPay\Repository\SaferPaySavedCreditCardRepository;
use Invertus\SaferPay\Adapter\Configuration;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialSettingsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminSaferPayOfficialSettingsController';

    /** @var \SaferPayOfficial */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;

        $this->tpl_folder = 'field-option-settings/';
        $this->initOptions();
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function postProcess()
    {
        parent::postProcess();

        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        $isCreditCardSaveEnabled = $configuration->get(SaferPayConfig::CREDIT_CARD_SAVE);

        if (!$isCreditCardSaveEnabled) {
            /** @var SaferPaySavedCreditCardRepository $cardRepo */
            $cardRepo = $this->module->getService(SaferPaySavedCreditCardRepository::class);
            $cardRepo->deleteAllSavedCreditCards();
        }

        $haveFieldToken = $configuration->get(SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::getConfigSuffix());
        $haveBusinessLicense = $configuration->get(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix());

        if (!$haveFieldToken && $haveBusinessLicense) {
            $configuration->set(SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::getConfigSuffix(), 0);
            $this->errors[] = $this->module->l('Field Access Token is required to use business license');
        }

        // Validate Terminal ID (soft validation - only if credentials are present)
        $this->validateTerminalId();

        return true;
    }

    /**
     * Validate Terminal ID against available terminals from SaferPay API
     * This is a soft validation - if API is not accessible, validation passes
     */
    private function validateTerminalId()
    {
        try {
            /** @var Configuration $configuration */
            $configuration = $this->module->getService(Configuration::class);

            $suffix = SaferPayConfig::getConfigSuffix();
            $terminalId = Tools::getValue(SaferPayConfig::TERMINAL_ID . $suffix);
            $customerId = $configuration->get(SaferPayConfig::CUSTOMER_ID . $suffix);
            $username = $configuration->get(SaferPayConfig::USERNAME . $suffix);
            $password = $configuration->get(SaferPayConfig::PASSWORD . $suffix);

            // Skip validation if terminal ID is empty or credentials are not set
            if (empty($terminalId) || empty($customerId) || empty($username) || empty($password)) {
                return;
            }

            /** @var \Invertus\SaferPay\Service\SaferPayTerminalService $terminalService */
            $terminalService = $this->module->getService(\Invertus\SaferPay\Service\SaferPayTerminalService::class);

            // Try to validate terminal ID
            $isValid = $terminalService->isValidTerminal($terminalId);

            if (!$isValid) {
                // Get available terminals to show in warning
                $terminals = $terminalService->getAvailableTerminals();

                if (!empty($terminals)) {
                    $this->warnings[] = $this->module->l('Warning: The Terminal ID you entered was not found in the list of available terminals. Please verify the Terminal ID is correct.');
                }
                // If no terminals found, API might be down - skip validation silently
            }
        } catch (Exception $e) {
            // Silently fail validation if there's an error - don't block saving
            // Errors are already logged by the service
        }
    }

    public function initOptions()
    {
        $this->context->smarty->assign(SaferPayConfig::PASSWORD, SaferPayConfig::WEB_SERVICE_PASSWORD_PLACEHOLDER);

        $this->fields_options[] = $this->displayEnvironmentSelectorConfiguration();
        $this->fields_options[] = $this->displayLiveEnvironmentConfiguration();
        $this->fields_options[] = $this->displayTestEnvironmentConfiguration();
        $this->fields_options[] = $this->displayPaymentBehaviorConfiguration();
        $this->fields_options[] = $this->displayStylingConfiguration();
        $this->fields_options[] = $this->displaySavedCardsConfiguration();
        $this->fields_options[] = $this->displayEmailSettings();
        $this->fields_options[] = $this->getFieldOptionsOrderState();
        $this->fields_options[] = $this->displayConfigurationSettings();
    }

    /**
     * @param $isNewTheme
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS('modules/' . $this->module->name . '/views/js/admin/saferpay_settings.js');
    }

    /**
     * Get available terminals for a specific environment
     *
     * @param string $environment 'test' or 'live'
     * @return array
     */
    private function getTerminalsForEnvironment($environment = 'live')
    {
        try {
            $suffix = ($environment === 'test') ? SaferPayConfig::TEST_SUFFIX : '';

            // Try to get credentials from form input first (for first-time setup)
            // Fall back to database values if not in POST
            $customerId = Tools::getValue(SaferPayConfig::CUSTOMER_ID . $suffix)
                ?: \Configuration::get(SaferPayConfig::CUSTOMER_ID . $suffix);
            $username = Tools::getValue(SaferPayConfig::USERNAME . $suffix)
                ?: \Configuration::get(SaferPayConfig::USERNAME . $suffix);
            $password = Tools::getValue(SaferPayConfig::PASSWORD . $suffix)
                ?: \Configuration::get(SaferPayConfig::PASSWORD . $suffix);

            // If credentials are not present, return empty array
            if (empty($customerId) || empty($username) || empty($password)) {
                return [];
            }

            // Temporarily set credentials and test mode for API call
            $originalCustomerId = \Configuration::get(SaferPayConfig::CUSTOMER_ID . $suffix);
            $originalUsername = \Configuration::get(SaferPayConfig::USERNAME . $suffix);
            $originalPassword = \Configuration::get(SaferPayConfig::PASSWORD . $suffix);
            $originalTestMode = \Configuration::get(SaferPayConfig::TEST_MODE);

            \Configuration::updateValue(SaferPayConfig::CUSTOMER_ID . $suffix, $customerId);
            \Configuration::updateValue(SaferPayConfig::USERNAME . $suffix, $username);
            \Configuration::updateValue(SaferPayConfig::PASSWORD . $suffix, $password);
            \Configuration::updateValue(SaferPayConfig::TEST_MODE, $environment === 'test' ? 1 : 0);

            /** @var \Invertus\SaferPay\Service\SaferPayTerminalService $terminalService */
            $terminalService = $this->module->getService(\Invertus\SaferPay\Service\SaferPayTerminalService::class);
            $terminals = $terminalService->getAvailableTerminals($customerId);

            // Restore original credentials and test mode
            \Configuration::updateValue(SaferPayConfig::CUSTOMER_ID . $suffix, $originalCustomerId);
            \Configuration::updateValue(SaferPayConfig::USERNAME . $suffix, $originalUsername);
            \Configuration::updateValue(SaferPayConfig::PASSWORD . $suffix, $originalPassword);
            \Configuration::updateValue(SaferPayConfig::TEST_MODE, $originalTestMode);

            return $terminals;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @return array
     */
    private function getFieldOptionsOrderState()
    {
        return [
            'title' => $this->module->l('Order state'),
            'fields' => [
                SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT => [
                    'title' => $this->module->l(
                        sprintf(
                            'Status for %s',
                            Tools::ucfirst(Tools::strtolower(SaferPayConfig::SAFERPAY_PAYMENT_AWAITING))
                        )
                    ),
                    'required' => false,
                    'cast' => 'intval',
                    'type' => 'select',
                    'list' => OrderState::getOrderStates($this->context->language->id),
                    'identifier' => 'id_order_state',
                    'desc' => 'Default status on SaferPay order creation',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayConfigurationSettings()
    {
        return [
            'title' => $this->module->l('Configuration', self::FILE_NAME),
            'fields' => [
                SaferPayConfig::SAFERPAY_PAYMENT_DESCRIPTION => [
                    'title' => $this->module->l('Description', self::FILE_NAME),
                    'type' => 'text',
                    'desc' => 'This description is visible in payment page also in payment confirmation email',
                    'class' => 'fixed-width-xxl',
                ],
                SaferPayConfig::SAFERPAY_DEBUG_MODE => [
                    'title' => $this->module->l('Debug mode', self::FILE_NAME),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                    'desc' => $this->module->l('Enable debug mode to see more information in logs', self::FILE_NAME),
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save', self::FILE_NAME),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displaySavedCardsConfiguration()
    {
        return [
            'title' => $this->module->l('Credit card saving'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::CREDIT_CARD_SAVE => [
                    'type' => 'radio',
                    'title' => $this->module->l('Credit card saving for customers'),
                    'validation' => 'isInt',
                    'choices' => [
                        1 => $this->module->l('Enable'),
                        0 => $this->module->l('Disable'),
                    ],
                    'desc' => $this->module->l('Allow customers to save credit card for faster purchase'),
                    'form_group_class' => 'thumbs_chose',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayStylingConfiguration()
    {
        return [
            'title' => $this->module->l('Styling'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::CONFIGURATION_NAME => [
                    'title' => $this->module->l('Payment Page configurations name'),
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayEmailSettings()
    {
        return [
            'title' => $this->module->l('Email sending'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::SAFERPAY_ALLOW_SAFERPAY_SEND_CUSTOMER_MAIL => [
                    'title' => $this->module->l('Send an email from Saferpay on payment completion'),
                    'desc' => $this->module->l('With this setting enabled an email from the Saferpay system will be sent to the customer'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
                SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL => [
                    'title' => $this->module->l('Send new order mail on authorization'),
                    'desc' => $this->module->l('Receive a notification when an order is authorized by Saferpay (Using the Mail alert module)'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
                SaferPayConfig::SAFERPAY_SEND_ORDER_CONF_MAIL => [
                    'title' => $this->module->l('Send order confirmation mail on payment completion'),
                    'desc' => $this->module->l('Send an email from Saferpay on payment completion'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
                SaferPayConfig::SAFERPAY_SEND_NEW_ORDER_MAIL . '_description' => [
                    'type' => 'desc',
                    'class' => 'col-lg-12',
                    'template' => 'field-new-order-mail-desc.tpl',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayPaymentBehaviorConfiguration()
    {
        return [
            'title' => $this->module->l('Payment behavior'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::PAYMENT_BEHAVIOR => [
                    'type' => 'radio',
                    'title' => $this->module->l('Default payment behavior'),
                    'validation' => 'isInt',
                    'choices' => [
                        0 => $this->module->l('Capture'),
                        1 => $this->module->l('Authorize'),
                    ],
                    'desc' => $this->module->l('How payment provider should behave when order is created'),
                    'form_group_class' => 'thumbs_chose',
                ],
                SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D => [
                    'type' => 'radio',
                    'title' => $this->module->l('Behaviour when 3D secure fails'),
                    'validation' => 'isInt',
                    'choices' => [
                        SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_CANCEL => $this->module->l('Cancel'),
                        SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D_AUTHORIZE => $this->module->l('Authorize'),
                    ],
                    'desc' => $this->module->l('Default payment behavior for payment without 3-D Secure'),
                    'form_group_class' => 'thumbs_chose',
                ],
                SaferPayConfig::RESTRICT_REFUND_AMOUNT_TO_CAPTURED_AMOUNT => [
                    'type' => 'radio',
                    'title' => $this->module->l('Restrict RefundAmount To Captured Amount'),
                    'validation' => 'isInt',
                    'choices' => [
                        1 => $this->module->l('Enable'),
                        0 => $this->module->l('Disable'),
                    ],
                    'desc' => $this->module->l('If set to true, the refund will be rejected if the sum of authorized refunds exceeds the capture value.'),
                    'form_group_class' => 'thumbs_chose',
                ],
                SaferPayConfig::SAFERPAY_ORDER_CREATION_AFTER_AUTHORIZATION => [
                    'type' => 'radio',
                    'title' => $this->module->l('Order creation rule'),
                    'validation' => 'isInt',
                    'choices' => [
                        1 => $this->module->l('After authorization'),
                        0 => $this->module->l('Before authorization'),
                    ],
                    'desc' => $this->module->l('Select the option to determine whether the order should be created'),
                    'form_group_class' => 'thumbs_chose',
                ],
                SaferPayConfig::SAFERPAY_GROUP_CARDS => [
                    'type' => 'bool',
                    'title' => $this->module->l("Group debit/credit cards as 'Cards' in checkout", self::FILE_NAME),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'desc' => $this->module->l("If enabled, all supported card brands (Visa, Mastercard, Amex, etc.) will be grouped and shown as a single 'Cards' payment method at checkout.", self::FILE_NAME),
                ],
                SaferPayConfig::SAFERPAY_GROUP_CARDS_LOGO => [
                    'type' => 'bool',
                    'title' => $this->module->l("Show 'Cards' payment method logo", self::FILE_NAME),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'desc' => $this->module->l("If enabled, a logo for the grouped 'Cards' payment method will be displayed at checkout.", self::FILE_NAME),
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayTestEnvironmentConfiguration()
    {
        return [
            'title' => $this->module->l('Test environment'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::USERNAME . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('JSON API Username'),
                    'type' => 'text',
                    'validation' => 'isGenericName',
                    'class' => 'fixed-width-xl',
                ],
                SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('JSON API Password'),
                    'type' => 'password_input',
                    'class' => 'fixed-width-xl',
                    'value' => \Configuration::get(SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX),
                ],
                SaferPayConfig::CUSTOMER_ID . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('Customer ID'),
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
                    'size' => 3,
                ],
                SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('Terminal ID'),
                    'type' => 'terminal_selector',
                    'class' => 'fixed-width-xl',
                    'value' => \Configuration::get(SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX),
                    'environment' => 'test',
                    'terminals' => $this->getTerminalsForEnvironment('test'),
                ],
                SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('Merchant emails'),
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
                ],
                SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX . '_description' => [
                    'type' => 'desc',
                    'class' => 'col-lg-12',
                    'template' => 'field-access-token-desc.tpl',
                ],
                SaferPayConfig::FIELDS_ACCESS_TOKEN . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('Field Access Token'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX . '_description' => [
                    'type' => 'desc',
                    'class' => 'col-lg-12',
                    'template' => 'field-javascript-library-desc.tpl',
                ],
                SaferPayConfig::FIELDS_LIBRARY . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('Field Javascript library url'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX => [
                    'title' => $this->module->l('I have Business license'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayLiveEnvironmentConfiguration()
    {
        return [
            'title' => $this->module->l('Live environment'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::USERNAME => [
                    'title' => $this->module->l('JSON API Username'),
                    'type' => 'text',
                    'validation' => 'isGenericName',
                    'class' => 'fixed-width-xl',
                ],
                SaferPayConfig::PASSWORD => [
                    'title' => $this->module->l('JSON API Password'),
                    'type' => 'password_input',
                    'class' => 'fixed-width-xl',
                    'value' => \Configuration::get(SaferPayConfig::PASSWORD),
                ],
                SaferPayConfig::CUSTOMER_ID => [
                    'title' => $this->module->l('Customer ID'),
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
                    'size' => 3,
                ],
                SaferPayConfig::TERMINAL_ID => [
                    'title' => $this->module->l('Terminal ID'),
                    'type' => 'terminal_selector',
                    'class' => 'fixed-width-xl',
                    'value' => \Configuration::get(SaferPayConfig::TERMINAL_ID),
                    'environment' => 'live',
                    'terminals' => $this->getTerminalsForEnvironment('live'),
                ],
                SaferPayConfig::MERCHANT_EMAILS => [
                    'title' => $this->module->l('Merchant emails'),
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
                ],
                SaferPayConfig::FIELDS_ACCESS_TOKEN . '_description' => [
                    'type' => 'desc',
                    'class' => 'col-lg-12',
                    'template' => 'field-access-token-desc.tpl',
                ],
                SaferPayConfig::FIELDS_ACCESS_TOKEN => [
                    'title' => $this->module->l('Field Access Token'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                SaferPayConfig::FIELDS_LIBRARY . '_description' => [
                    'type' => 'desc',
                    'class' => 'col-lg-12',
                    'template' => 'field-javascript-library-desc.tpl',
                ],
                SaferPayConfig::FIELDS_LIBRARY => [
                    'title' => $this->module->l('Field Javascript library url'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                ],
                SaferPayConfig::BUSINESS_LICENSE => [
                    'title' => $this->module->l('I have Business license'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function displayEnvironmentSelectorConfiguration()
    {
        return [
            'title' => $this->module->l('Select environment'),
            'icon' => 'icon-settings',
            'fields' => [
                SaferPayConfig::TEST_MODE => [
                    'title' => $this->module->l('Test mode'),
                    'validation' => 'isBool',
                    'cast' => 'intval',
                    'type' => 'bool',
                ],
            ],
            'buttons' => [
                'save_and_connect' => [
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type' => 'submit',
                ],
            ],
        ];
    }
}
