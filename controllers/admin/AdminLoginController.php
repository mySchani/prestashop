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
*  @version  Release: $Revision: 8971 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminLoginController extends AdminController
{
	public function __construct()
	{
	 	$this->errors = array();
	 	$this->context = Context::getContext();
	 	$this->display_header = false;
	 	$this->display_footer = false;
		parent::__construct();
	}
	
	
	public function setMedia()
	{
		$this->addJquery();
		$this->addJqueryPlugin('flip');
		$this->addCSS(_PS_CSS_DIR_.'login.css');
		$this->addJS(_PS_JS_DIR_.'login.js');
		$this->addJqueryUI('ui.widget');
		$this->addJqueryUI('effects.shake');
		$this->addJqueryUI('effects.slide');
	}
	
	public function initContent()
	{
		if ((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) == 'off') AND Configuration::get('PS_SSL_ENABLED'))
		{
			// You can uncomment theses lines if you want to force https even from localhost and automatically redirect
			// header('HTTP/1.1 301 Moved Permanently');
			// header('Location: '.Tools::getShopDomainSsl(true).$_SERVER['REQUEST_URI']);
			// exit();
			$clientIsMaintenanceOrLocal = in_array(Tools::getRemoteAddr(), array_merge(array('127.0.0.1'),explode(',', Configuration::get('PS_MAINTENANCE_IP'))));
			// If ssl is enabled, https protocol is required. Exception for maintenance and local (127.0.0.1) IP
			if ($clientIsMaintenanceOrLocal)
				$this->errors = Tools::displayError('SSL is activated. However, your IP is allowed to use unsecure mode (Maintenance or local IP).');
			else
			{
				$warningSslMessage = Tools::displayError('SSL is activated. Please connect using the following url to log in in secure mode (https).');
				$warningSslMessage .= '<a href="https://'.Tools::getServerName().Tools::safeOutput($_SERVER['REQUEST_URI']).'">https://'.Tools::getServerName().Tools::safeOutput($_SERVER['REQUEST_URI']).'</a>';
				$this->context->smarty->assign(array('warningSslMessage' => $warningSslMessage));
			}
		}
		
		
		
		if(file_exists(_PS_ADMIN_DIR_.'/../install') OR file_exists(_PS_ADMIN_DIR_.'/../admin'))
			$this->context->smarty->assign(
				array(
				'randomNb' => rand(100, 999),
				'wrong_folder_name'	=> true)
				);
		
		if ($nbErrors = sizeof($this->errors))
			$this->context->smarty->assign(
				array(
					'errors' => $this->errors,
					'nbErrors' => $nbErrors,
					'shop_name' => Tools::safeOutput(Configuration::get('PS_SHOP_NAME'))
					)
				);
		$this->setMedia();
		$this->initHeader();
		parent::initContent();
		$this->initFooter();
	}
	
	public function checkToken()
	{
		return true;
	}
	
	public function postProcess()
	{
		if (Tools::isSubmit('submitLogin'))
			$this->processLogin();
		elseif (Tools::isSubmit('submitForgot'))
			$this->processForgot();
	}
	
	public function processLogin()
	{
		/* Check fields validity */
		$passwd = trim(Tools::getValue('passwd'));
		$email = trim(Tools::getValue('email'));
		if (empty($email))
			$this->errors[] = Tools::displayError('E-mail is empty');
		elseif (!Validate::isEmail($email))
			$this->errors[] = Tools::displayError('Invalid e-mail address');
		
		
		if (empty($passwd))
			$this->errors[] = Tools::displayError('Password is blank');
		else if (!Validate::isPasswd($passwd))
			$this->errors[] = Tools::displayError('Invalid password');
			
		if (!sizeof($this->errors))
		{
		 	/* Seeking for employee */
			$employee = new Employee();
			if (!$employee->getByemail($email, $passwd))
			{
				$this->errors[] = Tools::displayError('Employee does not exist or password is incorrect.');
				$employee->logout();
			}
			else
			{
				$employee->remote_addr = ip2long(Tools::getRemoteAddr());
			 	/* Creating cookie */
				$cookie = Context::getContext()->cookie;
				$cookie->id_employee = $employee->id;
				$cookie->email = $employee->email;
				$cookie->profile = $employee->id_profile;
				$cookie->passwd = $employee->passwd;
				$cookie->remote_addr = $employee->remote_addr;
				$cookie->write();
								
				/* Redirect to admin panel */
				if (isset($_GET['redirect']))
					$url = strval($_GET['redirect'].(isset($_GET['token']) ? ('&token='.$_GET['token']) : ''));
				else
					$url = 'index.php';
				if (!Validate::isCleanHtml($url))
					die(Tools::displayError());
				
				if (Tools::isSubmit('ajax'))
					die(Tools::jsonEncode(array('hasErrors' => false, 'redirect' => $this->context->link->getAdminLink('AdminHome'))));
				else
					$this->redirect_after = $this->context->link->getAdminLink('AdminHome');
			}
		}
		if (Tools::isSubmit('ajax'))
			die(Tools::jsonEncode(array('hasErrors' => true, 'errors' => $this->errors)));
	}
	
	public function processForgot()
	{
		$email = trim(Tools::getValue('email_forgot'));
		if (empty($email))
			$this->errors[] = Tools::displayError('E-mail is empty');
		elseif (!Validate::isEmail($email))
			$this->errors[] = Tools::displayError('Invalid e-mail address');
		else
		{
			$employee = new Employee();
			if (!$employee->getByemail($email) OR !$employee)
				$this->errors[] = Tools::displayError('This account does not exist');
			else if ((strtotime($employee->last_passwd_gen.'+'.Configuration::get('PS_PASSWD_TIME_BACK').' minutes') - time()) > 0 )
					$this->errors[] = Tools::displayError('You can regenerate your password only every').' '.Configuration::get('PS_PASSWD_TIME_BACK').' '.Tools::displayError('minute(s)');
		}
		if (_PS_MODE_DEMO_)
			$errors[] = Tools::displayError('This functionnality has been disabled.');

		if(!sizeof($this->errors))
		{	
			$pwd = Tools::passwdGen();
			$employee->passwd = md5(pSQL(_COOKIE_KEY_.$pwd));
			$employee->last_passwd_gen = date('Y-m-d H:i:s', time());
			$result = $employee->update();
			if (!$result)
				$this->errors[] = Tools::displayError('An error occurred during your password change.');
			else
			{
				$params = array(
							'{email}' => $employee->email, 
							'{lastname}' => $employee->lastname, 
							'{firstname}' => $employee->firstname, 
							'{passwd}' => $pwd
							);
							
				if (Mail::Send((int)Configuration::get('PS_LANG_DEFAULT'), 'password', Mail::l('Your new admin password'), $params, $employee->email, $employee->firstname.' '.$employee->lastname))
					die(Tools::jsonEncode(array('hasErrors' => false, 'confirm' => $this->l('Your password has been e-mailed to you'))));
				else
					die(Tools::jsonEncode(array('hasErrors' => true, 'errors' => array(Tools::displayError('An error occurred during your password change.')))));
			}
		
		}
		else if (Tools::isSubmit('ajax'))
			die(Tools::jsonEncode(array('hasErrors' => true, 'errors' => $this->errors)));
	}
}