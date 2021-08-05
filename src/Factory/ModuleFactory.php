<?php
declare(strict_types=1);

namespace Invertus\SaferPay\Factory;

use Module;
use SaferPayOfficial;

class ModuleFactory
{
    public function getModule() : Saferpayofficial
    {
        /** @var SaferPayOfficial $module */
        $module = Module::getInstanceByName('saferpayofficial');

        return $module;
    }
}

