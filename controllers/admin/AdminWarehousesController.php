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

/**
 * @since 1.5.0
 */
class AdminWarehousesControllerCore extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'warehouse';
	 	$this->className = 'Warehouse';
		$this->context = Context::getContext();
		$this->deleted = true;
		$this->lang = false;

		$this->fieldsDisplay = array(
			'reference'	=> array(
				'title' => $this->l('Reference'),
				'width' => 150,
			),
			'name' => array(
				'title' => $this->l('Name'),
			),
			'management_type' => array(
				'title' => $this->l('Managment type'),
				 'width' => 80,
			),
			'employee' => array(
				'title' => $this->l('Manager'),
				'width' => 200,
				'filter_key' => 'employee',
				'havingFilter' => true
			),
			'location' => array(
				'title' => $this->l('Location'),
				'width' => 200,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
			'contact' => array(
				'title' => $this->l('Phone Number'),
				'width' => 200,
				'orderby' => false,
				'filter' => false,
				'search' => false,
			),
		);

		parent::__construct();
	}

	/**
	 * AdminController::initList() override
	 * @see AdminController::initList()
	 */
	public function initList()
	{
		// Checks access
		if (!($this->tabAccess['add'] === '1'))
			unset($this->toolbar_btn['new']);

		// removes links on rows
		$this->list_no_link = true;

		// adds actions on rows
		$this->addRowAction('edit');
		$this->addRowAction('view');
		$this->addRowAction('delete');

		// query: select
		$this->_select = '
			reference,
			name,
			management_type,
			CONCAT(e.lastname, \' \', e.firstname) as employee,
			ad.phone as contact,
			CONCAT(ad.city, \' - \', c.iso_code) as location';

		// query: join
		$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'employee` e ON (e.id_employee = a.id_employee)
			LEFT JOIN `'._DB_PREFIX_.'address` ad ON (ad.id_address = a.id_address)
			LEFT JOIN `'._DB_PREFIX_.'country` c ON (c.id_country = ad.id_country)';

		// display help informations
		$this->displayInformation($this->l('This interface allows you to manage your warehouses.').'<br />');
		$this->displayInformation($this->l('Before adding stock in your warehouses, you should check the general default currency used.').'<br />');
		$this->displayInformation($this->l('Futhermore, for each warehouse, you have to check :
											the management type (according to the law in your country), the valuation currency,
											its associated carriers and shops.').'<br />');
		$this->displayInformation($this->l('Finally, you can see detailed informations on your stock per warehouse, such as its valuation,
											the number of products and quantities stored.'));

		return parent::initList();
	}

	/**
	 * AdminController::initForm() override
	 * @see AdminController::initForm()
	 */
	public function initForm()
	{
		// loads current warehouse
		if (!($obj = $this->loadObject(true)))
			return;

		// gets the manager of the warehouse
		$query = new DbQuery();
		$query->select('id_employee, CONCAT(lastname," ",firstname) as name');
		$query->from('employee');
		$query->where('active = 1');
		$employees_array = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		// sets the title of the toolbar
		$this->toolbar_title = $this->l('Stock : Warehouse management');

		// sets the fields of the form
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Warehouse management'),
				'image' => '../img/admin/edit.gif'
			),
			'input' => array(
				array(
					'type' => 'hidden',
					'name' => 'id_address',
				),
				array(
					'type' => 'text',
					'label' => $this->l('Reference:'),
					'name' => 'reference',
					'size' => 30,
					'maxlength' => 32,
					'required' => true,
					'desc' => $this->l('Reference of this warehouse'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 40,
					'maxlength' => 45,
					'required' => true,
					'desc' => $this->l('Name of this warehouse')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Phone:'),
					'name' => 'phone',
					'size' => 15,
					'maxlength' => 16,
					'desc' => $this->l('Phone number of this warehouse')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Adress:'),
					'name' => 'address',
					'size' => 100,
					'maxlength' => 128,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Adress:').' (2)',
					'name' => 'address2',
					'size' => 100,
					'maxlength' => 128,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Postcode/ Zip Code:'),
					'name' => 'postcode',
					'size' => 10,
					'maxlength' => 12,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('City:'),
					'name' => 'city',
					'size' => 20,
					'maxlength' => 32,
					'required' => true,
				),
				array(
					'type' => 'select',
					'label' => $this->l('Country:'),
					'name' => 'id_country',
					'required' => true,
					'options' => array(
						'query' => Country::getCountries($this->context->language->id, false),
						'id' => 'id_country',
						'name' => 'name'
					),
					'desc' => $this->l('Country where the state, region or city is located')
				),
				array(
					'type' => 'select',
					'label' => $this->l('State'),
					'name' => 'id_state',
					'required' => true,
					'options' => array(
						'id' => 'id_state',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Manager:'),
					'name' => 'id_employee',
					'required' => true,
					'options' => array(
						'query' => $employees_array,
						'id' => 'id_employee',
						'name' => 'name'
					),
				),
				array(
					'type' => 'select',
					'label' => $this->l('Carriers:'),
					'name' => 'ids_carriers[]',
					'required' => true,
					'multiple' => true,
					'options' => array(
						'query' => Carrier::getCarriers($this->context->language->id, true),
						'id' => 'id_reference',
						'name' => 'name'
					),
					'desc' => $this->l('Associated carriers'),
					'hint' => $this->l('You can specifiy the carriers available to ship orders from this warehouse'),
				),
			),

		);

		// Shop Association
		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shops:'),
				'name' => 'checkBoxShopAsso',
				'desc' => 'Associated shops',
				'values' => Shop::getTree()
			);
		}

		// It is not possible to change currency valuation and management type
		if (Tools::isSubmit('addwarehouse') || Tools::isSubmit('submitAddwarehouse'))
		{
			$this->fields_form['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Management type:'),
				'name' => 'management_type',
				'required' => true,
				'options' => array(
					'query' => array(
						array(
							'id' => 'WA',
							'name' => $this->l('Weight Average')
						),
						array(
							'id' => 'FIFO',
							'name' => $this->l('First In, First Out')
						),
						array(
							'id' => 'LIFO',
							'name' => $this->l('Last In, First Out')
						),
					),
					'id' => 'id',
					'name' => 'name'
				),
				'desc' => $this->l('Inventory valuation method')
			);

			$this->fields_form['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Stock valuation currency:'),
				'name' => 'id_currency',
				'required' => true,
				'options' => array(
					'query' => Currency::getCurrencies(),
					'id' => 'id_currency',
					'name' => 'name'
				)
			);
		}
		else
		{
			$this->fields_form['input'][] = array(
				'type' => 'hidden',
				'name' => 'management_type'
			);

			$this->fields_form['input'][] = array(
				'type' => 'hidden',
				'name' => 'id_currency'
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('   Save   '),
			'class' => 'button'
		);

		// loads current address for this warehouse - if possible
		$address = null;
		if ($obj->id_address > 0)
			$address = new Address($obj->id_address);

		// loads current shops associated with this warehouse
		$shops = $obj->getShops();
		$ids_shop = array();
		foreach ($shops as $shop)
			$ids_shop[] = $shop['id_shop'];

		// loads current carriers associated with this warehouse
		$carriers = $obj->getCarriers();

		// force specific fields values
		if ($address != null)
			$this->fields_value = array(
				'id_address' => $address->id,
				'phone' => $address->phone,
				'address' => $address->address1,
				'address2' => $address->address2,
				'postcode' => $address->postcode,
				'city' => $address->city,
				'id_country' => $address->id_country,
				'id_state' => $address->id_state,
			);
		else
			$this->fields_value['id_address'] = 0;
		$this->fields_value['ids_shops[]'] = $ids_shop;
		$this->fields_value['ids_carriers[]'] = $carriers;

		return parent::initForm();
	}

	/**
	 * AdminController::postProcess() override
	 * @see AdminController::postProcess()
	 */
	public function postProcess()
	{
		// checks access
		if (Tools::isSubmit('submitAdd'.$this->table) && !($this->tabAccess['add'] === '1'))
		{
			$this->_errors[] = Tools::displayError('You do not have the required permissions to add warehouses.');
			return parent::postProcess();
		}

		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			if (!($obj = $this->loadObject(true)))
				return;

			// handles shops associations
			if (Tools::isSubmit('ids_shops'))
				$obj->setShops(Tools::getValue('ids_shops'));

			// handles carriers associations
			if (Tools::isSubmit('ids_carriers'))
				$obj->setCarriers(Tools::getValue('ids_carriers'));

			// updates/creates address if it does not exist
			if (Tools::isSubmit('id_address') && (int)Tools::getValue('id_address') > 0)
				$address = new Address((int)Tools::getValue('id_address')); // updates address
			else
				$address = new Address(); // creates address

			$address->alias = Tools::getValue('reference', null);
			$address->lastname = 'warehouse'; // skip problem with numeric characters in warehouse name
			$address->firstname = 'warehouse'; // skip problem with numeric characters in warehouse name
			$address->address1 = Tools::getValue('address', null);
			$address->address2 = Tools::getValue('address2', null);
			$address->postcode = Tools::getValue('postcode', null);
			$address->phone = Tools::getValue('phone', null);
			$address->id_country = Tools::getValue('id_country', null);
			$address->id_state = Tools::getValue('id_state', null);
			$address->city = Tools::getValue('city', null);

			$validation = $address->validateController();

			// checks address validity
			if (count($validation) > 0)
			{
				foreach ($validation as $item)
					$this->_errors[] = $item;
				$this->_errors[] = Tools::displayError('The address is not correct. Check if all required fields are filled.');
			}
			else
			{
				if (Tools::isSubmit('id_address') && Tools::getValue('id_address') > 0)
					$address->update();
				else
				{
					$address->save();
					$_POST['id_address'] = $address->id;
				}
			}

			// hack for enable the possibility to update a warehouse without recreate new id
			$this->deleted = false;

			return parent::postProcess();
		}
		else if (Tools::isSubmit('delete'.$this->table))
			if (!($obj = $this->loadObject(true)))
				return;
			else if ($obj->getQuantitiesOfProducts() > 0)
				$this->_errors[] = $this->l('It is not possible to delete a Warehosue when there are products in it.');
			else if (SupplyOrder::warehouseHasPendingOrders($obj->id))
				$this->_errors[] = $this->l('It is not possible to delete a Warehouse if it has pending supply orders.');
			else
				return parent::postProcess();
	}

	/**
	 * @see AdminController::initView()
	 */
	public function initView()
	{
		$this->displayInformation($this->l('This interface allows you to display detailed informations on your warehouse.').'<br />');

		$id_warehouse = (int)Tools::getValue('id_warehouse');
		$warehouse = new Warehouse($id_warehouse);
		$employee = new Employee($warehouse->id_employee);
		$currency = new Currency($warehouse->id_currency);
		$address = new Address($warehouse->id_address);
		$shops = $warehouse->getShops();

		if (!Validate::isLoadedObject($warehouse) ||
			!Validate::isLoadedObject($employee) ||
			!Validate::isLoadedObject($currency))
			return parent::initView();

		$this->tpl_view_vars = array(
			'warehouse' => $warehouse,
			'employee' => $employee,
			'currency' => $currency,
			'address' => $address,
			'shops' => $shops,
			'warehouse_num_products' => $warehouse->getNumberOfProducts(),
			'warehouse_value' => Tools::ps_round($warehouse->getStockValue(), 2),
			'warehouse_quantities' => $warehouse->getQuantitiesofProducts(),
		);

		return parent::initView();
	}
}
