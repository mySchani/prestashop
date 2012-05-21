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

class AdminAccessController extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'access';
		$this->className = 'Profile';
	 	$this->lang = false;
		$this->context = Context::getContext();

		parent::__construct();
	}

	/**
	 * AdminController::initForm() override
	 * @see AdminController::initForm()
	 */
	public function initForm()
	{
		$current_profile = (int)$this->getCurrentProfileId();
		$profiles = Profile::getProfiles($this->context->language->id);
		$accesses = array();
		foreach ($profiles as $profile)
			$accesses[$profile['id_profile']] = Profile::getProfileAccesses($profile['id_profile']);

		$modules = array();
		foreach ($profiles as $profile)
			$modules[$profile['id_profile']] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT ma.`id_module`, m.`name`, ma.`view`, ma.`configure`
				FROM '._DB_PREFIX_.'module_access ma
				LEFT JOIN '._DB_PREFIX_.'module m
					ON ma.id_module = m.id_module
				WHERE id_profile = '.(int)$profile['id_profile'].'
				ORDER BY m.name
			');

		$this->fields_form = array('');
		$this->tpl_form_vars = array(
			'profiles' => $profiles,
			'accesses' => $accesses,
			'tabs' => Tab::getTabs($this->context->language->id),
			'current_profile' => (int)$current_profile,
			'admin_profile' => (int)_PS_ADMIN_PROFILE_,
			'access_edit' => $this->tabAccess['edit'],
			'perms' => array('view', 'add', 'edit', 'delete'),
			'modules' => $modules,
			'link' => $this->context->link
		);

		return parent::initForm();
	}

	/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
		$this->display = 'edit';
		// toolbar (save, cancel, new, ..)
		$this->initToolbar();
		unset($this->toolbar_btn['save']);
		unset($this->toolbar_btn['cancel']);

		if (!$this->loadObject(true))
			return;

		$this->content .= $this->initForm();

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}
	
	public function ajaxProcessUpdateAccess()
	{
		if ($this->tabAccess['edit'] != '1')
			throw new PrestashopException(Tools::displayError('You do not have permission to edit here.'));
			
		if (Tools::isSubmit('submitAddAccess'))
		{
			$perm = Tools::getValue('perm');
			if (!in_array($perm, array('view', 'add', 'edit', 'delete', 'all')))
				throw new PrestashopException('permission not exists');

			$enabled = (int)Tools::getValue('enabled');
			$id_tab = (int)Tools::getValue('id_tab');
			$id_profile = (int)Tools::getValue('id_profile');
			$res = true;

			if ($id_tab == -1 && $perm == 'all' && $enabled == 0)
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'access`
					SET `view` = '.(int)$enabled.', `add` = '.(int)$enabled.', `edit` = '.(int)$enabled.', `delete` = '.(int)$enabled.'
					WHERE `id_profile` = '.(int)$id_profile.' AND `id_tab` != 31
				');
			else if ($id_tab == -1 && $perm == 'all')
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'access`
					SET `view` = '.(int)$enabled.', `add` = '.(int)$enabled.', `edit` = '.(int)$enabled.', `delete` = '.(int)$enabled.'
					WHERE `id_profile` = '.(int)$id_profile
				);
			else if ($id_tab == -1)
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'access`
					SET `'.bqSQL($perm).'` = '.(int)$enabled.'
					WHERE `id_profile` = '.(int)$id_profile
				);
			else if ($perm == 'all')
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'access`
					SET `view` = '.(int)$enabled.', `add` = '.(int)$enabled.', `edit` = '.(int)$enabled.', `delete` = '.(int)$enabled.'
					WHERE `id_tab` = '.(int)$id_tab.'
						AND `id_profile` = '.(int)$id_profile
				);
			else
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'access`
					SET `'.bqSQL($perm).'` = '.(int)$enabled.'
					WHERE `id_tab` = '.(int)$id_tab.'
						AND `id_profile` = '.(int)$id_profile
				);
			$res = $res?'ok':'error';
			die($res);
		}
	}
	
	public function ajaxProcessUpdateModuleAccess()
	{
		if ($this->tabAccess['edit'] != '1')
			throw new PrestashopException(Tools::displayError('You do not have permission to edit here.'));
			/* Update Access Modules */
	
		if (Tools::isSubmit('changeModuleAccess'))
		{
			$perm = Tools::getValue('perm');
			$enabled = (int)Tools::getValue('enabled');
			$id_module = (int)Tools::getValue('id_module');
			$id_profile = (int)Tools::getValue('id_profile');
			$res = true;

			if (!in_array($perm, array('view', 'configure')))
				throw new PrestashopException('permission not exists');

			if ($id_module == -1)
			{
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'module_access`
					SET `'.bqSQL($perm).'` = '.(int)$enabled.'
					WHERE `id_profile` = '.(int)$id_profile
				);
			}
			else
			{
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'module_access`
					SET `'.bqSQL($perm).'` = '.(int)$enabled.'
					WHERE `id_module` = '.(int)$id_module.'
						AND `id_profile` = '.(int)$id_profile
				);
			}

			$res = $res?'ok':'error';
			die($res);
		}
	}

	/**
	* Get the current profile id
	*
	* @return the $_GET['profile'] if valid, else 1 (the first profile id)
	*/
	public function getCurrentProfileId()
	{
	 	return (isset($_GET['id_profile']) && !empty($_GET['id_profile']) && is_numeric($_GET['id_profile'])) ? (int)$_GET['id_profile'] : 1;
	}
}