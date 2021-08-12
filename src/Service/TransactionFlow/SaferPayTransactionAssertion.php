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

use Invertus\SaferPay\Api\Request\AssertService;
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\AssertRequestObjectCreator;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Order;
use SaferPayOrder;

class SaferPayTransactionAssertion
{
    /**
     * @var AssertRequestObjectCreator
     */
    private $assertRequestCreator;

    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;

    /**
     * @var AssertService
     */
    private $assertionService;

    /**
     * @var SaferPayOrderStatusService
     */
    private $orderStatusService;

    public function __construct(
        AssertRequestObjectCreator $assertRequestCreator,
        SaferPayOrderRepository $orderRepository,
        AssertService $assertionService,
        SaferPayOrderStatusService $orderStatusService
    ) {
        $this->assertRequestCreator = $assertRequestCreator;
        $this->orderRepository = $orderRepository;
        $this->assertionService = $assertionService;
        $this->orderStatusService = $orderStatusService;
    }

    /**
     * @param int $orderId
     *
     * @return AssertBody
     * @throws \Exception
     */
    public function assert($orderId)
    {
        $saferPayOrder = $this->getSaferPayOrder($orderId);
        $order = new Order($orderId);

        $assertRequest = $this->assertRequestCreator->create($orderId);
        $assertResponse = $this->assertionService->assert($assertRequest, $saferPayOrder->id);

        $assertBody = $this->assertionService->createObjectsFromAssertResponse(
            $assertResponse,
            $saferPayOrder->id
        );

        $saferPayOrder->transaction_id = $assertBody->getTransaction()->getId();
        $saferPayOrder->update();

        $this->orderStatusService->assert($order, $assertBody->getTransaction()->getStatus());

        return $assertBody;
    }

    /**
     * @param $orderId
     *
     * @return false|SaferPayOrder
     * @throws \Exception
     */
    private function getSaferPayOrder($orderId)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($orderId);

        return new SaferPayOrder($saferPayOrderId);
    }
}
