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

use Invertus\SaferPay\Factory\ModuleFactory;
use SaferPayOfficial;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SettingsTranslationService
{
    const FILE_NAME = 'SettingsTranslationService';

    /** @var SaferPayOfficial */
    private $module;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->module = $moduleFactory->getModule();
    }

    /**
     * @return array<string, string>
     */
    public function getAll()
    {
        return array_merge(
            $this->getAppTranslations(),
            $this->getTabTranslations(),
            $this->getCommonTranslations(),
            $this->getApiCredentialsTranslations(),
            $this->getPaymentMethodsTranslations(),
            $this->getPaymentProcessingTranslations(),
            $this->getEmailTranslations(),
            $this->getGeneralSettingsTranslations(),
            $this->getToastTranslations()
        );
    }

    private function getAppTranslations()
    {
        return [
            'saferpaySettings' => $this->module->l('Saferpay Settings', self::FILE_NAME),
            'configureIntegration' => $this->module->l('Configure your Saferpay payment integration for your Prestashop store.', self::FILE_NAME),
            'errorLoadingSettings' => $this->module->l('Something went wrong loading Saferpay settings. Please refresh the page.', self::FILE_NAME),
            'failedToLoadSettings' => $this->module->l('Failed to load settings data.', self::FILE_NAME),
        ];
    }

    private function getTabTranslations()
    {
        return [
            'tabApiCredentials' => $this->module->l('API Credentials', self::FILE_NAME),
            'tabPaymentMethods' => $this->module->l('Payment Methods', self::FILE_NAME),
            'tabPaymentProcessing' => $this->module->l('Payment Processing', self::FILE_NAME),
            'tabEmailNotifications' => $this->module->l('Email Notifications', self::FILE_NAME),
            'tabGeneralSettings' => $this->module->l('General Settings', self::FILE_NAME),
        ];
    }

    private function getCommonTranslations()
    {
        return [
            'saveChanges' => $this->module->l('Save Changes', self::FILE_NAME),
            'saving' => $this->module->l('Saving...', self::FILE_NAME),
            'enable' => $this->module->l('Enable', self::FILE_NAME),
            'disable' => $this->module->l('Disable', self::FILE_NAME),
            'search' => $this->module->l('Search...', self::FILE_NAME),
            'noResultsFound' => $this->module->l('No results found.', self::FILE_NAME),
            'clearAll' => $this->module->l('Clear all', self::FILE_NAME),
            'selected' => $this->module->l('selected', self::FILE_NAME),
        ];
    }

    private function getApiCredentialsTranslations()
    {
        return [
            'environment' => $this->module->l('Environment', self::FILE_NAME),
            'envDescription' => $this->module->l('Select your active environment. Credentials are stored separately for each.', self::FILE_NAME),
            'selectEnvironment' => $this->module->l('Select environment', self::FILE_NAME),
            'testEnvironment' => $this->module->l('Test Environment', self::FILE_NAME),
            'liveEnvironment' => $this->module->l('Live Environment', self::FILE_NAME),
            'testModeWarning' => $this->module->l('You are currently in test mode. No real transactions will be processed.', self::FILE_NAME),
            'liveModeWarning' => $this->module->l('You are in live mode. Real transactions will be processed.', self::FILE_NAME),
            'test' => $this->module->l('Test', self::FILE_NAME),
            'live' => $this->module->l('Live', self::FILE_NAME),
            'apiCredentials' => $this->module->l('API Credentials', self::FILE_NAME),
            'enterSaferpayCredentials' => $this->module->l('Enter your Saferpay %s environment API credentials.', self::FILE_NAME),
            'credentialsHint' => html_entity_decode($this->module->l('You can generate your API credentials inside the [backoffice_link] under Settings > JSON API Basic authentication. [more_info_link]', self::FILE_NAME), ENT_QUOTES, 'UTF-8'),
            'credentialsBackofficeLinkText' => $this->module->l('Saferpay Backoffice', self::FILE_NAME),
            'credentialsMoreInfoLinkText' => $this->module->l('More information', self::FILE_NAME),
            'jsonApiUsername' => $this->module->l('JSON API Username', self::FILE_NAME),
            'enterApiUsername' => $this->module->l('Enter %s API username', self::FILE_NAME),
            'jsonApiPassword' => $this->module->l('JSON API Password', self::FILE_NAME),
            'enterApiPassword' => $this->module->l('Enter %s API password', self::FILE_NAME),
            'hidePassword' => $this->module->l('Hide password', self::FILE_NAME),
            'showPassword' => $this->module->l('Show password', self::FILE_NAME),
            'changePassword' => $this->module->l('Change password', self::FILE_NAME),
            'passwordSavedHint' => $this->module->l('Password saved. Click the pencil icon to enter a new one.', self::FILE_NAME),
            'terminalId' => $this->module->l('Terminal ID', self::FILE_NAME),
            'selectTerminal' => $this->module->l('Select a terminal', self::FILE_NAME),
            'refreshTerminals' => $this->module->l('Refresh terminals', self::FILE_NAME),
            'fetchTerminalsFromApi' => $this->module->l('Fetch terminals from API', self::FILE_NAME),
            'merchantEmails' => $this->module->l('Merchant Emails', self::FILE_NAME),
            'enterMerchantEmails' => $this->module->l('Enter merchant email addresses (comma-separated)', self::FILE_NAME),
            'separateEmails' => $this->module->l('These email addresses receive payment notification emails directly from SaferPay. Separate multiple email addresses with commas.', self::FILE_NAME),
            'invalidMerchantEmails' => $this->module->l('Invalid email address', self::FILE_NAME),
            'saferpayFields' => $this->module->l('Saferpay Fields', self::FILE_NAME),
            'saferpayFieldsDescription' => $this->module->l('Configure Saferpay Fields for inline payment form integration.', self::FILE_NAME),
            'fieldAccessTokenInfo' => $this->module->l('Saferpay Field Access Token can be found in Saferpay Backoffice, navigate to', self::FILE_NAME),
            'fieldAccessTokenPath' => $this->module->l('Settings', self::FILE_NAME) . ' > ' . $this->module->l('Saferpay Fields Access Tokens', self::FILE_NAME),
            'fieldAccessToken' => $this->module->l('Field Access Token', self::FILE_NAME),
            'enterFieldAccessToken' => $this->module->l('Enter or generate token', self::FILE_NAME),
            'generate' => $this->module->l('Generate', self::FILE_NAME),
            'enterCredentialsToGenerateToken' => $this->module->l('Enter your API credentials first to generate a token.', self::FILE_NAME),
            'moreInformation' => $this->module->l('More information', self::FILE_NAME),
            'fieldJsUrl' => $this->module->l('Field Javascript Library URL', self::FILE_NAME),
            'findLibraryUrlHere' => $this->module->l('Find the library URL here', self::FILE_NAME),
            'enterCredentialsFirst' => $this->module->l('Enter credentials first', self::FILE_NAME),
            'enterCredentialsToLoadTerminals' => $this->module->l('Enter your API username and password first to load available terminals.', self::FILE_NAME),
            'validatingCredentials' => $this->module->l('Validating credentials...', self::FILE_NAME),
            'credentialsValid' => $this->module->l('Credentials verified successfully.', self::FILE_NAME),
            'invalidCredentials' => $this->module->l('Invalid credentials. Please check your username and password.', self::FILE_NAME),
            'saferpayFieldsIncluded' => $this->module->l('Saferpay Fields is included in your license', self::FILE_NAME),
            'saferpayFieldsIncludedDescription' => $this->module->l('You can use hosted payment fields for a seamless checkout experience.', self::FILE_NAME),
            'tokenGeneratedSuccessfully' => $this->module->l('Access token generated successfully.', self::FILE_NAME),
            'failedToGenerateToken' => $this->module->l('Failed to generate access token.', self::FILE_NAME),
        ];
    }

    private function getPaymentMethodsTranslations()
    {
        return [
            'paymentMethods' => $this->module->l('Payment Methods', self::FILE_NAME),
            'paymentMethodsDescription' => $this->module->l('Enable and configure available payment methods for your checkout.', self::FILE_NAME),
            'active' => $this->module->l('active', self::FILE_NAME),
            'paymentMethod' => $this->module->l('Payment method', self::FILE_NAME),
            'enabled' => $this->module->l('Enabled', self::FILE_NAME),
            'logos' => $this->module->l('Logos', self::FILE_NAME),
            'customForm' => $this->module->l('Saferpay Fields', self::FILE_NAME),
            'countries' => $this->module->l('Countries', self::FILE_NAME),
            'currencies' => $this->module->l('Currencies', self::FILE_NAME),
            'selectCountries' => $this->module->l('Select countries', self::FILE_NAME),
            'selectCurrencies' => $this->module->l('Select currencies', self::FILE_NAME),
            'select' => $this->module->l('Select', self::FILE_NAME),
            'noPaymentMethods' => $this->module->l('No payment methods available. Please configure your API credentials first.', self::FILE_NAME),
        ];
    }

    private function getPaymentProcessingTranslations()
    {
        return [
            'transactionHandling' => $this->module->l('Transaction Handling', self::FILE_NAME),
            'transactionHandlingDescription' => $this->module->l('Configure how payments are processed, authorized, and captured.', self::FILE_NAME),
            'defaultPaymentBehavior' => $this->module->l('Default payment behavior', self::FILE_NAME),
            'paymentBehaviorDescription' => $this->module->l('How payment provider should behave when order is created.', self::FILE_NAME),
            'capture' => $this->module->l('Capture', self::FILE_NAME),
            'chargeImmediately' => $this->module->l('Charge immediately', self::FILE_NAME),
            'authorize' => $this->module->l('Authorize', self::FILE_NAME),
            'reserveAndCaptureLater' => $this->module->l('Reserve and capture later', self::FILE_NAME),
            'behaviourWhen3dsFails' => $this->module->l('Behavior when liability shift through 3D Secure has not been granted', self::FILE_NAME),
            'behaviourWhen3dsDescription' => $this->module->l('Default payment behavior for payment without 3-D Secure.', self::FILE_NAME),
            'cancel' => $this->module->l('Cancel', self::FILE_NAME),
            'rejectPayment' => $this->module->l('Reject the payment', self::FILE_NAME),
            'continueWithout3ds' => $this->module->l('Cancel or Capture manually', self::FILE_NAME),
            'captureWithout3ds' => $this->module->l('Charge immediately', self::FILE_NAME),
            'restrictRefundAmount' => $this->module->l('Restrict RefundAmount to Captured Amount', self::FILE_NAME),
            'restrictRefundDescription' => $this->module->l('If set to true, the refund will be rejected if the sum of authorized refunds exceeds the capture value.', self::FILE_NAME),
            'orderCreationRule' => $this->module->l('Order creation rule', self::FILE_NAME),
            'orderCreationDescription' => $this->module->l('Select the option to determine whether the order should be created.', self::FILE_NAME),
            'afterAuthorization' => $this->module->l('After authorization', self::FILE_NAME),
            'createWhenAuthorized' => $this->module->l('Create when authorized', self::FILE_NAME),
            'beforeAuthorization' => $this->module->l('Before authorization', self::FILE_NAME),
            'createBeforePayment' => $this->module->l('Create before payment', self::FILE_NAME),
            'cardDisplaySaving' => html_entity_decode($this->module->l('Card Display & Saving', self::FILE_NAME), ENT_QUOTES, 'UTF-8'),
            'cardDisplay' => $this->module->l('Card Display', self::FILE_NAME),
            'cardDisplayDescription' => $this->module->l('Configure how cards appear and are grouped at checkout.', self::FILE_NAME),
            'cardSavingForCustomers' => $this->module->l('Card Saving for Customers', self::FILE_NAME),
            'groupCardsLabel' => $this->module->l('Group debit/credit cards as \'Cards\' in checkout', self::FILE_NAME),
            'groupCardsDescription' => $this->module->l('If enabled, all supported card brands will be grouped and shown as a single \'Cards\' payment method at checkout.', self::FILE_NAME),
            'showCardsLogo' => $this->module->l('Show \'Cards\' payment method logo', self::FILE_NAME),
            'showCardsLogoDescription' => $this->module->l('If enabled, a logo for the grouped \'Cards\' payment method will be displayed at checkout.', self::FILE_NAME),
            'creditCardSaving' => $this->module->l('Credit card saving for customers', self::FILE_NAME),
            'creditCardSavingDescription' => $this->module->l('Allow customers to save credit card for faster purchase.', self::FILE_NAME),
        ];
    }

    private function getEmailTranslations()
    {
        return [
            'emailSending' => $this->module->l('Email Sending', self::FILE_NAME),
            'emailSendingDescription' => $this->module->l('Configure which emails are sent during the payment process. Merchant notifications sent by Saferpay use the Merchant Email(s) field on the API Credentials tab.', self::FILE_NAME),
            'saferpayCustomerMail' => $this->module->l('Send an email from Saferpay on payment completion', self::FILE_NAME),
            'saferpayCustomerMailDescription' => $this->module->l('Saferpay sends a payment confirmation email directly to the customer.', self::FILE_NAME),
            'newOrderMail' => $this->module->l('Send new order mail on authorization', self::FILE_NAME),
            'newOrderMailDescription' => $this->module->l('Notify the shop owner when an order is authorized (requires the Mail Alert module).', self::FILE_NAME),
            'orderConfMail' => $this->module->l('Send order confirmation mail on payment completion', self::FILE_NAME),
            'orderConfMailDescription' => $this->module->l('Send the shop\'s order confirmation email to the customer, only once payment is authorized by Saferpay.', self::FILE_NAME),
            'emailConfInfo' => $this->module->l('When this feature is enabled, a confirmation email will be only sent once the payment is authorized by Saferpay.', self::FILE_NAME),
            'emailConfMailAlert' => $this->module->l('For this feature to be functioning you need to have the Mail Alert module configured.', self::FILE_NAME),
        ];
    }

    private function getGeneralSettingsTranslations()
    {
        return [
            'orderState' => $this->module->l('Order State', self::FILE_NAME),
            'orderStateDescription' => $this->module->l('Define the default order status for Saferpay payments.', self::FILE_NAME),
            'statusAwaitingPayment' => $this->module->l('Status for Saferpay payment awaiting', self::FILE_NAME),
            'selectOrderStatus' => $this->module->l('Select order status', self::FILE_NAME),
            'defaultStatusDescription' => $this->module->l('Default status on SaferPay order creation.', self::FILE_NAME),
            'styling' => $this->module->l('Styling', self::FILE_NAME),
            'stylingDescription' => $this->module->l('Customize the appearance of the payment page.', self::FILE_NAME),
            'configName' => $this->module->l('Payment Page configurations name', self::FILE_NAME),
            'enterConfigName' => $this->module->l('Enter configuration name', self::FILE_NAME),
            'configNameDescription' => html_entity_decode($this->module->l('Name of the Payment Page Configuration created in Saferpay Backoffice (Settings > Payment Page Configuration). Max 20 characters. Allowed: letters, numbers, dots, colons, hyphens, underscores.', self::FILE_NAME), ENT_QUOTES, 'UTF-8'),
            'configuration' => $this->module->l('Configuration', self::FILE_NAME),
            'configurationDescription' => $this->module->l('General module configuration settings.', self::FILE_NAME),
            'description' => $this->module->l('Description', self::FILE_NAME),
            'enterDescription' => $this->module->l('Enter description', self::FILE_NAME),
            'descriptionHelp' => $this->module->l('This description is visible in payment page also in payment confirmation email.', self::FILE_NAME),
            'orderReferenceOnPaymentPage' => $this->module->l('Order reference on payment page', self::FILE_NAME),
            'usePrestaShopOrderReference' => $this->module->l('Use PrestaShop Order reference (default)', self::FILE_NAME),
            'useDescriptionFieldValue' => $this->module->l('Use Description field value', self::FILE_NAME),
            'orderReferenceFallbackInfo' => html_entity_decode($this->module->l('When "Use PrestaShop Order reference" is selected and the order is not yet created (e.g. order creation after authorization), the Description field value is used as fallback.', self::FILE_NAME), ENT_QUOTES, 'UTF-8'),
            'debugMode' => $this->module->l('Debug mode', self::FILE_NAME),
            'debugModeDescription' => $this->module->l('Enable debug mode to see more information in logs.', self::FILE_NAME),
        ];
    }

    private function getToastTranslations()
    {
        return [
            'failedToFetchTerminals' => $this->module->l('Failed to fetch terminals', self::FILE_NAME),
            'errorFetchingTerminals' => $this->module->l('Error fetching terminals', self::FILE_NAME),
            'savedSuccessfully' => $this->module->l('%s saved successfully', self::FILE_NAME),
            'failedToSave' => $this->module->l('Failed to save %s', self::FILE_NAME),
            'errorSaving' => $this->module->l('Error saving %s: %s', self::FILE_NAME),
            'errorRefreshingPaymentMethods' => $this->module->l('Error refreshing payment methods: %s', self::FILE_NAME),
            'paymentMethodsUnreachable' => $this->module->l('Could not reach your Saferpay account. Please check the error logs for more details', self::FILE_NAME),
        ];
    }
}
