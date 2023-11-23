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

namespace Invertus\SaferPay\Api\Request;

use Exception;
use Invertus\SaferPay\Api\ApiRequest;
use Invertus\SaferPay\DTO\Request\Assert\AssertRequest;
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\EntityBuilder\SaferPayAssertBuilder;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Service\Response\AssertResponseObjectCreator;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AssertService
{
    const ASSERT_API_PAYMENT = 'Payment/v1/PaymentPage/Assert';

    const ASSERT_API_TRANSACTION = 'Payment/v1/Transaction/Authorize';

    /**
     * @var ApiRequest
     */
    private $apiRequest;
    /**
     * @var AssertResponseObjectCreator
     */
    private $assertResponseObjectCreator;
    /**
     * @var SaferPayAssertBuilder
     */
    private $assertBuilder;

    public function __construct(
        ApiRequest $apiRequest,
        AssertResponseObjectCreator $assertResponseObjectCreator,
        SaferPayAssertBuilder $assertBuilder
    ) {
        $this->apiRequest = $apiRequest;
        $this->assertResponseObjectCreator = $assertResponseObjectCreator;
        $this->assertBuilder = $assertBuilder;
    }

    /**
     * @param AssertRequest $assertRequest
     * @param int $saferPayOrderId
     *
     * @return array|null
     * @throws \Exception
     */
    public function assert(AssertRequest $assertRequest, $saferPayOrderId)
    {
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        $assertApi = self::ASSERT_API_PAYMENT;

        //TODO: refactor this to use authorize request.
        // naming is weird. With transaction, we do a request to an authorize endpoint but name it assert ?
        // also we call authorize method in some of the success controllers, so if we leave the logic here,
        // we get an error with TRANSACTION_IN_WRONG_STATE
        if ($saferPayOrder->is_transaction) {
            return [];
//          $assertApi = self::ASSERT_API_TRANSACTION;
        }

        try {
            return $this->apiRequest->post(
                $assertApi,
                $assertRequest->getAsArray()
            );
        } catch (Exception $e) {
            throw new SaferPayApiException('Assert API failed', SaferPayApiException::ASSERT);
        }
    }

    /**
     * @param object $responseBody
     * @param int $saferPayOrderId
     *
     * @return AssertBody
     * @throws Exception
     */
    public function createObjectsFromAssertResponse($responseBody, $saferPayOrderId)
    {
        $assertBody = $this->assertResponseObjectCreator->createAssertObject($responseBody);
        $this->assertBuilder->createAssert($assertBody, $saferPayOrderId);

        return $assertBody;
    }
}
