<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Klarna Bank AB www.klarna.com
 * @copyright Copyright (c) permanent, Klarna Bank AB
 * @license   ISC
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Klarna Bank AB
 */

namespace Invertus\SaferPay\Controller;

use Invertus\SaferPay\Response\JsonResponse;

class AbstractAdminSaferPayController extends \ModuleAdminController
{
    const FILE_NAME = 'AbstractAdminSaferPayController';

    protected function ajaxResponse($value = null, $controller = null, $method = null): void
    {
//        /** @var LoggerInterface $logger */
//        $logger = $this->module->getService(LoggerInterface::class);

        if ($value instanceof JsonResponse) {
            if ($value->getStatusCode() === JsonResponse::HTTP_INTERNAL_SERVER_ERROR) {
//                $logger->error('Failed to return valid response', [
//                    'context' => [
//                        'response' => $value->getContent(),
//                    ],
//                ]);
            }

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

    public function ensureHasPermissions(array $permissions, bool $ajax = false): bool
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