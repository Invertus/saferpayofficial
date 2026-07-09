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

namespace Invertus\SaferPay\Builder;

use Invertus\SaferPay\Factory\ModuleFactory;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderConfirmationMessageTemplate
{
    /**
     * @var SaferPayOfficial
     */
    private $module;

    /**
     * Order Message Template Class Variable Declaration.
     *
     * @var string $orderMessageTemplateClass
     */
    private $orderMessageTemplateClass;

    /**
     * Order Message text Variable Declaration.
     *
     * @var string $orderMessageText
     */
    private $orderMessageText;

    /**
     * Smarty Variable Declaration.
     *
     * @var \Smarty
     */
    private $smarty;

    /**
     * TagScriptTemplate constructor.
     *
     * @param ModuleFactory $module
     */
    public function __construct(ModuleFactory $module)
    {
        $this->module = $module->getModule();
    }

    /**
     * Sets Smarty From Given Param.
     *
     * @param \Smarty $smarty
     * @return void
     */
    public function setSmarty(\Smarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * Sets Order Message Template Class.
     *
     * @param string $orderMessageTemplateClass
     * @return void
     */
    public function setOrderMessageTemplateClass(string $orderMessageTemplateClass): void
    {
        $this->orderMessageTemplateClass = $orderMessageTemplateClass;
    }

    /**
     * Sets Order Message Text.
     *
     * @param string $orderMessageText
     * @return void
     */
    public function setOrderMessageText(string $orderMessageText): void
    {
        $this->orderMessageText = $orderMessageText;
    }

    /**
     * Gets Smarty Params.
     *
     * @return array
     */
    public function getSmartyParams(): array
    {
        return [
            'orderMessageText' => $this->orderMessageText,
            'orderMessageClass' => $this->orderMessageTemplateClass,
        ];
    }

    /**
     * Gets Order Confirmation Message Template.
     *
     * @return string
     *
     * @throws \SmartyException
     */
    public function getHtml(): string
    {
        $this->smarty->assign($this->getSmartyParams());
        return $this->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/front/payment_return.tpl'
        );
    }
}
