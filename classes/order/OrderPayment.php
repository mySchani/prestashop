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

class OrderPaymentCore extends ObjectModel
{
	public $id_order;
	public $id_currency;
	public $amount;
	public $payment_method;
	public $conversion_rate;
	public $transaction_id;
	public $card_number;
	public $card_brand;
	public $card_expiration;
	public $card_holder;
	public $date_add;

	protected	$fieldsRequired = array('id_order', 'id_currency', 'amount');
	protected	$fieldsSize = array('transaction_id' => 254, 'card_number' => 254, 'card_brand' => 254, 'card_expiration' => 254, 'card_holder' => 254);
	protected	$fieldsValidate = array(
		'id_order' => 'isUnsignedId',
		'id_currency' => 'isUnsignedId',
		'amount' => 'isPrice',
		'payment_method' => 'isName',
		'conversion_rate' => 'isFloat',
		'transaction_id' => 'isAnything',
		'card_number' => 'isAnything',
		'card_brand' => 'isAnything',
		'card_expiration' => 'isAnything',
		'card_holder' => 'isAnything'
	);

	protected 	$table = 'order_payment';
	protected 	$identifier = 'id_order_payment';

	public function getFields()
	{
		$this->validateFields();
		$fields['id_order'] = (int)($this->id_order);
		$fields['id_currency'] = (int)($this->id_currency);
		$fields['amount'] = (float)($this->amount);
		$fields['payment_method'] = pSQL($this->payment_method);
		$fields['transaction_id'] = pSQL($this->transaction_id);
		$fields['card_number'] = pSQL($this->card_number);
		$fields['card_brand'] = pSQL($this->card_brand);
		$fields['card_expiration'] = pSQL($this->card_expiration);
		$fields['card_holder'] = pSQL($this->card_holder);
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}

	public function add($autodate = true, $nullValues = false)
	{
		if (parent::add($autodate, $nullValues))
		{
			Hook::exec('paymentCCAdded', array('paymentCC' => $this));
			return true;
		}
		return false;
	}

	/**
	* Get the detailed payment of an order
	* @param int $id_order
	* @return array
	*/
	public static function getByOrderId($id_order)
	{
		return Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'payment_order`
			WHERE `id_order` = '.(int)$id_order);
	}
}
