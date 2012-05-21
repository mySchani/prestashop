<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 10056 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Step 3 : configure database and email connection
 */
class InstallControllerHttpDatabase extends InstallControllerHttp
{
	/**
	 * @var InstallModelDatabase
	 */
	public $model_database;

	/**
	 * @var InstallModelMail
	 */
	public $model_mail;

	public function init()
	{
		require_once _PS_INSTALL_MODELS_PATH_.'database.php';
		$this->model_database = new InstallModelDatabase();

		require_once _PS_INSTALL_MODELS_PATH_.'mail.php';
		$this->model_mail = new InstallModelMail();
	}

	/**
	 * @see InstallAbstractModel::processNextStep()
	 */
	public function processNextStep()
	{
		$this->session->install_type = Tools::getValue('db_mode');

		// Save database config
		$this->session->database_server = trim(Tools::getValue('dbServer'));
		$this->session->database_name = trim(Tools::getValue('dbName'));
		$this->session->database_login = trim(Tools::getValue('dbLogin'));
		$this->session->database_password = trim(Tools::getValue('dbPassword'));
		$this->session->database_prefix = trim(Tools::getValue('db_prefix'));
		$this->session->database_engine = Tools::getValue('dbEngine');
		$this->session->database_clear = Tools::getValue('database_clear');

		// Save email config
		$this->session->use_smtp = (bool)Tools::getValue('smtpChecked');
		$this->session->smtp_server = trim(Tools::getValue('smtpSrv'));
		$this->session->smtp_encryption = Tools::getValue('smtpEnc');
		$this->session->smtp_port = (int)Tools::getValue('smtpPort');
		$this->session->smtp_login = trim(Tools::getValue('smtpLogin'));
		$this->session->smtp_password = trim(Tools::getValue('smtpPassword'));
	}

	/**
	 * Database configuration must be valid to validate this step
	 *
	 * @see InstallAbstractModel::validate()
	 */
	public function validate()
	{
		$this->errors = $this->model_database->testDatabaseSettings(
			$this->session->database_server,
			$this->session->database_name,
			$this->session->database_login,
			$this->session->database_password,
			$this->session->database_prefix,
			$this->session->database_engine
		);

		return count($this->errors) ? false : true;
	}

	public function process()
	{
		if (Tools::getValue('checkDb'))
			$this->processCheckDb();
		else if (Tools::getValue('sendMail'))
			$this->processSendMail();
	}

	/**
	 * Check if a connection to database is possible with these data
	 */
	public function processCheckDb()
	{
		$server = Tools::getValue('dbServer');
		$database = Tools::getValue('dbName');
		$login = Tools::getValue('dbLogin');
		$password = Tools::getValue('dbPassword');
		$prefix = Tools::getValue('db_prefix');
		$engine = Tools::getValue('dbEngine');

		$errors = $this->model_database->testDatabaseSettings($server, $database, $login, $password, $prefix, $engine);

		$this->ajaxJsonAnswer(
			(count($errors)) ? false : true,
			(count($errors)) ? implode('<br />', $errors) : $this->l('Database is connected')
		);
	}

	/**
	 * Send a test email
	 */
	public function processSendMail()
	{
		$smtp_checked = (Tools::getValue('smtpChecked') == 'true');
		$server = Tools::getValue('smtpSrv');
		$encryption = Tools::getValue('smtpEnc');
		$port = Tools::getValue('smtpPort');
		$login = Tools::getValue('smtpLogin');
		$password = Tools::getValue('smtpPassword');
		$email = Tools::getValue('testEmail');

		$result = $this->model_mail->sendTestMail($smtp_checked, $server, $login, $password, $port, $encryption, $email);

		$this->ajaxJsonAnswer(
			(bool)$result,
			($result) ? $this->l('A test e-mail has been sent to %s', $email) : $this->l('An error occurred while sending email, please verify your parameters')
		);
	}

	/**
	 * @see InstallAbstractModel::display()
	 */
	public function display()
	{
		if (!$this->session->install_type)
		{
			if (file_exists(_PS_ROOT_DIR_.'/config/settings.inc.php'))
			{
				include_once _PS_ROOT_DIR_.'/config/settings.inc.php';
				$this->database_server = _DB_SERVER_;
				$this->database_name = _DB_NAME_;
				$this->database_login = _DB_USER_;
				$this->database_password = _DB_PASSWD_;
				$this->database_engine = _MYSQL_ENGINE_;
				$this->database_prefix = _DB_PREFIX_;
			}
			else
			{
				$this->database_server = 'localhost';
				$this->database_name = 'prestashop';
				$this->database_login = 'root';
				$this->database_password = '';
				$this->database_engine = 'InnoDB';
				$this->database_prefix = 'ps_';
			}

			$this->database_clear = true;
			$this->install_type = 'full';
			$this->use_smtp = false;
			$this->smtp_server = 'smtp.';
			$this->smtp_encryption = 'off';
			$this->smtp_port = 25;
			$this->smtp_login = '';
			$this->smtp_password = '';
		}
		else
		{
			$this->database_server = $this->session->database_server;
			$this->database_name = $this->session->database_name;
			$this->database_login = $this->session->database_login;
			$this->database_password = $this->session->database_password;
			$this->database_engine = $this->session->database_engine;
			$this->database_prefix = $this->session->database_prefix;
			$this->database_clear = $this->session->database_clear;

			$this->install_type = $this->session->install_type;
			$this->use_smtp = $this->session->use_smtp;
			$this->smtp_server = $this->session->smtp_server;
			$this->smtp_encryption = $this->session->smtp_encryption;
			$this->smtp_port = $this->session->smtp_port;
			$this->smtp_login = $this->session->smtp_login;
			$this->smtp_password = $this->session->smtp_password;
		}

		$this->displayTemplate('database');
	}
}