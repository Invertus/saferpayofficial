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

namespace Invertus\SaferPay\Service\Request;

use Invertus\SaferPay\Config\SaferPayConfig;
use Configuration;
use Invertus\SaferPay\DTO\Request\ObtainPaymentMethods\ObtainPaymentMethodsRequest;
use Invertus\SaferPay\DTO\Request\RequestHeader;

class ObtainPaymentMethodsObjectCreator
{
    private $requestObjectCreator;

    public function __construct(
        RequestObjectCreator $requestObjectCreator
    ){
        $this->requestObjectCreator = $requestObjectCreator;
    }

    public function create()
    {
        $requestHeader = $this->requestObjectCreator->createRequestHeader();
        $terminalId = Configuration::get(SaferPayConfig::TERMINAL_ID . SaferPayConfig::getConfigSuffix());

        return new ObtainPaymentMethodsRequest($requestHeader, (string) $terminalId);
    }
}
