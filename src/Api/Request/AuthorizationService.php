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
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\Authorization\AuthorizationRequest;
use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\EntityBuilder\SaferPayAssertBuilder;
use Invertus\SaferPay\EntityBuilder\SaferPayCardAliasBuilder;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use Invertus\SaferPay\Service\Response\AssertResponseObjectCreator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AuthorizationService
{
    const AUTHORIZE_API = 'Payment/v1/Transaction/Authorize';

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

    /**
     * @var SaferPayCardAliasBuilder
     */
    private $aliasBuilder;

    private $aliasRepository;

    public function __construct(
        ApiRequest $apiRequest,
        AssertResponseObjectCreator $assertResponseObjectCreator,
        SaferPayAssertBuilder $assertBuilder,
        SaferPayCardAliasBuilder $aliasBuilder,
        SaferPayCardAliasRepository $aliasRepository
    ) {
        $this->apiRequest = $apiRequest;
        $this->assertResponseObjectCreator = $assertResponseObjectCreator;
        $this->assertBuilder = $assertBuilder;
        $this->aliasBuilder = $aliasBuilder;
        $this->aliasRepository = $aliasRepository;
    }

    public function authorize(AuthorizationRequest $authorizationRequest)
    {
        try {
            return $this->apiRequest->post(
                self::AUTHORIZE_API,
                $authorizationRequest->getAsArray()
            );
        } catch (Exception $e) {
            throw new SaferPayApiException('Authorize API failed', SaferPayApiException::AUTHORIZE);
        }
    }

    /**
     * @param $responseBody
     * @param $saferPayOrderId
     * @param $customerId
     * @param $selectedCardOption
     *
     * @return AssertBody
     * @throws Exception
     */
    public function createObjectsFromAuthorizationResponse(
        $responseBody,
        $saferPayOrderId,
        $customerId,
        $selectedCardOption
    ) {
        $assertBody = $this->assertResponseObjectCreator->createAssertObject($responseBody);
        $this->assertBuilder->createAssert($assertBody, $saferPayOrderId);
        $isPaymentSafe = $assertBody->getLiability()->getLiabilityShift();
        $allSavedCards = array_column($this->aliasRepository->getSavedCardsNumbersByCustomerId($customerId), 'card_number');
        $currentCard = $assertBody->getPaymentMeans()->getDisplayText();

        if (in_array($currentCard, $allSavedCards)) {
            return $assertBody;
        }

        if ((int) $selectedCardOption === SaferPayConfig::CREDIT_CARD_OPTION_SAVE && $isPaymentSafe) {
            $this->aliasBuilder->createCardAlias($assertBody, $customerId);
        }

        return $assertBody;
    }
}
