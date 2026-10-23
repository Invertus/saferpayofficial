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

namespace Invertus\SaferPay\Tests\Unit\Tools;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Psr4ComplianceTest extends UnitTestCase
{
    /**
     * Composer skips a class whose namespace or name does not match its path, so
     * the --classmap-authoritative release build cannot load it.
     */
    public function testEverySourceClassMatchesItsPath()
    {
        $srcDir = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS));
        $mismatches = [];
        $checked = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'index.php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (!preg_match('/^namespace\s+([^;{\s]+)/m', $content, $namespace)) {
                continue;
            }

            ++$checked;
            $relativeDir = trim(str_replace($srcDir, '', $file->getPath()), '/\\');
            $expectedNamespace = rtrim('Invertus\\SaferPay\\' . str_replace(['/', '\\'], '\\', $relativeDir), '\\');

            if ($namespace[1] !== $expectedNamespace) {
                $mismatches[] = $file->getPathname() . ' declares namespace ' . $namespace[1];
            }

            if (preg_match('/^(?:abstract\s+|final\s+)?(?:class|interface|trait)\s+(\w+)/m', $content, $class)
                && $class[1] !== $file->getBasename('.php')
            ) {
                $mismatches[] = $file->getPathname() . ' declares class ' . $class[1];
            }
        }

        $this->assertGreaterThan(0, $checked);
        $this->assertSame([], $mismatches);
    }
}
