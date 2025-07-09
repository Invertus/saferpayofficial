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

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;

        $this->override_folder = 'field-option-settings/';
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
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
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
                    'type' => 'text',
                    'class' => 'fixed-width-xl',
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
