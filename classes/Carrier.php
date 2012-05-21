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

class CarrierCore extends ObjectModel
{
	/**
	 * getCarriers method filter
	 */
	const PS_CARRIERS_ONLY = 1;
	const CARRIERS_MODULE = 2;
	const CARRIERS_MODULE_NEED_RANGE = 3;
	const PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE = 4;
	const ALL_CARRIERS = 5;

	const SHIPPING_METHOD_DEFAULT = 0;
	const SHIPPING_METHOD_WEIGHT = 1;
	const SHIPPING_METHOD_PRICE = 2;
	const SHIPPING_METHOD_FREE = 3;

	const SORT_BY_PRICE = 0;
	const SORT_BY_POSITION = 1;

	const SORT_BY_ASC = 0;
	const SORT_BY_DESC = 1;

	/** @var int Tax id (none = 0) */
	public $id_tax_rules_group;

	/** @var int common id for carrier historization */
	public $id_reference;

 	/** @var string Name */
	public $name;

 	/** @var string URL with a '@' for */
	public $url;

	/** @var string Delay needed to deliver customer */
	public $delay;

	/** @var boolean Carrier statuts */
	public $active = true;

	/** @var boolean True if carrier has been deleted (staying in database as deleted) */
	public $deleted = 0;

	/** @var boolean Active or not the shipping handling */
	public $shipping_handling = true;

	/** @var int Behavior taken for unknown range */
	public $range_behavior;

	/** @var boolean Carrier module */
	public $is_module;

	/** @var boolean Free carrier */
	public $is_free = false;

	/** @var int shipping behavior: by weight or by price */
	public $shipping_method = 0;

	/** @var boolean Shipping external */
	public $shipping_external = 0;

	/** @var string Shipping external */
	public $external_module_name = null;

	/** @var boolean Need Range */
	public $need_range = 0;

	/** @var int Position */
	public $position;

	/** @var int maximum package width managed by the transporter */
	public $max_width;

	/** @var int maximum package height managed by the transporter */
	public $max_height;

	/** @var int maximum package deep managed by the transporter */
	public $max_depth;

	/** @var int maximum package weight managed by the transporter */
	public $max_weight;

	/** @var int grade of the shipping delay (0 for longest, 9 for shortest) */
	public $grade;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'carrier',
		'primary' => 'id_carrier',
		'multilang' => true,
		'multishop' => true,
		'fields' => array(
			/* Classic fields */
			'id_reference' => 			array('type' => self::TYPE_INT),
			'id_tax_rules_group' => 	array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'name' => 					array('type' => self::TYPE_STRING, 'validate' => 'isCarrierName', 'required' => true, 'size' => 64),
			'active' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'is_free' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'url' => 					array('type' => self::TYPE_STRING, 'validate' => 'isAbsoluteUrl'),
			'shipping_handling' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'shipping_external' => 		array('type' => self::TYPE_BOOL),
			'range_behavior' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'shipping_method' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'max_width' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'max_height' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'max_depth' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'max_weight' => 			array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'grade' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'size' => 1),
			'external_module_name' => 	array('type' => self::TYPE_STRING, 'size' => 64),
			'is_module' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'need_range' => 			array('type' => self::TYPE_BOOL),
			'position' => 				array('type' => self::TYPE_INT),
			'deleted' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

