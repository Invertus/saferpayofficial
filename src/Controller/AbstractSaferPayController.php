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

use Invertus\Lock\Lock;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Response\Response;
use SaferPayLog;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AbstractSaferPayController extends \ModuleFrontControllerCore
{
    const FILE_NAME = 'AbstractSaferPayController';

    /** @var \SaferPayOfficial */
    public $module;

    /**
     * @var Lock
     */
    protected $lock;

    public function __construct()
    {
        parent::__construct();

        $this->lock = new Lock($this->module->getLocalPath() . 'var/cache');
    }

    public function redirectWithNotifications()
    {
        $notifications = json_encode([
            'error' => $this->errors,
            'warning' => $this->warning,
            'success' => $this->success,
            'info' => $this->info,
        ]);

        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        } elseif (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['notifications'] = $notifications;
        } else {
            setcookie('notifications', $notifications);
        }

        if (!SaferPayConfig::isVersion17()) {
            $this->context->cookie->saferpay_payment_canceled_error =
                json_encode($this->warning);
        }
        return call_user_func_array(['Tools', 'redirect'], func_get_args());
    }

    protected function applyLock($resource)
    {
        try {
            $this->lock->create($resource);

            if (!$this->lock->acquire()) {

                if (!SaferPayConfig::isVersion17()) {
                    return  http_response_code(409);
                }
                return Response::respond(
                    $this->module->l('Resource conflict', self::FILE_NAME),
                    Response::HTTP_CONFLICT
                );
            }
        } catch (\Exception $exception) {
            $logger = new SaferPayLog();
            $logger->message = 'Failed to lock process';
            $logger->payload = $resource;
            $logger->save();

            if (!SaferPayConfig::isVersion17()) {
                return  http_response_code(500);
            }

            return Response::respond(
                $this->module->l('Internal error', self::FILE_NAME),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if (!SaferPayConfig::isVersion17()) {
            return  http_response_code(200);
        }

        return Response::respond(
            '',
            Response::HTTP_OK
        );
    }

    protected function lockExist()
    {
        try {
            return $this->lock->acquire();
        } catch (\Exception $exception) {
            return false;
        }
    }

    protected function releaseLock()
    {
        try {
            $this->lock->release();
        } catch (\Exception $exception) {
            $logger = new SaferPayLog();
            $logger->message = 'Failed to release process';
            $logger->payload = $exception->getMessage() . $this->lock->acquire();
            $logger->save();
        }
    }
}
