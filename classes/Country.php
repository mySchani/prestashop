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

class CountryCore extends ObjectModel
{
	public $id;

	/** @var integer Zone id which country belongs */
	public $id_zone;

	/** @var integer Currency id which country belongs */
	public $id_currency;

	/** @var string 2 letters iso code */
	public $iso_code;

	/** @var integer international call prefix */
	public $call_prefix;

	/** @var string Name */
	public $name;

	/** @var boolean Contain states */
	public $contains_states;

	/** @var boolean Need identification number dni/nif/nie */
	public $need_identification_number;

	/** @var boolean Need Zip Code */
	public $need_zip_code;

	/** @var string Zip Code Format */
	public $zip_code_format;

	/** @var boolean Display or not the tax incl./tax excl. mention in the front office */
	public $display_tax_label = true;

	/** @var boolean Status for delivery */
	public $active = true;

	protected static $_idZones = array();

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'country',
		'primary' => 'id_country',
		'multilang' => true,
		'fields' => array(
			'id_zone' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_currency' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'call_prefix' => 				array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'iso_code' => 					array('type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 3),
			'active' => 					array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'contains_states' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'need_identification_number' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'need_zip_code' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'zip_code_format' => 			array('type' => self::TYPE_STRING, 'validate' => 'isZipCodeFormat'),
			'display_tax_label' => 			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),

			// Lang fields
			'name' => 						array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
		),
	);

	protected $webserviceParameters = array(
		'objectsNodeName' => 'countries',
		'fields' => array(
			'id_zone' => array('sqlId' => 'id_zone', 'xlink_resource'=> 'zones'),
			'id_currency' => array('sqlId' => 'id_currency', 'xlink_resource'=> 'currencies'),
		),
	);

	public function delete()
	{
		if (!parent::delete())
			return false;
		return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_rule_country WHERE id_country = '.(int)$this->id);
	}

	/**
	  * Return available countries
	  *
	  * @param integer $id_lang Language ID
	  * @param boolean $active return only active coutries
	  * @return array Countries and corresponding zones
	  */
	public static function getCountries($id_lang, $active = false, $contain_states = null, Shop $shop = null)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		if (!$shop)
			$shop = Context::getContext()->shop;

		$states = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT s.*
		FROM `'._DB_PREFIX_.'state` s
		ORDER BY s.`name` ASC');

		$sql = 'SELECT cl.*,c.*, cl.`name` AS country, z.`name` AS zone
				FROM `'._DB_PREFIX_.'country` c
				'.$shop->addSqlAssociation('country', 'c', false).'
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'zone` z ON z.`id_zone` = c.`id_zone`
				WHERE 1'
					.($active ? ' AND c.active = 1' : '')
					.(!is_null($contain_states) ? ' AND c.`contains_states` = '.(int)$contain_states : '').'
		ORDER BY cl.name ASC';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$countries = array();
		foreach ($result as &$country)
			$countries[$country['id_country']] = $country;
		foreach ($states as &$state)
			if (isset($countries[$state['id_country']])) /* Does not keep the state if its country has been disabled and not selected */
				$countries[$state['id_country']]['states'][] = $state;

		return $countries;
	}

	/**
	  * Get a country ID with its iso code
	  *
	  * @param string $iso_code Country iso code
	  * @return integer Country ID
	  */
	public static function getByIso($iso_code)
	{
		if (!Validate::isLanguageIsoCode($iso_code))
			die(Tools::displayError());
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `id_country`
		FROM `'._DB_PREFIX_.'country`
		WHERE `iso_code` = \''.pSQL(strtoupper($iso_code)).'\'');

		return $result['id_country'];
	}

	public static function getIdZone($id_country)
	{
		if (!Validate::isUnsignedId($id_country))
			die(Tools::displayError());

		if (isset(self::$_idZones[$id_country]))
			return self::$_idZones[$id_country];

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `id_zone`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);

		self::$_idZones[$id_country] = $result['id_zone'];
		return $result['id_zone'];
	}

	/**
	* Get a country name with its ID
	*
	* @param integer $id_lang Language ID
	* @param integer $id_country Country ID
	* @return string Country name
	*/
	public static function getNameById($id_lang, $id_country)
	{
		$key = 'country_getNameById_'.$id_country.'_'.$id_lang;
		if (!Cache::isStored($key))
			Cache::store($key, Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `name`
				FROM `'._DB_PREFIX_.'country_lang`
				WHERE `id_lang` = '.(int)$id_lang.'
				AND `id_country` = '.(int)$id_country
			));

		return Cache::retrieve($key);
	}

	/**
	* Get a country iso with its ID
	*
	* @param integer $id_country Country ID
	* @return string Country iso
	*/
	public static function getIsoById($id_country)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `iso_code`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)$id_country
		);

		return $result['iso_code'];
	}

	/**
	* Get a country id with its name
	*
	* @param integer $id_lang Language ID
	* @param string $country Country Name
	* @return intval Country id
	*/
	public static function getIdByName($id_lang = null, $country)
	{
		$sql = '
		SELECT `id_country`
		FROM `'._DB_PREFIX_.'country_lang`
		WHERE `name` LIKE \''.pSQL($country).'\'';
		if ($id_lang)
			$sql .= ' AND `id_lang` = '.(int)$id_lang;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

		return (int)$result['id_country'];
	}

	public static function getNeedZipCode($id_country)
	{
		if (!(int)$id_country)
			return false;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `need_zip_code`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);
	}

	public static function getZipCodeFormat($id_country)
	{
		if (!(int)$id_country)
			return false;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `zip_code_format`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);
	}

	/**
	 * Returns the default country Id
	 *
	 * @deprecated as of 1.5 use $context->country->id instead
	 * @return integer default country id
	 */
	public static function getDefaultCountryId()
	{
		Tools::displayAsDeprecated();
		return Context::getContext()->country->id;
	}

    public static function getCountriesByZoneId($id_zone, $id_lang, Shop $shop = null)
    {
        if (empty($id_zone) || empty($id_lang))
            die(Tools::displayError());

        if (!$shop)
        	$shop = Context::getContext()->shop;

		$sql = ' SELECT DISTINCT c.*, cl.*
        		FROM `'._DB_PREFIX_.'country` c
				'.$shop->addSqlAssociation('country', 'c', false).'
				LEFT JOIN `'._DB_PREFIX_.'state` s ON (s.`id_country` = c.`id_country`)
		        LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
        		WHERE (c.`id_zone` = '.(int)$id_zone.' OR s.`id_zone` = '.(int)$id_zone.')
        			AND `id_lang` = '.(int)$id_lang;
        return Db::getInstance()->executeS($sql);
    }

	public function isNeedDni()
	{
		return (bool)self::isNeedDniByCountryId($this->id);
	}

	public static function isNeedDniByCountryId($id_country)
	{
		return (bool)Db::getInstance()->getValue('
			SELECT `need_identification_number`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)$id_country);
	}

	public static function containsStates($id_country)
	{
		return (bool)Db::getInstance()->getValue('
			SELECT `contains_states`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)$id_country);
	}

	/**
	 * @param $ids_countries
	 * @param $id_zone
	 * @return bool
	 */
	public function affectZoneToSelection($ids_countries, $id_zone)
	{
		// cast every array values to int (security)
		$ids_countries = array_map('intval', $ids_countries);
		return Db::getInstance()->execute('
		UPDATE `'._DB_PREFIX_.'country` SET `id_zone` = '.(int)$id_zone.' WHERE `id_country` IN ('.implode(',', $ids_countries).')
		');
	}
}
