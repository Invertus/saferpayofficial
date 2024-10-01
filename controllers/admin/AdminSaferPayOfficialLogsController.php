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

use Invertus\Saferpay\Context\GlobalShopContextInterface;
use Invertus\SaferPay\Controller\AbstractAdminSaferPayController;
use Invertus\SaferPay\Enum\PermissionType;
use Invertus\SaferPay\Logger\Formatter\LogFormatter;
use Invertus\SaferPay\Utility\VersionUtility;
use Invertus\SaferPay\Logger\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSaferPayOfficialLogsController extends AbstractAdminSaferPayController
{
    const FILE_NAME = 'AdminSaferPayOfficialLogsController';
    const LOG_INFORMATION_TYPE_REQUEST = 'request';
    const LOG_INFORMATION_TYPE_RESPONSE = 'response';
    const LOG_INFORMATION_TYPE_CONTEXT = 'context';

    public function __construct()
    {
        $this->table = 'log';
        $this->className = 'PrestaShopLogger';
        $this->bootstrap = true;
        $this->lang = false;
        $this->noLink = true;
        $this->allow_export = true;

        parent::__construct();

        $this->toolbar_btn = [];
        $this->initList();

        $this->_select .= '
            REPLACE(a.`message`, "' . LogFormatter::SAFERPAY_LOG_PREFIX . '", "") as message,
            spl.request, spl.response, spl.context
        ';

        $shopIdCheck = '';

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.8.0')) {
            $shopIdCheck = ' AND spl.id_shop = a.id_shop';
        }

        $this->_join .= ' JOIN ' . _DB_PREFIX_ . SaferPayLog::$definition['table'] . ' spl ON (spl.id_log = a.id_log' . $shopIdCheck . ' AND a.object_type = "' . pSQL(Logger::LOG_OBJECT_TYPE) . '")';
        $this->_use_found_rows = false;
        $this->list_no_link = true;
    }

    public function initContent()
    {
        if ($this->module instanceof SaferPayOfficial) {
            $this->content .= $this->module->displayNavigationTop();
        }

        $this->content .= $this->displaySeverityInformation();
        
        parent::initContent();
    }

    public function initList()
    {
        $this->fields_list = [
            'id_log' => [
                'title' => $this->module->l('ID', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'severity' => [
                'title' => $this->module->l('Severity (1-4)', self::FILE_NAME),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'callback' => 'printSeverityLevel',
            ],
            'request' => [
                'title' => $this->module->l('Request', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printRequestButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'response' => [
                'title' => $this->module->l('Response', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printResponseButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'message' => [
                'title' => $this->module->l('Message', self::FILE_NAME),
            ],
            'context' => [
                'title' => $this->module->l('Context', self::FILE_NAME),
                'align' => 'text-center',
                'callback' => 'printContextButton',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true,
            ],
            'date_add' => [
                'title' => $this->module->l('Date', self::FILE_NAME),
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
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
        parent::setMedia($isNewTheme);

        $context = $this->module->getService(\Invertus\SaferPay\Adapter\LegacyContext::class);

        Media::addJsDef([
            'saferpayofficial' => [
                'logsUrl' => $context->getAdminLink(SaferPayOfficial::ADMIN_LOGS_CONTROLLER),
            ],
        ]);

        $this->addCSS("{$this->module->getPathUri()}views/css/admin/logs_tab.css");
        $this->addJS($this->module->getPathUri() . 'views/js/admin/log.js', false);
    }

    public function displaySeverityInformation()
    {
        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_levels.tpl"
        );
    }

    public function printSeverityLevel(int $level)
    {
        $this->context->smarty->assign([
            'log_severity_level' => $level,
            'log_severity_level_informative' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE :
                Config::LOG_SEVERITY_LEVEL_INFORMATIVE,
            'log_severity_level_warning' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING :
                Config::LOG_SEVERITY_LEVEL_WARNING,
            'log_severity_level_error' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR :
                Config::LOG_SEVERITY_LEVEL_ERROR,
            'log_severity_level_major' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR :
                Config::LOG_SEVERITY_LEVEL_MAJOR,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_level_column.tpl"
        );
    }

    public function getDisplayButton(int $logId, string $data, string $logInformationType)
    {
        $unserializedData = json_decode($data);

        if (empty($unserializedData)) {
            return '--';
        }

        $this->context->smarty->assign([
            'log_id' => $logId,
            'log_information_type' => $logInformationType,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/log_modal.tpl"
        );
    }

    /**
     * @param string $request
     * @param array $data
     *
     * @return false|string
     *
     * @throws SmartyException
     */
    public function printRequestButton(string $request, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $request, self::LOG_INFORMATION_TYPE_REQUEST);
    }

    public function printResponseButton(string $response, array $data)
    {
        return $this->getDisplayButton($data['id_log'], $response, self::LOG_INFORMATION_TYPE_RESPONSE);
    }

    public function displayAjaxGetLog()
    {
        if (!$this->ensureHasPermissions([PermissionType::EDIT, PermissionType::VIEW], true)) {
            return;
        }

        /** @var \Invertus\SaferPay\Adapter\Tools $tools */
        $tools = $this->module->getService(\Invertus\SaferPay\Adapter\Tools::class);

        /** @var \Invertus\SaferPay\Repository\SaferPayLogRepositoryInterface $logRepository */
        $logRepository = $this->module->getService(\Invertus\SaferPay\Repository\SaferPayLogRepositoryInterface::class);

        /** @var GlobalShopContextInterface $globalShopContext */
        $globalShopContext = $this->module->getService(GlobalShopContextInterface::class);

        $logId = $tools->getValueAsInt('log_id');

//        /** @var LoggerInterface $logger */
//        $logger = $this->module->getService(LoggerInterface::class);

        try {
            /** @var \SaferPayLog|null $log */
            $log = $logRepository->findOneBy([
                'id_log' => $logId,
                'id_shop' => $globalShopContext->getShopId(),
            ]);
        } catch (Exception $exception) {
//            $logger->error('Failed to find log', [
//                'context' => [
//                    'id_log' => $logId,
//                    'id_shop' => $globalShopContext->getShopId(),
//                ],
//                'exceptions' => ExceptionUtility::getExceptions($exception),
//            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('Failed to find log.', self::FILE_NAME),
            ]));
        }

        if (!isset($log)) {
//            $logger->error('No log information found.', [
//                'context' => [
//                    'id_log' => $logId,
//                    'id_shop' => $globalShopContext->getShopId(),
//                ],
//                'exceptions' => [],
//            ]);

            $this->ajaxRender(json_encode([
                'error' => true,
                'message' => $this->module->l('No log information found.', self::FILE_NAME),
            ]));
        }
        $this->ajaxRender($log);

        $this->ajaxResponse(json_encode([
            'error' => false,
            'log' => [
                self::LOG_INFORMATION_TYPE_REQUEST => $log->request,
                self::LOG_INFORMATION_TYPE_RESPONSE => $log->response,
                self::LOG_INFORMATION_TYPE_CONTEXT => $log->context,
            ],
        ]));
    }
}
