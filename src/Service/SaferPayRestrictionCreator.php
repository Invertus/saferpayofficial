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

namespace Invertus\SaferPay\Service;

use Invertus\SaferPay\Exception\Restriction\RestrictionException;
use Invertus\SaferPay\Exception\Restriction\WrongRestrictionTypeException;
use Invertus\SaferPay\Repository\SaferPayRestrictionRepository;
use SaferPayCountry;
use SaferPayCurrency;

class SaferPayRestrictionCreator
{
    const COUNTRY_RESTRICTION = 1;
    const CURRENCY_RESTRICTION = 2;

    const COUNTRY_SUFFIX = '_countries';
    const CURRENCY_SUFFIX = '_currencies';
    /**
     * @var SaferPayRestrictionRepository
     */
    private $payRestrictionRepository;

    public function __construct(SaferPayRestrictionRepository $payRestrictionRepository)
    {
        $this->payRestrictionRepository = $payRestrictionRepository;
    }

    public function updateRestriction($paymentMethod, $restrictionType, $restrictions)
    {
        $success = true;
        $restrictionIds = $this->payRestrictionRepository->getRestrictionIdsByName($paymentMethod, $restrictionType);
        foreach ($restrictionIds as $restrictionId) {
            try {
                $restriction = $this->getRestriction($restrictionType, $restrictionId['id']);
            } catch (RestrictionException $e) {
                throw $e;
            }
            if (!$restriction->delete()) {
                $success = false;
                break;
            }
        }
        if (!$restrictions) {
            return true;
        }

        foreach ($restrictions as $restriction) {
            $success &= $this->addRestriction($restrictionType, $paymentMethod, $restriction);
        }

        return $success;
    }

    private function getRestriction($restrictionType, $id = null)
    {
        switch ($restrictionType) {
            case SaferPayRestrictionCreator::COUNTRY_RESTRICTION:
                $restriction = new SaferPayCountry($id);
                break;
            case SaferPayRestrictionCreator::CURRENCY_RESTRICTION:
                $restriction = new SaferPayCurrency($id);
                break;
            default:
                throw new WrongRestrictionTypeException('Wrong restriction type');
        }

        return $restriction;
    }

    private function addRestriction($restrictionType, $paymentMethod, $restriction)
    {
        $success = true;
        switch ($restrictionType) {
            case self::COUNTRY_RESTRICTION:
                $saferPayCountry = new SaferPayCountry();
                $saferPayCountry->payment_name = $paymentMethod;
                if ((int) $restriction === 0) {
                    $saferPayCountry->all_countries = 1;
                    if (!$saferPayCountry->add()) {
                        $success = false;
                    }
                    break;
                }
                $saferPayCountry->id_country = (int) $restriction;
                if (!$saferPayCountry->add()) {
                    $success = false;
                    break;
                }
                break;
            case self::CURRENCY_RESTRICTION:
                $saferPayCurrency = new SaferPayCurrency();
                $saferPayCurrency->payment_name = $paymentMethod;
                if ((int) $restriction === 0) {
                    $saferPayCurrency->all_currencies = 1;
                    if (!$saferPayCurrency->add()) {
                        $success = false;
                    }
                    break;
                }
                $saferPayCurrency->id_currency = (int) $restriction;
                if (!$saferPayCurrency->add()) {
                    $success = false;
                    break;
                }
                break;
            default:
                return false;
        }

        return $success;
    }
}
