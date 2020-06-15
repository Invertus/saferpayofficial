<?php

namespace Invertus\SaferPay\Controller;

use Invertus\SaferPay\Config\SaferPayConfig;

class AbstractSaferPayController extends \ModuleFrontControllerCore
{
    public function redirectWithNotifications($url)
    {
        $notifications = json_encode(array(
            'error' => $this->errors,
            'warning' => $this->warning,
            'success' => $this->success,
            'info' => $this->info,
        ));

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

}