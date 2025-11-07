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

namespace Invertus\SaferPay\Infrastructure\Hook\Display;

use Invertus\SaferPay\Infrastructure\Hook\HookInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hook handler for hookDisplayOrderConfirmation
 * Displays pending payment message on order confirmation page
 */
class OrderConfirmationHook implements HookInterface
{
    /**
     * @var SaferPayOfficial
     */
    private $module;

    /**
     * @var SaferPayOrderRepository
     */
    private $saferPayOrderRepository;

    public function __construct(
        SaferPayOfficial $module,
        SaferPayOrderRepository $saferPayOrderRepository
    ) {
        $this->module = $module;
        $this->saferPayOrderRepository = $saferPayOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $params)
    {
        if (empty($params['order'])) {
            return '';
        }

        /** @var \Order $psOrder */
        $psOrder = $params['order'];

        $sfOrder = $this->saferPayOrderRepository->getByOrderId((int) $psOrder->id);
        if (!$sfOrder->pending) {
            return '';
        }

        return $this->module->l('Your payment is still being processed by your bank. This can take up to 5 days (120 hours). Once we receive the final status, we will notify you immediately.
Thank you for your patience!');
    }
}
