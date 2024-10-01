<?php

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
    public function findOneBy(array $keyValueCriteria, $langId = null);
}