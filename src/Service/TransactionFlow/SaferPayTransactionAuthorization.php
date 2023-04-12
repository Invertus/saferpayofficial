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

use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Api\Request\AuthorizationService;
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\AuthorizationRequestObjectCreator;
use Invertus\SaferPay\Service\SaferPayOrderStatusService;
use Order;
use SaferPayOrder;

class SaferPayTransactionAuthorization
{
    /**
     * @var AuthorizationRequestObjectCreator
     */
    private $authRequestCreator;

    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var SaferPayOrderStatusService
     */
    private $orderStatusService;

    /**
     * @var LegacyContext
     */
    private $context;

    public function __construct(
        AuthorizationRequestObjectCreator $authRequestCreator,
        SaferPayOrderRepository $orderRepository,
        AuthorizationService $authorizationService,
        SaferPayOrderStatusService $orderStatusService,
        LegacyContext $context
    ) {
        $this->authRequestCreator = $authRequestCreator;
        $this->orderRepository = $orderRepository;
        $this->authorizationService = $authorizationService;
        $this->orderStatusService = $orderStatusService;
        $this->context = $context;
    }

    /**
     * @param int $orderId
     * @param int $saveCard
     * @param string $selectedCard
     *
     * @return AssertBody
     * @throws \Exception
     */
    public function authorize($orderId, $saveCard, $selectedCard)
    {
        $order = new Order($orderId);

        $saferPayOrderId = $this->orderRepository->getIdByOrderId($orderId);
        $saferPayOrder =  new SaferPayOrder($saferPayOrderId);

        $authRequest = $this->authRequestCreator->create($saferPayOrder->token, $saveCard);
        $authResponse = $this->authorizationService->authorize($authRequest);

        $assertBody = $this->authorizationService->createObjectsFromAuthorizationResponse(
            $authResponse,
            $saferPayOrder->id,
            $order->id_customer,
            $selectedCard
        );

        $saferPayOrder->transaction_id = $assertBody->getTransaction()->getId();
        $saferPayOrder->update();

        return $assertBody;
    }
}
