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

namespace Invertus\SaferPay\Controller;

use Invertus\SaferPay\Response\JsonResponse;

class AbstractAdminSaferPayController extends \ModuleAdminController
{
    const FILE_NAME = 'AbstractAdminSaferPayController';

    protected function ajaxResponse($value = null, $controller = null, $method = null)
    {
//        /** @var LoggerInterface $logger */
//        $logger = $this->module->getService(LoggerInterface::class);

        if ($value instanceof JsonResponse) {
//            if ($value->getStatusCode() === JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {
//                $logger->error('Failed to return valid response', [
//                    'context' => [
//                        'response' => $value->getContent(),
//                    ],
//                ]);
//            }

            http_response_code($value->getStatusCode());

            $value = $value->getContent();
        }

        try {
            if (method_exists(\ControllerCore::class, 'ajaxRender')) {
                $this->ajaxRender($value, $controller, $method);

                exit;
            }

            $this->ajaxDie($value, $controller, $method);
        } catch (\Exception $exception) {
//            $logger->error('Could not return ajax response', [
//                'context' => [
//                    'response' => json_encode($value ?: []),
//                    'exceptions' => ExceptionUtility::getExceptions($exception),
//                ],
//            ]);
        }

        exit;
    }

    public function ensureHasPermissions($permissions, $ajax = false)
    {
        foreach ($permissions as $permission) {
            if (!$this->access($permission)) {
                if ($ajax) {
                    $this->ajaxResponse(json_encode([
                        'error' => true,
                        'message' => $this->module->l('Unauthorized.', self::FILE_NAME),
                    ]), JsonResponse::HTTP_UNAUTHORIZED);
                } else {
                    $this->errors[] = $this->module->l(
                        'You do not have permission to view this.',
                        self::FILE_NAME
                    );
                }

                return false;
            }
        }

        return true;
    }
}
