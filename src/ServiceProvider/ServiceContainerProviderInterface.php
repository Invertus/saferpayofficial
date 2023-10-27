<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Square,, squareup.com <https://squareup.com/help/us/en>
 * @copyright Copyright (c) permanent, Square, Inc.
 * @license   Apache License
 *
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of Square, Inc.
 */

namespace Invertus\SaferPay\ServiceProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ServiceContainerProviderInterface
{
    /**
     * Gets service that is defined by module container.
     *
     * @param string $serviceName
     */
    public function getService($serviceName);

    /**
     * Extending the service. Useful for tests to dynamically change the implementations
     *
     * @param string $id
     * @param string $concrete - a class name
     *
     * @return mixed
     */
    public function extend($id, $concrete = null);
}