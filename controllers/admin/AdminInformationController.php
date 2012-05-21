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
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminInformationControllerCore extends AdminController
{
	public function initContent()
	{
		$this->display = 'view';
		parent::initContent();
	}

	public function renderView()
	{
		$this->tpl_view_vars = array(
			'version' => array(
				'mysql' => Db::getInstance()->getVersion(),
				'php' => phpversion(),
				'ps' => _PS_VERSION_,
				'server' => $_SERVER['SERVER_SOFTWARE'],
			),
			'uname' => function_exists('php_uname') ? php_uname('s').' '.php_uname('v').' '.php_uname('m') : '',
			'apache_instaweb' => Tools::apacheModExists('mod_instaweb'),
			'shop' => array(
				'url' => Tools::getHttpHost(true).__PS_BASE_URI__,
				'theme' => _THEME_NAME_,
			),
			'mail' => Configuration::get('PS_MAIL_METHOD') == 1,
			'smtp' => array(
				'server' => Configuration::get('PS_MAIL_SERVER'),
				'user' => Configuration::get('PS_MAIL_USER'),
				'password' => Configuration::get('PS_MAIL_PASSWD'),
				'encryption' => Configuration::get('PS_MAIL_SMTP_ENCRYPTION'),
				'port' => Configuration::get('PS_MAIL_SMTP_PORT'),
			),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
		);
		$this->tpl_view_vars = array_merge($this->getTestResult(), $this->tpl_view_vars);

		$this->toolbar_title = $this->l('Tools : Informations');
		unset($this->toolbar_btn['cancel']);
		return parent::renderView();
	}

	/**
	 * get all tests
	 *
	 * @return array of test results
	 */
	public function getTestResult()
	{
		// Functions list to test with 'test_system'
		// Test to execute (function/args) : lets uses the default test
		$tests = ConfigurationTest::getDefaultTests();
		$tests_op = ConfigurationTest::getDefaultTestsOp();

		$tests_errors = array(
			'phpversion' => $this->l('Update your PHP version'),
			'upload' => $this->l('Configure your server to allow the upload file'),
			'system' => $this->l('Configure your server to allow the creation of directories and write to files'),
			'gd' => $this->l('Enable the GD library on your server'),
			'mysql_support' => $this->l('Enable the MySQL support on your server'),
			'config_dir' => $this->l('Set write permissions for config folder'),
			'cache_dir' => $this->l('Set write permissions for cache folder'),
			'sitemap' => $this->l('Set write permissions for sitemap.xml file'),
			'img_dir' => $this->l('Set write permissions for img folder and subfolders/recursively'),
			'mails_dir' => $this->l('Set write permissions for mails folder and subfolders/recursively'),
			'module_dir' => $this->l('Set write permissions for modules folder and subfolders/recursively'),
			'theme_lang_dir' => $this->l('Set write permissions for themes/')._THEME_NAME_.$this->l('/lang/ folder and subfolders/recursively'),
			'translations_dir' => $this->l('Set write permissions for translations folder and subfolders/recursively'),
			'customizable_products_dir' => $this->l('Set write permissions for upload folder and subfolders/recursively'),
			'virtual_products_dir' => $this->l('Set write permissions for download folder and subfolders/recursively'),
			'fopen' => $this->l('Enable fopen on your server'),
			'register_globals' => $this->l('Set PHP register global option to off'),
			'gz' => $this->l('Enable GZIP compression on your server')
		);

		$params_required_results = ConfigurationTest::check($tests);
		$params_optional_results = ConfigurationTest::check($tests_op);

		return array(
			'failRequired' => in_array('fail', $params_required_results),
			'failOptional' => in_array('fail', $params_optional_results),
			'testsErrors' => $tests_errors,
			'testsRequired' => $params_required_results,
			'testsOptional' => $params_optional_results,
		);
	}
}

