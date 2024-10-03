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

namespace Invertus\SaferPay\EntityManager;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ObjectModelEntityManager implements EntityManagerInterface
{
    private $unitOfWork;

    public function __construct(ObjectModelUnitOfWork $unitOfWork)
    {
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * @param \ObjectModel $model
     * @param string $unitOfWorkType
     * @param string|null $specificKey
     *                                 for example external_id key to make it easier to keep
     *                                 track of which object model is related to which external_id
     */
    public function persist(
        \ObjectModel $model,
        $unitOfWorkType,
        $specificKey = null
    ) {
        $this->unitOfWork->setWork($model, $unitOfWorkType, $specificKey);

        return $this;
    }

    /**
     * @return array<\ObjectModel>
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function flush()
    {
        $persistenceModels = $this->unitOfWork->getWork();
        $persistedModels = [];

        foreach ($persistenceModels as $externalId => $persistenceModel) {
            if ($persistenceModel['unit_of_work_type'] === ObjectModelUnitOfWork::UNIT_OF_WORK_SAVE) {
                $persistenceModel['object']->save();
            }

            if ($persistenceModel['unit_of_work_type'] === ObjectModelUnitOfWork::UNIT_OF_WORK_DELETE) {
                $persistenceModel['object']->delete();
            }

            if (!empty($externalId)) {
                $persistedModels[$externalId] = $persistenceModel['object'];
            } else {
                $persistedModels[] = $persistenceModel['object'];
            }
        }
        $this->unitOfWork->clearWork();

        return $persistedModels;
    }
}