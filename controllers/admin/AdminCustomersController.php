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

class AdminCustomersControllerCore extends AdminController
{
	protected $delete_mode;

	public function __construct()
	{
		$this->required_database = true;
		$this->required_fields = array('newsletter','optin');
	 	$this->table = 'customer';
		$this->className = 'Customer';
	 	$this->lang = false;
		$this->deleted = true;

		$this->addRowAction('edit');
		$this->addRowAction('view');
		$this->addRowAction('delete');
	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->context = Context::getContext();

		$this->default_form_language = $this->context->language->id;

		$genders_icon = array('default' => 'unknown.gif');
		$genders = array(0 => $this->l('?'));
		foreach (Gender::getGenders() as $gender)
		{
			$genders_icon[$gender->id] = '../genders/'.$gender->id.'.jpg';
			$genders[$gender->id] = $gender->name;
		}

		$this->fieldsDisplay = array(
			'id_customer' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 20
 			),
			'id_gender' => array(
				'title' => $this->l('Gender'),
				'width' => 70,
				'align' => 'center',
				'icon' => $genders_icon,
				'orderby' => false,
				'type' => 'select',
				'list' => $genders,
				'filter_key' => 'a!id_gender',
 			),
			'lastname' => array(
				'title' => $this->l('Last Name'),
				'width' => 'auto'
 			),
			'firstname' => array(
				'title' => $this->l('First name'),
				'width' => 'auto'
 			),
			'email' => array(
				'title' => $this->l('E-mail address'),
				'width' => 140,
 			),
			'age' => array(
				'title' => $this->l('Age'),
				'width' => 20,
				'search' => false,
 				'align' => 'center'
 			),
			'active' => array(
				'title' => $this->l('Enabled'),
				'width' => 70,
				'align' => 'center',
				'active' => 'status',
				'type' => 'bool',
				'orderby' => false
 			),
			'newsletter' => array(
				'title' => $this->l('News.'),
				'width' => 70,
				'align' => 'center',
				'type' => 'bool',
				'callback' => 'printNewsIcon',
				'orderby' => false
 			),
			'optin' => array(
				'title' => $this->l('Opt.'),
				'width' => 70,
				'align' => 'center',
				'type' => 'bool',
				'callback' => 'printOptinIcon',
				'orderby' => false
 			),
			'date_add' => array(
				'title' => $this->l('Registration'),
				'width' => 150,
				'type' => 'date',
				'align' => 'right'
 			),
			'connect' => array(
				'title' => $this->l('Connection'),
				'width' => 100,
				'type' => 'datetime',
				'search' => false
 			)
 		);

		$this->shopLinkType = 'shop';
		$this->shopShareDatas = Shop::SHARE_CUSTOMER;

		$this->options = array(
			'general' => array(
				'title' =>	$this->l('Customers options'),
				'fields' =>	array(
					'PS_PASSWD_TIME_FRONT' => array(
						'title' => $this->l('Regenerate password:'),
						'desc' => $this->l('Security minimum time to wait to regenerate the password'),
						'validation' => 'isUnsignedInt',
						'cast' => 'intval',
						'size' => 5,
						'type' => 'text',
						'suffix' => ' '.$this->l('minutes')
					)
				),
				'submit' => array()
			)
		);

