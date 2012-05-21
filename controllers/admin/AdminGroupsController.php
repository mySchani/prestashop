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
*  @version  Release: $Revision: 7332 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminGroupsController extends AdminController
{
	public function __construct()
	{
		$this->table = 'group';
		$this->className = 'Group';
		$this->lang = true;
		$this->addRowAction('edit');
		$this->addRowAction('view');
		$this->addRowAction('delete');

		$this->_select = '
		(SELECT COUNT(jcg.`id_customer`)
		FROM `'._DB_PREFIX_.'customer_group` jcg
		LEFT JOIN `'._DB_PREFIX_.'customer` jc ON (jc.`id_customer` = jcg.`id_customer`)
		WHERE jc.`deleted` != 1
		AND jcg.`id_group` = a.`id_group`) AS nb';

		$groups_to_keep = array(
			Configuration::get('PS_UNIDENTIFIED_GROUP'),
			Configuration::get('PS_GUEST_GROUP'),
			Configuration::get('PS_CUSTOMER_GROUP')
		);

		$this->fieldsDisplay = array(
			'id_group' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 80, 'filter_key' => 'b!name'),
			'reduction' => array('title' => $this->l('Discount'), 'width' => 50, 'align' => 'right'),
			'nb' => array('title' => $this->l('Members'), 'width' => 25, 'align' => 'center'),
			'date_add' => array('title' => $this->l('Creation date'), 'width' => 60, 'type' => 'date', 'align' => 'right'));

		$this->addRowActionSkipList('delete', $groups_to_keep);

		parent::__construct();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryPlugin('fancybox');
		$this->addJqueryUi('ui.sortable');
	}

	public function initView()
	{
		$this->context = Context::getContext();
		if (!($group = $this->loadObject(true)))
			return;

		$this->tpl_view_vars = array(
			'group' => $group,
			'customerList' => $this->initCustomersList($group),
			'categorieReductions' => $this->formatCategoryDiscountList($group->id)
		);

		return parent::initView();
	}

	protected function initCustomersList($group)
	{
		$genders = array(0 => $this->l('?'));
		foreach (Gender::getGenders() as $gender)
		{
			$genders_icon[$gender->id] = '../genders/'.(int)$gender->id.'.jpg';
			$genders[$gender->id] = $gender->name;
		}
		$customer_fields_display = (array(
				'id_customer' => array('title' => $this->l('ID'), 'align' => 'center'),
				'id_gender' => array('title' => $this->l('Gender'), 'align' => 'center', 'icon' => $genders_icon, 'list' => $genders),
				'firstname' => array('title' => $this->l('Name'), 'align' => 'center'),
				'lastname' => array('title' => $this->l('Name'), 'align' => 'center'),
				'email' => array('title' => $this->l('E-mail address'), 'align' => 'center'),
				'birthday' => array('title' => $this->l('Birth date'), 'align' => 'center', 'type' => 'date'),
				'date_add' => array('title' => $this->l('Register date'), 'align' => 'center', 'type' => 'date'),
				'orders' => array('title' => $this->l('Orders'), 'align' => 'center'),
				'active' => array('title' => $this->l('Enabled'),'align' => 'center','active' => 'status','type' => 'bool')
			));

		$customer_list = $group->getCustomers(false);

		$helper = new HelperList();
		$helper->currentIndex = self::$currentIndex;
		$helper->token = $this->token;
		$helper->shopLinkType = '';
		$helper->identifier = 'id_customer';
		$helper->actions = array('edit', 'view');
		$helper->show_toolbar = false;

		return $helper->generateList($customer_list, $customer_fields_display);
	}

	public function initForm()
	{
		if (!($group = $this->loadObject(true)))
			return;

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Customer group'),
				'image' => '../img/admin/tab-groups.gif'
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 33,
					'required' => true,
					'lang' => true,
					'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Discount:'),
					'name' => 'reduction',
					'size' => 33,
					'desc' => $this->l('Will automatically apply this value as a discount on ALL shop\'s products for this group\'s members.')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Price display method:'),
					'name' => 'price_display_method',
					'desc' => $this->l('How the prices are displayed on order summary for this customer group (tax included or excluded).'),
					'options' => array(
						'query' => array(
							array(
								'id_method' => PS_TAX_EXC,
								'name' => $this->l('Tax excluded'),
								array(
									'id_method' => PS_TAX_INC,
									'name' => $this->l('Tax included')
								)
							)
						),
						'id' => 'id_method',
						'name' => 'name'
					)
				),
				array(
					'type' => 'group_discount_category',
					'label' => $this->l('Category discount:'),
					'name' => 'reduction',
					'size' => 33,
					'values' => ($group->id ? $this->formatCategoryDiscountList((int)$group->id) : array())
				),
				array(
					'type' => 'modules',
					'label' => array('auth_modules' => $this->l('Authorized modules :'), 'unauth_modules' => $this->l('Unauthorized modules :')),
					'name' => 'auth_modules',
					'values' => $this->formatModuleListAuth($group->id)
				)
			)
		);

		$trads = array(
			'Home' => $this->l('Home'),
			'selected' => $this->l('selected'),
			'Collapse All' => $this->l('Collapse All'),
			'Expand All' => $this->l('Expand All'),
			'Check All' => $this->l('Check All'),
			'Uncheck All'  => $this->l('Uncheck All'),
			'search' => $this->l('Search a category')
		);
		$this->tpl_form_vars['categoryTreeView'] = Helper::renderAdminCategorieTree($trads, array(), 'id_category', true);

		return parent::initForm();

	}

	protected function formatCategoryDiscountList($id)
	{
		$categorie = GroupReduction::getGroupReductions((int)$id, $this->context->language->id);
		$categorie_reductions = array();
		$category_reduction = Tools::getValue('category_reduction');

		foreach ($categorie as $category)
		{
			if (is_array($category_reduction) && array_key_exists($category['id_category'], $category_reduction))
				$category['reduction'] = $category_reduction[$category['id_category']];

			$tmp = array();
			$tmp['path'] = getPath(self::$currentIndex.'?tab=AdminCatalog', (int)$category['id_category']);
			$tmp['reduction'] = (float)$category['reduction'] * 100;
			$tmp['id_category'] = (int)$category['id_category'];
			$categorie_reductions[(int)$category['id_category']] = $tmp;
		}

		if (is_array($category_reduction))
			foreach ($category_reduction as $key => $val)
			{
				if (!array_key_exists($key, $categorie_reductions))
				{
					$tmp = array();
					$tmp['path'] = getPath(self::$currentIndex.'?tab=AdminCatalog', (int)$key);
					$tmp['reduction'] = (float)$val * 100;
					$tmp['id_category'] = (int)$key;
					$categorie_reductions[(int)$category['id_category']] = $tmp;
				}
			}

		return $categorie_reductions;
	}

	public function formatModuleListAuth($id_group)
	{
		$modules = Module::getModulesInstalled();
		$authorized_modules = '';

		$auth_modules = array();
		$unauth_modules = array();

		if ($id_group)
			$authorized_modules = Module::getAuthorizedModules($id_group);

		if (is_array($authorized_modules))
		{
			foreach ($modules as $module)
			{
				$authorized = false;
				foreach ($authorized_modules as $auth_module)
					if ($module['id_module'] == $auth_module['id_module'])
						$authorized = true;

				if ($authorized)
					$auth_modules[] = $module;
				else
					$unauth_modules[] = $module;
			}
		}
		else
			$auth_modules = $modules;
		$auth_modules_tmp = array();
		foreach ($auth_modules as $key => $val)
			$auth_modules_tmp[] = Module::getInstanceById($val['id_module']);

		$auth_modules = $auth_modules_tmp;

		$unauth_modules_tmp = array();
		foreach ($unauth_modules as $key => $val)
			$unauth_modules_tmp[] = Module::getInstanceById($val['id_module']);

		$unauth_modules = $unauth_modules_tmp;

		return array('unauth_modules' => $unauth_modules, 'auth_modules' => $auth_modules);
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAddgroup'))
		{
			if ($this->tabAccess['add'] === '1')
			{
				if (!$this->validateDiscount(Tools::getValue('reduction')))
					$this->_errors[] = Tools::displayError('Discount value is incorrect (must be a percentage)');
				else
				{
					$this->updateCategoryReduction();
					$this->updateRestrictions();
					parent::postProcess();
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add here.');
		}
		else
			parent::postProcess();
	}

	protected function validateDiscount($reduction)
	{
		if (!Validate::isPrice($reduction) || $reduction > 100 || $reduction < 0)
			return false;
		else
			return true;
	}

	public function ajaxProcessAddCategoryReduction()
	{
		$category_reduction = Tools::getValue('category_reduction');
		$id_category = Tools::getValue('id_category'); //no cast validation is done with Validate::isUnsignedId($id_category)

		$result = array();
		if (!Validate::isUnsignedId($id_category))
		{
			$result['errors'][] = Tools::displayError('Wrong category ID');
			$result['hasError'] = true;
		}
		else if (!$this->validateDiscount($category_reduction))
		{
			$result['errors'][] = Tools::displayError('Discount value is incorrect (must be a percentage)');
			$result['hasError'] = true;
		}
		else
		{
			$result['id_category'] = (int)$id_category;
			$result['catPath'] = getPath(self::$currentIndex.'?tab=AdminCatalog', (int)$id_category);
			$result['discount'] = $category_reduction;
			$result['hasError'] = false;
		}
		die(Tools::jsonEncode($result));
	}

	/**
	 * Update (or create) restrictions for modules by group
	 */
	protected function updateRestrictions()
	{
		$id_group = Tools::getValue('id_group');
		$unauth_modules = Tools::getValue('modulesBoxUnauth');
		$auth_modules = Tools::getValue('modulesBoxAuth');
		$return = true;
		if ($id_group)
			Group::truncateModulesRestrictions((int)$id_group);

		if (is_array($auth_modules))
			$return &= Group::addModulesRestrictions($id_group, $auth_modules, 1);
		if (is_array($unauth_modules))
			$return &= Group::addModulesRestrictions($id_group, $unauth_modules, 0);
		return $return;
	}

	protected function updateCategoryReduction()
	{
		$category_reduction = Tools::getValue('category_reduction');
		if (is_array($category_reduction))
		{
			foreach ($category_reduction as $cat => $reduction)
			{
				if (!Validate::isUnsignedId($cat) || !$this->validateDiscount($reduction))
					$this->_errors[] = Tools::displayError('Discount value is incorrect');
				else
				{
					Db::getInstance()->execute('
						DELETE FROM `'._DB_PREFIX_.'group_reduction`
						WHERE `id_group` = '.(int)Tools::getValue('id_group').'
							AND `id_category` = '.(int)$cat
					);
					Db::getInstance()->execute('
						DELETE FROM `'._DB_PREFIX_.'product_group_reduction_cache`
						WHERE `id_group` = '.(int)Tools::getValue('id_group')
					);
					$category = new Category((int)$cat);
					$category->addGroupsIfNoExist((int)Tools::getValue('id_group'));
					$group_reduction = new GroupReduction();
					$group_reduction->id_group = (int)Tools::getValue('id_group');
					$group_reduction->reduction = (float)($reduction / 100);
					$group_reduction->id_category = (int)$cat;
					if (!$group_reduction->save())
						$this->_errors[] = Tools::displayError('Cannot save group reductions');
				}
			}
		}
	}
}