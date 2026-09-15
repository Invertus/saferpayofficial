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

use Dotenv\Dotenv;
use Invertus\IntegrationTestSuite\Service\CreateDatabaseAndInsertTables;
use Invertus\SaferPay\Install\Installer;

$rootDirectory = __DIR__ . '/../../../../';
$projectDir = __DIR__ . '/../../';

require_once $rootDirectory . 'config/config.inc.php';
require_once $projectDir . 'vendor/autoload.php';

require_once realpath("../../vendor/autoload.php");
require_once realpath('../../../../config/config.inc.php');
require_once realpath('../../../../app/AppKernel.php');
require_once realpath('../../saferpay.php');

$dotenv = Dotenv::create(realpath("../../tests"));
$dotenv->load();

$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$servername = getenv('DB_HOST');
$dbName = getenv('DB_NAME');

$databaseCreation = new CreateDatabaseAndInsertTables();

$databaseCreation->createDatabaseAndInsertTables($username, $password, $servername, $dbName);

$installer = new Installer(new SaferPay());
$installer->install();
