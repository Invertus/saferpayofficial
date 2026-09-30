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
use Invertus\SaferPay\Api\Request\GetLicenseService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\GetLicense\GetLicenseRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayGetLicense
{
    const FEATURE_HOSTED_ENTRY_FORM = 'HOSTED_ENTRY_FORM';

    /** @var GetLicenseService */
    private $getLicenseService;

    public function __construct(GetLicenseService $getLicenseService)
    {
        $this->getLicenseService = $getLicenseService;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $customerId
     * @param bool $isTestMode
     *
     * @return array{hasBusinessLicense: bool, packageName: string, features: array}
     *
     * @throws Exception
     */
    public function fetchLicenseWithCredentials($username, $password, $customerId, $isTestMode)
    {
        $baseUrl = $isTestMode ? SaferPayConfig::TEST_API : SaferPayConfig::API;
        $request = new GetLicenseRequest($customerId);

        $response = $this->fetchWithFallback($request, $username, $password, $baseUrl);

        $packageName = '';
        if (isset($response->Package->DisplayName)) {
            $packageName = $response->Package->DisplayName;
        }

        $features = [];
        $featureList = isset($response->Features) ? $response->Features : [];
        if (is_array($featureList)) {
            foreach ($featureList as $feature) {
                if (isset($feature->Id)) {
                    $features[] = $feature->Id;
                }
            }
        }

        $hasBusinessLicense = in_array(self::FEATURE_HOSTED_ENTRY_FORM, $features, true);

        return [
            'hasBusinessLicense' => $hasBusinessLicense,
            'packageName' => $packageName,
            'features' => $features,
        ];
    }

    /**
     * @param GetLicenseRequest $request
     * @param string $username
     * @param string $password
     * @param string $baseUrl
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function fetchWithFallback(GetLicenseRequest $request, $username, $password, $baseUrl)
    {
        try {
            return $this->getLicenseService->getLicense($request, $username, $password, $baseUrl);
        } catch (Exception $e) {
            return $this->getLicenseService->getLicenseFallback($request, $username, $password, $baseUrl);
        }
    }
}
