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

use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Exception;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayRefreshPaymentsService
{
    const ALL_COUNTRIES_ENABLED = "1";
    const ALL_CURRENCIES_ENABLED = "1";

    private $paymentRepository;
    private $obtainPayments;
    private $restrictionRepository;
    private $fieldRepository;
    private $logger;

    public function __construct(
        SaferPayPaymentRepository $paymentRepository,
        SaferPayObtainPaymentMethods $obtainPaymentMethods,
        SaferPayRestrictionRepository $restrictionRepository,
        SaferPayFieldRepository $fieldRepository,
        LoggerInterface $logger
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->obtainPayments = $obtainPaymentMethods;
        $this->restrictionRepository = $restrictionRepository;
        $this->fieldRepository = $fieldRepository;
        $this->logger = $logger;
    }

    public function refreshPayments()
    {
        // Get payments from API.
        try {
            $paymentsFromAPI = $this->obtainPayments->obtainPaymentMethods();
        } catch (Exception $exception) {
            throw new SaferPayApiException('Initialize API failed', SaferPayApiException::INITIALIZE);
        }

        // Read every stored row, not only the enabled ones, so that a method the merchant
        // deliberately switched off keeps its flags across a refresh instead of silently
        // reappearing as enabled-by-default.
        $paymentsInfo = [];
        foreach ($this->paymentRepository->getAllPaymentMethods() as $payment) {
            $paymentsInfo[$payment['name']]['active'] = $payment['active'];
            $paymentsInfo[$payment['name']]['field'] = $this->fieldRepository->isActiveByName($payment['name']);
        }

        $paymentNamesFromAPI = [];
        foreach ($paymentsFromAPI as $payment) {
            $paymentNamesFromAPI[] = $this->getPaymentName($payment);
        }

        // Logged before the rebuild so that a failure part way through the inserts still
        // leaves a record of what the account stopped offering.
        $this->logRemovedPayments($paymentsInfo, $paymentNamesFromAPI);

        // Truncate tables.
        $this->paymentRepository->truncateTable();
        $this->fieldRepository->truncateTable();

        foreach ($paymentsFromAPI as $payment) {
            $paymentName = $this->getPaymentName($payment);
            $paymentActive = (isset($paymentsInfo[$paymentName]['active'])) ? (int) $paymentsInfo[$paymentName]['active'] : 0;
            $fieldActive = (isset($paymentsInfo[$paymentName]['field'])) ? (int) $paymentsInfo[$paymentName]['field'] : 0;

            // The logo and the supported currencies are the only two things the checkout
            // needed the account for. Persisting them here is what lets hookPaymentOptions
            // build the payment list without calling the Management API on every render.
            $currencies = isset($payment['currencies']) && is_array($payment['currencies'])
                ? $payment['currencies']
                : [];

            $this->paymentRepository->insertPayment([
                'name' => pSQL($paymentName),
                'active' => $paymentActive,
                'logo_url' => pSQL((string) $payment['logoUrl']),
                'currencies' => pSQL(implode(',', $currencies)),
            ]);

            $this->fieldRepository->insertField([
                'name' => pSQL($paymentName),
                'active' => $fieldActive,
            ]);
        }
    }

    /**
     * @param array $payment
     *
     * @return string
     */
    private function getPaymentName(array $payment)
    {
        return str_replace(' ', '', $payment['paymentMethod']);
    }

    /**
     * A method that disappears from the Saferpay account is dropped from storage without
     * leaving any trace, so the merchant finds it gone from both the settings page and the
     * checkout with nothing to explain why. Logging it is what lets support tell them that
     * Saferpay stopped offering it, instead of the module having lost it.
     *
     * @param array $storedPayments
     * @param array $paymentNamesFromAPI
     *
     * @return void
     */
    private function logRemovedPayments(array $storedPayments, array $paymentNamesFromAPI)
    {
        $removedPayments = array_diff(array_keys($storedPayments), $paymentNamesFromAPI);

        foreach ($removedPayments as $paymentName) {
            $message = sprintf(
                'Payment method "%s" is no longer available on the Saferpay account and was removed from this shop',
                $paymentName
            );

            if (empty($storedPayments[$paymentName]['active'])) {
                $this->logger->notice($message, ['context' => []]);

                continue;
            }

            $this->logger->warning(
                sprintf('%s. It was enabled, so it is no longer offered in the checkout', $message),
                ['context' => []]
            );
        }
    }
}
