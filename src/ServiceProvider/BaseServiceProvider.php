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

namespace Invertus\SaferPay\ServiceProvider;

use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Context\GlobalShopContext;
use Invertus\SaferPay\Context\GlobalShopContextInterface;
use Invertus\SaferPay\Provider\BasicIdempotencyProvider;
use Invertus\SaferPay\Provider\IdempotencyProviderInterface;
use Invertus\SaferPay\Repository\OrderRepository;
use Invertus\SaferPay\Repository\OrderRepositoryInterface;
use Invertus\SaferPay\Repository\SaferPayLogRepository;
use Invertus\SaferPay\Repository\SaferPayLogRepositoryInterface;
use League\Container\Container;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Load base services here which are usually required
 */
final class BaseServiceProvider
{
    private $extendedServices;

    public function __construct($extendedServices)
    {
        $this->extendedServices = $extendedServices;
    }

    public function register(Container $container)
    {
        $container->add(GlobalShopContext::class, function() {
            return new GlobalShopContext($this->getService(LegacyContext::class, new LegacyContext()));
        });
        $this->addService($container, GlobalShopContextInterface::class, $container->get(GlobalShopContext::class));
        $this->addService($container, IdempotencyProviderInterface::class, $container->get(BasicIdempotencyProvider::class));
        $this->addService($container, OrderRepositoryInterface::class, $container->get(OrderRepository::class));
        $this->addService($container, SaferPayLogRepositoryInterface::class, $container->get(SaferPayLogRepository::class));
    }

    private function addService(Container $container, $className, $service)
    {
        return $container->add($className, $this->getService($className, $service));
    }

    //NOTE need to call this as extended services should be initialized everywhere.
    public function getService($className, $service)
    {
        if (isset($this->extendedServices[$className])) {
            return $this->extendedServices[$className];
        }

        return $service;
    }
}
