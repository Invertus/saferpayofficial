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

class AdminSaferPayOfficialSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->initOptions();
    }

    public function initContent()
    {
        if ($this->module instanceof SaferPayOfficial) {
            $this->content .= $this->module->displayNavigationTop();
        }
        parent::initContent();
    }

    public function postProcess()
    {
        parent::postProcess();

        $isCreditCardSaveEnabled = Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);
        if (!$isCreditCardSaveEnabled) {
            /** @var SaferPaySavedCreditCardRepository $cardRepo */
            $cardRepo = $this->module->getContainer()->get(SaferPaySavedCreditCardRepository::class);
            $cardRepo->deleteAllSavedCreditCards();
        }
    }

    public function initOptions()
    {
        $this->context->smarty->assign(SaferPayConfig::PASSWORD, SaferPayConfig::WEB_SERVICE_PASSWORD_PLACEHOLDER);

        $this->fields_options = [
            'login_configurations' => [
                'title' => $this->l('TEST MODE'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::TEST_MODE => [
                        'title' => $this->l('Test mode'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ],
                ],
                'buttons' => [
                    'save_and_connect' => [
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                    ],
                ],
            ],
            [
                'title' => $this->l('Live environment'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::USERNAME => [
                        'title' => $this->l('JSON API Username'),
                        'type' => 'text',
                        'validation' => 'isGenericName',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::PASSWORD => [
                        'title' => $this->l('JSON API Password'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::CUSTOMER_ID => [
                        'title' => $this->l('Customer ID'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                        'size' => 3,
                    ],
                    SaferPayConfig::TERMINAL_ID => [
                        'title' => $this->l('Terminal ID'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::MERCHANT_EMAILS => [
                        'title' => $this->l('Merchant emails'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::BUSINESS_LICENSE => [
                        'title' => $this->l('I have Business license'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ],
                ],
                'buttons' => [
                    'save_and_connect' => [
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                    ],
                ],
            ],
            [
                'title' => $this->l('Test environment'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::USERNAME . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('JSON API Username'),
                        'type' => 'text',
                        'validation' => 'isGenericName',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::PASSWORD . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('JSON API Password'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::CUSTOMER_ID . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('Customer ID'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                        'size' => 3,
                    ],
                    SaferPayConfig::TERMINAL_ID . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('Terminal ID'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::MERCHANT_EMAILS . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('Merchant emails'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::BUSINESS_LICENSE . SaferPayConfig::TEST_SUFFIX => [
                        'title' => $this->l('I have Business license'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
                    ],
                ],
                'buttons' => [
                    'save_and_connect' => [
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                    ],
                ],
            ],
            [
                'title' => $this->l('Payment behavior'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::PAYMENT_BEHAVIOR => [
                        'type' => 'radio',
                        'title' => $this->l('Default payment behavior'),
                        'validation' => 'isInt',
                        'choices' => [
                            0 => $this->l('Capture'),
                            1 => $this->l('Authorize'),
                        ],
                        'desc' => $this->l('How payment provider should behave when order is created'),
                        'form_group_class' => 'thumbs_chose',
                    ],
                    SaferPayConfig::PAYMENT_BEHAVIOR_WITHOUT_3D => [
                        'type' => 'radio',
                        'title' => $this->l('Behaviour when 3D secure fails'),
                        'validation' => 'isInt',
                        'choices' => [
                            0 => $this->l('Cancel'),
                            1 => $this->l('Authorize'),
                        ],
                        'desc' => $this->l('Default payment behavior for payment without 3-D Secure'),
                        'form_group_class' => 'thumbs_chose',
                    ],
                    SaferPayConfig::CREDIT_CARD_SAVE => [
                        'type' => 'radio',
                        'title' => $this->l('Credit card saving for customers'),
                        'validation' => 'isInt',
                        'choices' => [
                            1 => $this->l('Enable'),
                            0 => $this->l('Disable'),
                        ],
                        'desc' => $this->l('Allow customers to save credit card for faster purchase'),
                        'form_group_class' => 'thumbs_chose',
                    ],
                ],
                'buttons' => [
                    'save_and_connect' => [
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                    ],
                ],
            ],
            [
                'title' => $this->l('STYLING'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::CONFIGURATION_NAME => [
                        'title' => $this->l('Payment Page configurations name'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                    SaferPayConfig::CSS_FILE => [
                        'title' => $this->l('CSS url'),
                        'type' => 'text',
                        'class' => 'fixed-width-xl',
                    ],
                ],
                'buttons' => [
                    'save_and_connect' => [
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save',
                        'class' => 'btn btn-default pull-right',
                        'type' => 'submit',
                    ],
                ],
            ],
        ];

        if (SaferPayConfig::isVersion17()) {
            $this->fields_options[] =
                [
                    'title' => $this->l('Email sending'),
                    'icon' => 'icon-settings',
                    'fields' => [
                        SaferPayConfig::SAFERPAY_SEND_ORDER_CONFIRMATION => [
                            'title' => $this->l('Send order confirmation email'),
                            'desc' => $this->l('Send order confirmation email before payment is executed'),
                            'validation' => 'isBool',
                            'cast' => 'intval',
                            'type' => 'bool',
                        ],
                    ],
                    'buttons' => [
                        'save_and_connect' => [
                            'title' => $this->l('Save'),
                            'icon' => 'process-icon-save',
                            'class' => 'btn btn-default pull-right',
                            'type' => 'submit',
                        ],
                    ],
                ];
        }
    }
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJS("{$this->module->getPathUri()}views/js/admin/saferpay_settings.js");
    }
}
