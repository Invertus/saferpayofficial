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

namespace Invertus\SaferPay\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Exception thrown when repository/database operations fail
 */
class CouldNotAccessDatabase extends SaferPayException
{
    /**
     * @param string $entityClass
     * @param array $criteria
     * @param \Exception|null $previous
     * @return static
     */
    public static function failedToQuery($entityClass, array $criteria = [], \Exception $previous = null)
    {
        return new static(
            sprintf(
                'Failed to query database for entity "%s". Criteria: %s',
                $entityClass,
                json_encode($criteria)
            ),
            ExceptionCode::REPOSITORY_FAILED_TO_QUERY,
            [
                'entity_class' => $entityClass,
                'criteria' => $criteria,
                'previous_error' => $previous ? $previous->getMessage() : null,
            ]
        );
    }

    /**
     * @param string $entityClass
     * @param \Exception|null $previous
     * @return static
     */
    public static function failedToCreateCollection($entityClass, \Exception $previous = null)
    {
        return new static(
            sprintf('Failed to create PrestaShop collection for entity "%s"', $entityClass),
            ExceptionCode::REPOSITORY_FAILED_TO_CREATE_COLLECTION,
            [
                'entity_class' => $entityClass,
                'previous_error' => $previous ? $previous->getMessage() : null,
            ]
        );
    }

    /**
     * @param string $entityClass
     * @param array $criteria
     * @return static
     */
    public static function entityNotFound($entityClass, array $criteria = [])
    {
        return new static(
            sprintf(
                'Entity "%s" not found with criteria: %s',
                $entityClass,
                json_encode($criteria)
            ),
            ExceptionCode::REPOSITORY_ENTITY_NOT_FOUND,
            [
                'entity_class' => $entityClass,
                'criteria' => $criteria,
            ]
        );
    }

    /**
     * @param array $criteria
     * @param string $reason
     * @return static
     */
    public static function invalidCriteria(array $criteria, $reason = '')
    {
        return new static(
            sprintf(
                'Invalid search criteria provided: %s. Reason: %s',
                json_encode($criteria),
                $reason
            ),
            ExceptionCode::REPOSITORY_INVALID_CRITERIA,
            [
                'criteria' => $criteria,
                'reason' => $reason,
            ]
        );
    }

    /**
     * @param string $entityClass
     * @param array $data
     * @param \Exception|null $previous
     * @return static
     */
    public static function failedToPersist($entityClass, array $data = [], \Exception $previous = null)
    {
        return new static(
            sprintf(
                'Failed to persist entity "%s" to database',
                $entityClass
            ),
            ExceptionCode::ENTITY_FAILED_TO_PERSIST,
            [
                'entity_class' => $entityClass,
                'data' => $data,
                'previous_error' => $previous ? $previous->getMessage() : null,
            ]
        );
    }

    /**
     * @param string $entityClass
     * @param int|null $entityId
     * @param array $data
     * @param \Exception|null $previous
     * @return static
     */
    public static function failedToUpdate($entityClass, $entityId = null, array $data = [], \Exception $previous = null)
    {
        return new static(
            sprintf(
                'Failed to update entity "%s"%s',
                $entityClass,
                $entityId ? sprintf(' with ID %d', $entityId) : ''
            ),
            ExceptionCode::ENTITY_FAILED_TO_UPDATE,
            [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'data' => $data,
                'previous_error' => $previous ? $previous->getMessage() : null,
            ]
        );
    }

    /**
     * @param string $entityClass
     * @param string $reason
     * @param array $data
     * @return static
     */
    public static function invalidEntityData($entityClass, $reason, array $data = [])
    {
        return new static(
            sprintf(
                'Invalid data provided for entity "%s": %s',
                $entityClass,
                $reason
            ),
            ExceptionCode::ENTITY_INVALID_DATA,
            [
                'entity_class' => $entityClass,
                'reason' => $reason,
                'data' => $data,
            ]
        );
    }
}
