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
*  @version  Release: $Revision: 7310 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
/**
 * @since 1.5.0
 */
class ProductSupplierCore extends ObjectModel
{
	/**
	 * @var integer product ID
	 * */
	public $id_product;

	/**
	 * @var integer product attribute ID
	 * */
	public $id_product_attribute;

	/**
	 * @var integer the supplier ID
	 * */
	public $id_supplier;

	/**
	 * @var string The supplier reference of the product
	 * */
	public $product_supplier_reference;

	/**
	 * @var integer the currency ID for unit price tax excluded
	 * */
	public $id_currency;

	/**
	 * @var string The unit price tax excluded of the product
	 * */
	public $product_supplier_price_te;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'product_supplier',
		'primary' => 'id_product_supplier',
		'fields' => array(
			'product_supplier_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32),
			'id_product' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_product_attribute' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_supplier' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'product_supplier_price_te' => 	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'id_currency' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
		),
	);

	/**
	 * For a given product and supplier, get the product reference
	 *
	 * @param int $id_product
	 * @param int $id_product_attribute
	 * @param int $id_supplier
	 * @return array
	 */
	public static function getProductSupplierReference($id_product, $id_product_attribute, $id_supplier)
	{
		// build query
		$query = new DbQuery();
		$query->select('ps.product_supplier_reference');
		$query->from('product_supplier', 'ps');
		$query->where('ps.id_product = '.(int)$id_product.'
			AND ps.id_product_attribute = '.(int)$id_product_attribute.'
			AND ps.id_supplier = '.(int)$id_supplier
		);

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	/**
	 * For a given product and supplier, get the product unit price
	 *
	 * @param int $id_product
	 * @param int $id_product_attribute
	 * @param int $id_supplier
	 * @return array
	 */
	public static function getProductSupplierPrice($id_product, $id_product_attribute, $id_supplier)
	{
		// build query
		$query = new DbQuery();
		$query->select('ps.product_supplier_price_te');
		$query->from('product_supplier', 'ps');
		$query->where('ps.id_product = '.(int)$id_product.'
			AND ps.id_product_attribute = '.(int)$id_product_attribute.'
			AND ps.id_supplier = '.(int)$id_supplier
		);

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	/**
	 * For a given product and supplier, get the ProductSupplier corresponding ID
	 *
	 * @param int $id_product
	 * @param int $id_product_attribute
	 * @param int $id_supplier
	 * @return array
	 */
	public static function getIdByProductAndSupplier($id_product, $id_product_attribute, $id_supplier)
	{
		// build query
		$query = new DbQuery();
		$query->select('ps.id_product_supplier');
		$query->from('product_supplier', 'ps');
		$query->where('ps.id_product = '.(int)$id_product.'
			AND ps.id_product_attribute = '.(int)$id_product_attribute.'
			AND ps.id_supplier = '.(int)$id_supplier
		);

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}

	/**
	 * For a given product, retrieves its suppliers
	 *
	 * @param int $id_product
	 * @param int $group_by_supplier
	 * @return Collection
	 */
	public static function getSupplierCollection($id_product, $group_by_supplier = true)
	{
		$suppliers = new Collection('ProductSupplier');
		$suppliers->where('id_product', '=', $id_product);

		if ($group_by_supplier)
			$suppliers->groupBy('id_supplier');

		return $suppliers;
	}

	public function delete()
	{
		$res = parent::delete();

		if ($res && $this->id_product_attribute == 0)
		{
			$items = self::getSupplierCollection($this->id_product, false);
			foreach ($items as $item)
			{
				if ($item->id_product_attribute > 0)
					$item->delete();
			}
		}

		return $res;
	}

	/**
	 * For a given Supplier, Product, returns the purchased price
	 *
	 * @param int $id_product
	 * @param int $id_product_attribute Optional
	 * @return Array keys: price_te, id_currency
	 */
	public static function getProductPrice($id_supplier, $id_product, $id_product_attribute = 0)
	{
		if (is_null($id_supplier) || is_null($id_product))
			return;

		$query = new DbQuery();
		$query->select('product_supplier_price_te as price_te, id_currency');
		$query->from('product_supplier');
		$query->where('id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute);
		$query->where('id_supplier = '.(int)$id_supplier);

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
	}
}
