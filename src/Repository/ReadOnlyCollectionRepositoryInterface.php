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

namespace Invertus\SaferPay\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ReadOnlyCollectionRepositoryInterface
{
    /**
     * @param int|null $langId - objects which ussualy are type of array will become strings. E.g
     *                         $product->name is string instead of multidimensional array where key is id_language.
     *                         Always pass language id
     *                         unless there is a special need not to. Synchronization or smth.
     *                         It saves quite a lot performance wise.
     *
     * @return \PrestaShopCollection
     */
    public function findAllInCollection($langId = null);

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     * @param int|null $langId
     *
     * @return \ObjectModel|null
     */
    public function findOneBy($keyValueCriteria, $langId = null);
}