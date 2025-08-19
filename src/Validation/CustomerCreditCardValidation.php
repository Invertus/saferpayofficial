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

namespace Invertus\SaferPay\Validation;

use Invertus\SaferPay\Exception\Restriction\UnauthenticatedCardUserException;
use Invertus\SaferPay\Exception\SaferPayException;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayCardAliasRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerCreditCardValidation
{
    /**
     * @var SaferPayCardAliasRepository
     */
    private $saferPayCardAliasRepository;
    /**
     * @var mixed
     */
    private $logger;

    const FILE_NAME = 'CustomerCreditCardValidation';

    public function __construct(SaferPayCardAliasRepository $saferPayCardAliasRepository, LoggerInterface $logger)
    {
        $this->saferPayCardAliasRepository = $saferPayCardAliasRepository;
        $this->logger = $logger;
    }

    /**
     * @return true
     *
     * @throws UnauthenticatedCardUserException
     * @throws SaferPayException
     */
    public function validate($idSavedCard, $idCustomer)
    {
        if ($idSavedCard <= 0 || empty($idSavedCard)) {
            return true;
        }

        if (!is_numeric($idCustomer) || !is_numeric($idSavedCard)) {
            $this->logger->error(sprintf('%s - Invalid data or bad types', self::FILE_NAME), [
                'context' => [],
                'id_saved_card' => $idSavedCard,
                'id_customer' => $idCustomer,
            ]);

            throw SaferPayException::unknownError();
        }

        $cardOwnerId = $this->saferPayCardAliasRepository->getCustomerIdByReferenceId(pSQL($idSavedCard), pSQL($idCustomer));

        if (empty($cardOwnerId)) {
            throw UnauthenticatedCardUserException::unauthenticatedCard($cardOwnerId);
        }

        return true;
    }
}
