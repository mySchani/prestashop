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

class AdminTabsControllerCore extends AdminController
{
	public function __construct()
	{
		$this->context = Context::getContext();
		$this->table = 'tab';
		$this->className = 'Tab';
		$this->lang = true;

		$this->fieldImageSettings = array(
			'name' => 'icon',
			'dir' => 't'
		);

		$this->imageType = 'gif';

		$this->fieldsDisplay = array(
			'id_tab' => array(
				'title' => $this->l('ID'),
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 200
			),
			'logo' => array(
				'title' => $this->l('Icon'),
				'image' => 't',
				'image_id' => 'class_name',
				'orderby' => false,
				'search' => false
			),
			'module' => array(
				'title' => $this->l('Module')
			),
			'position' => array(
				'title' => $this->l('Position'),
				'width' => 40,
				'filter_key' => 'cp!position',
				'position' => 'position'
			)
		);

		parent::__construct();
	}

	/**
	 * AdminController::initForm() override
	 * @see AdminController::initForm()
	 */
	public function initForm()
	{
		$tabs = Tab::getTabs($this->context->language->id, 0);

		// added category "Home" in var $tabs
		$tab_zero = array(
			'id_tab' => 0,
			'name' => $this->l('Home')
		);
		array_unshift($tabs, $tab_zero);

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Tabs'),
				'image' => '../img/admin/tab.gif'
			),
			'input' => array(
				array(
					'type' => 'hidden',
					'name' => 'position',
					'required' => false
				),
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'lang' => true,
					'size' => 33,
					'required' => true,
					'hint' => $this->l('Invalid characters:').' <>;=#{}'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Class:'),
					'name' => 'class_name',
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Module:'),
					'name' => 'module'
				),
				array(
					'type' => 'file',
					'label' => $this->l('Icon:'),
					'name' => 'icon',
					'desc' => $this->l('Upload logo from your computer').' (.gif, .jpg, .jpeg '.$this->l('or').' .png)'
				),
				array(
					'type' => 'select',
					'label' => $this->l('Parent:'),
					'name' => 'id_parent',
					'options' => array(
						'query' => $tabs,
						'id' => 'id_tab',
						'name' => 'name',
						'default' => array(
							'value' => -1,
							'label' => $this->l('None')
						)
					)
				)
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			)
		);

		return parent::initForm();
	}

	/**
	 * AdminController::initList() override
	 * @see AdminController::initList()
	 */
	public function initList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('details');

		$this->_where = 'AND a.`id_parent` = 0';
		$this->_orderBy = 'position';

		return parent::initList();
	}

	/**
	 * method call when ajax request is made with the details row action
	 * @see AdminController::postProcess()
	 */
	public function ajaxProcess()
	{
		// test if an id is submit
		if (($id = Tools::getValue('id')) && Tools::isSubmit('id'))
		{
			// override attributes
			$this->display = 'list';
			$this->lang = false;

			$this->addRowAction('edit');
			$this->addRowAction('delete');

			$this->_select = 'b.*';
			$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'tab_lang` b ON (b.`id_tab` = a.`id_tab` AND b.`id_lang` = '.$this->context->language->id.')';
			$this->_where = 'AND a.`id_parent` = '.(int)$id;
			$this->_orderBy = 'position';

			// get list and force no limit clause in the request
			$this->getList($this->context->language->id);

			// Render list
			$helper = new HelperList();
			$helper->actions = $this->actions;
			$helper->list_skip_actions = $this->list_skip_actions;
			$helper->no_link = true;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
			$helper->imageType = $this->imageType;
			$helper->toolbar_fix = false;
			$helper->show_toolbar = false;
			$helper->orderBy = 'position';
			$helper->orderWay = 'ASC';
			$helper->currentIndex = self::$currentIndex;
			$helper->token = $this->token;
			$helper->table = $this->table;
			// Force render - no filter, form, js, sorting ...
			$helper->simple_header = true;
			$content = $helper->generateList($this->_list, $this->fieldsDisplay);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
		}

		die;
	}

	public function postProcess()
	{
		/* PrestaShop demo mode */
		if (_PS_MODE_DEMO_)
		{
			$this->_errors[] = Tools::displayError('This functionnality has been disabled.');
			return;
		}
		/* PrestaShop demo mode*/

		if (($id_tab = (int)Tools::getValue('id_tab')) && ($direction = Tools::getValue('move')) && Validate::isLoadedObject($tab = new Tab($id_tab)))
		{
			if ($tab->move($direction))
				Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
		}
		else if (Tools::getValue('position') && !Tools::isSubmit('submitAdd'.$this->table))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			else if (!Validate::isLoadedObject($object = new Tab((int)Tools::getValue($this->identifier))))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').
					' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			if (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.Tools::getAdminTokenLite('AdminTabs'));
		}
		else
		{
			// Temporary add the position depend of the selection of the parent category
			$_POST['position'] = Tab::getNbTabs(Tools::getValue('id_parent'));
			parent::postProcess();
		}
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, 'position', $order_way, $start, $limit, $id_lang_shop);
	}

	public function afterImageUpload()
	{
		if (!($obj = $this->loadObject(true)))
			return;
		@rename(_PS_IMG_DIR_.'t/'.$obj->id.'.gif', _PS_IMG_DIR_.'t/'.$obj->class_name.'.gif');
	}
}