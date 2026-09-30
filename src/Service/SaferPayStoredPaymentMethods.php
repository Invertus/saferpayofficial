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

namespace Invertus\SaferPay\Service;

use Exception;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Serves the checkout the payment method list that used to come from the Management API.
 *
 * The account is only asked when nothing is stored yet, so a shop that has never opened the
 * settings page still recovers on its own instead of showing an empty payment step.
 */
class SaferPayStoredPaymentMethods
{
    const FILE_NAME = 'SaferPayStoredPaymentMethods';

    /** @var SaferPayPaymentRepository */
    private $paymentRepository;

    /** @var SaferPayRefreshPaymentsService */
    private $refreshPaymentsService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SaferPayPaymentRepository $paymentRepository,
        SaferPayRefreshPaymentsService $refreshPaymentsService,
        LoggerInterface $logger
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->refreshPaymentsService = $refreshPaymentsService;
        $this->logger = $logger;
    }

    public function getPaymentMethods(): array
    {
        $paymentMethods = $this->readStoredPaymentMethods();

        if ($this->isPopulated($paymentMethods)) {
            return $paymentMethods;
        }

        try {
            $this->refreshPaymentsService->refreshPayments();
        } catch (Exception $exception) {
            $this->logger->error(sprintf('%s - failed to populate the stored payment methods', self::FILE_NAME), [
                'context' => [],
                'exception' => $exception,
            ]);

            return [];
        }

        return $this->readStoredPaymentMethods();
    }

    /**
     * Rows written before the logo and currencies were stored carry neither, and so cannot
     * drive the checkout. Every method the account returns has a logo, so its absence across
     * the board means the upgrade backfill never ran or could not reach the account.
     */
    private function isPopulated(array $paymentMethods): bool
    {
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod['logoUrl'] !== '') {
                return true;
            }
        }

        return false;
    }

    private function readStoredPaymentMethods(): array
    {
        $paymentMethods = [];

        foreach ($this->paymentRepository->getAllPaymentMethods() as $payment) {
            $currencies = empty($payment['currencies']) ? [] : explode(',', $payment['currencies']);

            $paymentMethods[$payment['name']] = [
                'paymentMethod' => $payment['name'],
                'logoUrl' => (string) $payment['logo_url'],
                'currencies' => $currencies,
            ];
        }

        return $paymentMethods;
    }
}
