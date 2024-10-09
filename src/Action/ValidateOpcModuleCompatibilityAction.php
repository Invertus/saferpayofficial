<?php

namespace Invertus\SaferPay\Action;

use Invertus\SaferPay\Config\SaferPayConfig;
use Module;

class ValidateOpcModuleCompatibilityAction
{
    public function run()
    {
        foreach (SaferPayConfig::OPC_MODULE_LIST as $opcModule){
            if (Module::isInstalled($opcModule) && Module::isEnabled($opcModule)) {
                return true;
            }
        }

        return false;
    }
}