<?php

use Composer\Autoload\ClassLoader;

require_once __DIR__ . '/../../vendor/autoload.php';

// php-dev-tools registers the tests/ci-tools autoloader before PrestaShop's and the module's,
// so its newer copies of shared libraries (psr/container, symfony) would shadow the ones the
// module runs with. Move it to the end of the autoloader stack.
foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
    if (realpath($vendorDir) === realpath(__DIR__ . '/../ci-tools/vendor')) {
        $loader->unregister();
        $loader->register(false);
    }
}
