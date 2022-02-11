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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Repository\SaferPayFieldRepository;
use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;

class SaferPayRefreshPaymentsService
{
    const ALL_COUNTRIES_ENABLED = "1";
    const ALL_CURRENCIES_ENABLED = "1";

    private $paymentRepository;
    private $obtainPayments;
    private $restrictionRepository;
    private $fieldRepository;

    public function __construct(
        SaferPayPaymentRepository $paymentRepository,
        SaferPayObtainPaymentMethods $obtainPaymentMethods,
        SaferPayRestrictionRepository $restrictionRepository,
        SaferPayFieldRepository $fieldRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->obtainPayments = $obtainPaymentMethods;
        $this->restrictionRepository = $restrictionRepository;
        $this->fieldRepository = $fieldRepository;
    }

    public function refreshPayments()
    {
        // Get enabled payments.
        $activePayments = $this->paymentRepository->getActivePaymentMethods();
        if (empty($activePayments)) {
            return;
        }

        // Get payments from API.
        $paymentsFromAPI = $this->obtainPayments->obtainPaymentMethodsNamesAsArray();

        $paymentsInfo = [];
        foreach ($activePayments as $payment) {
            $paymentsInfo[$payment['name']]['name'] = $payment['name'];
            $paymentsInfo[$payment['name']]['active'] = $payment['active'];
            $paymentsInfo[$payment['name']]['field'] = $this->fieldRepository->isActiveByName($payment['name']);
        }

        // Truncate tables.
        $this->paymentRepository->truncateTable();
        $this->fieldRepository->truncateTable();

        foreach ($paymentsFromAPI as $payment) {
            $paymentActive = (isset($paymentsInfo[$payment]['active'])) ? (int)$paymentsInfo[$payment]['active'] : 0;
            $fieldActive = (isset($paymentsInfo[$payment]['field'])) ? (int)$paymentsInfo[$payment]['field'] : 0;

            $this->paymentRepository->insertPayment([
                'name' => $payment,
                'active' => $paymentActive
            ]);

            $this->fieldRepository->insertField([
                'name' => $payment,
                'active' => $fieldActive
            ]);
        }
    }
}
