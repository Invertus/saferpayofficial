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

use Exception;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Factory\ModuleFactory;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayExceptionService
{
    const SHORT_CLASS_NAME = 'SaferPayExceptionService';

    /**
     * @var SaferPayOfficial
     */
    private $module;

    public function __construct(ModuleFactory $module)
    {
        $this->module = $module->getModule();
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        //todo: test translations
        return [
            SaferPayApiException::class =>
                [
                    SaferPayApiException::CAPTURE =>
                        $this->module->l('Failed to capture', self::SHORT_CLASS_NAME),
                    SaferPayApiException::CANCEL =>
                        $this->module->l('Failed to Cancel', self::SHORT_CLASS_NAME),
                    SaferPayApiException::REFUND =>
                        $this->module->l('Failed to Refund', self::SHORT_CLASS_NAME),
                    SaferPayApiException::AUTHORIZE =>
                        $this->module->l('Failed to Authorize', self::SHORT_CLASS_NAME),
                    SaferPayApiException::INITIALIZE =>
                        $this->module->l('Failed to Initialize', self::SHORT_CLASS_NAME),
                    SaferPayApiException::ASSERT =>
                        $this->module->l('Failed to Assert', self::SHORT_CLASS_NAME),
                ],
        ];
    }

    /**
     * @param Exception $exception
     * @param array $messages
     * @return string
     */
    public function getErrorMessageForException(Exception $exception, array $messages): string
    {
        $exceptionType = get_class($exception);
        $exceptionCode = $exception->getCode();

        if (isset($messages[$exceptionType])) {
            $message = $messages[$exceptionType];

            if (is_string($message)) {
                return $message;
            }

            if (is_array($message) && isset($message[$exceptionCode])) {
                return $message[$exceptionCode];
            }
        }

        return $this->module->l('Unknown exception in SaferPay');
    }
}
