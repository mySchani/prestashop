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

class AdminCarriersControllerCore extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'carrier';
		$this->className = 'Carrier';
	 	$this->lang = false;
		$this->deleted = true;

		$this->addRowAction('edit');
		$this->addRowAction('delete');

		$this->_defaultOrderBy = 'position';
		$this->requiredDatabase = true;

		$this->context = Context::getContext();

	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fieldImageSettings = array(
			'name' => 'logo',
			'dir' => 'st'
		);

		$this->fieldsDisplay = array(
			'id_carrier' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto'
			),
			'image' => array(
				'title' => $this->l('Logo'),
				'align' => 'center',
				'image' => 's',
				'orderby' => false,
				'search' => false,
				'width' => 120
			),
			'delay' => array(
				'title' => $this->l('Delay'),
				'width' => 300,
				'orderby' => false
			),
			'active' => array(
				'title' => $this->l('Status'),
				'align' => 'center',
				'active' => 'status',
				'type' => 'bool',
				'orderby' => false,
				'width' => 25
			),
			'is_free' => array(
				'title' => $this->l('Is Free'),
				'align' => 'center',
				'icon' => array(
					0 => 'disabled.gif',
					1 => 'enabled.gif',
					'default' => 'disabled.gif'
				),
				'type' => 'bool',
				'orderby' => false,
				'width' => 150
			),
			'position' => array(
				'title' => $this->l('Position'),
				'width' => 40,
				'filter_key' => 'cp!position',
				'align' => 'center',
				'position' => 'position'
			)
		);

		$carrier_default_sort = array(
			array('value' => Carrier::SORT_BY_PRICE, 'name' => $this->l('Price')),
			array('value' => Carrier::SORT_BY_POSITION, 'name' => $this->l('Position'))
		);

		$carrier_default_order = array(
			array('value' => Carrier::SORT_BY_ASC, 'name' => $this->l('Ascending')),
			array('value' => Carrier::SORT_BY_DESC, 'name' => $this->l('Descending'))
		);

		$this->options = array(
			'general' => array(
				'title' => $this->l('Carrier options'),
				'fields' => array(
					'PS_CARRIER_DEFAULT' => array(
						'title' => $this->l('Default carrier:'),
						'desc' => $this->l('The default carrier used in shop'),
						'cast' => 'intval',
						'type' => 'select',
						'identifier' => 'id_carrier',
						'list' => Carrier::getCarriers((int)Configuration::get('PS_LANG_DEFAULT'), true, false, false, null, Carrier::ALL_CARRIERS)
					),
					'PS_CARRIER_DEFAULT_SORT' => array(
						'title' => $this->l('Carrier default sort:'),
						'desc' => $this->l('This default sort will be available only on front-office'),
						'cast' => 'intval',
						'type' => 'select',
						'identifier' => 'value',
						'list' => $carrier_default_sort
					),
					'PS_CARRIER_DEFAULT_ORDER' => array(
						'title' => $this->l('Carrier default order:'),
						'desc' => $this->l('This default order will be available only on front-office'),
						'cast' => 'intval',
						'type' => 'select',
						'identifier' => 'value',
						'list' => $carrier_default_order
					),
				),
				'submit' => array()
			)
		);

		parent::__construct();
	}

	public function renderList()
	{
		$this->displayInformation(
			'&nbsp;<b>'.$this->l('How to create a new carrier?').'</b>
			<br />
			<ul>
			<li>'.$this->l('Click "Add new".').'<br /></li>
				<li>'.$this->l('Fill in the fields and click "Save".').'</li>
				<li>'.
					$this->l('You need to decide a price range or a weight range for which the new carrier will be available.
					Under the "Shipping" tab, click either "Price Ranges" or "Weight Ranges".').'
				</li>
				<li>'.$this->l('Click "Add new".').'</li>
				<li>'.
					$this->l('Select the name of the carrier and define the price range or the weight range.
					For example the carrier can be made available for a weight range between 0 and 5kgs. Another carrier will have a range between 5 and 10kgs.').'
				</li>
				<li>'.$this->l('When you are done, click "Save".').'</li>
				<li>'.$this->l('Click on the "Shipping" tab.').'</li>
				<li>'.
					$this->l('You need to choose the fees that will be applied for this carrier.
					At the bottom on the page, in the "Fees" section, select the name of the carrier.').'
				</li>
				<li>'.$this->l('For each zone, enter a price. Click "Save".').'</li>
				<li>'.$this->l('You\'re set! The new carrier will be displayed to your customers.').'</li>
			</ul>'
		);
		$this->_select = 'b.*';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'carrier_lang` b ON a.id_carrier = b.id_carrier';
		$this->_where = 'AND b.id_lang = '.$this->context->language->id;

		return parent::renderList();
	}

	public function renderForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Carriers'),
				'image' => '../img/admin/delivery.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Company:'),
					'name' => 'name',
					'size' => 25,
					'required' => true,
					'hint' => $this->l('Allowed characters: letters, spaces and').' ().-',
					'desc' => array(
						$this->l('Carrier name displayed during checkout'),
						$this->l('With a value of 0, the carrier name will be replaced by the shop name')
					)
				),
				array(
					'type' => 'file',
					'label' => $this->l('Logo:'),
					'name' => 'logo',
					'desc' => $this->l('Upload logo from your computer').' (.gif, .jpg, .jpeg '.$this->l('or').' .png)'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Transit time:'),
					'name' => 'delay',
					'lang' => true,
					'required' => true,
					'size' => 41,
					'maxlength' => 128,
					'desc' => $this->l('Time taken for product delivery; displayed during checkout')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Grade:'),
					'name' => 'grade',
					'required' => false,
					'size' => 1,
					'desc' => $this->l('"0" for a longest shipping delay,"9" for the shortest shipping delay.')
				),
				array(
					'type' => 'text',
					'label' => $this->l('URL:'),
					'name' => 'url',
					'size' => 40,
					'desc' => $this->l('URL for the tracking number; type \'@\' where the tracking number will appear')
				),
				array(
					'type' => 'checkbox',
					'label' => $this->l('Zone:'),
					'name' => 'zone',
					'values' => array(
						'query' => Zone::getZones(false),
						'id' => 'id_zone',
						'name' => 'name'
					),
					'desc' => $this->l('The zone in which this carrier is to be used')
				),
				array(
					'type' => 'group',
					'label' => $this->l('Group access:'),
					'name' => 'groupBox',
					'values' => Group::getGroups(Context::getContext()->language->id),
					'desc' => $this->l('Mark all groups you want to give access to this carrier')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Status:'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Include or exclude carrier from list of carriers on Front Office')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Apply shipping cost:'),
					'name' => 'is_free',
					'required' => false,
					'class' => 't',
					'values' => array(
						array(
							'id' => 'is_free_on',
							'value' => 0,
							'label' => '<img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" />'
						),
						array(
							'id' => 'is_free_off',
							'value' => 1,
							'label' => '<img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" />'
						)
					),
					'desc' => $this->l('Apply shipping costs and additional shipping costs by products in carrier price')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Tax:'),
					'name' => 'id_tax_rules_group',
					'options' => array(
						'query' => TaxRulesGroup::getTaxRulesGroups(true),
						'id' => 'id_tax_rules_group',
						'name' => 'name',
						'default' => array(
							'label' => $this->l('No Tax'),
							'value' => 0
						)
					)
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Shipping & handling:'),
					'name' => 'shipping_handling',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'shipping_handling_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'shipping_handling_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Include the shipping & handling costs in carrier price')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Billing:'),
					'name' => 'shipping_method',
					'required' => false,
					'class' => 't',
					'br' => true,
					'values' => array(
						array(
							'id' => 'billing_default',
							'value' => Carrier::SHIPPING_METHOD_DEFAULT,
							'label' => $this->l('Default behavior')
						),
						array(
							'id' => 'billing_price',
							'value' => Carrier::SHIPPING_METHOD_PRICE,
							'label' => $this->l('According to total price')
						),
						array(
							'id' => 'billing_weight',
							'value' => Carrier::SHIPPING_METHOD_WEIGHT,
							'label' => $this->l('According to total weight')
						)
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Out-of-range behavior:'),
					'name' => 'range_behavior',
					'options' => array(
						'query' => array(
							array(
								'id' => 0,
								'name' => $this->l('Apply the cost of the highest defined range')
							),
							array(
								'id' => 1,
								'name' => $this->l('Disable carrier')
							)
						),
						'id' => 'id',
						'name' => 'name'
					),
					'desc' => $this->l('Out-of-range behavior when none is defined (e.g., when a customer\'s cart weight is greater than the highest range limit)')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Maximium package height:'),
					'name' => 'max_height',
					'required' => false,
					'size' => 10,
					'desc' => $this->l('Maximum height managed by this carrier. Set "0" or nothing, to ignore this field.')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Maximium package width:'),
					'name' => 'max_width',
					'required' => false,
					'size' => 10,
					'desc' => $this->l('Maximum width managed by this carrier. Set "0" or nothing, to ignore this field.')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Maximium package deep:'),
					'name' => 'max_depth',
					'required' => false,
					'size' => 10,
					'desc' => $this->l('Maximum deep managed by this carrier. Set "0" or nothing, to ignore this field.')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Maximium package weigth:'),
					'name' => 'max_weight',
					'required' => false,
					'size' => 10,
					'desc' => $this->l('Maximum weight managed by this carrier. Set "0" or nothing, to ignore this field.')
				),
				array(
					'type' => 'hidden',
					'name' => 'is_module'
				),
				array(
					'type' => 'hidden',
					'name' => 'external_module_name',
				),
				array(
					'type' => 'hidden',
					'name' => 'shipping_external'
				),
				array(
					'type' => 'hidden',
					'name' => 'need_range'
				),
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shop association:'),
				'name' => 'checkBoxShopAsso',
				'values' => Shop::getTree()
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('   Save   '),
			'class' => 'button'
		);

		if (!($obj = $this->loadObject(true)))
			return;

		$this->getFieldsValues($obj);

		return parent::renderForm();
	}

	public function postProcess()
	{
		if (Tools::getValue('submitAdd'.$this->table))
		{
		 	/* Checking fields validity */
			$this->validateRules();
			if (!count($this->_errors))
			{
				$id = (int)Tools::getValue('id_'.$this->table);

				/* Object update */
				if (isset($id) && !empty($id))
				{
					if ($this->tabAccess['edit'] === '1')
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'carrier_group WHERE id_carrier = '.(int)$id);
							$object->deleted = 1;
							$object->update();
							$object_new = new $this->className();
							$this->copyFromPost($object_new, $this->table);
							$object_new->position = $object->position;
							$result = $object_new->add();
							$this->updateAssoShop($object->id, $object_new->id);
							if (Validate::isLoadedObject($object_new))
							{
								$this->afterDelete($object_new, $object->id);
								Hook::updateCarrier((int)$object->id, $object_new);
							}
							$this->changeGroups($object_new->id);
							if (!$result)
								$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.'</b>';
							else if ($this->postImage($object_new->id))
							{
								$this->changeZones($object_new->id);
								Tools::redirectAdmin(self::$currentIndex.'&id_'.$this->table.'='.$object->id.'&conf=4&token='.$this->token);
							}
						}
						else
							$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.
												$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
				}

				/* Object creation */
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						$object = new $this->className();
						$this->copyFromPost($object, $this->table);
						$object->position = Carrier::getHigherPosition() + 1;
						if (!$object->add())
							$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.'</b>';
						else if (($_POST['id_'.$this->table] = $object->id /* voluntary */) && $this->postImage($object->id) && $this->_redirect)
						{
							$this->changeZones($object->id);
							$this->changeGroups($object->id);
							$this->updateAssoShop($object->id);
							Tools::redirectAdmin(self::$currentIndex.'&id_'.$this->table.'='.$object->id.'&conf=3&token='.$this->token);
						}
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to add here.');
				}
			}
		}
		else if ((isset($_GET['status'.$this->table]) || isset($_GET['status'])) && Tools::getValue($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Tools::getValue('id_carrier') == Configuration::get('PS_CARRIER_DEFAULT'))
					$this->_errors[] = Tools::displayError('You can\'t disable the default carrier, please change your default carrier first.');
				else
					parent::postProcess();
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		else
		{
			if ((Tools::isSubmit('submitDel'.$this->table) && in_array(Configuration::get('PS_CARRIER_DEFAULT'), Tools::getValue('carrierBox')))
				|| (isset($_GET['delete'.$this->table]) && Tools::getValue('id_carrier') == Configuration::get('PS_CARRIER_DEFAULT')))
					$this->_errors[] = $this->l('Please set another carrier as default before deleting');
			else
			{
				// if deletion : removes the carrier from the warehouse/carrier association
				if (Tools::isSubmit('delete'.$this->table))
				{
					$id = (int)Tools::getValue('id_'.$this->table);
					Warehouse::removeCarrier($id);
				}
				parent::postProcess();
			}
		}
	}

	/**
	 * Overload the property $fields_value
	 *
	 * @param object $obj
	 */
	public function getFieldsValues($obj)
	{
		if ($this->getFieldValue($obj, 'is_module'))
			$this->fields_value['is_module'] = 1;

		if ($this->getFieldValue($obj, 'shipping_external'))
			$this->fields_value['shipping_external'] = 1;

		if ($this->getFieldValue($obj, 'need_range'))
			$this->fields_value['need_range'] = 1;

		// Added values of object Zone
		$carrier_zones = $obj->getZones();
		$carrier_zones_ids = array();
		if (is_array($carrier_zones))
			foreach ($carrier_zones as $carrier_zone)
				$carrier_zones_ids[] = $carrier_zone['id_zone'];

		$zones = Zone::getZones(false);
		foreach ($zones as $zone)
			$this->fields_value['zone_'.$zone['id_zone']] = Tools::getValue('zone_'.$zone['id_zone'], (in_array($zone['id_zone'], $carrier_zones_ids)));

		// Added values of object Group
		$carrier_groups = $obj->getGroups();
		$carrier_groups_ids = array();
		if (is_array($carrier_groups))
			foreach ($carrier_groups as $carrier_group)
				$carrier_groups_ids[] = $carrier_group['id_group'];

		$groups = Group::getGroups($this->context->language->id);
		// if empty $carrier_groups_ids : object creation : we set the default groups
		if (empty($carrier_groups_ids))
		{
			$preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
			$carrier_groups_ids = array_merge($carrier_groups_ids, $preselected);
		}
		foreach ($groups as $group)
			$this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue('groupBox_'.$group['id_group'], (in_array($group['id_group'], $carrier_groups_ids)));
	}

	public function beforeDelete($object)
	{
		return $object->isUsed();
	}

	public function afterDelete($object, $old_id)
	{
		$object->copyCarrierData((int)$old_id);
	}

	private function changeGroups($id_carrier, $delete = true)
	{
		if ($delete)
			Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'carrier_group WHERE id_carrier = '.(int)$id_carrier);
		$groups = Db::getInstance()->executeS('SELECT id_group FROM `'._DB_PREFIX_.'group`');
		foreach ($groups as $group)
			if (Tools::getIsset('groupBox') && in_array($group['id_group'], Tools::getValue('groupBox')))
				Db::getInstance()->execute('
					INSERT INTO '._DB_PREFIX_.'carrier_group (id_group, id_carrier)
					VALUES('.(int)$group['id_group'].','.(int)$id_carrier.')
				');
	}


	public function changeZones($id)
	{
		$carrier = new $this->className($id);
		if (!Validate::isLoadedObject($carrier))
			die (Tools::displayError('Object cannot be loaded'));
		$zones = Zone::getZones(true);
		foreach ($zones as $zone)
			if (count($carrier->getZone($zone['id_zone'])))
			{
				if (!isset($_POST['zone_'.$zone['id_zone']]) || !$_POST['zone_'.$zone['id_zone']])
					$carrier->deleteZone($zone['id_zone']);
			}
			else
				if (isset($_POST['zone_'.$zone['id_zone']]) && $_POST['zone_'.$zone['id_zone']])
					$carrier->addZone($zone['id_zone']);
	}

	/**
	 * Modifying initial getList method to display position feature (drag and drop)
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
		
		foreach ($this->_list as $key => $list)
			if ($list['name'] == '0')
				$this->_list[$key]['name'] = Configuration::get('PS_SHOP_NAME');
	}

}


