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

use Invertus\SaferPay\Exception\CouldNotAccessDatabase;
use ObjectModel;
use PrestaShopCollection;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AbstractRepository implements ReadOnlyRepositoryInterface
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    /**
     * @param string $fullyClassifiedClassName
     */
    public function __construct($fullyClassifiedClassName)
    {
        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    /**
     * Find all entities of the repository type
     *
     * @return PrestaShopCollection
     *
     * @throws CouldNotAccessDatabase
     */
    public function findAll()
    {
        try {
            return new PrestaShopCollection($this->fullyClassifiedClassName);
        } catch (\Exception $exception) {
            throw CouldNotAccessDatabase::failedToCreateCollection(
                $this->fullyClassifiedClassName,
                $exception
            );
        }
    }

    /**
     * Find one entity by criteria
     *
     * @param array $keyValueCriteria - Key-value pairs to filter by (e.g., ['id_order' => 123])
     *
     * @return ObjectModel|null - Returns entity or null if not found
     *
     * @throws CouldNotAccessDatabase - If database query fails
     * @throws PrestaShopException - If PrestaShop collection operations fail
     */
    public function findOneBy(array $keyValueCriteria)
    {
        // Validate criteria is not empty
        if (empty($keyValueCriteria)) {
            throw CouldNotAccessDatabase::invalidCriteria(
                $keyValueCriteria,
                'Search criteria cannot be empty'
            );
        }

        try {
            $psCollection = new PrestaShopCollection($this->fullyClassifiedClassName);

            foreach ($keyValueCriteria as $field => $value) {
                // Validate field name to prevent SQL injection attempts
                if (!is_string($field) || empty($field)) {
                    throw CouldNotAccessDatabase::invalidCriteria(
                        $keyValueCriteria,
                        sprintf('Invalid field name provided: %s', var_export($field, true))
                    );
                }

                $psCollection = $psCollection->where($field, '=', $value);
            }

            $first = $psCollection->getFirst();

            /* @phpstan-ignore-next-line */
            return false === $first ? null : $first;
        } catch (CouldNotAccessDatabase $exception) {
            // Re-throw our custom exceptions
            throw $exception;
        } catch (\Exception $exception) {
            // Wrap any other exceptions in our custom exception
            throw CouldNotAccessDatabase::failedToQuery(
                $this->fullyClassifiedClassName,
                $keyValueCriteria,
                $exception
            );
        }
    }
}
