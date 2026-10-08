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

use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Controller\AbstractSaferPayController;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;
use Invertus\SaferPay\Validation\CardAliasOwnershipValidation;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOfficialCreditCardsModuleFrontController extends AbstractSaferPayController
{
    const FILE_NAME = 'creditCards';

    public function display()
    {
        $this->redirectIfCardsPageIsNotAvailable();
        $this->initCardList();
        $this->setBreadcrumb();
        $this->setTemplate(SaferPayConfig::SAFERPAY_TEMPLATE_LOCATION . '/front/credit_cards.tpl');

        parent::display();

        return true;
    }

    private function initCardList()
    {
        /** @var SaferPayCardAliasRepository $cardAliasRep */
        $cardAliasRep = $this->module->getService(SaferPayCardAliasRepository::class);

        $savedCustomerCards = $cardAliasRep->getSavedCardsByCustomerId($this->context->customer->id);

        $rows = [];

        foreach ($savedCustomerCards as $savedCard) {
            $dateTill = date(
                'Y-m-d',
                strtotime($savedCard['date_add'] . ' + ' . $savedCard['lifetime'] . ' days')
            );

            $this->context->smarty->assign([
                'saved_card_id' => $savedCard['id_saferpay_card_alias'],
                'date_ends' => $dateTill,
                'card_number' => $savedCard['card_number'],
                'payment_method' => $savedCard['payment_method'],
                'date_add' => $savedCard['date_add'],
                'card_img' => "{$this->module->getPathUri()}views/img/{$savedCard['payment_method']}.png",
            ]);

            $rows[] = $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/front/credit_card.tpl'
            );
        }

        $this->context->smarty->assign([
            'rows' => $rows,
            'remove_card_url' => $this->context->link->getModuleLink($this->module->name, self::FILE_NAME),
            'remove_card_token' => Tools::getToken(false),
        ]);
    }

    private function redirectIfCardsPageIsNotAvailable()
    {
        $isCreditCardSaveEnabled = Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);
        if (!$this->context->customer->isLogged() || !$isCreditCardSaveEnabled) {
            $back_url = $this->context->link->getModuleLink('saferpay', 'my-account');

            Tools::redirect(
                $this->context->link->getPageLink('authentication', null, null, ['back' => $back_url])
            );
        }
    }

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $this->redirectIfCardsPageIsNotAvailable();

        $selectedCard = (int) Tools::getValue('saved_card_id');

        if ($selectedCard) {
            $cardAlias = new SaferPayCardAlias($selectedCard);

            if (!$this->canRemoveCard($cardAlias)) {
                $logger->warning(sprintf('%s - Card removal rejected', self::FILE_NAME), [
                    'context' => [
                        'id_saferpay_card_alias' => $selectedCard,
                        'id_customer' => (int) $this->context->customer->id,
                    ],
                ]);

                $this->errors[] = $this->module->l('Failed to removed credit card', self::FILE_NAME);

                return;
            }

            if ($cardAlias->delete()) {
                $this->success[] = $this->module->l('Successfully removed credit card', self::FILE_NAME);
                return;
            }
            $this->errors[] = $this->module->l('Failed to removed credit card', self::FILE_NAME);
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        parent::postProcess();
    }

    /**
     * @param SaferPayCardAlias $cardAlias
     *
     * @return bool
     */
    private function canRemoveCard(SaferPayCardAlias $cardAlias)
    {
        $isPostRequest = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';

        if (!Tools::isSubmit('submitRemoveCard') || !$isPostRequest) {
            return false;
        }

        if (!hash_equals(Tools::getToken(false), (string) Tools::getValue('token'))) {
            return false;
        }

        /** @var CardAliasOwnershipValidation $cardAliasOwnershipValidation */
        $cardAliasOwnershipValidation = $this->module->getService(CardAliasOwnershipValidation::class);

        return $cardAliasOwnershipValidation->validate($cardAlias, $this->context->customer->id);
    }

    private function setBreadcrumb()
    {
        $breadcrumb = $this->getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->module->l('Your account', self::FILE_NAME),
            'url' => $this->context->link->getPageLink('my-account'),
        ];

        $breadcrumb['count'] = count($breadcrumb['links']);

        $this->context->smarty->assign('breadcrumb', $breadcrumb);
    }
}
