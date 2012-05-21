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


class TaxRulesGroupCore extends ObjectModel
{
    public $name;

    /** @var bool active state */
    public $active;

 	protected $fieldsRequired = array('name');
 	protected $fieldsSize = array('name' => 64);
 	protected $fieldsValidate = array('name' => 'isGenericName');

	protected $table = 'tax_rules_group';
	protected $identifier = 'id_tax_rules_group';

    protected static $_taxes = array();

	public function getFields()
	{
		$this->validateFields();
		$fields['name'] = ($this->name);
		$fields['active'] = (int)$this->active;
		return $fields;
	}

	public static function getTaxRulesGroups($only_active = true)
	{
	    return Db::getInstance()->executeS('
	    SELECT *
	    FROM `'._DB_PREFIX_.'tax_rules_group` g'
	    .($only_active ? ' WHERE g.`active` = 1' : '')
	    );
	}

	/**
	* @return array an array of tax rules group formatted as $id => $name
	*/
	public static function getTaxRulesGroupsForOptions()
	{
		$tax_rules[] = array('id_tax_rules_group' => 0, 'name' => Tools::displayError('No tax'));
		return array_merge($tax_rules, TaxRulesGroup::getTaxRulesGroups());
	}


	/**
	* @return array
	*/
	public static function getAssociatedTaxRatesByIdCountry($id_country)
	{
	    $rows = Db::getInstance()->executeS('
	    SELECT rg.`id_tax_rules_group`, t.`rate`
	    FROM `'._DB_PREFIX_.'tax_rules_group` rg
   	    LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (tr.`id_tax_rules_group` = rg.`id_tax_rules_group`)
	    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
	    WHERE tr.`id_country` = '.(int)$id_country.'
	    AND tr.`id_state` = 0
	    AND 0 between `zipcode_from` AND `zipcode_to`'
	    );

	    $res = array();
	    foreach ($rows as $row)
	        $res[$row['id_tax_rules_group']] = $row['rate'];

	    return $res;
	}

	/**
	* Returns the tax rules group id corresponding to the name
	*
	* @param string name
	* @return int id of the tax rules
	*/
	public static function getIdByName($name)
	{
	    return Db::getInstance()->getValue(
	    'SELECT `id_tax_rules_group`
	    FROM `'._DB_PREFIX_.'tax_rules_group` rg
	    WHERE `name` = \''.pSQL($name).'\''
	    );
	}

	/**
	* @deprecated since 1.5
	*/
	public static function getTaxesRate($id_tax_rules_group, $id_country, $id_state, $zipcode)
	{
		Tools::displayAsDeprecated();
	    $rate = 0;
	    foreach (TaxRulesGroup::getTaxes($id_tax_rules_group, $id_country, $id_state, $zipcode) as $tax)
	        $rate += (float)$tax->rate;

	    return $rate;
	}

	/**
	 * Return taxes associated to this para
	 * @deprecated since 1.5
	 */
	public static function getTaxes($id_tax_rules_group, $id_country, $id_state, $id_county)
	{
		Tools::displayAsDeprecated();
		return array();
	}

}

