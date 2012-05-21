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
*  @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


/**
* @deprecated since 1.5
*/
class CountyCore extends ObjectModel
{
	public $id;
	public $name;
	public $id_state;
	public $active;

	protected 	$fieldsRequired = array('name');
	protected 	$fieldsSize = array('name' => 64);
	protected 	$fieldsValidate = array('name' => 'isGenericName', 'id_state' => 'isUnsignedId', 'active' => 'isBool');

	protected 	$table = 'county';
	protected 	$identifier = 'id_county';

	private static $_cache_get_counties = array();
	private static $_cache_county_zipcode = array();

	const USE_BOTH_TAX = 0;
	const USE_COUNTY_TAX = 1;
	const USE_STATE_TAX = 2;

	protected	$webserviceParameters = array(
		'fields' => array(
			'id_state' => array('xlink_resource'=> 'states'),
		),
	);

	public function getFields()
	{
		$this->validateFields();
		$fields['id_state'] = (int)($this->id_state);
		$fields['name'] = pSQL($this->name);
		$fields['active'] = (int)($this->active);
		return $fields;
	}

	public function delete()
	{
		return true;
	}

	/**
	* @deprecated since 1.5
	*/
	public static function getCounties($id_state)
	{
		Tools::displayAsDeprecated();
		return false;
	}

	/**
	* @deprecated since 1.5
	*/
	public function getZipCodes()
	{
		Tools::displayAsDeprecated();
		return false;
	}

	/**
	* @deprecated since 1.5
	*/
	public function addZipCodes($zip_codes)
	{
		Tools::displayAsDeprecated();
		return true;
	}

	/**
	* @deprecated since 1.5
	*/
	public function removeZipCodes($zip_codes)
	{
		Tools::displayAsDeprecated();
		return true;
	}

	/**
	* @deprecated since 1.5
	*/
	public function breakDownZipCode($zip_codes)
	{
		Tools::displayAsDeprecated();
		return array(0,0);
	}

	/**
	* @deprecated since 1.5
	*/
	public static function getIdCountyByZipCode($id_state, $zip_code)
	{
		Tools::displayAsDeprecated();
		return false;
	}

	/**
	* @deprecated since 1.5
	*/
	public function isZipCodeRangePresent($zip_codes)
	{
		Tools::displayAsDeprecated();
		return false;
	}

	/**
	* @deprecated since 1.5
	*/
	public function isZipCodePresent($zip_code)
	{
		Tools::displayAsDeprecated();
		return false;
	}

	/**
	* @deprecated since 1.5
	*/
	public static function deleteZipCodeByIdCounty($id_county)
	{
		Tools::displayAsDeprecated();
		return true;
	}

	/**
	* @deprecated since 1.5
	*/
	public static function getIdCountyByNameAndIdState($name, $id_state)
	{
		Tools::displayAsDeprecated();
		return false;
	}

}

