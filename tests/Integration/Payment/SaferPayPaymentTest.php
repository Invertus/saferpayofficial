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

use Invertus\SaferPay\Repository\SaferPayPaymentRepository;
use Invertus\SaferPay\Service\PaymentRestrictionValidation;
use Invertus\SaferPay\Service\SaferPayLogoCreator;
use Invertus\SaferPay\Service\SaferPayPaymentCreator;
use Invertus\SaferPay\Service\SaferPayRestrictionCreator;
use PHPUnit\Framework\TestCase;

class SaferPayPaymentTest extends TestCase
{
    private $module;

    public function setUp(): void
    {
        parent::setUp();
        $this->module = new SaferPay();
    }

    /**
     * @dataProvider provider
     *
     * @param $paymentMethod
     * @param $isActive
     * @param $countries
     * @param $currencies
     * @param $isLogoActive
     * @param $country
     * @param $currency
     * @param $isValid
     */
    public function testUpdatePayment(
        $paymentMethod,
        $isActive,
        $countries,
        $currencies,
        $isLogoActive,
        $country,
        $currency,
        $isValid
    ) {
        /** @var SaferPayPaymentCreator $paymentCreation */
        $paymentCreation = $this->module->getModuleContainer()->get(SaferPayPaymentCreator::class);
        /** @var SaferPayRestrictionCreator $restrictionCreator */
        $restrictionCreator = $this->module->getModuleContainer()->get(SaferPayRestrictionCreator::class);
        /** @var PaymentRestrictionValidation $paymentValidation */
        $paymentValidation = $this->module->getModuleContainer()->get(PaymentRestrictionValidation::class);
        /** @var SaferPayPaymentRepository $paymentRepository */
        $paymentRepository = $this->module->getModuleContainer()->get(SaferPayPaymentRepository::class);

        $success = 1;
        $success &= $paymentCreation->updatePayment($paymentMethod, $isActive);

        $success &= $restrictionCreator->updateRestriction(
            $paymentMethod,
            SaferPayRestrictionCreator::RESTRICTION_COUNTRY,
            $countries
        );

        $success &= $restrictionCreator->updateRestriction(
            $paymentMethod,
            SaferPayRestrictionCreator::RESTRICTION_CURRENCY,
            $currencies
        );

        /** @var SaferPayLogoCreator $logoCreation */
        $logoCreation = $this->module->getModuleContainer()->get(SaferPayLogoCreator::class);
        $success &= $logoCreation->updateLogo($paymentMethod, $isLogoActive);
        $this->assertEquals($success, 1);

        $success = $paymentRepository->isLogoEnabledByName($paymentMethod);
        $this->assertEquals($success, $isLogoActive);

        $success = $paymentValidation->isPaymentMethodValid($paymentMethod);

        $this->assertEquals($success, $isValid);
    }

    public function provider()
    {
        return [
            [
                'saved_card_method' => 'Visa',
                'isActive' => 1,
                'countries' => [0],
                'currencies' => [0],
                'isLogoActive' => 1,
                'country' => 1,
                'currency' => 1,
                'isValid' => 1,
            ],
            [
                'saved_card_method' => 'PayPal',
                'isActive' => 1,
                'countries' => [2, 3],
                'currencies' => [0],
                'isLogoActive' => 1,
                'country' => 1,
                'currency' => 1,
                'isValid' => 0,
            ],
            [
                'saved_card_method' => 'IDEAL',
                'isActive' => 1,
                'countries' => [1],
                'currencies' => [1, 2],
                'isLogoActive' => 1,
                'country' => 1,
                'currency' => 1,
                'isValid' => 1,
            ],
            [
                'saved_card_method' => 'IDEAL',
                'isActive' => 0,
                'countries' => [0],
                'currencies' => [0],
                'isLogoActive' => 1,
                'country' => 1,
                'currency' => 1,
                'isValid' => 0,
            ],
        ];
    }
}
