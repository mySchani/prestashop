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
*  @version  Release: $Revision: 7307 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminPaymentControllerCore extends AdminController
{
	public $payment_modules = array();

	public function __construct()
	{
		$shop_id = Context::getContext()->shop->getID(true);

		/* Get all modules then select only payment ones */
		$modules = Module::getModulesOnDisk();

		foreach ($modules as $module)
			if ($module->tab == 'payments_gateways')
			{
				if ($module->id)
				{
					if (!get_class($module) == 'SimpleXMLElement')
						$module->country = array();
					$countries = DB::getInstance()->executeS('
						SELECT id_country
						FROM '._DB_PREFIX_.'module_country
						WHERE id_module = '.(int)$module->id.' AND `id_shop`='.(int)$shop_id
					);
					foreach ($countries as $country)
						$module->country[] = $country['id_country'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->currency = array();
					$currencies = DB::getInstance()->executeS('
						SELECT id_currency
						FROM '._DB_PREFIX_.'module_currency
						WHERE id_module = '.(int)$module->id.' AND `id_shop`='.(int)$shop_id
					);
					foreach ($currencies as $currency)
						$module->currency[] = $currency['id_currency'];

					if (!get_class($module) == 'SimpleXMLElement')
						$module->group = array();
					$groups = DB::getInstance()->executeS('
						SELECT id_group
						FROM '._DB_PREFIX_.'module_group
						WHERE id_module = '.(int)$module->id.' AND `id_shop`='.(int)$shop_id
					);
					foreach ($groups as $group)
						$module->group[] = $group['id_group'];
				}
				else
				{
					$module->country = null;
					$module->currency = null;
					$module->group = null;
				}

				$this->payment_modules[] = $module;
			}

		parent::__construct();
	}

	public function postProcess()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			if (Tools::isSubmit('submitModulecountry'))
				$this->saveRestrictions('country');
			else if (Tools::isSubmit('submitModulecurrency'))
				$this->saveRestrictions('currency');
			else if (Tools::isSubmit('submitModulegroup'))
				$this->saveRestrictions('group');
		}
	}

	private function saveRestrictions($type)
	{
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'module_'.bqSQL($type).'` WHERE id_shop = '.Context::getContext()->shop->getID(true));
		foreach ($this->payment_modules as $module)
			if ($module->active && isset($_POST[$module->name.'_'.$type.'']))
				foreach ($_POST[$module->name.'_'.$type.''] as $selected)
					$values[] = '('.(int)$module->id.', '.Context::getContext()->shop->getID(true).', '.(int)$selected.')';

		if (count($values))
			Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'module_'.$type.' (`id_module`, `id_shop`, `id_'.$type.'`) VALUES '.implode(',', $values));
		Tools::redirectAdmin(self::$currentIndex.'&conf=4'.'&token='.$this->token);
	}

	public function initContent()
	{
		$this->display = 'view';
		return parent::initContent();
	}

	public function initView()
	{	
		// link to modules page
		if (isset($this->payment_modules[0]))
			$token_modules = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$this->context->employee->id);

		$display_restrictions = false;
		foreach ($this->payment_modules as $module)
			if ($module->active)
			{
				$display_restrictions = true;
				break;
			}

		$lists = array('currencies' =>
					array('items' => Currency::getCurrencies(),
						  'title' => $this->l('Currencies restrictions'),
						  'desc' => $this->l('Please mark the checkbox(es) for the currency or currencies for which you want the payment module(s) to be available.'),
						  'name_id' => 'currency',
						  'identifier' => 'id_currency',
						  'icon' => 'dollar',
					),
					array('items' => Group::getGroups($this->context->language->id),
						  'title' => $this->l('Groups restrictions'),
						  'desc' => $this->l('Please mark the checkbox(es) for the groups for which you want the payment module(s) available.'),
						  'name_id' => 'group',
						  'identifier' => 'id_group',
						  'icon' => 'group',
					),
					array('items' =>Country::getCountries($this->context->language->id),
						  'title' => $this->l('Countries restrictions'),
						  'desc' => $this->l('Please mark the checkbox(es) for the country or countries for which you want the payment module(s) to be available.'),
						  'name_id' => 'country',
						  'identifier' => 'id_country',
						  'icon' => 'world',
					)
				);

		foreach ($lists as $key_list => $list)
		{
			$list['check_list'] = array();
			foreach ($list['items'] as $key_item => $item)
			{
				$name_id = $list['name_id'];
				foreach ($this->payment_modules as $key_module => $module)
				{
					if (isset($module->$name_id) && in_array($item['id_'.$name_id], $module->$name_id))
						$list['items'][$key_item]['check_list'][$key_module] = 'checked';
					else
						$list['items'][$key_item]['check_list'][$key_module] = 'unchecked';

					// If is a country list and the country is limited, remove it from list
					if ($name_id == 'country'
						&& isset($module->limited_countries)
						&& !empty($module->limited_countries)
						&& !(in_array(strtoupper($item['iso_code']), array_map('strtoupper', $module->limited_countries))))
						$list['check_list'][$key_module] = null;
				}
			}
			// update list
			$lists[$key_list] = $list;
		}

		$shop_context = (!Shop::isFeatureActive() || $this->context->shop->getContextType() == Shop::CONTEXT_SHOP);
		$this->tpl_view_vars = array(
			'url_modules' => isset($token_modules) ? 'index.php?tab=AdminModules&token='.$token_modules.'&&filterCategory=payments_gateways' : null,
			'display_restrictions' => $display_restrictions,
			'lists' => $lists,
			'ps_base_uri' => __PS_BASE_URI__,
			'payment_modules' => $this->payment_modules,
			'url_submit' => self::$currentIndex.'&token='.$this->token,
			'shop_context' => $shop_context
		);

		$this->toolbar_title = $this->l('Paiement');
		unset($this->toolbar_btn['cancel']);
		return parent::initView();
	}
}

