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

class InstallControllerHttpProcess extends InstallControllerHttp
{
	const SETTINGS_FILE = 'config/settings.inc.php';

	public function init()
	{
		require_once _PS_INSTALL_MODELS_PATH_.'install.php';
		$this->model_install = new InstallModelInstall();
	}

	/**
	 * @see InstallAbstractModel::processNextStep()
	 */
	public function processNextStep()
	{
	}

	/**
	 * @see InstallAbstractModel::validate()
	 */
	public function validate()
	{
		return false;
	}

	public function process()
	{
		if (file_exists(_PS_ROOT_DIR_.'/'.self::SETTINGS_FILE))
			require_once _PS_ROOT_DIR_.'/'.self::SETTINGS_FILE;

		if (Tools::getValue('installDatabase'))
			$this->processInstallDatabase();
		else if (Tools::getValue('populateDatabase'))
			$this->processPopulateDatabase();
		else if (Tools::getValue('configureShop'))
			$this->processConfigureShop();
		else if (Tools::getValue('installModules'))
			$this->processInstallModules();
		else if (Tools::getValue('installFixtures'))
			$this->processInstallFixtures();
		else if (Tools::getValue('preactivation'))
			$this->processPreactivation();
	}

	/**
	 * PROCESS : installDatabase
	 * Generate settings file and create database structure
	 */
	public function processInstallDatabase()
	{
		$success = $this->model_install->installDatabase(
			$this->session->database_server,
			$this->session->database_login,
			$this->session->database_password,
			$this->session->database_name,
			$this->session->database_prefix,
			$this->session->database_engine,
			$this->session->database_clear
		);

		if (!$success || $this->model_install->getErrors())
			$this->ajaxJsonAnswer(false, $this->model_install->getErrors());
		$this->ajaxJsonAnswer(true);
	}

	/**
	 * PROCESS : populateDatabase
	 * Populate database with default data
	 */
	public function processPopulateDatabase()
	{
		$this->initializeContext();

		if (!$this->model_install->populateDatabase(true) || $this->model_install->getErrors())
			$this->ajaxJsonAnswer(false, $this->model_install->getErrors());
		$this->session->xml_loader_ids = $this->model_install->xml_loader_ids;
		$this->ajaxJsonAnswer(true);
	}

	/**
	 * PROCESS : configureShop
	 * Set default shop configuration
	 */
	public function processConfigureShop()
	{
		$this->initializeContext();

		$success = $this->model_install->configureShop(array(
			'shop_name' =>		$this->session->shop_name,
			'shop_activity' =>	$this->session->shop_activity,
			'shop_country' =>	$this->session->shop_country,
			'shop_timezone' =>	$this->session->shop_timezone,
			'use_smtp' =>		$this->session->use_smtp,
			'smtp_server' =>	$this->session->smtp_server,
			'smtp_login' =>		$this->session->smtp_login,
			'smtp_password' =>	$this->session->smtp_password,
			'smtp_encryption' =>$this->session->smtp_encryption,
			'smtp_port' =>		$this->session->smtp_port,
			'admin_firstname' =>$this->session->admin_firstname,
			'admin_lastname' =>	$this->session->admin_lastname,
			'admin_password' =>	$this->session->admin_password,
			'admin_email' =>	$this->session->admin_email,
		));

		if (!$success || $this->model_install->getErrors())
			$this->ajaxJsonAnswer(false, $this->model_install->getErrors());
		$this->ajaxJsonAnswer(true);
	}

	public function initializeContext()
	{
		global $smarty;

		Context::getContext()->shop = new Shop(1);
		Configuration::loadConfiguration();
		Context::getContext()->language = new Language(Configuration::get('PS_LANG_DEFAULT'));

		require_once _PS_ROOT_DIR_.'/config/smarty.config.inc.php';
		Context::getContext()->smarty = $smarty;
	}

	/**
	 * PROCESS : installModules
	 * Install all modules in ~/modules/ directory
	 */
	public function processInstallModules()
	{
		$this->initializeContext();

		// Remove all modules from module table, just in case
		Db::getInstance()->delete(_DB_PREFIX_.'module');

		if (!$this->model_install->installModules() || $this->model_install->getErrors())
			$this->ajaxJsonAnswer(false, $this->model_install->getErrors());
		$this->ajaxJsonAnswer(true);
	}

	/**
	 * PROCESS : installFixtures
	 * Install fixtures (E.g. demo products)
	 */
	public function processInstallFixtures()
	{
		$this->initializeContext();

		$this->model_install->xml_loader_ids = $this->session->xml_loader_ids;
		if (!$this->model_install->installFixtures() || $this->model_install->getErrors())
			$this->ajaxJsonAnswer(false, $this->model_install->getErrors());
		$this->ajaxJsonAnswer(true);
	}

	/**
	 * PROCESS : preactivation
	 * (currently not used)
	 */
	public function processPreactivation()
	{
		foreach ($this->session->partners as $partner => $data)
		{
			/*$stream_context = @stream_context_create(array('http' => array('method'=> 'GET', 'timeout' => 5)));
			$url = 'http://www.prestashop.com/partner/preactivation/actions.php?version=1.0&partner='.addslashes($_GET['partner']);

			// Protect fields
			foreach ($_GET as $key => $value)
				$_GET[$key] = strip_tags(str_replace(array('\'', '"'), '', trim($value)));

			// Encore Get, Send It and Get Answers
			@require_once('../config/settings.inc.php');
			foreach ($_GET as $key => $val)
				$url .= '&'.$key.'='.urlencode($val);
			$url .= '&security='.md5($_GET['email']._COOKIE_IV_);*/
		}

		$this->ajaxJsonAnswer(true);
	}

	/**
	 * @see InstallAbstractModel::display()
	 */
	public function display()
	{
		$this->process_steps = array(
			'installDatabase',
			'populateDatabase',
			'configureShop',
			'installModules',
			'installFixtures',
			//'preactivation',
		);
		$this->displayTemplate('process');
	}
}
