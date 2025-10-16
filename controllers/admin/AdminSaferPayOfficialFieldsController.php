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

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialFieldsController extends ModuleAdminController
{
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

    public function initOptions()
    {
        $this->fields_options = [
            'hosted_fields_settings' => [
                'title' => $this->module->l('Hosted fields settings'),
                'icon' => 'icon-settings',
                'fields' => [
                    SaferPayConfig::HOSTED_FIELDS_TEMPLATE . '_description' => [
                        'type' => 'desc',
                        'class' => 'col-lg-12',
                        'template' => 'field-hosted-field-template-desc.tpl',
                    ],

                    SaferPayConfig::HOSTED_FIELDS_TEMPLATE => [
                        'type' => 'select-template',
                        'name' => SaferPayConfig::HOSTED_FIELDS_TEMPLATE,
                        'templateOptions' => [
                            "{$this->module->getPathUri()}views/img/hosted-templates/template1.jpg",
                            "{$this->module->getPathUri()}views/img/hosted-templates/template2.jpg",
                            "{$this->module->getPathUri()}views/img/hosted-templates/template3.jpg",
                        ],
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
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS('modules/' . $this->module->name . '/views/js/admin/saferpay_fields.js');
        $this->addCSS('modules/' . $this->module->name . '/views/css/admin/saferpay_fields.css');
    }
}
