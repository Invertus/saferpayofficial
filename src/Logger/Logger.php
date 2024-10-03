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

namespace Invertus\SaferPay\Logger;

use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\Saferpay\Context\GlobalShopContext;
use Invertus\SaferPay\EntityManager\EntityManagerInterface;
use Invertus\SaferPay\EntityManager\ObjectModelEntityManager;
use Invertus\SaferPay\EntityManager\ObjectModelUnitOfWork;
use Invertus\SaferPay\Logger\Formatter\LogFormatterInterface;
use Invertus\SaferPay\Provider\BasicIdempotencyProvider;
use Invertus\SaferPay\Repository\PrestashopLoggerRepositoryInterface;

class Logger implements LoggerInterface
{
    const FILE_NAME = 'Logger';

    const LOG_OBJECT_TYPE = 'saferpayLog';

    const SEVERITY_INFO = 1;
    const SEVERITY_WARNING = 2;
    const SEVERITY_ERROR = 3;

    private $logFormatter;
    private $globalShopContext;
    private $configuration;
    private $context;
    private $entityManager;
    private $idempotencyProvider;
    private $prestashopLoggerRepository;

    public function __construct(
        LogFormatterInterface $logFormatter,
        GlobalShopContext $globalShopContext,
        Configuration $configuration,
        LegacyContext $context,
        ObjectModelEntityManager $entityManager,
        BasicIdempotencyProvider $idempotencyProvider,
        PrestashopLoggerRepositoryInterface $prestashopLoggerRepository
    ) {
        $this->logFormatter = $logFormatter;
        $this->globalShopContext = $globalShopContext;
        $this->configuration = $configuration;
        $this->context = $context;
        $this->entityManager = $entityManager;
        $this->idempotencyProvider = $idempotencyProvider;
        $this->prestashopLoggerRepository = $prestashopLoggerRepository;
    }

    public function emergency($message, array $context = [])
    {
        $this->log(
            $this->configuration->getAsInteger(
                'PS_LOGS_BY_EMAIL',
                $this->globalShopContext->getShopId()
            ),
            $message,
            $context
        );
    }

    public function alert($message, array $context = [])
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(
            $this->configuration->getAsInteger(
                'PS_LOGS_BY_EMAIL',
                $this->globalShopContext->getShopId()
            ),
            $message,
            $context
        );
    }

    public function error($message, array $context = [])
    {
        $this->log(self::SEVERITY_ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(self::SEVERITY_WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        if (!SaferPayConfig::isDebugMode()) {
            return;
        }

        $this->log(self::SEVERITY_INFO, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $idempotencyKey = $this->idempotencyProvider->getIdempotencyKey();

        \PrestaShopLogger::addLog(
            $this->logFormatter->getMessage($message),
            $level,
            null,
            self::LOG_OBJECT_TYPE,
            $idempotencyKey
        );

        $logId = $this->prestashopLoggerRepository->getLogIdByObjectId(
            $idempotencyKey,
            $this->globalShopContext->getShopId()
        );

        if (!$logId) {
            return;
        }

        $this->logContext($logId, $context);
    }

    private function logContext($logId, array $context)
    {
        $request = '';
        $response = '';

        if (isset($context['request'])) {
            $request = $context['request'];
            unset($context['request']);
        }

        if (isset($context['response'])) {
            $response = $context['response'];
            unset($context['response']);
        }

        if (isset($context['correlation-id'])) {
            $correlationId = $context['correlation-id'];
            unset($context['correlation-id']);
        }

        $log = new \SaferPayLog();
        $log->id_log = $logId;
        $log->id_shop = $this->globalShopContext->getShopId();
        $log->context = json_encode($this->getFilledContextWithShopData($context));
        $log->request = json_encode($request);
        $log->response = json_encode($response);

        $this->entityManager->persist($log, ObjectModelUnitOfWork::UNIT_OF_WORK_SAVE);
        $this->entityManager->flush();
    }

    private function getFilledContextWithShopData(array $context = [])
    {
        $context['context_id_customer'] = $this->context->getCustomerId();
        $context['id_shop'] = $this->globalShopContext->getShopId();
        $context['currency'] = $this->globalShopContext->getCurrencyIso();
        $context['id_language'] = $this->globalShopContext->getLanguageId();

        return $context;
    }
}
