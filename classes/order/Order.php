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
*  @version  Release: $Revision: 7331 $
*  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class OrderCore extends ObjectModel
{
	/** @var integer Delivery address id */
	public $id_address_delivery;

	/** @var integer Invoice address id */
	public $id_address_invoice;

	public $id_group_shop;

	public $id_shop;

	/** @var integer Cart id */
	public $id_cart;

	/** @var integer Currency id */
	public $id_currency;

	/** @var integer Language id */
	public $id_lang;

	/** @var integer Customer id */
	public $id_customer;

	/** @var integer Carrier id */
	public $id_carrier;

	/** @var string Secure key */
	public $secure_key;

	/** @var string Payment method */
	public $payment;

	/** @var string Payment module */
	public $module;

	/** @var float Currency conversion rate */
	public $conversion_rate;

	/** @var boolean Customer is ok for a recyclable package */
	public $recyclable = 1;

	/** @var boolean True if the customer wants a gift wrapping */
	public $gift = 0;

	/** @var string Gift message if specified */
	public $gift_message;

	/** @var string Shipping number */
	public $shipping_number;

	/** @var float Discounts total */
	public $total_discounts;

	public $total_discounts_tax_incl;
	public $total_discounts_tax_excl;

	/** @var float Total to pay */
	public $total_paid;

	/** @var float Total to pay tax included */
	public $total_paid_tax_incl;

	/** @var float Total to pay tax excluded */
	public $total_paid_tax_excl;

	/** @var float Total really paid @deprecated 1.5.0.1 */
	public $total_paid_real;

	/** @var float Products total */
	public $total_products;

	/** @var float Products total tax included */
	public $total_products_wt;

	/** @var float Shipping total */
	public $total_shipping;

	/** @var float Shipping total tax included */
	public $total_shipping_tax_incl;

	/** @var float Shipping total tax excluded */
	public $total_shipping_tax_excl;

	/** @var float Shipping tax rate */
	public $carrier_tax_rate;

	/** @var float Wrapping total */
	public $total_wrapping;

	/** @var float Wrapping total tax included */
	public $total_wrapping_tax_incl;

	/** @var float Wrapping total tax excluded */
	public $total_wrapping_tax_excl;

	/** @var integer Invoice number */
	public $invoice_number;

	/** @var integer Delivery number */
	public $delivery_number;

	/** @var string Invoice creation date */
	public $invoice_date;

	/** @var string Delivery creation date */
	public $delivery_date;

	/** @var boolean Order validity (paid and not canceled) */
	public $valid;

	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;

	/** @var string Order reference
	 * This reference is not unique, but unique for a payment
	 */
	public $reference;

	/** @var int Id warehouse */
	public $id_warehouse;

	protected $tables = array ('orders');

	protected $fieldsRequired = array('conversion_rate', 'id_address_delivery', 'id_address_invoice', 'id_cart', 'id_currency', 'id_lang', 'id_customer', 'id_carrier', 'payment', 'total_paid', 'total_paid_real', 'total_products', 'total_products_wt');
	protected $fieldsValidate = array(
		'id_address_delivery' => 'isUnsignedId',
		'id_address_invoice' => 'isUnsignedId',
		'id_cart' => 'isUnsignedId',
		'id_currency' => 'isUnsignedId',
		'id_group_shop' => 'isUnsignedId',
		'id_shop' => 'isUnsignedId',
		'id_lang' => 'isUnsignedId',
		'id_customer' => 'isUnsignedId',
		'id_carrier' => 'isUnsignedId',
		'id_warehouse' => 'isUnsignedId',
		'secure_key' => 'isMd5',
		'payment' => 'isGenericName',
		'recyclable' => 'isBool',
		'gift' => 'isBool',
		'gift_message' => 'isMessage',
		'total_discounts' => 'isPrice',
		'total_discounts_tax_incl' => 'isPrice',
		'total_discounts_tax_excl' => 'isPrice',
		'total_paid' => 'isPrice',
		'total_paid_tax_incl' => 'isPrice',
		'total_paid_tax_excl' => 'isPrice',
		'total_paid_real' => 'isPrice',
		'total_products' => 'isPrice',
		'total_products_wt' => 'isPrice',
		'total_shipping' => 'isPrice',
		'total_shipping_tax_incl' => 'isPrice',
		'total_shipping_tax_excl' => 'isPrice',
		'carrier_tax_rate' => 'isFloat',
		'total_wrapping' => 'isPrice',
		'total_wrapping_tax_incl' => 'isPrice',
		'total_wrapping_tax_excl' => 'isPrice',
		'shipping_number' => 'isUrl',
		'conversion_rate' => 'isFloat'
	);

	protected $webserviceParameters = array(
		'objectMethods' => array('add' => 'addWs'),
		'objectNodeName' => 'order',
		'objectsNodeName' => 'orders',
		'fields' => array(
			'id_address_delivery' => array('xlink_resource'=> 'addresses'),
			'id_address_invoice' => array('xlink_resource'=> 'addresses'),
			'id_cart' => array('xlink_resource'=> 'carts'),
			'id_currency' => array('xlink_resource'=> 'currencies'),
			'id_lang' => array('xlink_resource'=> 'languages'),
			'id_customer' => array('xlink_resource'=> 'customers'),
			'id_carrier' => array('xlink_resource'=> 'carriers'),
			'module' => array('required' => true),
			'invoice_number' => array(),
			'invoice_date' => array(),
			'delivery_number' => array(),
			'delivery_date' => array(),
			'valid' => array(),
			'current_state' => array('getter' => 'getCurrentState', 'setter' => 'setCurrentState', 'xlink_resource'=> 'order_states'),
			'date_add' => array(),
			'date_upd' => array(),
		),
		'associations' => array(
			'order_rows' => array('resource' => 'order_row', 'setter' => false, 'virtual_entity' => true,
				'fields' => array(
					'id' =>  array(),
					'product_id' => array('required' => true),
					'product_attribute_id' => array('required' => true),
					'product_quantity' => array('required' => true),
					'product_name' => array('setter' => false),
					'product_price' => array('setter' => false),
			)),
		),

	);

	/* MySQL does not allow 'order' for a table name */
	protected $table = 'orders';
	protected $identifier = 'id_order';
	protected $_taxCalculationMethod = PS_TAX_EXC;

	protected static $_historyCache = array();

	public function getFields()
	{
		if (!$this->id_lang)
			$this->id_lang = Configuration::get('PS_LANG_DEFAULT');

		$this->validateFields();

		$fields['id_group_shop'] = (int)$this->id_group_shop;
		$fields['id_shop'] = (int)$this->id_shop;
		$fields['id_address_delivery'] = (int)($this->id_address_delivery);
		$fields['id_address_invoice'] = (int)($this->id_address_invoice);
		$fields['id_cart'] = (int)$this->id_cart;
		$fields['id_currency'] = (int)($this->id_currency);
		$fields['id_lang'] = (int)($this->id_lang);
		$fields['id_customer'] = (int)($this->id_customer);
		$fields['id_carrier'] = (int)($this->id_carrier);
		$fields['secure_key'] = pSQL($this->secure_key);
		$fields['payment'] = pSQL($this->payment);
		$fields['module'] = pSQL($this->module);
		$fields['conversion_rate'] = (float)($this->conversion_rate);
		$fields['recyclable'] = (int)($this->recyclable);
		$fields['gift'] = (int)($this->gift);
		$fields['gift_message'] = pSQL($this->gift_message);
		$fields['shipping_number'] = pSQL($this->shipping_number);
		$fields['total_discounts'] = (float)($this->total_discounts);
		$fields['total_discounts_tax_incl'] = (float)($this->total_discounts_tax_incl);
		$fields['total_discounts_tax_excl'] = (float)($this->total_discounts_tax_excl);
		$fields['total_paid'] = (float)($this->total_paid);
		$fields['total_paid_tax_incl'] = (float)($this->total_paid_tax_incl);
		$fields['total_paid_tax_excl'] = (float)($this->total_paid_tax_excl);
		$fields['total_paid_real'] = (float)($this->total_paid_real);
		$fields['total_products'] = (float)($this->total_products);
		$fields['total_products_wt'] = (float)($this->total_products_wt);
		$fields['total_shipping'] = (float)($this->total_shipping);
		$fields['total_shipping_tax_incl'] = (float)($this->total_shipping_tax_incl);
		$fields['total_shipping_tax_excl'] = (float)($this->total_shipping_tax_excl);
		$fields['carrier_tax_rate'] = (float)($this->carrier_tax_rate);
		$fields['total_wrapping'] = (float)($this->total_wrapping);
		$fields['total_wrapping_tax_incl'] = (float)($this->total_wrapping_tax_incl);
		$fields['total_wrapping_tax_excl'] = (float)($this->total_wrapping_tax_excl);
		$fields['invoice_number'] = (int)($this->invoice_number);
		$fields['delivery_number'] = (int)($this->delivery_number);
		$fields['invoice_date'] = pSQL($this->invoice_date);
		$fields['delivery_date'] = pSQL($this->delivery_date);
		$fields['valid'] = (int)($this->valid) ? 1 : 0;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['reference'] = pSQL($this->reference);
		$fields['id_warehouse'] = pSQL($this->id_warehouse);

		return $fields;
	}

	public function __construct($id = NULL, $id_lang = NULL)
	{
		parent::__construct($id, $id_lang);
		if ($this->id_customer)
		{
			$customer = new Customer((int)($this->id_customer));
			$this->_taxCalculationMethod = Group::getPriceDisplayMethod((int)($customer->id_default_group));
		}
		else
			$this->_taxCalculationMethod = Group::getDefaultPriceDisplayMethod();
	}

	public function getTaxCalculationMethod()
	{
		return (int)($this->_taxCalculationMethod);
	}

	/* Does NOT delete a product but "cancel" it (which means return/refund/delete it depending of the case) */
	public function deleteProduct($order, $orderDetail, $quantity)
	{
		if (!(int)($this->getCurrentState()))
			return false;

		if ($this->hasBeenDelivered())
		{
			if (!Configuration::get('PS_ORDER_RETURN'))
				die(Tools::displayError());
			$orderDetail->product_quantity_return += (int)($quantity);
			return $orderDetail->update();
		}
		elseif ($this->hasBeenPaid())
		{
			$orderDetail->product_quantity_refunded += (int)($quantity);
			return $orderDetail->update();
		}
		return $this->_deleteProduct($orderDetail, (int)($quantity));
	}

	/* DOES delete the product */
	protected function _deleteProduct($orderDetail, $quantity)
	{
		$tax_calculator = $orderDetail->getTaxCalculator();

		$price = $tax_calculator->addTaxes($orderDetail->product_price);
		if ($orderDetail->reduction_percent != 0.00)
			$reduction_amount = $price * $orderDetail->reduction_percent / 100;
		elseif ($orderDetail->reduction_amount != '0.000000')
			$reduction_amount = Tools::ps_round($orderDetail->reduction_amount, 2);
		if (isset($reduction_amount) AND $reduction_amount)
			$price = Tools::ps_round($price - $reduction_amount, 2);
		$productPriceWithoutTax = number_format($tax_calculator->removeTaxes($price), 2, '.', '');
		$price += Tools::ps_round($orderDetail->ecotax * (1 + $orderDetail->ecotax_tax_rate / 100), 2);
		$productPrice = number_format($quantity * $price, 2, '.', '');
		/* Update cart */
		$cart = new Cart($this->id_cart);
		$cart->updateQty($quantity, $orderDetail->product_id, $orderDetail->product_attribute_id, false, 0, 'down'); // customization are deleted in deleteCustomization
		$cart->update();

		/* Update order */
		$shippingDiff = $this->total_shipping - $cart->getOrderShippingCost();
		$this->total_products -= $productPriceWithoutTax;

		// After upgrading from old version
		// total_products_wt is null
		// removing a product made order total negative
		// and don't recalculating totals (on getTotalProductsWithTaxes)
		if ($this->total_products_wt != 0)
		$this->total_products_wt -= $productPrice;

		$this->total_shipping = $cart->getTotalShippingCost();

		/* It's temporary fix for 1.3 version... */
		if ($orderDetail->product_quantity_discount != '0.000000')
			$this->total_paid -= ($productPrice + $shippingDiff);
		else
			$this->total_paid = $cart->getOrderTotal();

		$this->total_paid_real -= ($productPrice + $shippingDiff);

		/* Prevent from floating precision issues (total_products has only 2 decimals) */
		if ($this->total_products < 0)
			$this->total_products = 0;

		if ($this->total_paid < 0)
			$this->total_paid = 0;

		if ($this->total_paid_real < 0)
			$this->total_paid_real = 0;

		/* Prevent from floating precision issues */
		$this->total_paid = number_format($this->total_paid, 2, '.', '');
		$this->total_paid_real = number_format($this->total_paid_real, 2, '.', '');
		$this->total_products = number_format($this->total_products, 2, '.', '');
		$this->total_products_wt = number_format($this->total_products_wt, 2, '.', '');

		/* Update order detail */
		$orderDetail->product_quantity -= (int)($quantity);

		if (!$orderDetail->product_quantity)
		{
			if (!$orderDetail->delete())
				return false;
			if (count($this->getProductsDetail()) == 0)
			{
				$history = new OrderHistory();
				$history->id_order = (int)($this->id);
				$history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), (int)($this->id));
				if (!$history->addWithemail())
					return false;
			}
			return $this->update();
		}
		return $orderDetail->update() AND $this->update();
	}

	public function deleteCustomization($id_customization, $quantity, $orderDetail)
	{
		if (!(int)($this->getCurrentState()))
			return false;

		if ($this->hasBeenDelivered())
			return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity_returned` = `quantity_returned` + '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id));
		elseif ($this->hasBeenPaid())
			return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity_refunded` = `quantity_refunded` + '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id));
		if (!Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity` = `quantity` - '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id)))
			return false;
		if (!Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'customization` WHERE `quantity` = 0'))
			return false;
		return $this->_deleteProduct($orderDetail, (int)($quantity));
	}

	/**
	 * Get order history
	 *
	 * @param integer $id_lang Language id
	 *
	 * @return array History entries ordered by date DESC
	 */
	public function getHistory($id_lang, $id_order_state = false, $no_hidden = false)
	{
		if (!$id_order_state)
			$id_order_state = 0;

		if (!isset(self::$_historyCache[$this->id.'_'.$id_order_state]) OR $no_hidden)
		{
			$id_lang = $id_lang ? (int)($id_lang) : 'o.`id_lang`';
			$result = Db::getInstance()->executeS('
			SELECT oh.*, e.`firstname` AS employee_firstname, e.`lastname` AS employee_lastname, osl.`name` AS ostate_name
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON o.`id_order` = oh.`id_order`
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'employee` e ON e.`id_employee` = oh.`id_employee`
			WHERE oh.id_order = '.(int)($this->id).'
			'.($no_hidden ? ' AND os.hidden = 0' : '').'
			'.((int)($id_order_state) ? ' AND oh.`id_order_state` = '.(int)($id_order_state) : '').'
			ORDER BY oh.date_add DESC, oh.id_order_history DESC');
			if ($no_hidden)
				return $result;
			self::$_historyCache[$this->id.'_'.$id_order_state] = $result;
		}
		return self::$_historyCache[$this->id.'_'.$id_order_state];
	}

	public function getProductsDetail()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_detail` od
		WHERE od.`id_order` = '.(int)($this->id));
	}

	public function getFirstMessage()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `message`
			FROM `'._DB_PREFIX_.'message`
			WHERE `id_order` = '.(int)$this->id.'
			ORDER BY `id_message`
		');
	}

	/**
	 *
	 * @deprecated 1.5.0.1
	 * @see OrderInvoice::setProductPrices
	 */
	public function setProductPrices(&$row)
	{
		//Tools::displayAsDeprecated();
		$tax_calculator = OrderDetail::getTaxCalculatorStatic((int)$row['id_order_detail']);
		$row['tax_calculator'] = $tax_calculator;
		$row['tax_rate'] = $tax_calculator->getTotalRate();

		if ($this->_taxCalculationMethod == PS_TAX_EXC)
			$row['product_price'] = Tools::ps_round($row['product_price'], 2);
		else
			$row['product_price_wt'] = Tools::ps_round($tax_calculator->addTaxes($row['product_price']), 2);

		$group_reduction = 1;
		if ($row['group_reduction'] > 0)
			$group_reduction =  1 - $row['group_reduction'] / 100;

		if ($row['reduction_percent'] != 0)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
				$row['product_price'] = ($row['product_price'] - $row['product_price'] * ($row['reduction_percent'] * 0.01));
			else
				$row['product_price_wt'] = Tools::ps_round(($row['product_price_wt'] - $row['product_price_wt'] * ($row['reduction_percent'] * 0.01)), 2);
		}

		if ($row['reduction_amount'] != 0)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
				$row['product_price'] = ($row['product_price'] - ($tax_calculator->removeTaxes($row['reduction_amount'])));
			else
				$row['product_price_wt'] = Tools::ps_round(($row['product_price_wt'] - $row['reduction_amount']), 2);
		}

		if ($row['group_reduction'] > 0)
		{
			if ($this->_taxCalculationMethod == PS_TAX_EXC)
				$row['product_price'] = $row['product_price'] * $group_reduction;
			else
				$row['product_price_wt'] = Tools::ps_round($row['product_price_wt'] * $group_reduction , 2);
		}

		if (($row['reduction_percent'] OR $row['reduction_amount'] OR $row['group_reduction']) AND $this->_taxCalculationMethod == PS_TAX_EXC)
			$row['product_price'] = Tools::ps_round($row['product_price'], 2);

		if ($this->_taxCalculationMethod == PS_TAX_EXC)
			$row['product_price_wt'] = Tools::ps_round($tax_calculator->addTaxes($row['product_price']), 2) + Tools::ps_round($row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100), 2);
		else
		{
			$row['product_price_wt_but_ecotax'] = $row['product_price_wt'];
			$row['product_price_wt'] = Tools::ps_round($row['product_price_wt'] + $row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100), 2);
		}

		$row['total_wt'] = $row['product_quantity'] * $row['product_price_wt'];
		$row['total_price'] = $row['product_quantity'] * $row['product_price'];
	}


	/**
	 * Get order products
	 *
	 * @return array Products with price, quantity (with taxe and without)
	 */
	public function getProducts($products = false, $selectedProducts = false, $selectedQty = false)
	{
		if (!$products)
			$products = $this->getProductsDetail();

		$customized_datas = Product::getAllCustomizedDatas($this->id_cart);

		$resultArray = array();
		foreach ($products AS $row)
		{
			// Change qty if selected
			if ($selectedQty)
			{
				$row['product_quantity'] = 0;
				foreach ($selectedProducts AS $key => $id_product)
					if ($row['id_order_detail'] == $id_product)
						$row['product_quantity'] = (int)($selectedQty[$key]);
				if (!$row['product_quantity'])
					continue ;
			}

			$this->setProductImageInformations($row);
			$this->setProductCurrentStock($row);
			$this->setProductPrices($row);
			$this->setProductCustomizedDatas($row, $customized_datas);

			// Add information for virtual product
			if ($row['download_hash'] && !empty($row['download_hash']))
			{
				if ($row['product_attribute_id'] && !empty($row['product_attribute_id']))
					$row['filename'] = ProductDownload::getFilenameFromIdAttribute((int)$row['product_id'], (int)$row['product_attribute_id']);
				else
					$row['filename'] = ProductDownload::getFilenameFromIdProduct((int)$row['product_id']);
				// Get the display filename
				$row['display_filename'] = ProductDownload::getFilenameFromFilename($row['filename']);
			}
			/* Stock product */
			$resultArray[(int)$row['id_order_detail']] = $row;
		}

		if ($customized_datas)
			Product::addCustomizationPrice($resultArray, $customized_datas);

		return $resultArray;
	}

	protected function setProductCustomizedDatas(&$product, $customized_datas)
	{
		$product['customizedDatas'] = null;
		if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']]))
			$product['customizedDatas'] = $customized_datas[$product['product_id']][$product['product_attribute_id']];
		else
			$product['customizationQuantityTotal'] = 0;
	}

	/**
	 *
	 * This method allow to add stock information on a product detail
	 * @param array &$product
	 */
	protected function setProductCurrentStock(&$product)
	{
		$product['current_stock'] = StockManagerFactory::getManager()->getProductPhysicalQuantities($product['product_id'], $product['product_attribute_id'], null, true);
	}

	/**
	 *
	 * This method allow to add image information on a product detail
	 * @param array &$product
	 */
	protected function setProductImageInformations(&$product)
	{
		if (isset($product['product_attribute_id']) && $product['product_attribute_id'])
			$id_image = Db::getInstance()->getValue('
				SELECT id_image
				FROM '._DB_PREFIX_.'product_attribute_image
				WHERE id_product_attribute = '.(int)$product['product_attribute_id']);

		if (!isset($image['id_image']) || !$image['id_image'])
			$id_image = Db::getInstance()->getValue('
				SELECT id_image
				FROM '._DB_PREFIX_.'image
				WHERE id_product = '.(int)($product['product_id']).' AND cover = 1
			');

		$product['image'] = null;
		$product['image_size'] = null;

		if ($id_image)
			$product['image'] = new Image($id_image);
	}

	public function getTaxesAverageUsed()
	{
		return Cart::getTaxesAverageUsed((int)($this->id_cart));
	}

	/**
	 * Count virtual products in order
	 *
	 * @return int number of virtual products
	 */
	public function getVirtualProducts()
	{
		$sql = '
			SELECT `product_id`, `product_attribute_id`, `download_hash`, `download_deadline`
			FROM `'._DB_PREFIX_.'order_detail` od
			WHERE od.`id_order` = '.(int)($this->id).'
				AND `download_hash` <> \'\'';
		return Db::getInstance()->executeS($sql);
	}

	/**
	* Check if order contains (only) virtual products
	*
	* @param boolean $strict If false return true if there are at least one product virtual
	* @return boolean true if is a virtual order or false
	*
	*/
	public function isVirtual($strict = true)
	{
		$products = $this->getProducts();
		if (count($products) < 1)
			return false;
		$virtual = true;
		foreach ($products AS $product)
		{
			$pd = ProductDownload::getIdFromIdProduct((int)($product['product_id']));
			if ($pd AND Validate::isUnsignedInt($pd) AND $product['download_hash'] AND $product['display_filename'] != '')
			{
				if ($strict === false)
					return true;
			}
			else
				$virtual &= false;
		}
		return $virtual;
	}

	/**
	 * @deprecated 1.5.0.1
	 */
	public function getDiscounts($details = false)
	{
		Tools::displayAsDeprecated();
		return Order::getCartRules();
	}

	public function getCartRules()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT *
		FROM `'._DB_PREFIX_.'order_cart_rule` ocr
		LEFT JOIN `'._DB_PREFIX_.'cart_rule` cr ON cr.`id_cart_rule` = ocr.`id_cart_rule`
		WHERE ocr.`id_order` = '.(int)$this->id);
	}

	public static function getDiscountsCustomer($id_customer, $id_cart_rule)
	{
		return Db::getInstance()->getValue('
		SELECT COUNT(*) FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN '._DB_PREFIX_.'order_cart_rule ocr ON (ocr.id_order = o.id_order)
		WHERE o.id_customer = '.(int)$id_customer.'
		AND ocr.id_cart_rule = '.(int)$id_cart_rule);
	}

	/**
	 * Get current order state (eg. Awaiting payment, Delivered...)
	 *
	 * @return array Order state details
	 */
	public function getCurrentState()
	{
		$orderHistory = OrderHistory::getLastOrderState($this->id);
		if (!isset($orderHistory) OR !$orderHistory)
			return false;
		return $orderHistory->id;
	}

	/**
	 * Get current order state name (eg. Awaiting payment, Delivered...)
	 *
	 * @return array Order state details
	 */
	public function getCurrentStateFull($id_lang)
	{
		return Db::getInstance()->getRow('
		SELECT oh.`id_order_state`, osl.`name`, os.`logable`, os.`shipped`
		FROM `'._DB_PREFIX_.'order_history` oh
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		WHERE osl.`id_lang` = '.(int)($id_lang).' AND oh.`id_order` = '.(int)($this->id).'
		ORDER BY `date_add` DESC, `id_order_history` DESC');
	}

	public function hasBeenDelivered()
	{
		return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_DELIVERED')));
	}

	public function hasBeenPaid()
	{
		return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PAYMENT')));
	}

	public function hasBeenShipped()
	{
		return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_SHIPPING')));
	}

	public function isInPreparation()
	{
		return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PREPARATION')));
	}

	/**
	 * Get customer orders
	 *
	 * @param integer $id_customer Customer id
	 * @param boolean $showHiddenStatus Display or not hidden order statuses
	 * @return array Customer orders
	 */
	static public function getCustomerOrders($id_customer, $showHiddenStatus = false, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();

		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT o.*, (SELECT SUM(od.`product_quantity`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = o.`id_order`) nb_products
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.`id_customer` = '.(int)$id_customer.'
		GROUP BY o.`id_order`
		ORDER BY o.`date_add` DESC');
		if (!$res)
			return array();

		foreach ($res AS $key => $val)
		{
			$res2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT os.`id_order_state`, osl.`name` AS order_state, os.`invoice`
				FROM `'._DB_PREFIX_.'order_history` oh
				LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
				INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$context->language->id.')
			WHERE oh.`id_order` = '.(int)($val['id_order']).(!$showHiddenStatus ? ' AND os.`hidden` != 1' : '').'
				ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
			LIMIT 1');

			if ($res2)
				$res[$key] = array_merge($res[$key], $res2[0]);

		}
		return $res;
	}

	public static function getOrdersIdByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
	{
		$sql = 'SELECT `id_order`
				FROM `'._DB_PREFIX_.'orders`
				WHERE DATE_ADD(date_upd, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\' AND date_upd >= \''.pSQL($date_from).'\'
					'.Context::getContext()->shop->addSqlRestriction()
					.($type ? ' AND '.pSQL(strval($type)).'_number != 0' : '')
					.($id_customer ? ' AND id_customer = '.(int)($id_customer) : '');
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		$orders = array();
		foreach ($result AS $order)
			$orders[] = (int)($order['id_order']);
		return $orders;
	}

	static public function getOrdersWithInformations($limit = NULL, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();

		$sql = 'SELECT *, (
					SELECT `name`
					FROM `'._DB_PREFIX_.'order_history` oh
					LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
					WHERE oh.`id_order` = o.`id_order`
					AND osl.`id_lang` = '.(int)$context->language->id.'
					ORDER BY oh.`date_add` DESC
					LIMIT 1
				) AS `state_name`
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
				WHERE 1
					'.Context::getContext()->shop->addSqlRestriction(false, 'o').'
				ORDER BY o.`date_add` DESC
				'.((int)$limit ? 'LIMIT 0, '.(int)$limit : '');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}

	public static function getOrdersIdInvoiceByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
	{
		$sql = 'SELECT `id_order`
				FROM `'._DB_PREFIX_.'orders`
				WHERE DATE_ADD(invoice_date, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\' AND invoice_date >= \''.pSQL($date_from).'\'
					'.Context::getContext()->shop->addSqlRestriction()
					.($type ? ' AND '.pSQL(strval($type)).'_number != 0' : '')
					.($id_customer ? ' AND id_customer = '.(int)($id_customer) : '').
				' ORDER BY invoice_date ASC';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		$orders = array();
		foreach ($result AS $order)
			$orders[] = (int)($order['id_order']);
		return $orders;
	}

	public static function getOrderIdsByStatus($id_order_state)
	{
		$sql = 'SELECT id_order
				FROM '._DB_PREFIX_.'orders o
				WHERE '.(int)$id_order_state.' = (
					SELECT id_order_state
					FROM '._DB_PREFIX_.'order_history oh
					WHERE oh.id_order = o.id_order
					ORDER BY date_add DESC, id_order_history DESC
					LIMIT 1
				)
				'.Context::getContext()->shop->addSqlRestriction(false, 'o').'
				ORDER BY invoice_date ASC';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		$orders = array();
		foreach ($result AS $order)
			$orders[] = (int)($order['id_order']);
		return $orders;
	}

	/**
	 * Get product total without taxes
	 *
	 * @return Product total with taxes
	 */
	public function getTotalProductsWithoutTaxes($products = false)
	{
		return $this->total_products;
	}

	/**
	 * Get product total with taxes
	 *
	 * @return Product total with taxes
	 */
	public function getTotalProductsWithTaxes($products = false)
	{
		if ($this->total_products_wt != '0.00' AND !$products)
			return $this->total_products_wt;
		/* Retro-compatibility (now set directly on the validateOrder() method) */

		if (!$products)
			$products = $this->getProductsDetail();

		$return = 0;

		foreach ($products AS $row)
		{
			if (!isset($row['tax_rate']))
				$row['tax_rate'] = 0;

			$price = Tools::ps_round($row['product_price'] * (1 + $row['tax_rate'] / 100), 2);
			if ($row['reduction_percent'])
				$price -= $price * ($row['reduction_percent'] * 0.01);
			if ($row['reduction_amount'])
				$price -= $row['reduction_amount'] * (1 + ($row['tax_rate'] * 0.01));
			if ($row['group_reduction'])
				$price -= $price * ($row['group_reduction'] * 0.01);
			$price += $row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100);
			$return += Tools::ps_round($price, 2) * $row['product_quantity'];
		}
		if (!$products)
		{
			$this->total_products_wt = $return;
			$this->update();
		}
		return $return;
	}

	/**
	 * Get customer orders number
	 *
	 * @param integer $id_customer Customer id
	 * @return array Customer orders number
	 */
	public static function getCustomerNbOrders($id_customer)
	{
		$sql = 'SELECT COUNT(`id_order`) AS nb
				FROM `'._DB_PREFIX_.'orders`
				WHERE `id_customer` = '.(int)$id_customer
					.Context::getContext()->shop->addSqlRestriction();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

		return isset($result['nb']) ? $result['nb'] : 0;
	}

	/**
	 * Get an order by its cart id
	 *
	 * @param integer $id_cart Cart id
	 * @return array Order details
	 */
	public static function getOrderByCartId($id_cart)
	{
		$sql = 'SELECT `id_order`
				FROM `'._DB_PREFIX_.'orders`
				WHERE `id_cart` = '.(int)($id_cart)
					.Context::getContext()->shop->addSqlRestriction();
		$result = Db::getInstance()->getRow($sql);

		return isset($result['id_order']) ? $result['id_order'] : false;
	}

	/**
	 * @deprecated 1.5.0.1
	 */
	public function addDiscount($id_cart_rule, $name, $value)
	{
		Tools::displayAsDeprecated();
		return Order::addCartRule($id_cart_rule, $name, $value);
	}

	public function addCartRule($id_cart_rule, $name, $value)
	{
		return Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_cart_rule', array('id_order' => (int)$this->id, 'id_cart_rule' => (int)$id_cart_rule, 'name' => pSQL($name), 'value' => (float)$value), 'INSERT');
	}

	public function getNumberOfDays()
	{
		$nbReturnDays = (int)(Configuration::get('PS_ORDER_RETURN_NB_DAYS'));
		if (!$nbReturnDays)
			return true;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT TO_DAYS(NOW()) - TO_DAYS(`delivery_date`)  AS days FROM `'._DB_PREFIX_.'orders`
		WHERE `id_order` = '.(int)($this->id));
		if ($result['days'] <= $nbReturnDays)
			return true;
		return false;
	}


	public function isReturnable()
	{
		$payment = $this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PAYMENT'));
		$delivred = $this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_DELIVERED'));
		if ($payment AND $delivred AND strtotime($delivred[0]['date_add']) < strtotime($payment[0]['date_add']))
			return ((int)(Configuration::get('PS_ORDER_RETURN')) == 1 AND $this->getNumberOfDays());
		else
			return ((int)(Configuration::get('PS_ORDER_RETURN')) == 1 AND (int)($this->getCurrentState()) == Configuration::get('PS_OS_DELIVERED') AND $this->getNumberOfDays());
	}

    public static function getLastInvoiceNumber()
    {
		return Db::getInstance()->getValue('
			SELECT MAX(`number`)
			FROM `'._DB_PREFIX_.'order_invoice`
		');
	}

	public function setInvoice()
	{
		$order_invoice = new OrderInvoice();
		$order_invoice->id_order = $this->id;
		$order_invoice->number = Configuration::get('PS_INVOICE_START_NUMBER');
		// If invoice start number has been set, you clean the value of this configuration
		if ($order_invoice->number)
			Configuration::updateValue('PS_INVOICE_START_NUMBER', false);
		else
			$order_invoice->number = self::getLastInvoiceNumber() + 1;

		$order_invoice->total_discount_tax_excl = $this->total_discount_tax_excl;
		$order_invoice->total_discount_tax_incl = $this->total_discount_tax_incl;
		$order_invoice->total_paid_tax_excl = $this->total_paid_tax_excl;
		$order_invoice->total_paid_tax_incl = $this->total_paid_tax_incl;
		$order_invoice->total_products = $this->total_products;
		$order_invoice->total_products_wt = $this->total_products_wt;
		$order_invoice->total_shipping_tax_excl = $this->total_shipping_tax_excl;
		$order_invoice->total_shipping_tax_incl = $this->total_shipping_tax_incl;
		$order_invoice->total_wrapping_tax_excl = $this->total_wrapping_tax_excl;
		$order_invoice->total_wrapping_tax_incl = $this->total_wrapping_tax_incl;

		// Save Order invoice
		$order_invoice->add();

		// Update order_carrier
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'order_carrier`
			SET `id_order_invoice` = '.(int)$order_invoice->id.'
			WHERE `id_order` = '.(int)$order_invoice->id_order);

		// Update order detail
		Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'order_detail`
			SET `id_order_invoice` = '.(int)$order_invoice->id.'
			WHERE `id_order` = '.(int)$order_invoice->id_order);

		$this->invoice_date = $res['invoice_date'];
		$this->invoice_number = $res['invoice_number'];
	}

	public function setDelivery()
	{
		// Set delivery number
		$number = (int)(Configuration::get('PS_DELIVERY_NUMBER'));
		if (!(int)($number))
			die(Tools::displayError('Invalid delivery number'));
		$this->delivery_number = $number;
		Configuration::updateValue('PS_DELIVERY_NUMBER', $number + 1);

		// Set delivery date
		$this->delivery_date = date('Y-m-d H:i:s');

		// Update object
		$this->update();
	}

	public static function printPDFIcons($id_order, $tr)
	{
		$order = new Order($id_order);
		$orderState = OrderHistory::getLastOrderState($id_order);
		if (!Validate::isLoadedObject($orderState) OR !Validate::isLoadedObject($order))
			die(Tools::displayError('Invalid objects'));
		echo '<span style="width:20px; margin-right:5px;">';
		if (($orderState->invoice AND $order->invoice_number) AND (int)($tr['product_number']))
			echo '<a href="pdf.php?id_order='.(int)($order->id).'&pdf"><img src="../img/admin/tab-invoice.gif" alt="invoice" /></a>';
		else
			echo '&nbsp;';
		echo '</span>';
		echo '<span style="width:20px;">';
		if ($orderState->delivery AND $order->delivery_number)
			echo '<a href="pdf.php?id_delivery='.(int)($order->delivery_number).'"><img src="../img/admin/delivery.gif" alt="delivery" /></a>';
		else
			echo '&nbsp;';
		echo '</span>';
	}

	public static function getByDelivery($id_delivery)
	{
		$sql = 'SELECT id_order
				FROM `'._DB_PREFIX_.'orders`
				WHERE `delivery_number` = '.(int)($id_delivery).'
					'.Context::getContext()->shop->addSqlRestriction();
		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		return new Order((int)($res['id_order']));
	}

	public function getTotalWeight()
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT SUM(product_weight * product_quantity) weight
		FROM '._DB_PREFIX_.'order_detail
		WHERE id_order = '.(int)($this->id));

		return (float)($result['weight']);
	}

	/**
	 *
	 * @param int $id_invoice
	 * @deprecated 1.5.0.1
	 */
	public static function getInvoice($id_invoice)
	{
		Tools::displayAsDeprecated();
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `invoice_number`, `id_order`
		FROM `'._DB_PREFIX_.'orders`
		WHERE invoice_number = '.(int)($id_invoice));
	}

	public function isAssociatedAtGuest($email)
	{
		if (!$email)
			return false;
		$sql = 'SELECT COUNT(*)
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
				WHERE o.`id_order` = '.(int)$this->id.'
					AND c.`email` = \''.pSQL($email).'\'
					AND c.`is_guest` = 1
					'.Context::getContext()->shop->addSqlRestriction(false, 'c');
		return (bool)Db::getInstance()->getValue($sql);
	}

	/**
	 * @param int $id_order
	 * @param int $id_customer optionnal
	 * @return int id_cart
	 */
	public static function getCartIdStatic($id_order, $id_customer = 0)
	{
		return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_cart`
			FROM `'._DB_PREFIX_.'orders`
			WHERE `id_order` = '.(int)$id_order.'
			'.($id_customer ? 'AND `id_customer` = '.(int)$id_customer : ''));
	}

	public function getWsOrderRows()
	{
		$query = 'SELECT id_order_detail as `id`, `product_id`, `product_price`, `id_order`, `product_attribute_id`, `product_quantity`, `product_name`
		FROM `'._DB_PREFIX_.'order_detail`
		WHERE id_order = '.(int)$this->id;
		$result = Db::getInstance()->executeS($query);
		return $result;
	}

	/** Set current order state
	 * @param int $id_order_state
	 * @param int $id_employee (/!\ not optional except for Webservice.
	 */
	public function setCurrentState($id_order_state, $id_employee = 0)
	{
		if (empty($id_order_state))
			return false;
		$history = new OrderHistory();
		$history->id_order = (int)$this->id;
		$history->id_employee = (int)$id_employee;
		$history->changeIdOrderState((int)$id_order_state, (int)$this->id);
		$res = Db::getInstance()->getRow('
			SELECT `invoice_number`, `invoice_date`, `delivery_number`, `delivery_date`
			FROM `'._DB_PREFIX_.'orders`
			WHERE `id_order` = '.(int)$this->id);
		$this->invoice_date = $res['invoice_date'];
		$this->invoice_number = $res['invoice_number'];
		$this->delivery_date = $res['delivery_date'];
		$this->delivery_number = $res['delivery_number'];
		$history->addWithemail();
	}

	public function addWs($autodate = true, $nullValues = false)
	{
		$paymentModule = Module::getInstanceByName($this->module);
		$customer = new Customer($this->id_customer);
		$paymentModule->validateOrder($this->id_cart, Configuration::get('PS_OS_WS_PAYMENT'), $this->total_paid, $this->payment, NULL, array(), null, false, $customer->secure_key);
		$this->id = $paymentModule->currentOrder;
		return true;
	}

	public function deleteAssociations()
	{
		return (Db::getInstance()->execute('
				DELETE FROM `'._DB_PREFIX_.'order_detail`
				WHERE `id_order` = '.(int)($this->id)) !== false);
	}

	/**
	 * This method return the ID of the previous order
	 * @since 1.5.0.1
	 * @return int
	 */
	public function getPreviousOrderId()
	{
		return Db::getInstance()->getValue('
			SELECT id_order
			FROM '._DB_PREFIX_.'orders
			WHERE id_order < '.(int)$this->id.'
			ORDER BY id_order DESC');
	}

	/**
	 * This method return the ID of the next order
	 * @since 1.5.0.1
	 * @return int
	 */
	public function getNextOrderId()
	{
		return Db::getInstance()->getValue('
		SELECT id_order
		FROM '._DB_PREFIX_.'orders
		WHERE id_order > '.(int)$this->id.'
		ORDER BY id_order ASC');
	}

	/**
	 * Get the an order detail list of the current order
	 * @return array
	 */
	public function getOrderDetailList()
	{
		return OrderDetail::getList($this->id);
	}

	/**
	 * Gennerate a unique reference for orders generated with the same cart id
	 * This references, is usefull for check payment
	 *
	 * @return String
	 */
	public static function generateReference()
	{
		// To generate a random reference, we first generate a random number
		// This number is a rand concated with the current timestamp
		return strtoupper(Tools::passwdGen(9)); // lol Max !
	}

	public function orderContainProduct($id_product)
	{
		$product_list = $this->getOrderDetailList();
		foreach ($product_list as $product)
			if ($product['product_id'] == (int)$id_product)
				return true;
		return false;
	}
	/**
	 * This method returns true if at least one order details uses the
	 * One After Another tax computation method.
	 *
	 * @since 1.5.0.1
	 * @return boolean
	 */
	public function useOneAfterAnotherTaxComputationMethod()
	{
		// if one of the order details use the tax computation method the display will be different
		return Db::getInstance()->getValue('
		SELECT od.`tax_computation_method`
		FROM `'._DB_PREFIX_.'order_detail_tax` odt
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
		WHERE od.`id_order` = '.(int)$this->id.'
		AND od.`tax_computation_method` = '.(int)TaxCalculator::ONE_AFTER_ANOTHER_METHOD
		);
	}

	/**
	 * This method allows to get all Order Payment for the current order
	 * @since 1.5.0.1
	 * @return array Collection of Order Payment
	 */
	public function getOrderPaymentCollection()
	{
		$order_payment = Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'order_payment`
			WHERE `id_order` = '.(int)$this->id);
		return ObjectModel::hydrateCollection('OrderPayment', $order_payment);
	}

	/**
	 *
	 * This method allows to add a payment to the current order
	 * @since 1.5.0.1
	 * @param float $amount_paid
	 * @param string $payment_method
	 * @param string $payment_transaction_id
	 * @param Currency $currency
	 * @param string $date
	 * @return bool
	 */
	public function addOrderPayment($amount_paid, $payment_method = null, $payment_transaction_id = null, $currency = null, $date = null)
	{
		$order_payment = new OrderPayment();
		$order_payment->id_order = $this->id;
		$order_payment->id_currency = ($currency ? $currency->id : $this->id_currency);
		// we kept the currency rate for historization reasons
		$order_payment->conversion_rate = ($currency ? $currency->conversion_rate : 1);
		// if payment_method is define, we used this
		$order_payment->payment_method = ($payment_method ? $payment_method : $this->payment);
		$order_payment->transacation_id = $payment_transaction_id;
		$order_payment->amount = $amount_paid;
		$order_payment->date_add = ($date ? $date : null);

		// Update total_paid_real value for backward compatibility reasons
		if ($order_payment->id_currency == $this->id_currency)
			$this->total_paid_real += $order_payment->amount;
		else
			$this->total_paid_real += Tools::ps_round(Tools::convertPrice($order_payment->amount, $order_payment->id_currency, false), 2);

		return $order_payment->add() && $this->update();
	}

	/**
	 * Returns the correct product taxes breakdown.
	 *
	 * Get all documents linked to the current order
	 *
	 * @since 1.5.0.1
	 * @return array
	 */
	public function getDocuments()
	{
		// TODO
		$invoices = $this->getInvoicesCollection();

		return $invoices;
	}

	public function getReturn()
	{
		return OrderReturn::getOrdersReturn($this->id_customer, $this->id);
	}

	public function getShipping()
	{
		$shipping = Db::getInstance()->ExecuteS('
			SELECT DISTINCT oc.`id_order_invoice`, oc.`weight`, oc.`shipping_cost_tax_excl`, oc.`shipping_cost_tax_incl`, c.`url`, oc.`id_carrier`, c.`name` as `state_name`, oc.`date_add`, "Delivery" as `type`, "true" as `can_edit`, oc.`tracking_number`
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_history` oh
				ON (o.`id_order` = oh.`id_order`)
			LEFT JOIN `'._DB_PREFIX_.'order_carrier` oc
				ON (o.`id_order` = oc.`id_order`)
			LEFT JOIN `'._DB_PREFIX_.'carrier` c
				ON (oc.`id_carrier` = c.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
				ON (oh.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)Context::getContext()->language->id.')
			WHERE o.`id_order` = '.(int)$this->id);
		return $shipping;
	}

	/**
	 *
	 * Get all invoices for the current order
	 * @since 1.5.0.1
	 * @return array Collection of Order invoice
	 */
	public function getInvoicesCollection()
	{
		$invoices = Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'order_invoice`
			WHERE `id_order` = '.(int)$this->id);
		return ObjectModel::hydrateCollection('OrderInvoice', $invoices);
	}

	/**
	 * Get total paid
	 *
	 * @since 1.5.0.1
	 * @param Currency $currency currency used for the total paid of the current order
	 * @return float amount in the $currency
	 */
	public function getTotalPaid($currency = null)
	{
		if (!$currency)
			$currency = new Currency($this->id_currency);

		$total = 0;
		// Retrieve all payments
		$payments = $this->getOrderPaymentCollection();
		foreach($payments as $payment)
		{
			if ($payment->id_currency == $currency->id)
				$total += $payment->amount;
			else
			{
				$amount = Tools::convertPrice($payment->amount, $payment->id_currency, false);
				if ($currency->id == Configuration::get('PS_DEFAULT_CURRENCY'))
					$total += $amount;
				else
					$total += Tool::convertPrice($amount, $currency->id, true);
			}
		}

		return Tools::ps_round($total, 2);
	}

	/**
	 *
	 * This method allows to change the shipping cost of the current order
	 * @since 1.5.0.1
	 * @param float $amount
	 * @return bool
	 */
	public function updateShippingCost($amount)
	{
		$difference = $amount - $this->total_shipping;
		// if the current amount is same as the new, we return true
		if ($difference == 0)
			return true;

		// update the total_shipping value
		$this->total_shipping = $amount;
		// update the total of this order
		$this->total_paid += $difference;

		// update database
		return $this->update();
	}

	/**
	 * Returns the correct product taxes breakdown.
	 *
	 * @since 1.5.0.1
	 * @return array
	 */
	public function getProductTaxesBreakdown()
	{
		$tmp_tax_infos = array();
		if ($this->useOneAfterAnotherTaxComputationMethod())
		{
			// sum by taxes
			$taxes_by_tax = Db::getInstance()->executeS('
			SELECT odt.`id_order_detail`, t.`name`, t.`rate`, SUM(`total_amount`) AS `total_amount`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id.'
			GROUP BY odt.`id_tax`
			');

			// format response
			$tmp_tax_infos = array();
			foreach ($taxes_infos as $tax_infos)
			{
				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] = $tax_infos['tax_amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
			}
		}
		else
		{
			// sum by order details in order to retrieve real taxes rate
			$taxes_infos = Db::getInstance()->executeS('
			SELECT odt.`id_order_detail`, t.`rate` AS `name`, SUM(od.`total_price_tax_excl`) AS total_price_tax_excl, SUM(t.`rate`) AS rate, SUM(`total_amount`) AS `total_amount`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id.'
			GROUP BY odt.`id_order_detail`
			');

			// sum by taxes
			$tmp_tax_infos = array();
			foreach ($taxes_infos as $tax_infos)
			{
				if (!isset($tmp_tax_infos[$tax_infos['rate']]))
					$tmp_tax_infos[$tax_infos['rate']] = array('total_amount' => 0,
																'name' => 0,
																'total_price_tax_excl' => 0);

				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] += $tax_infos['total_amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
				$tmp_tax_infos[$tax_infos['rate']]['total_price_tax_excl'] += $tax_infos['total_price_tax_excl'];
			}
		}

		return $tmp_tax_infos;
	}

	/**
	 * Returns the shipping taxes breakdown
	 *
	 * @since 1.5.0.1
	 * @return array
	 */
	public function getShippingTaxesBreakdown()
	{
		$taxes_breakdown = array();

		$shipping_tax_amount = $this->total_shipping_tax_incl - $this->total_shipping_tax_excl;

		if ($shipping_tax_amount > 0)
			$taxes_breakdown[] = array(
				'rate' => $this->carrier_tax_rate,
				'total_amount' => $shipping_tax_amount
			);

		return $taxes_breakdown;
	}

	/**
	 * Returns the wrapping taxes breakdown
	 * @todo

	 * @since 1.5.0.1
	 * @return array
	 */
	public function getWrappingTaxesBreakdown()
	{
		$taxes_breakdown = array();
		return $taxes_breakdown;
	}

	/**
	 * Returns the ecotax taxes breakdown
	 *
	 * @since 1.5.0.1
	 * @return array
	 */
	public function getEcoTaxTaxesBreakdown()
	{
		return Db::getInstance()->executeS('
		SELECT `ecotax_tax_rate`, SUM(`ecotax`) as `ecotax_tax_excl`, SUM(`ecotax`) as `ecotax_tax_incl`
		FROM `'._DB_PREFIX_.'order_detail`
		WHERE `id_order` = '.(int)$this->id
		);
	}
}

