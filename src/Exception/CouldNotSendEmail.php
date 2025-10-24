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
 * Exception thrown when email sending operations fail
 */
class CouldNotSendEmail extends SaferPayException
{
    /**
     * @param string $emailType - Type of email (e.g., 'order_conf', 'new_order')
     * @param string $recipient - Email recipient address
     * @param array $context - Additional context data
     * @param \Exception|null $previous - Previous exception if any
     * @return static
     */
    public static function failedToSend($emailType, $recipient, array $context = [], \Exception $previous = null)
    {
        return new static(
            sprintf(
                'Failed to send email of type "%s" to recipient "%s"',
                $emailType,
                $recipient
            ),
            ExceptionCode::EMAIL_FAILED_TO_SEND,
            array_merge(
                [
                    'email_type' => $emailType,
                    'recipient' => $recipient,
                    'previous_error' => $previous ? $previous->getMessage() : null,
                ],
                $context
            )
        );
    }

    /**
     * @param string $templateName - Email template name
     * @param int $langId - Language ID
     * @return static
     */
    public static function templateNotFound($templateName, $langId)
    {
        return new static(
            sprintf(
                'Email template "%s" not found for language ID %d',
                $templateName,
                $langId
            ),
            ExceptionCode::EMAIL_TEMPLATE_NOT_FOUND,
            [
                'template_name' => $templateName,
                'lang_id' => $langId,
            ]
        );
    }

    /**
     * @param string $email - Invalid email address
     * @param string $reason - Reason why email is invalid
     * @return static
     */
    public static function invalidRecipient($email, $reason = 'Invalid email format')
    {
        return new static(
            sprintf('Invalid email recipient: %s. Reason: %s', $email, $reason),
            ExceptionCode::EMAIL_INVALID_RECIPIENT,
            [
                'email' => $email,
                'reason' => $reason,
            ]
        );
    }
}
