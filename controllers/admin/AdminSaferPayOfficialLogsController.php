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

class AdminSaferPayOfficialLogsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->className = SaferPayLog::class;
        $this->table = SaferPayLog::$definition['table'];
        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->initList();
    }

    public function initContent()
    {
        if ($this->module instanceof SaferPayOfficial) {
            $this->content .= $this->module->displayNavigationTop();
        }
        parent::initContent();
    }

    public function initList()
    {
        $this->fields_list = [
            'id_saferpay_log' => [
                'title' => $this->l('ID'),
                'align' => 'center',
            ],
            'payload' => [
                'title' => $this->l('Payload'),
                'align' => 'center',
                'class' => 'saferpay-text-break',
            ],
            'message' => [
                'align' => 'center',
                'title' => $this->l('Message'),
                'class' => 'saferpay-text-break',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'type' => 'datetime',
            ],
        ];

        $this->_defaultOrderBy = 'id_saferpay_log';
        $this->_defaultOrderWay = 'DESC';

        $this->actions_available = [''];
    }

    public function renderList()
    {
        unset($this->toolbar_btn['new']);
        return parent::renderList();
    }

    public function setMedia($isNewTheme = false)
    {
        $this->addCSS("{$this->module->getPathUri()}views/css/admin/logs_tab.css");
        parent::setMedia($isNewTheme);
    }
}
