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

namespace Invertus\SaferPay\Adapter;

use Configuration as PrestaShopConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Configuration
{

    /**
     * @var LegacyContext
     */
    private $context;

    public function __construct(LegacyContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $id
     * @param $value
     * @param int|null $shopId
     * @return void
     */
    public function set($id, $value, $shopId = null)
    {
        if (!$shopId) {
            $shopId = $this->context->getShopId();
        }

        PrestaShopConfiguration::updateValue($id, $value, false, null, $shopId);
    }

    /**
     * @param string $id
     * @param int|null $shopId
     * @return false|string|null
     */
    public function get($id, $shopId = null)
    {
        if (!$shopId) {
            $shopId = $this->context->getShopId();
        }

        $result = PrestaShopConfiguration::get($id, null, null, $shopId);

        return $result ?: null;
    }

    /**
     * @param string $id
     * @param int|null $shopId
     * @return bool
     */
    public function getAsBoolean($id, $shopId = null)
    {
        $result = $this->get($id, $shopId);

        if (in_array($result, ['null', 'false', '0', null, false, 0], true)) {
            return false;
        }

        return (bool) $result;
    }

    /**
     * @param string $id
     * @param int|null $shopId
     * @return int
     */
    public function getAsInteger($id, $shopId = null)
    {
        $result = $this->get($id, $shopId);

        if (in_array($result, ['null', 'false', '0', null, false, 0], true)) {
            return 0;
        }

        return (int) $result;
    }

    /**
     * Removes by specific shop id
     *
     * @param string $id
     * @param int|null $shopId
     */
    public function remove($id, $shopId)
    {
        // making sure to set to null value only for single shop id
        PrestaShopConfiguration::updateValue($id, null, false, null, $shopId);
    }

    /**
     * Drops configuration from all shops.
     *
     * @param string $id
     */
    public function delete($id)
    {
        PrestaShopConfiguration::deleteByName($id);
    }
}
