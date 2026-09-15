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

namespace Invertus\SaferPay\Service\TransactionFlow;

use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Saferpay requires a transaction to be marked as processed once a definite authorization response
 * has been received, so the shop never authorizes the same transaction twice. The saferpay_order
 * row carries that mark already, this only reads it before the assert is attempted again.
 */
class SaferPayTransactionProcessedGuard
{
    /** @var SaferPayOrderRepository */
    private $saferPayOrderRepository;

    public function __construct(SaferPayOrderRepository $saferPayOrderRepository)
    {
        $this->saferPayOrderRepository = $saferPayOrderRepository;
    }

    /**
     * A pending transaction is deliberately not treated as processed. Pending is not a definite
     * response, the notification is still expected to settle it.
     *
     * @param int $cartId
     *
     * @return bool
     */
    public function isProcessed(int $cartId): bool
    {
        $saferPayOrderId = (int) $this->saferPayOrderRepository->getIdByCartId($cartId);

        if (!$saferPayOrderId) {
            return false;
        }

        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        return (bool) $saferPayOrder->authorized || (bool) $saferPayOrder->captured;
    }
}