		parent::__construct();
	}

	public function initContent()
	{
		if ($this->action == 'select_delete')
			$this->context->smarty->assign(array(
				'delete_form' => true,
				'url_delete' => htmlentities($_SERVER['REQUEST_URI']),
				'boxes' => $this->boxes,
			));

		parent::initContent();
	}

	public function initProcess()
	{
		$this->id_object = Tools::getValue('id_'.$this->table);
		if (Tools::isSubmit('submitGuestToCustomer') && $this->id_object)
			if ($this->tabAccess['edit'] === '1')
				$this->action = 'guest_to_customer';
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		elseif (Tools::isSubmit('changeNewsletterVal') && $this->id_object)
			$this->action = 'change_newsletter_val';
		elseif (Tools::isSubmit('changeOptinVal') && $this->id_object)
			$this->action = 'change_optin_val';

		parent::initProcess();
		// When deleting, first display a form to select the type of deletion
		if ($this->action == 'delete' || $this->action == 'bulkdelete')
			if (Tools::getValue('deleteMode') == 'real' || Tools::getValue('deleteMode') == 'deleted')
				$this->delete_mode = Tools::getValue('deleteMode');
			else
				$this->action = 'select_delete';
	}

	public function renderList()
	{
		$this->_select = '(YEAR(CURRENT_DATE)-YEAR(`birthday`)) - (RIGHT(CURRENT_DATE, 5) < RIGHT(birthday, 5)) AS `age`, (
			SELECT c.date_add FROM '._DB_PREFIX_.'guest g
			LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
			WHERE g.id_customer = a.id_customer
			ORDER BY c.date_add DESC
			LIMIT 1
		) as connect';

		return parent::renderList();
	}

	public function renderForm()
	{
		if (!($obj = $this->loadObject(true)))
			return;

		$genders = Gender::getGenders();
		$list_genders = array();
		foreach ($genders as $key => $gender)
		{
			$list_genders[$key]['id'] = 'gender_'.$gender->id;
			$list_genders[$key]['value'] = $gender->id;
			$list_genders[$key]['label'] = $gender->name;
		}
		$key++;
		$list_genders[$key]['id'] = 'gender_unknown';
		$list_genders[$key]['value'] = 0;
		$list_genders[$key]['label'] = $this->l('Unknown');

		$years = Tools::dateYears();
		$months = Tools::dateMonths();
		$days = Tools::dateDays();

		$groups = Group::getGroups($this->default_form_language, true);

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Customer'),
				'image' => '../img/admin/tab-customers.gif'
			),
			'input' => array(
				array(
					'type' => 'radio',
					'label' => $this->l('Gender:'),
					'name' => 'id_gender',
					'required' => false,
					'class' => 't',
					'values' => $list_genders
				),
				array(
					'type' => 'text',
					'label' => $this->l('Last name:'),
					'name' => 'lastname',
					'size' => 33,
					'required' => true,
					'hint' => $this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
				),
				array(
					'type' => 'text',
					'label' => $this->l('First name:'),
					'name' => 'firstname',
					'size' => 33,
					'required' => true,
					'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
				),
				array(
					'type' => 'password',
					'label' => $this->l('Password:'),
					'name' => 'passwd',
					'size' => 33,
					'desc' => ($obj->id ? $this->l('Leave blank if no change') : $this->l('5 characters min., only letters, numbers, or').' -_')
				),
				array(
					'type' => 'text',
					'label' => $this->l('E-mail address:'),
					'name' => 'email',
					'size' => 33,
					'required' => true
				),
				array(
					'type' => 'birthday',
					'label' => $this->l('Birthday:'),
					'name' => 'birthday',
					'options' => array(
						'days' => $days,
						'months' => $months,
						'years' => $years
					)
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
					'desc' => $this->l('Allow or disallow this customer to log in')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Newsletter:'),
					'name' => 'newsletter',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'newsletter_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'newsletter_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Customer will receive your newsletter via e-mail')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Opt-in:'),
					'name' => 'optin',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'optin_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'optin_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Customer will receive your ads via e-mail')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Default group:'),
					'name' => 'id_default_group',
					'options' => array(
						'query' => $groups,
						'id' => 'id_group',
						'name' => 'name'
					),
					'hint' => $this->l('The group will be as applied by default.')
				),
				array(
					'type' => 'group',
					'label' => $this->l('Group access:'),
					'name' => 'groupBox',
					'values' => $groups,
					'required' => true,
					'desc' => $this->l('Check all the box(es) of groups of which the customer is to be a member')
				)
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Shop:'),
				'name' => 'id_shop',
				'options' => array(
					'query' => Shop::getShops(),
					'id' => 'id_shop',
					'name' => 'name'
				),
			);
		}

		if (Configuration::get('PS_B2B_ENABLE')) {
			$risks = Risk::getRisks();

			$list_risks = array();
			foreach ($risks as $key => $risk)
			{
				$list_risks[$key]['id_risk'] = (int) $risk->id;
				$list_risks[$key]['name'] = $risk->name;
			}

			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Company:'),
				'name' => 'company',
				'size' => 33
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('SIRET:'),
				'name' => 'siret',
				'size' => 14
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('APE:'),
				'name' => 'ape',
				'size' => 5
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Website:'),
				'name' => 'website',
				'size' => 33
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Outstanding allow:'),
				'name' => 'outstanding_allow_amount',
				'size' => 10,
				'hint' => $this->l('Valid characters:').' 0-9',
				'suffix' => '¤'
			);
			/*
			@todo RC Version
			$this->fields_form['input'][] = array(
				'type' => 'radio',
				'label' => $this->l('Show public prices:'),
				'name' => 'show_public_prices',
				'required' => false,
				'class' => 't',
				'is_bool' => true,
				'values' => array(
					array(
						'id' => 'show_public_prices_on',
						'value' => 1,
						'label' => $this->l('Enabled')
					),
					array(
						'id' => 'show_public_prices_off',
						'value' => 0,
						'label' => $this->l('Disabled')
					)
				),
				'desc' => $this->l('Show public prices on the front office if specific prices have been set ')
			);
			*/
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Max payment days:'),
				'name' => 'max_payment_days',
				'size' => 10,
				'hint' => $this->l('Valid characters:').' 0-9'
			);
			$this->fields_form['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Risk:'),
				'name' => 'id_risk',
				'required' => false,
				'class' => 't',
				'options' => array(
					'query' => $list_risks,
					'id' => 'id_risk',
					'name' => 'name'
				),
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('   Save   '),
			'class' => 'button'
		);

		$birthday = explode('-', $this->getFieldValue($obj, 'birthday'));

		$this->fields_value = array(
			'years' => $this->getFieldValue($obj, 'birthday') ? $birthday[0] : 0,
			'months' => $this->getFieldValue($obj, 'birthday') ? $birthday[1] : 0,
			'days' => $this->getFieldValue($obj, 'birthday') ? $birthday[2] : 0,
			'id_shop' => (int)Configuration::get('PS_SHOP_DEFAULT')
		);

		// Added values of object Group
		$customer_groups = $obj->getGroups();
		$customer_groups_ids = array();
		if (is_array($customer_groups))
			foreach ($customer_groups as $customer_group)
				$customer_groups_ids[] = $customer_group;
		foreach ($groups as $group)
			$this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue('groupBox_'.$group['id_group'], in_array($group['id_group'], $customer_groups_ids));

		return parent::renderForm();
	}

	public function renderView()
	{
		if (!($customer = $this->loadObject()))
			return;

		$this->context->customer = $customer;
		$gender = new Gender($customer->id_gender);
		$gender_image = $gender->getImage();

		$customer_stats = $customer->getStats();
		$sql = 'SELECT SUM(total_paid_real) FROM '._DB_PREFIX_.'orders WHERE id_customer = %d AND valid = 1';
		if ($total_customer = Db::getInstance()->getValue(sprintf($sql, $customer->id)))
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS COUNT(*) FROM '._DB_PREFIX_.'orders WHERE valid = 1 GROUP BY id_customer HAVING SUM(total_paid_real) > %d';
			Db::getInstance()->getValue(sprintf($sql, (int)$total_customer));
			$count_better_customers = (int)Db::getInstance()->getValue('SELECT FOUND_ROWS()') + 1;
		}
		else
			$count_better_customers = '-';

		$orders = Order::getCustomerOrders($customer->id, true);
		$total_orders = count($orders);
		for ($i = 0; $i < $total_orders; $i++)
		{
			$orders[$i]['date_add'] = Tools::displayDate($orders[$i]['date_add'], $this->context->language->id);
			$orders[$i]['total_paid_real'] = Tools::displayPrice($orders[$i]['total_paid_real'], new Currency((int)$orders[$i]['id_currency']));
		}

		$messages = CustomerThread::getCustomerMessages((int)$customer->id);
		$total_messages = count($messages);
		for ($i = 0; $i < $total_messages; $i++)
		{
			$messages[$i]['message'] = substr(strip_tags(html_entity_decode($messages[$i]['message'], ENT_NOQUOTES, 'UTF-8')), 0, 75);
			$messages[$i]['date_add'] = Tools::displayDate($messages[$i]['date_add'], $this->context->language->id, true);
		}

		$groups = $customer->getGroups();
		$total_groups = count($groups);
		for ($i = 0; $i < $total_groups; $i++)
		{
			$group = new Group($groups[$i]);
			$groups[$i] = array();
			$groups[$i]['id_group'] = $group->id;
			$groups[$i]['name'] = $group->name[$this->default_form_language];
		}

		$total_ok = 0;
		$orders_ok = array();
		$orders_ko = array();
		foreach ($orders as $order)
		{
			if ($order['valid'])
			{
				$orders_ok[] = $order;
				$total_ok += $order['total_paid_real'];
			}
			else
				$orders_ko[] = $order;
		}

		$products = $customer->getBoughtProducts();
		$total_products = count($products);
		for ($i = 0; $i < $total_products; $i++)
			$products[$i]['date_add'] = Tools::displayDate($products[$i]['date_add'], $this->default_form_language, true);

		$carts = Cart::getCustomerCarts($customer->id);
		$total_carts = count($carts);
		for ($i = 0; $i < $total_carts; $i++)
		{
			$cart = new Cart((int)$carts[$i]['id_cart']);
			$this->context->cart = $cart;
			$summary = $cart->getSummaryDetails();
			$currency = new Currency((int)$carts[$i]['id_currency']);
			$carrier = new Carrier((int)$carts[$i]['id_carrier']);
			$carts[$i]['id_cart'] = sprintf('%06d', $carts[$i]['id_cart']);
			$carts[$i]['date_add'] = Tools::displayDate($carts[$i]['date_add'], $this->default_form_language, true);
			$carts[$i]['total_price'] = Tools::displayPrice($summary['total_price'], $currency);
			$carts[$i]['name'] = $carrier->name;
		}

		$sql = 'SELECT DISTINCT id_product, c.id_cart, c.id_shop, cp.id_shop AS cp_id_shop
				FROM '._DB_PREFIX_.'cart_product cp
				JOIN '._DB_PREFIX_.'cart c ON (c.id_cart = cp.id_cart)
				WHERE c.id_customer = '.(int)$customer->id.'
					AND cp.id_product NOT IN (
							SELECT product_id
							FROM '._DB_PREFIX_.'orders o
							JOIN '._DB_PREFIX_.'order_detail od ON (o.id_order = od.id_order)
							WHERE o.valid = 1 AND o.id_customer = '.(int)$customer->id.'
						)';
		$interested = Db::getInstance()->executeS($sql);
		$total_interested = count($interested);
		for ($i = 0; $i < $total_interested; $i++)
		{
			$product = new Product($interested[$i]['id_product'], false, $this->default_form_language, $interested[$i]['id_shop']);
			$interested[$i]['url'] = $this->context->link->getProductLink(
				$product->id,
				$product->link_rewrite,
				Category::getLinkRewrite($product->id_category_default, $this->default_form_language),
				null,
				null,
				$interested[$i]['cp_id_shop']
			);
			$interested[$i]['id'] = (int)$product->id;
			$interested[$i]['name'] = Tools::htmlentitiesUTF8($product->name);
		}

		$connections = $customer->getLastConnections();
		$total_connections = count($connections);
		for ($i = 0; $i < $total_connections; $i++)
		{
			$connections[$i]['date_add'] = Tools::displayDate($connections[$i]['date_add'], $this->default_form_language, true);
			$connections[$i]['http_referer'] = $connections[$i]['http_referer'] ?
													preg_replace('/^www./', '', parse_url($connections[$i]['http_referer'], PHP_URL_HOST)) :
														$this->l('Direct link');
		}

		$referrers = Referrer::getReferrers($customer->id);
		$total_referrers = count($referrers);
		for ($i = 0; $i < $total_referrers; $i++)
			$referrers[$i]['date_add'] = Tools::displayDate($referrers[$i]['date_add'], $this->default_form_language, true);

		$this->tpl_view_vars = array(
			'customer' => $customer,
			'gender_image' => $gender_image,

			// General information of the customer
			'registration_date' => Tools::displayDate($customer->date_add, $this->default_form_language, true),
			'customer_stats' => $customer_stats,
			'last_visit' => Tools::displayDate($customer_stats['last_visit'], $this->default_form_language, true),
			'count_better_customers' => $count_better_customers,
			'shop_is_feature_active' => Shop::isFeatureActive(),
			'name_shop' => Shop::getInstance($customer->id_shop)->name,
			'customer_birthday' => Tools::displayDate($customer->birthday, $this->default_form_language),
			'last_update' => Tools::displayDate($customer->date_upd, $this->default_form_language, true),
			'customer_exists' => Customer::customerExists($customer->email),
			'id_lang' => (int)(count($orders) ? $orders[0]['id_lang'] : Configuration::get('PS_LANG_DEFAULT')),

			// Add a Private note
			'customer_note' => Tools::htmlentitiesUTF8($customer->note),

			// Messages
			'messages' => $messages,

			// Display hook specified to this page : AdminCustomers
			'hook' => Hook::exec('adminCustomers', array('id_customer' => $customer->id)),

			// Groups
			'groups' => $groups,

			// Orders
			'orders' => $orders,
			'orders_ok' => $orders_ok,
			'orders_ko' => $orders_ko,
			'total_ok' => Tools::displayPrice($total_ok, $this->context->currency->id),

			// Products
			'products' => $products,

			// Addresses
			'addresses' => $customer->getAddresses($this->default_form_language),

			// Discounts
			'discounts' => Discount::getCustomerDiscounts($this->default_form_language, $customer->id, false, false),

			// Carts
			'carts' => $carts,

			// Interested
			'interested' => $interested,

			// Connections
			'connections' => $connections,

			// Referrers
			'referrers' => $referrers,
			'show_toolbar' => true
		);

		return parent::renderView();
	}

	public function processDelete($token)
	{
		if ($this->delete_mode == 'real')
		{
			$this->deleted = false;
			Discount::deleteByIdCustomer((int)Tools::getValue('id_customer'));
		}
		elseif ($this->delete_mode == 'deleted')
			$this->deleted = true;
		else
		{
			$this->_errors[] = Tools::displayError('Unknown delete mode:'.' '.$this->deleted);
			return;
		}

		parent::processDelete($token);
	}

	public function processBulkDelete($token)
	{
		if ($this->delete_mode == 'real')
		{
			$this->deleted = false;
			foreach (Tools::getValue('customerBox') as $id_customer)
				Discount::deleteByIdCustomer((int)$id_customer);
		}
		elseif ($this->delete_mode == 'deleted')
			$this->deleted = true;
		else
		{
			$this->_errors[] = Tools::displayError('Unknown delete mode:'.' '.$this->deleted);
			return;
		}

		parent::processBulkDelete($token);
	}

	public function processSave($token)
	{
		// Check that the new email is not already in use
		// Case add
		if (!$this->id_object)
		{
			$customer_email = strval(Tools::getValue('email'));
			$customer = new Customer();
			$customer->getByEmail($customer_email);
			if ($customer->id)
				$this->_errors[] = Tools::displayError('An account already exists for this e-mail address:').' '.$customer_email;
		}
		// Case update
		else
		{
			$object = new $this->className($this->id_object);
			if (Validate::isLoadedObject($object))
			{
				$customer_email = strval(Tools::getValue('email'));

				// check if e-mail already used
				if ($customer_email != $object->email)
				{
					$customer = new Customer();
					$customer->getByEmail($customer_email);
					if ($customer->id)
						$this->_errors[] = Tools::displayError('An account already exists for this e-mail address:').' '.$customer_email;
				}
			}
			else
				$this->_errors[] = Tools::displayError('An error occurred while loading object.').'
					<b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
		}

		// Check that default group is selected
		if (!is_array(Tools::getValue('groupBox')) || !in_array(Tools::getValue('id_default_group'), Tools::getValue('groupBox')))
			$this->_errors[] = $this->_errors[] = Tools::displayError('Default customer group must be selected in group box.');

		parent::processSave($token);
	}

	/**
	 * Transform a guest account into a registered customer account
	 *
	 * @param string $token
	 */
	public function processGuestToCustomer($token)
	{
		$customer = new Customer((int)Tools::getValue('id_customer'));
		if (!Validate::isLoadedObject($customer))
			$this->_errors[] = Tools::displayError('This customer does not exist.');
		if (Customer::customerExists($customer->email))
			$this->_errors[] = Tools::displayError('This customer already exist as non-guest.');
		else if ($customer->transformToCustomer(Tools::getValue('id_lang', $this->context->language->id)))
			Tools::redirectAdmin(self::$currentIndex.'&'.$this->identifier.'='.$customer->id.'&conf=3&token='.$this->token);
		else
			$this->_errors[] = Tools::displayError('An error occurred while updating customer.');
	}

	/**
	 * Toggle the newsletter flag
	 *
	 * @param string $token
	 */
	public function processChangeNewsletterVal($token)
	{
		$customer = new Customer($this->id_object);
		if (!Validate::isLoadedObject($customer))
			$this->_errors[] = Tools::displayError('An error occurred while updating customer.');
		$update = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'customer` SET newsletter = '.($customer->newsletter ? 0 : 1).' WHERE `id_customer` = '.(int)$customer->id);
		if (!$update)
			$this->_errors[] = Tools::displayError('An error occurred while updating customer.');
		Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
	}

	/**
	 * Toggle newsletter optin flag
	 *
	 * @param string $token
	 */
	public function processChangeOptinVal($token)
	{
		$customer = new Customer($this->id_object);
		if (!Validate::isLoadedObject($customer))
			$this->_errors[] = Tools::displayError('An error occurred while updating customer.');
		$update = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'customer` SET optin = '.($customer->optin ? 0 : 1).' WHERE `id_customer` = '.(int)$customer->id);
		if (!$update)
			$this->_errors[] = Tools::displayError('An error occurred while updating customer.');
		Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = null)
	{
		return parent::getList(
			Context::getContext()->language->id,
			!Tools::getValue($this->table.'Orderby') ? 'date_add' : null,
			!Tools::getValue($this->table.'Orderway') ? 'DESC' : null
		);
	}

	public static function printNewsIcon($id_customer, $tr)
	{
		$customer = new Customer($tr['id_customer']);
		if (!Validate::isLoadedObject($customer))
			return;
		return '<a href="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&changeNewsletterVal&token='.Tools::getAdminTokenLite('AdminCustomers').'">
				'.($customer->newsletter ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').
			'</a>';
	}

	public static function printOptinIcon($id_customer, $tr)
	{
		$customer = new Customer($tr['id_customer']);
		if (!Validate::isLoadedObject($customer))
			return;
		return '<a href="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&changeOptinVal&token='.Tools::getAdminTokenLite('AdminCustomers').'">
				'.($customer->optin ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').
			'</a>';
	}
}


