<?php

namespace Invertus\SaferPay\Factory;

use Module;

class ModuleFactory
{
    /**
     * @return \SaferPayOfficial|Module|null
     */
    public function getModule()
    {
        $response = Module::getInstanceByName('saferpayofficial');

        if (!$response) {
            return null;
        }

        return $response;
    }
}