			/* Lang fields */
			'delay' => 					array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 128),
		),
	);

	protected static $price_by_weight = array();
	protected static $price_by_weight2 = array();
	protected static $price_by_price = array();
	protected static $price_by_price2 = array();

	protected static $cache_tax_rule = array();

	protected	$webserviceParameters = array(
		'fields' => array(
			'id_tax_rules_group' => array(),
			'deleted' => array(),
			'is_module' => array(),
		),
	);

	public function __construct($id = null, $id_lang = null)
	{
		parent::__construct($id, $id_lang);
		if ($this->name == '0')
			$this->name = Configuration::get('PS_SHOP_NAME');
	}

	public function add($autodate = true, $null_values = false)
	{
		if (!parent::add($autodate, $null_values) || !Validate::isLoadedObject($this))
			return false;
		if (!Db::getInstance()->executeS('SELECT `id_carrier` FROM `'._DB_PREFIX_.$this->def['table'].'` WHERE `deleted` = 0'))
			return false;
		if (!$num_rows = Db::getInstance()->NumRows())
			return false;
		if ((int)$num_rows == 1)
			Configuration::updateValue('PS_CARRIER_DEFAULT', (int)$this->id);

		// Register reference
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.$this->def['table'].'` SET `id_reference` = '.$this->id.' WHERE `id_carrier` = '.$this->id);

		return true;
	}

	/**
	 * @since 1.5.0
	 * @see ObjectModel::delete()
	 */
	public function delete()
	{
		if (!parent::delete())
			return false;
		return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_rule_carrier WHERE id_carrier = '.(int)$this->id);
	}

	/**
	* Change carrier id in delivery prices when updating a carrier
	*
	* @param integer $id_old Old id carrier
	*/
	public function setConfiguration($id_old)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'delivery` SET `id_carrier` = '.(int)$this->id.' WHERE `id_carrier` = '.(int)$id_old);
	}

	/**
	 * Get delivery prices for a given order
	 *
	 * @param floatval $totalWeight Order total weight
	 * @param integer $id_zone Zone id (for customer delivery address)
	 * @return float Delivery price
	 */
	public function getDeliveryPriceByWeight($total_weight, $id_zone, Shop $shop = null)
	{
		$cache_key = $this->id.'_'.$total_weight.'_'.$id_zone;
		if (!isset(self::$price_by_weight[$cache_key]))
		{
			$sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_weight` w ON (d.`id_range_weight` = w.`id_range_weight`)
					WHERE d.`id_zone` = '.(int)$id_zone.'
						AND '.(float)$total_weight.' >= w.`delimiter1`
						AND '.(float)$total_weight.' < w.`delimiter2`
						AND d.`id_carrier` = '.(int)$this->id.'
						'.Carrier::sqlDeliveryRangeShop('range_weight', $shop).'
					ORDER BY w.`delimiter1` ASC';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			if (!isset($result['price']))
				self::$price_by_weight[$cache_key] = $this->getMaxDeliveryPriceByWeight($id_zone);
			else
				self::$price_by_weight[$cache_key] = $result['price'];
		}
		return self::$price_by_weight[$cache_key];
	}

	public static function checkDeliveryPriceByWeight($id_carrier, $total_weight, $id_zone, Shop $shop = null)
	{
		$cache_key = $id_carrier.'_'.$total_weight.'_'.$id_zone;
		if (!isset(self::$price_by_weight2[$cache_key]))
		{
			$sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
					WHERE d.`id_zone` = '.(int)$id_zone.'
						AND '.(float)$total_weight.' >= w.`delimiter1`
						AND '.(float)$total_weight.' < w.`delimiter2`
						AND d.`id_carrier` = '.(int)$id_carrier.'
						'.Carrier::sqlDeliveryRangeShop('range_weight', $shop).'
					ORDER BY w.`delimiter1` ASC';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			self::$price_by_weight2[$cache_key] = (isset($result['price']));
		}
		return self::$price_by_weight2[$cache_key];
	}

	public function getMaxDeliveryPriceByWeight($id_zone, Shop $shop = null)
	{
		$sql = 'SELECT d.`price`
				FROM `'._DB_PREFIX_.'delivery` d
				INNER JOIN `'._DB_PREFIX_.'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
				WHERE d.`id_zone` = '.(int)$id_zone.'
					AND d.`id_carrier` = '.(int)$this->id.'
					'.Carrier::sqlDeliveryRangeShop('range_weight', $shop).'
				ORDER BY w.`delimiter2` DESC LIMIT 1';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		if (!isset($result[0]['price']))
			return false;
		return $result[0]['price'];
	}

	/**
	 * Get delivery prices for a given order
	 *
	 * @param floatval $orderTotal Order total to pay
	 * @param integer $id_zone Zone id (for customer delivery address)
	 * @return float Delivery price
	 */
	public function getDeliveryPriceByPrice($order_total, $id_zone, $id_currency = null, Shop $shop = null)
	{
		$cache_key = $this->id.'_'.$order_total.'_'.$id_zone.'_'.$id_currency;
		if (!isset(self::$price_by_price[$cache_key]))
		{
			if (!empty($id_currency))
				$order_total = Tools::convertPrice($order_total, $id_currency, false);

			$sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
					WHERE d.`id_zone` = '.(int)$id_zone.'
						AND '.(float)$order_total.' >= r.`delimiter1`
						AND '.(float)$order_total.' < r.`delimiter2`
						AND d.`id_carrier` = '.(int)$this->id.'
						'.Carrier::sqlDeliveryRangeShop('range_price', $shop).'
					ORDER BY r.`delimiter1` ASC';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			if (!isset($result['price']))
				self::$price_by_price[$cache_key] = $this->getMaxDeliveryPriceByPrice($id_zone);
			else
				self::$price_by_price[$cache_key] = $result['price'];
		}
		return self::$price_by_price[$cache_key];
	}

	/**
	 * Check delivery prices for a given order
	 *
	 * @param id_carrier
	 * @param floatval $orderTotal Order total to pay
	 * @param integer $id_zone Zone id (for customer delivery address)
	 * @param integer $id_currency
	 * @return float Delivery price
	 */
	public static function checkDeliveryPriceByPrice($id_carrier, $order_total, $id_zone, $id_currency = null, Shop $shop = null)
	{
		$cache_key = $id_carrier.'_'.$order_total.'_'.$id_zone.'_'.$id_currency;
		if (!isset(self::$price_by_price2[$cache_key]))
		{
			if (!empty($id_currency))
				$order_total = Tools::convertPrice($order_total, $id_currency, false);

			$sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
					WHERE d.`id_zone` = '.(int)$id_zone.'
						AND '.(float)$order_total.' >= r.`delimiter1`
						AND '.(float)$order_total.' < r.`delimiter2`
						AND d.`id_carrier` = '.(int)$id_carrier.'
						'.Carrier::sqlDeliveryRangeShop('range_price', $shop).'
					ORDER BY r.`delimiter1` ASC';
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			self::$price_by_price2[$cache_key] = (isset($result['price']));
		}
		return self::$price_by_price2[$cache_key];
	}

	public function getMaxDeliveryPriceByPrice($id_zone, Shop $shop = null)
	{
		$sql = 'SELECT d.`price`
				FROM `'._DB_PREFIX_.'delivery` d
				INNER JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
				WHERE d.`id_zone` = '.(int)$id_zone.'
					AND d.`id_carrier` = '.(int)$this->id.'
					'.Carrier::sqlDeliveryRangeShop('range_price', $shop).'
				ORDER BY r.`delimiter2` DESC LIMIT 1';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		if (!isset($result[0]['price']))
			return false;
		return $result[0]['price'];
	}

	/**
	 * Get delivery prices for a given shipping method (price/weight)
	 *
	 * @param string $rangeTable Table name (price or weight)
	 * @return array Delivery prices
	 */
	public static function getDeliveryPriceByRanges($range_table, $id_carrier, Shop $shop = null)
	{
		$range_table = pSQL($range_table);
		$sql = 'SELECT d.id_'.$range_table.', d.id_carrier, d.id_zone, d.price
				FROM '._DB_PREFIX_.'delivery d
				LEFT JOIN '._DB_PREFIX_.$range_table.' r ON r.id_'.$range_table.' = d.id_'.$range_table.'
				WHERE d.id_carrier = '.(int)$id_carrier.'
					AND d.id_'.$range_table.' IS NOT NULL
					AND d.id_'.$range_table.' != 0
					'.Carrier::sqlDeliveryRangeShop($range_table, $shop).'
				ORDER BY r.delimiter1';
		return Db::getInstance()->executeS($sql);
	}

	/**
	 * Get all carriers in a given language
	 *
	 * @param integer $id_lang Language id
	 * @param $modules_filters, possible values:
			PS_CARRIERS_ONLY
			CARRIERS_MODULE
			CARRIERS_MODULE_NEED_RANGE
			PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
			ALL_CARRIERS
	 * @param boolean $active Returns only active carriers when true
	 * @return array Carriers
	 */
	public static function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false, $ids_group = null, $modules_filters = self::PS_CARRIERS_ONLY)
	{
		if (!Validate::isBool($active))
			die(Tools::displayError());
		if ($ids_group)
		{
			$ids = '';
			foreach ($ids_group as $id)
				$ids .= (int)$id.', ';
			$ids = rtrim($ids, ', ');
			if ($ids == '')
				return array();
		}

		$sql = 'SELECT c.*, cl.delay
				FROM `'._DB_PREFIX_.'carrier` c
				LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)$id_lang.Context::getContext()->shop->addSqlRestrictionOnLang('cl').')
				LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)'.
				($id_zone ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = '.(int)$id_zone.')' : '').'
				WHERE c.`deleted` = '.($delete ? '1' : '0').
					($active ? ' AND c.`active` = 1' : '').
					($id_zone ? ' AND cz.`id_zone` = '.(int)$id_zone.'
					AND z.`active` = 1 ' : ' ');
		switch ($modules_filters)
		{
			case 1 :
				$sql .= 'AND c.is_module = 0 ';
			break;
			case 2 :
				$sql .= 'AND c.is_module = 1 ';
			break;
			case 3 :
				$sql .= 'AND c.is_module = 1 AND c.need_range = 1 ';
			break;
			case 4 :
				$sql .= 'AND (c.is_module = 0 OR c.need_range = 1) ';
			break;
			case 5 :
				$sql .= '';
			break;

		}
		$sql .= ($ids_group ? ' AND c.id_carrier IN (SELECT id_carrier FROM '._DB_PREFIX_.'carrier_group WHERE id_group IN ('.$ids.')) ' : '').'
			GROUP BY c.`id_carrier`
			ORDER BY c.`position` ASC';

		$carriers = Db::getInstance()->executeS($sql);

		if (is_array($carriers) && count($carriers))
		{
			foreach ($carriers as $key => $carrier)
				if ($carrier['name'] == '0')
					$carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
		}
		else
			$carriers = array();

		return $carriers;
	}

	public static function getDeliveredCountries($id_lang, $active_countries = false, $active_carriers = false, $contain_states = null)
	{
		if (!Validate::isBool($active_countries) || !Validate::isBool($active_carriers))
			die(Tools::displayError());

		$states = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT s.*
		FROM `'._DB_PREFIX_.'state` s
		ORDER BY s.`name` ASC');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT cl.*,c.*, cl.`name` AS country, zz.`name` AS zone FROM `'._DB_PREFIX_.'country` c
			LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.(int)$id_lang.')
			INNER JOIN (`'._DB_PREFIX_.'carrier_zone` cz INNER JOIN `'._DB_PREFIX_.'carrier` cr ON ( cr.id_carrier = cz.id_carrier AND cr.deleted = 0 '.
			($active_carriers ? 'AND cr.active = 1) ' : ') ').'
			LEFT JOIN `'._DB_PREFIX_.'zone` zz ON cz.id_zone = zz.id_zone) ON zz.`id_zone` = c.`id_zone`
			WHERE 1
			'.($active_countries ? 'AND c.active = 1' : '').'
			'.(!is_null($contain_states) ? 'AND c.`contains_states` = '.(int)$contain_states : '').'
			ORDER BY cl.name ASC');

		$countries = array();
		foreach ($result as &$country)
			$countries[$country['id_country']] = $country;
		foreach ($states as &$state)
			if (isset($countries[$state['id_country']])) /* Does not keep the state if its country has been disabled and not selected */
				$countries[$state['id_country']]['states'][] = $state;

		return $countries;
	}

	/**
	 * Return the default carrier to use
	 *
	 * @param array $carriers
	 * @param array $defaultCarrier the last carrier selected
	 * @return number the id of the default carrier
	 */
	public static function getDefaultCarrierSelection($carriers, $default_carrier = 0)
	{
		if (empty($carriers))
			return 0;

		if ((int)$default_carrier != 0)
			foreach ($carriers as $carrier)
				if ($carrier['id_carrier'] == (int)$default_carrier)
					return (int)$carrier['id_carrier'];
		foreach ($carriers as $carrier)
			if ($carrier['id_carrier'] == (int)Configuration::get('PS_CARRIER_DEFAULT'))
				return (int)$carrier['id_carrier'];

		return (int)$carriers[0]['id_carrier'];
	}

	/**
	 *
	 * @param int $id_zone
	 * @param Array $groups group of the customer
	 * @return Array
	 */
	public static function getCarriersForOrder($id_zone, $groups = null, $cart = null)
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		if (is_null($cart))
			$cart = $context->cart;
		$id_currency = $context->currency->id;

		if (is_array($groups) && !empty($groups))
			$result = Carrier::getCarriers($id_lang, true, false, (int)$id_zone, $groups, self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		else
			$result = Carrier::getCarriers($id_lang, true, false, (int)$id_zone, array(Configuration::get('PS_UNIDENTIFIED_GROUP')), self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
		$results_array = array();

		foreach ($result as $k => $row)
		{
			$carrier = new Carrier((int)$row['id_carrier']);
			$shipping_method = $carrier->getShippingMethod();
			if ($shipping_method != Carrier::SHIPPING_METHOD_FREE)
			{
				// Get only carriers that are compliant with shipping method
				if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)
					|| ($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false))
				{
					unset($result[$k]);
					continue;
				}

				// If out-of-range behavior carrier is set on "Desactivate carrier"
				if ($row['range_behavior'])
				{
					// Get id zone
					if (!$id_zone)
							$id_zone = Country::getIdZone(Country::getDefaultCountryId());

					// Get only carriers that have a range compatible with cart
					if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT
						&& (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone)))
						|| ($shipping_method == Carrier::SHIPPING_METHOD_PRICE
						&& (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $id_currency))))
					{
						unset($result[$k]);
						continue;
					}
				}
			}

			$row['name'] = (strval($row['name']) != '0' ? $row['name'] : Configuration::get('PS_SHOP_NAME'));
			$row['price'] = ($shipping_method == Carrier::SHIPPING_METHOD_FREE ? 0 : $cart->getPackageShippingCost((int)$row['id_carrier']));
			$row['price_tax_exc'] = ($shipping_method == Carrier::SHIPPING_METHOD_FREE ? 0 : $cart->getPackageShippingCost((int)$row['id_carrier'], false));
			$row['img'] = file_exists(_PS_SHIP_IMG_DIR_.(int)$row['id_carrier']).'.jpg' ? _THEME_SHIP_DIR_.(int)$row['id_carrier'].'.jpg' : '';

			// If price is false, then the carrier is unavailable (carrier module)
			if ($row['price'] === false)
			{
				unset($result[$k]);
				continue;
			}
			$results_array[] = $row;
		}

		// if we have to sort carriers by price
		$prices = array();
		if (Configuration::get('PS_CARRIER_DEFAULT_SORT') == Carrier::SORT_BY_PRICE)
		{
			foreach ($results_array as $r)
				$prices[] = $r['price'];
			if (Configuration::get('PS_CARRIER_DEFAULT_ORDER') == Carrier::SORT_BY_ASC)
				array_multisort($prices, SORT_ASC, SORT_NUMERIC, $results_array);
			else
				array_multisort($prices, SORT_DESC, SORT_NUMERIC, $results_array);
		}

		return $results_array;
	}

	public static function checkCarrierZone($id_carrier, $id_zone)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.`id_carrier`
			FROM `'._DB_PREFIX_.'carrier` c
			LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = '.(int)$id_zone.')
			WHERE c.`id_carrier` = '.(int)$id_carrier.'
			AND c.`deleted` = 0
			AND c.`active` = 1
			AND cz.`id_zone` = '.(int)$id_zone.'
			AND z.`active` = 1'
		);
	}

	/**
	 * Get all zones
	 *
	 * @return array Zones
	 */
	public function getZones()
	{
		return Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'carrier_zone` cz
			LEFT JOIN `'._DB_PREFIX_.'zone` z ON cz.`id_zone` = z.`id_zone`
			WHERE cz.`id_carrier` = '.(int)$this->id);
	}

	/**
	 * Get a specific zones
	 *
	 * @return array Zone
	 */
	public function getZone($id_zone)
	{
		return Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE `id_carrier` = '.(int)$this->id.'
			AND `id_zone` = '.(int)$id_zone);
	}

	/**
	 * Add zone
	 */
	public function addZone($id_zone)
	{
		return Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'carrier_zone` (`id_carrier` , `id_zone`)
			VALUES ('.(int)$this->id.', '.(int)$id_zone.')');
	}

	/**
	 * Delete zone
	 */
	public function deleteZone($id_zone)
	{
		return Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE `id_carrier` = '.(int)$this->id.'
			AND `id_zone` = '.(int)$id_zone.' LIMIT 1');
	}

	/**
	 * Gets a specific group
	 *
	 * @since 1.5.0
	 * @return array Group
	 */
	public function getGroups()
	{
		return Db::getInstance()->executeS('
			SELECT id_group
			FROM '._DB_PREFIX_.'carrier_group
			WHERE id_carrier='.(int)$this->id);
	}

	/**
	 * Clean delivery prices (weight/price)
	 *
	 * @param string $rangeTable Table name to clean (weight or price according to shipping method)
	 * @return boolean Deletion result
	 */
	public function deleteDeliveryPrice($range_table, Shop $shop = null)
	{
		$where = '`id_carrier` = '.(int)$this->id.' AND (`id_'.$range_table.'` IS NOT NULL OR `id_'.$range_table.'` = 0) ';

		if (!$shop)
			$shop = Context::getContext()->shop;
		$shop_id = $shop->getID();
		$shop_group_id = $shop->getGroupID();
		if (!$shop_id && !$shop_group_id)
			$where .= 'AND id_shop IS NULL AND id_group_shop IS NULL';
		else if (!$shop_id)
			$where .= 'AND id_shop IS NULL AND id_group_shop = '.$shop_group_id;
		else
			$where .= 'AND id_shop = '.$shop_id;

		return Db::getInstance()->delete(_DB_PREFIX_.'delivery', $where);
	}

	/**
	 * Add new delivery prices
	 *
	 * @param array $priceList Prices list in multiple arrays (changed to array since 1.5.0)
	 * @param Shop $shop Optionnal
	 * @return boolean Insertion result
	 */
	public function addDeliveryPrice($price_list, Shop $shop = null)
	{
		if (!$price_list)
			return false;

		$keys = array_keys($price_list[0]);
		if (!in_array('id_shop', $keys))
			$keys[] = 'id_shop';
		if (!in_array('id_group_shop', $keys))
			$keys[] = 'id_group_shop';

		if (!$shop)
			$shop = Context::getContext()->shop;
		$shop_id = $shop->getID();
		$shop_group_id = $shop->getGroupID();

		$sql = 'INSERT INTO `'._DB_PREFIX_.'delivery` ('.implode(', ', $keys).') VALUES ';
		foreach ($price_list as $values)
		{
			if (!isset($values['id_shop']))
				$values['id_shop'] = ($shop_id) ? $shop_id : null;
			if (!isset($values['id_group_shop']))
				$values['id_group_shop'] = ($shop_group_id) ? $shop_group_id : null;

			$sql .= '(';
			foreach ($values as $v)
			{
				if (is_null($v))
					$sql .= 'NULL';
				else if (is_int($v) || is_float($v))
					$sql .= $v;
				else
					$sql .= '\''.$v.'\'';
				$sql .= ', ';
			}
			$sql = rtrim($sql, ', ').'), ';
		}
		$sql = rtrim($sql, ', ');
		return Db::getInstance()->execute($sql);
	}

	/**
	 * Copy old carrier informations when update carrier
	 *
	 * @param integer $oldId Old id carrier (copy from that id)
	 */
	public function copyCarrierData($old_id)
	{
		if (!Validate::isUnsignedId($old_id))
			die(Tools::displayError());

		if (!$this->id)
			return false;

		$old_logo = _PS_SHIP_IMG_DIR_.'/'.(int)$old_id.'.jpg';
		if (file_exists($old_logo))
			copy($old_logo, _PS_SHIP_IMG_DIR_.'/'.$this->id.'.jpg');

		// Copy existing ranges price
		foreach (array('range_price', 'range_weight') as $range)
		{
			$sql = 'SELECT id_'.$range.' id_range, delimiter1, delimiter2 FROM `'._DB_PREFIX_.$range.'`
					WHERE id_carrier = '.(int)$old_id;
			$res = Db::getInstance()->executeS($sql);
			foreach ($res as $val)
			{
				$sql = 'INSERT INTO `'._DB_PREFIX_.$range.'` (`id_carrier`, `delimiter1`, `delimiter2`)
						VALUES ('.$this->id.','.(float)$val['delimiter1'].','.(float)$val['delimiter2'].')';
				Db::getInstance()->execute($sql);
				$range_id = (int)Db::getInstance()->Insert_ID();

				$range_price_id = ($range == 'range_price') ? $range_id : 'NULL';
				$range_weight_id = ($range == 'range_weight') ? $range_id : 'NULL';
				$sql = 'INSERT INTO '._DB_PREFIX_.'delivery (id_carrier, id_range_price, id_range_weight, id_zone, price)
						SELECT '.$this->id.', '.$range_price_id.', '.$range_weight_id.', id_zone, price FROM '._DB_PREFIX_.'delivery
						WHERE id_carrier = '.(int)$old_id.' AND '.(($range == 'range_price') ? 'id_range_price' : 'id_range_weight').' = '.$val['id_range'];

				Db::getInstance()->execute($sql);
			}
		}

		// Copy existing zones
		$res = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'carrier_zone`
			WHERE id_carrier = '.(int)$old_id);
		foreach ($res as $val)
			Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'carrier_zone` (`id_carrier`, `id_zone`)
			VALUES ('.$this->id.','.(int)$val['id_zone'].')');

		//Copy default carrier
		if ((int)Configuration::get('PS_CARRIER_DEFAULT') == $old_id)
			Configuration::updateValue('PS_CARRIER_DEFAULT', (int)$this->id);

		// Copy reference
		$id_reference = Db::getInstance()->getValue('SELECT `id_reference` FROM `'._DB_PREFIX_.$this->def['table'].'` WHERE id_carrier = '.$old_id);
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.$this->def['table'].'`
			SET `id_reference` = '.$id_reference.'
			WHERE `id_carrier` = '.$this->id);
	}

	/**
	 * Get carrier using the reference id
	 */
	public static function getCarrierByReference($id_reference)
	{
		// @todo class var $table must became static. here I have to use 'carrier' because this method is static
		$id_carrier = Db::getInstance()->getValue('SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier`
			WHERE id_reference = '.(int)$id_reference.' AND deleted = 0 ORDER BY id_carrier DESC');
		if (!$id_carrier)
			return false;
		return new Carrier($id_carrier);
	}

	/**
	 * Check if carrier is used (at least one order placed)
	 *
	 * @return integer Order count for this carrier
	 */
	public function isUsed()
	{
		$row = Db::getInstance()->getRow('
		SELECT COUNT(`id_carrier`) AS total
		FROM `'._DB_PREFIX_.'orders`
		WHERE `id_carrier` = '.(int)$this->id);

		return (int)$row['total'];
	}

	public function getShippingMethod()
	{
		if ($this->is_free)
			return Carrier::SHIPPING_METHOD_FREE;

		$method = (int)$this->shipping_method;

		if ($this->shipping_method == Carrier::SHIPPING_METHOD_DEFAULT)
		{
			// backward compatibility
			if ((int)Configuration::get('PS_SHIPPING_METHOD'))
				$method = Carrier::SHIPPING_METHOD_WEIGHT;
			else
				$method = Carrier::SHIPPING_METHOD_PRICE;
		}

		return $method;
	}

	public function getRangeTable()
	{
		$shipping_method = $this->getShippingMethod();
		if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT)
			return 'range_weight';
		else if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE)
			return 'range_price';
		return false;
	}

	public function getRangeObject()
	{
		$shipping_method = $this->getShippingMethod();
		if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT)
			return new RangeWeight();
		else if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE)
			return new RangePrice();
		return false;
	}

	public function getRangeSuffix($currency = null)
	{
		if (!$currency)
			$currency = Context::getContext()->currency;
		$suffix = Configuration::get('PS_WEIGHT_UNIT');
		if ($this->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE)
			$suffix = $currency->sign;
		return $suffix;
	}

	/**
	 *
	 * @param int $id_carrier
	 */
	public static function getIdTaxRulesGroupByIdCarrier($id_carrier)
	{
		if (!isset(self::$cache_tax_rule[(int)$id_carrier]))
		{
			 self::$cache_tax_rule[$id_carrier] = Db::getInstance()->getValue('
			 SELECT `id_tax_rules_group`
			 FROM `'._DB_PREFIX_.'carrier`
			 WHERE `id_carrier` = '.(int)$id_carrier);
		}

		return self::$cache_tax_rule[$id_carrier];
	}


	/**
	 * Returns the taxes rate associated to the carrier
	 *
	 * @since 1.5
	 * @param Address $address
	 * @return
	 */
	public function getTaxesRate(Address $address)
	{
		$tax_manager = TaxManagerFactory::getManager($address, $this->id_tax_rules_group);
		$tax_calculator = $tax_manager->getTaxCalculator();
		return $tax_calculator->getTotalRate();
	}

	/**
	 * This tricky method generates a sql clause to check if ranged data are overloaded by multishop
	 *
	 * @since 1.5.0
	 * @param string $rangeTable
	 * @param Shop $shop
	 * @return string
	 */
	public static function sqlDeliveryRangeShop($range_table, Shop $shop = null, $alias = 'd')
	{
		if (!$shop)
			$shop = Context::getContext()->shop;
		$shop_id = $shop->getID();
		$shop_group_id = $shop->getGroupID();
		$where = '';
		if (!$shop_id && !$shop_group_id)
			$where = 'AND d2.id_shop IS NULL AND d2.id_group_shop IS NULL';
		else if (!$shop_id)
			$where = 'AND ((d2.id_group_shop IS NULL OR d2.id_group_shop = '.$shop_group_id.') AND d2.id_shop IS NULL)';
		else
			$where = 'AND (d2.id_shop = '.$shop_id.' OR (d2.id_group_shop = '.$shop_group_id.'
					AND d2.id_shop IS NULL) OR (d2.id_group_shop IS NULL AND d2.id_shop IS NULL))';

		$sql = 'AND '.$alias.'.id_delivery = (
					SELECT d2.id_delivery
					FROM '._DB_PREFIX_.'delivery d2
					WHERE d2.id_carrier = '.$alias.'.id_carrier
						AND d2.id_zone = '.$alias.'.id_zone
						AND d2.id_'.$range_table.' = '.$alias.'.id_'.$range_table.'
						'.$where.'
					ORDER BY d2.id_shop DESC, d2.id_group_shop DESC
					LIMIT 1
				)';
		return $sql;
	}

	/**
	 * Moves a carrier
	 *
	 * @since 1.5.0
	 * @param boolean $way Up (1) or Down (0)
	 * @param integer $position
	 * @return boolean Update result
	 */
	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->executeS('
			SELECT `id_carrier`, `position`
			FROM `'._DB_PREFIX_.'carrier`
			WHERE `deleted` = 0
			ORDER BY `position` ASC'
		))
			return false;

		foreach ($res as $carrier)
			if ((int)$carrier['id_carrier'] == (int)$this->id)
				$moved_carrier = $carrier;

		if (!isset($moved_carrier) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'carrier`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)$moved_carrier['position'].' AND `position` <= '.(int)$position
				: '< '.(int)$moved_carrier['position'].' AND `position` >= '.(int)$position.'
			AND `deleted` = 0'))
		&& Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'carrier`
			SET `position` = '.(int)$position.'
			WHERE `id_carrier` = '.(int)$moved_carrier['id_carrier']));
	}

	/**
	 * Reorders carrier positions.
	 * Called after deleting a carrier.
	 *
	 * @since 1.5.0
	 * @return bool $return
	 */
	public static function cleanPositions()
	{
		$return = true;

		$sql = '
		SELECT `id_carrier`
		FROM `'._DB_PREFIX_.'carrier`
		WHERE `deleted` = 0
		ORDER BY `position` ASC';
		$result = Db::getInstance()->executeS($sql);

		$i = 0;
		foreach ($result as $value)
			$return = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'carrier`
			SET `position` = '.(int)$i++.'
			WHERE `id_carrier` = '.(int)$value['id_carrier']);
		return $return;
	}

	/**
	 * Gets the highest carrier position
	 *
	 * @since 1.5.0
	 * @return int $position
	 */
	public static function getHigherPosition()
	{
		$sql = 'SELECT MAX(`position`)
				FROM `'._DB_PREFIX_.'carrier`
				WHERE `deleted` = 0';
		$position = DB::getInstance()->getValue($sql);
		return ($position !== false) ? (int)$position : -1;
	}

	/**
	 * For a given {product, warehouse}, gets the carrier available
	 *
	 * @since 1.5.0
	 * @param Product $product The id of the product, or an array with at least the package size and weight
	 * @return array
	 */
	public static function getAvailableCarrierList(Product $product, $id_warehouse, $id_address_delivery = null, $id_shop = null, $cart = null)
	{
		if (is_null($id_shop))
			$id_shop = Context::getContext()->shop->getID(true);
		if (is_null($cart))
			$cart = Context::getContext()->cart;
			

		// Does the product is linked with carriers?
		$query = new DbQuery();
		$query->select('id_carrier');
		$query->from('product_carrier', 'pc');
		$query->innerJoin('carrier', 'c', 'c.id_reference = pc.id_carrier_reference AND c.deleted = 0');
		$query->where('id_product = '.(int)$product->id);
		$query->where('id_shop = '.(int)$id_shop);
		$carriers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		if (!empty($carriers))
		{
			$carrier_list = array();
			foreach ($carriers as $carrier)
				$carrier_list[] = $carrier['id_carrier'];
			return $carrier_list;
		}

		$carrier_list = array();

		// The product is not dirrectly linked with a carrier
		// Get all the carriers linked to a warehouse
		if ($id_warehouse)
		{
			$warehouse = new Warehouse($id_warehouse);
			$carrier_list = $warehouse->getCarriers();
		}

		if (empty($carrier_list)) // No carriers defined, get all available carriers
		{
			$carrier_list = array();
			$id_address = (int)((!is_null($id_address_delivery) && $id_address_delivery != 0) ? $id_address_delivery :  $cart->id_address_delivery);
			if ($id_address)
			{
				$address = new Address($id_address);
				$id_zone = Address::getZoneById($address->id);
			}
			else
			{
				$country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
				$id_zone = $country->id_zone;
			}
			$customer = new Customer($cart->id_customer);
			$carriers = Carrier::getCarriersForOrder($id_zone, $customer->getGroups(), $cart);
			foreach ($carriers as $carrier)
				$carrier_list[] = $carrier['id_carrier'];
		}

		if ($product->width > 0 || $product->height > 0 || $product->depth > 0 || $product->weight > 0)
		{
			foreach ($carrier_list as $key => $id_carrier)
			{
				$carrier = new Carrier($id_carrier);
				if (($carrier->max_width > 0 && $carrier->max_width < $product->width)
					|| ($carrier->max_height > 0 && $carrier->max_height > $product->height)
					|| ($carrier->max_depth > 0 && $carrier->max_depth > $product->depth)
					|| ($carrier->max_weight > 0 && $carrier->max_weight > $product->weight))
					unset($carrier_list[$key]);
			}
		}
		return $carrier_list;
	}
	
	/**
	 * Assign one (ore more) group to all carriers
	 * 
	 * @since 1.5.0
	 * @param int|array $id_group_list group id or list of group ids
	 * @param array $exception list of id carriers to ignore
	 */
	public static function assignGroupToAllCarriers($id_group_list, $exception = null)
	{
		if (!is_array($id_group_list))
			$id_group_list = array($id_group_list);
		
		Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'carrier_group`
			WHERE `id_group` IN ('.join(',', $id_group_list).')');
		
		$carrier_list = Db::getInstance()->executeS('
			SELECT id_carrier FROM `'._DB_PREFIX_.'carrier`
			WHERE deleted = 0
			'.(is_array($exception) ? 'AND id_carrier NOT IN ('.join(',', $exception).')' : ''));
		
		if ($carrier_list)
		{
			$data = array();
			foreach ($carrier_list as $carrier)
			{
				foreach ($id_group_list as $id_group)
					$data[] = array(
						'id_carrier' => $carrier['id_carrier'],
						'id_group' => $id_group,
					);
			}
			return Db::getInstance()->insert('carrier_group', $data, false, false, Db::INSERT);
		}
		
		return true;
	}
}

