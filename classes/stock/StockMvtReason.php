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
*  @version  Release: $Revision: 10055 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class StockMvtReasonCore extends ObjectModel
{
	/** @var int identifier of the movement reason */
	public $id;

	/** @var string the name of the movement reason */
	public $name;

	/** @var int detrmine if the movement reason correspond to a positive or negative operation */
	public $sign;

	/** @var string the creation date of the movement reason */
	public $date_add;

	/** @var string the last update date of the movement reason */
	public $date_upd;

	/** @var boolean True if the movement reason has been deleted (staying in database as deleted) */
	public $deleted = 0;

	protected $table = 'stock_mvt_reason';
	protected $identifier = 'id_stock_mvt_reason';
 	protected $fieldsRequiredLang = array('name');
 	protected $fieldsSizeLang = array('name' => 255);
 	protected $fieldsValidateLang = array('name' => 'isGenericName');

	protected $webserviceParameters = array(
		'objectsNodeName' => 'stock_movement_reasons',
		'objectNodeName' => 'stock_movement_reason',
	);

	public function getFields()
	{
		$this->validateFields();
		$fields['sign'] = (int)$this->sign;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['deleted'] = (int)$this->deleted;
		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		$this->validateFieldsLang();
		return $this->getTranslationsFields(array('name'));
	}

	public static function getStockMvtReasons($id_lang, $sign = null)
	{
		$query = new DbQuery();
		$query->select('smrl.name, smr.id_stock_mvt_reason, smr.sign');
		$query->from('stock_mvt_reason smr');
		$query->leftjoin('stock_mvt_reason_lang smrl ON (smr.id_stock_mvt_reason = smrl.id_stock_mvt_reason AND smrl.id_lang='.(int)$id_lang.')');
		$query->where('smr.deleted = 0');

		if ($sign != null)
			$query->where('smr.sign = '.(int)$sign);

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}

	/**
	 * Same as StockMvtReason::getStockMvtReasons(), ignoring a specific lists of ids
	 *
	 * @since 1.5.0
	 * @param int $id_lang
	 * @param array $ids_ignore
	 * @param int $sign optional
	 */
	public static function getStockMvtReasonsWithFilter($id_lang, $ids_ignore, $sign = null)
	{
		$query = new DbQuery();
		$query->select('smrl.name, smr.id_stock_mvt_reason, smr.sign');
		$query->from('stock_mvt_reason smr');
		$query->leftjoin('stock_mvt_reason_lang smrl ON (smr.id_stock_mvt_reason = smrl.id_stock_mvt_reason AND smrl.id_lang='.(int)$id_lang.')');
		$query->where('smr.deleted = 0');

		if ($sign != null)
			$query->where('smr.sign = '.(int)$sign);

		if (count($ids_ignore))
		{
			$ids_ignore = array_map('intval', $ids_ignore);
			$query->where('smr.id_stock_mvt_reason NOT IN('.implode(', ', $ids_ignore).')');
		}

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
	}

	/**
	 * @since 1.5.0
	 *
	 * @param int $id_stock_mvt_reason
	 * @return bool
	 */
	public static function exists($id_stock_mvt_reason)
	{
		$query = new DbQuery();
		$query->select('smr.id_stock_mvt_reason');
		$query->from('stock_mvt_reason smr');
		$query->where('smr.id_stock_mvt_reason = '.(int)$id_stock_mvt_reason);
		$query->where('smr.deleted = 0');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
	}
}
