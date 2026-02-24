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

use Invertus\SaferPay\Api\Request\GetTerminalsService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\GetTerminals\GetTerminalsRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayGetTerminals
{
    /** @var GetTerminalsService */
    private $getTerminalsService;

    public function __construct(GetTerminalsService $getTerminalsService)
    {
        $this->getTerminalsService = $getTerminalsService;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $customerId
     * @param bool $isTestMode
     * @return array
     */
    public function fetchTerminalsWithCredentials($username, $password, $customerId, $isTestMode)
    {
        $baseUrl = $isTestMode ? SaferPayConfig::TEST_API : SaferPayConfig::API;
        $request = new GetTerminalsRequest($customerId);

        $response = $this->getTerminalsService->getTerminals(
            $request,
            $username,
            $password,
            $baseUrl
        );

        $terminals = [];
        $terminalList = isset($response->Terminals) ? $response->Terminals : [];
        if (is_array($terminalList)) {
            foreach ($terminalList as $terminal) {
                if (isset($terminal->TerminalId)) {
                    $terminals[] = [
                        'id' => $terminal->TerminalId,
                        'name' => isset($terminal->Description)
                            ? $terminal->Description . ' (' . $terminal->TerminalId . ')'
                            : $terminal->TerminalId,
                    ];
                }
            }
        }

        return $terminals;
    }
}
