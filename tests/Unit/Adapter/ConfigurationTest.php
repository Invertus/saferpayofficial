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

namespace Invertus\SaferPay\Tests\Unit\Adapter;

use Configuration as PrestaShopConfiguration;
use Db;
use DbQuery;
use Invertus\SaferPay\Adapter\Configuration;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Tests\Unit\Tools\UnitTestCase;

class ConfigurationTest extends UnitTestCase
{
    const KEY = 'SAFERPAY_UNIT_TEST_RAW_VALUE';

    protected function tearDown()
    {
        PrestaShopConfiguration::deleteByName(self::KEY);

        parent::tearDown();
    }

    /**
     * @dataProvider rawValueProvider
     */
    public function testSetRawStoresValueExactlyAsGiven($value)
    {
        $configuration = new Configuration(new LegacyContext());

        $configuration->setRaw(self::KEY, $value);

        $this->assertSame($value, $this->getStoredValue());
        $this->assertSame($value, $configuration->get(self::KEY));
    }

    public function testSetRawOverwritesAnExistingValue()
    {
        $configuration = new Configuration(new LegacyContext());

        $configuration->setRaw(self::KEY, 'Old<pass>word');
        $configuration->setRaw(self::KEY, 'New<pass>word&"2');

        $this->assertSame('New<pass>word&"2', $this->getStoredValue());
        $this->assertSame('New<pass>word&"2', $configuration->get(self::KEY));
    }

    public function rawValueProvider()
    {
        return [
            'tag-like part' => ['Pa<ss>word&"1'],
            'lone less-than' => ['abc<3xyz'],
            'quotes and ampersand' => ['a&b"c\'d>e'],
            'backslash' => ['back\\slash'],
            'whole value looks like a tag' => ['<abc>'],
            'trailing less-than' => ['abc<'],
            'newline' => ["a\nb<c"],
            'numeric looking' => ['0123'],
            'plain' => ['PlainPassword123'],
        ];
    }

    private function getStoredValue()
    {
        $query = new DbQuery();
        $query->select('value');
        $query->from('configuration');
        $query->where('name = "' . pSQL(self::KEY) . '"');

        return Db::getInstance()->getValue($query, false);
    }
}
