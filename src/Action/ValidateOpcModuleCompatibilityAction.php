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

    /**
     * Get only first enabled OPC module
     *
     * @return string
     */
    public function getEnabledOpcModule()
    {
        foreach (SaferPayConfig::OPC_MODULE_LIST as $opcModule) {
            if (Module::isEnabled($opcModule)) {
                return $opcModule;
            }
        }

        return '';
    }
}