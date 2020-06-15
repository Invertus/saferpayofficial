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
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;

class SaferPayOfficialCreditCards16ModuleFrontController extends AbstractSaferPayController
{
    const FILENAME = 'creditCards16';

    public $display_column_left = false;
    public $display_column_right = false;


    public function display()
    {
        $this->module_instance = Module::getInstanceByName($this->module->name);

        $isCreditCardSaveEnabled = Configuration::get(SaferPayConfig::CREDIT_CARD_SAVE);
        if (!$this->context->customer->logged || !$isCreditCardSaveEnabled) {
            $back_url = $this->context->link->getModuleLink('saferpay', 'my-account');

            Tools::redirect(
                $this->context->link->getPageLink(
                    'authentication',
                    null,
                    null,
                    ['back' => $back_url]
                )
            );
        }
        $this->initCardList();
        $this->setTemplate('credit_cards_16.tpl');
        parent::display();
    }

    private function initCardList()
    {
        $customerId = $this->context->customer->id;
        /** @var SaferPayCardAliasRepository $cardAliasRep */
        $cardAliasRep = $this->module->getContainer()->get(SaferPayCardAliasRepository::class);
        $savedCustomerCards = $cardAliasRep->getSavedCardsByCustomerId($customerId);
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
                'controller' => self::FILENAME,
            ]);
            $rows[] = $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/front/credit_card.tpl'
            );
        }
        $this->context->smarty->assign([
            'rows' => $rows,
        ]);
    }

    public function postProcess()
    {
        $selectedCard = Tools::getValue('saved_card_id');
        if ($selectedCard) {
            $cardAlias = new SaferPayCardAlias($selectedCard);
            if ($cardAlias->delete()) {
                $this->success[] = $this->l('Successfully removed credit card');
                return;
            }
            $this->errors[] = $this->l('Failed to removed credit card');
        }
        parent::postProcess();
    }

    private function setBreadcrumb()
    {
        $breadcrumb = $this->getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->l('Your account', self::FILENAME),
            'url' => $this->context->link->getPageLink('my-account'),
        ];

        $breadcrumb['count'] = count($breadcrumb['links']);

        $this->context->smarty->assign('breadcrumb', $breadcrumb);
    }
}
