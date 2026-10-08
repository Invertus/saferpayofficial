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

namespace Invertus\SaferPay\Api\Http;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HttpResponse
{
    /** @var int */
    private $code;

    /** @var string */
    private $rawBody;

    /** @var mixed */
    private $body;

    public function __construct(int $code, string $rawBody)
    {
        $this->code = $code;
        $this->rawBody = $rawBody;
        $this->body = $this->decode($rawBody);
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    /**
     * Decoded JSON body, or the raw body when it is not valid JSON.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $rawBody
     *
     * @return mixed
     */
    private function decode(string $rawBody)
    {
        $decoded = json_decode($rawBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $rawBody;
        }

        return $decoded;
    }
}
