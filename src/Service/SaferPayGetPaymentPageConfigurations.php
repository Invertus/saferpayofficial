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

use Invertus\SaferPay\Api\Request\GetPaymentPageConfigurationsService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\GetPaymentPageConfigurations\GetPaymentPageConfigurationsRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayGetPaymentPageConfigurations
{
    /** @var GetPaymentPageConfigurationsService */
    private $getPaymentPageConfigurationsService;

    public function __construct(GetPaymentPageConfigurationsService $getPaymentPageConfigurationsService)
    {
        $this->getPaymentPageConfigurationsService = $getPaymentPageConfigurationsService;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $customerId
     * @param bool $isTestMode
     * @return string[] configuration names as stored in the Saferpay account
     */
    public function fetchConfigurationsWithCredentials($username, $password, $customerId, $isTestMode)
    {
        $baseUrl = $isTestMode ? SaferPayConfig::TEST_API : SaferPayConfig::API;
        $request = new GetPaymentPageConfigurationsRequest($customerId);

        $response = $this->getPaymentPageConfigurationsService->getPaymentPageConfigurations(
            $request,
            $username,
            $password,
            $baseUrl
        );

        $configurationList = isset($response->Configurations) ? $response->Configurations : [];

        if (!is_array($configurationList)) {
            return [];
        }

        $configurations = [];
        foreach ($configurationList as $configuration) {
            if (!isset($configuration->Name) || $configuration->Name === '') {
                continue;
            }

            $configurations[] = $configuration->Name;
        }

        return $configurations;
    }
}
