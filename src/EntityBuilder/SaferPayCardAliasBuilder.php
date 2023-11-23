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

namespace Invertus\SaferPay\EntityBuilder;

use Invertus\SaferPay\DTO\Response\Assert\AssertBody;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use SaferPayCardAlias;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayCardAliasBuilder
{

    /**
     * @var SaferPayCardAliasRepository
     */
    private $aliasRepository;

    public function __construct(SaferPayCardAliasRepository $aliasRepository)
    {
        $this->aliasRepository = $aliasRepository;
    }

    /**
     * @param AssertBody $assertBody
     * @param $saferPayOrderId
     * @param $customerId
     * @return SaferPayCardAlias
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createCardAlias(AssertBody $assertBody, $customerId)
    {
        if ($assertBody->getRegistrationResult()) {
            $aliasId = $assertBody->getRegistrationResult()->getAliasId();
            $isCardAliasSaved = $this->aliasRepository->getSavedCardIdByCustomerIdAndAliasId($customerId, $aliasId);
            if (!$isCardAliasSaved && $assertBody->getLiability()->getThreeDs()->getAuthenticated()) {
                $cardAlias = new SaferPayCardAlias();
                $cardAlias->alias_id = $aliasId;
                $cardAlias->success = $assertBody->getRegistrationResult()->isSuccess();
                $cardAlias->lifetime = $assertBody->getRegistrationResult()->getLifetime();
                $cardAlias->id_customer = $customerId;
                $cardAlias->card_number = $assertBody->getPaymentMeans()->getDisplayText();
                $cardAlias->payment_method = $assertBody->getPaymentMeans()->getBrand()->getPaymentMethod();
                $dateTill = date(
                    'Y-m-d h:i:s',
                    strtotime(date('Y-m-d h:i:s') . ' + ' . $cardAlias->lifetime . ' days')
                );
                $cardAlias->valid_till = $dateTill;
                $cardAlias->add();

                return $cardAlias;
            }

            return new SaferPayCardAlias($isCardAliasSaved);
        }

        return null;
    }
}
