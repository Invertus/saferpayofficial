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

use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\Saferpay\Context\GlobalShopContext;
use Invertus\SaferPay\Controller\AbstractAdminSaferPayController;
use Invertus\SaferPay\Enum\PermissionType;
use Invertus\SaferPay\Logger\Formatter\LogFormatter;
use Invertus\Saferpay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayLogRepository;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Invertus\SaferPay\Utility\VersionUtility;
use Invertus\SaferPay\Logger\Logger;
use Invertus\SaferPay\Adapter\Tools;

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

        /** @var LegacyContext $context */
        $context = $this->module->getService(LegacyContext::class);

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

    public function printSeverityLevel($level)
    {
        $this->context->smarty->assign([
            'log_severity_level' => $level,
            'log_severity_level_informative' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_INFORMATIVE :
                SaferPayConfig::LOG_SEVERITY_LEVEL_INFORMATIVE,
            'log_severity_level_warning' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING :
                SaferPayConfig::LOG_SEVERITY_LEVEL_WARNING,
            'log_severity_level_error' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR :
                SaferPayConfig::LOG_SEVERITY_LEVEL_ERROR,
            'log_severity_level_major' => defined('\PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR') ?
                PrestaShopLogger::LOG_SEVERITY_LEVEL_MAJOR :
                SaferPayConfig::LOG_SEVERITY_LEVEL_MAJOR,
        ]);

        return $this->context->smarty->fetch(
            "{$this->module->getLocalPath()}views/templates/admin/logs/severity_level_column.tpl"
        );
    }

    public function getDisplayButton($logId, $data, $logInformationType)
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
    public function printRequestButton($request, $data)
    {
        return $this->getDisplayButton($data['id_log'], $request, self::LOG_INFORMATION_TYPE_REQUEST);
    }

    public function printResponseButton($response, $data)
    {
        return $this->getDisplayButton($data['id_log'], $response, self::LOG_INFORMATION_TYPE_RESPONSE);
    }

    public function printContextButton($context, $data)
    {
        return $this->getDisplayButton($data['id_log'], $context, self::LOG_INFORMATION_TYPE_CONTEXT);
    }

    public function displayAjaxGetLog()
    {
        if (!$this->ensureHasPermissions([PermissionType::EDIT, PermissionType::VIEW], true)) {
            return;
        }

        /** @var Tools $tools */
        $tools = $this->module->getService(Tools::class);

        /** @var SaferPayLogRepository $logRepository */
        $logRepository = $this->module->getService(SaferPayLogRepository::class);

        /** @var GlobalShopContext $shopContext */
        $globalShopContext = $this->module->getService(GlobalShopContext::class);

        $logId = $tools->getValueAsInt('log_id');

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            /** @var \SaferPayLog|null $log */
            $log = $logRepository->findOneBy([
                'id_log' => $logId,
                'id_shop' => $globalShopContext->getShopId(),
            ]);
        } catch (Exception $exception) {
            $logger->error('Failed to find log', [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $globalShopContext->getShopId(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxResponse(json_encode([
                'error' => true,
                'message' => $this->module->l('Failed to find log.', self::FILE_NAME),
            ]));
        }

        if (!isset($log)) {
            $logger->error('No log information found.', [
                'context' => [
                    'id_log' => $logId,
                    'id_shop' => $globalShopContext->getShopId(),
                ],
                'exceptions' => [],
            ]);

            $this->ajaxRender(json_encode([
                'error' => true,
                'message' => $this->module->l('No log information found.', self::FILE_NAME),
            ]));
        }

        $this->ajaxResponse(json_encode([
            'error' => false,
            'log' => [
                self::LOG_INFORMATION_TYPE_REQUEST => $log->request,
                self::LOG_INFORMATION_TYPE_RESPONSE => $log->response,
                self::LOG_INFORMATION_TYPE_CONTEXT => $log->context,
            ],
        ]));
    }

    public function processExport($textDelimiter = '"')
    {
        // clean buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }

        header('Content-type: text/csv');
        header('Content-Type: application/force-download; charset=UTF-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="' . $this->table . '_' . date('Y-m-d_His') . '.csv"');

        $fd = fopen('php://output', 'wb');

        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        /** @var LegacyContext $context */
        $context = $this->module->getService(LegacyContext::class);

        $storeInfo = [
            'PrestaShop Version' => _PS_VERSION_,
            'PHP Version' => phpversion(),
            'Module Version' => $this->module->version,
            'MySQL Version' => \Db::getInstance()->getVersion(),
            'Shop URL' => $context->getShopDomain(),
            'Shop Name' => $context->getShopName(),
        ];

        $moduleConfigurations = [
            'Test mode' => SaferPayConfig::isTestMode() ? 'Yes' : 'No',
        ];

        $psSettings = [
            'Default country' => $configuration->get('PS_COUNTRY_DEFAULT'),
            'Default currency' => $configuration->get('PS_CURRENCY_DEFAULT'),
            'Default language' => $configuration->get('PS_LANG_DEFAULT'),
            'Round mode' => $configuration->get('PS_PRICE_ROUND_MODE'),
            'Round type' => $configuration->get('PS_ROUND_TYPE'),
            'Current theme name' => $context->getShopThemeName(),
            'PHP memory limit' => ini_get('memory_limit'),
        ];

        $moduleConfigurationsInfo = "**Module configurations:**\n";
        foreach ($moduleConfigurations as $key => $value) {
            $moduleConfigurationsInfo .= "- $key: $value\n";
        }

        $psSettingsInfo = "**Prestashop settings:**\n";
        foreach ($psSettings as $key => $value) {
            $psSettingsInfo .= "- $key: $value\n";
        }

        fputcsv($fd, array_keys($storeInfo), ';', $textDelimiter);
        fputcsv($fd, $storeInfo, ';', $textDelimiter);
        fputcsv($fd, [], ';', $textDelimiter);

        fputcsv($fd, [$moduleConfigurationsInfo], ';', $textDelimiter);
        fputcsv($fd, [$psSettingsInfo], ';', $textDelimiter);

        $query = new \DbQuery();

        $query
            ->select('spl.id_log, l.severity, l.message, spl.request, spl.response, spl.context, spl.date_add')
            ->from('saferpay_log', 'spl')
            ->leftJoin('log', 'l', 'spl.id_log = l.id_log')
            ->orderBy('spl.id_log DESC')
            ->limit(1000);

        $result = \Db::getInstance()->executeS($query);

        $firstRow = $result[0];
        $headers = [];

        foreach ($firstRow as $key => $value) {
            $headers[] = strtoupper($key);
        }

        $fd = fopen('php://output', 'wb');

        fputcsv($fd, $headers, ';', $textDelimiter);

        $content = !empty($result) ? $result : [];

        foreach ($content as $row) {
            $rowValues = [];
            foreach ($row as $key => $value) {
                $rowValues[] = $value;
            }

            fputcsv($fd, $rowValues, ';', $textDelimiter);
        }

        @fclose($fd);
        die;
    }
}
