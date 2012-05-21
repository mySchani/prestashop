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

class CustomerMessageCore extends ObjectModel
{
	public $id;
	public $id_customer_thread;
	public $id_employee;
	public $message;
	public $file_name;
	public $ip_address;
	public $user_agent;
	public $private;
	public $date_add;
	
	protected $table = 'customer_message';
	protected $identifier = 'id_customer_message';
	
	protected $fieldsRequired = array('message');
	protected $fieldsSize = array('message' => 65000);
	protected $fieldsValidate = array('message' => 'isCleanHtml', 'id_employee' => 'isUnsignedId', 'ip_address' => 'isIp2Long');

	public	function getFields()
	{
	 	$this->validateFields();
		$fields['id_customer_thread'] = (int)($this->id_customer_thread);
		$fields['id_employee'] = (int)($this->id_employee);
		$fields['message'] = pSQL($this->message);
		$fields['file_name'] = pSQL($this->file_name);
		$fields['ip_address'] = (int)($this->ip_address);
		$fields['user_agent'] = pSQL($this->user_agent);
		$fields['private'] = pSQL($this->private);
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
	
	public static function getMessagesByOrderId($id_order, $private = true)
	{
		return Db::getInstance()->executeS('
		SELECT cm.*, c.`firstname` AS cfirstname, c.`lastname` AS clastname, e.`firstname` AS efirstname, e.`lastname` AS elastname, (COUNT(cm.id_customer_message) = 0 AND ct.id_customer != 0) AS is_new_for_me
		FROM `'._DB_PREFIX_.'customer_message` cm
		LEFT JOIN `'._DB_PREFIX_.'customer_thread` ct ON ct.`id_customer_thread` = cm.`id_customer_thread`
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON ct.`id_customer` = c.`id_customer`
		LEFT OUTER JOIN `'._DB_PREFIX_.'employee` e ON e.`id_employee` = cm.`id_employee`
		WHERE ct.id_order = '.(int)$id_order.' AND '.(!$private ? 'cm.`private` = 0' : '').'
		GROUP BY cm.id_customer_message
		ORDER BY cm.date_add DESC');
	}
	
}

