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

namespace Invertus\SaferPay\Response;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JsonResponse extends BaseJsonResponse
{
    /**
     * @param mixed $data
     */
    public function __construct($data = null, $status = 200, $headers = [])
    {
        parent::__construct($data, $status, $headers);
    }

    public static function success($data, $status = 200)
    {
        return new self([
            'success' => true,
            'errors' => [],
            'data' => $data,
        ], $status);
    }

    /**
     * @param string|array $error
     * @param int $status
     *
     * @return static
     */
    public static function error($error, $status = 400)
    {
        if ($status === JsonResponse::HTTP_UNPROCESSABLE_ENTITY) {
            // NOTE: removing rule name. ['required' => 'message'] becomes [0 => 'message']
            foreach ($error as $key => $messages) {
                $error[$key] = array_values($messages);
            }
        }

        if (!is_array($error)) {
            $error = [$error];
        }

        return new self([
            'success' => false,
            'errors' => $error,
            'data' => [],
        ], $status);
    }
}
