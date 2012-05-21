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
*  @version  Release: $Revision: 7060 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminCartRulesControllerCore extends AdminController
{
	public function __construct()
	{
		$this->table = 'cart_rule';
	 	$this->className = 'CartRule';
	 	$this->lang = true;
		$this->addRowAction('delete');
		$this->addRowAction('edit');
	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->fieldsDisplay = array(
			'id_cart_rule' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Code')),
			'priority' => array('title' => $this->l('Priority')),
			'code' => array('title' => $this->l('Code')),
			'quantity' => array('title' => $this->l('Quantity')),
			'date_to' => array('title' => $this->l('Until')),
			'active' => array('title' => $this->l('Status'), 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false),
		);

		parent::__construct();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAddcart_rule') || Tools::isSubmit('submitAddcart_ruleAndStay'))
		{
			// These are checkboxes (which aren't sent through POST when they are not check), so they are forced to 0
			foreach (array('country', 'carrier', 'group', 'cart_rule', 'product') as $type)
				if (!Tools::getValue($type.'_restriction'))
					$_POST[$type.'_restriction'] = 0;

			// Idiot-proof control
			if (strtotime(Tools::getValue('date_from')) > strtotime(Tools::getValue('date_to')))
				$this->_errors[] = Tools::displayError('The voucher cannot end before it begins');
			if ((int)Tools::getValue('minimum_amount') < 0)
				$this->_errors[] = Tools::displayError('Minimum amount cannot be lower than 0');
			if ((float)Tools::getValue('reduction_percent') < 0 || (float)Tools::getValue('reduction_percent') > 100)
				$this->_errors[] = Tools::displayError('Reduction percent must range from 0% to 100%');
			if ((int)Tools::getValue('reduction_amount') < 0)
				$this->_errors[] = Tools::displayError('Reduction amount cannot be lower than 0');
		}

		return parent::postProcess();
	}

	public function afterUpdate($currentObject)
	{
		// All the associations are deleted for an update, then recreated when we call the "afterAdd" method
		$id_cart_rule = Tools::getValue('id_cart_rule');
		foreach (array('country', 'carrier', 'group', 'product_rule') as $type)
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_'.$type.'` WHERE `id_cart_rule` = '.(int)$id_cart_rule);
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_product_rule_value` WHERE `id_product_rule` NOT IN (SELECT `id_product_rule` FROM `'._DB_PREFIX_.'cart_rule_product_rule`)');
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_combination` WHERE `id_cart_rule_1` = '.(int)$id_cart_rule.' OR `id_cart_rule_2` = '.(int)$id_cart_rule);

		$this->afterAdd($currentObject);
	}

	// TODO Move this function into CartRule
	public function afterAdd($currentObject)
	{
		// Add restrictions for generic entities like country, carrier and group
		foreach (array('country', 'carrier', 'group') as $type)
			if (Tools::getValue($type.'_restriction') && is_array($array = Tools::getValue($type.'_select')) && count($array))
			{
				$values = array();
				foreach ($array as $id)
					$values[] = '('.(int)$currentObject->id.','.(int)$id.')';
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_'.$type.'` (`id_cart_rule`, `id_'.$type.'`) VALUES '.implode(',', $values));
			}
		// Add cart rule restrictions
		if (Tools::getValue('cart_rule_restriction') && is_array($array = Tools::getValue('cart_rule_select')) && count($array))
		{
			$values = array();
			foreach ($array as $id)
				$values[] = '('.(int)$currentObject->id.','.(int)$id.')';
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) VALUES '.implode(',', $values));
		}
		// Add product rule restrictions
		if (Tools::getValue('product_restriction') && is_array($array = Tools::getValue('product_rule')) && count($array))
		{
			foreach ($array as $id)
			{
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule` (`id_cart_rule`, `quantity`, `type`)
				VALUES ('.(int)$currentObject->id.', '.(int)Tools::getValue('product_rule_'.$id.'_quantity').', "'.pSQL(Tools::getValue('product_rule_'.$id.'_type')).'")');
				$id_product_rule = Db::getInstance()->Insert_ID();

				$values = array();
				foreach (Tools::getValue('product_rule_select_'.$id) as $id)
					$values[] = '('.(int)$id_product_rule.','.(int)$id.')';
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES '.implode(',', $values));
			}
		}

		// If the new rule has no cart rule restriction, then it must be added to the white list of the other cart rules that have restrictions
		if ($currentObject->cart_rule_restriction == 0)
		{
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
				SELECT id_cart_rule, '.(int)$currentObject->id.' FROM `'._DB_PREFIX_.'cart_rule` WHERE cart_rule_restriction = 1
			)');
		}
		// And if the new cart rule has restrictions, previously unrestricted cart rules may now be restricted (a mug of coffee is strongly advised to understand this sentence)
		else
		{
			$ruleCombinations = Db::getInstance()->ExecuteS('
			SELECT cr.id_cart_rule
			FROM '._DB_PREFIX_.'cart_rule cr
			WHERE cr.id_cart_rule != '.(int)$currentObject->id.'
			AND cr.id_cart_rule NOT IN (
				SELECT IF(id_cart_rule_1 = '.(int)$currentObject->id.', id_cart_rule_2, id_cart_rule_1)
				FROM '._DB_PREFIX_.'cart_rule_combination
				WHERE '.(int)$currentObject->id.' = id_cart_rule_1
				OR '.(int)$currentObject->id.' = id_cart_rule_2
			)');
			foreach ($ruleCombinations as $incompatibleRule)
			{
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = '.(int)$incompatibleRule['id_cart_rule'].' LIMIT 1');
				Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
					SELECT id_cart_rule, '.(int)$incompatibleRule['id_cart_rule'].' FROM `'._DB_PREFIX_.'cart_rule` WHERE active = 1 AND id_cart_rule != '.(int)$currentObject->id.'
				)');
			}
		}
	}

	public function getProductRuleDisplay($product_rule_id, $product_rule_type, $product_rule_quantity = 1, $selected = array())
	{
		Context::getContext()->smarty->assign(
			array(
				'product_rule_id' => (int)$product_rule_id,
				'product_rule_type' => $product_rule_type,
				'product_rule_quantity' => (int)$product_rule_quantity
			)
		);

		switch ($product_rule_type)
		{
			case 'attributes':
				$attributes = array('selected' => array(), 'unselected' => array());
				$results = Db::getInstance()->ExecuteS('
				SELECT CONCAT(agl.name, " - ", al.name) as name, a.id_attribute as id
				FROM '._DB_PREFIX_.'attribute_group_lang agl
				LEFT JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute_group = agl.id_attribute_group
				LEFT JOIN '._DB_PREFIX_.'attribute_lang al ON (a.id_attribute = al.id_attribute AND al.id_lang = '.(int)Context::getContext()->language->id.')
				WHERE agl.id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY agl.name, al.name');
				foreach ($results as $row)
					$attributes[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
				Context::getContext()->smarty->assign('product_rule_itemlist', $attributes);
				$choose_content = Context::getContext()->smarty->fetch('cart_rules/product_rule_itemlist.tpl');
				Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
				break;
			case 'products':
				// Todo: Consider optimization
				$products = array('selected' => array(), 'unselected' => array());
				$results = Db::getInstance()->ExecuteS('
				SELECT name, id_product as id
				FROM '._DB_PREFIX_.'product_lang pl
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
				foreach ($results as $row)
					$products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
				Context::getContext()->smarty->assign('product_rule_itemlist', $products);
				$choose_content = Context::getContext()->smarty->fetch('cart_rules/product_rule_itemlist.tpl');
				Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
				break;
			case 'categories':
				// Todo: Consider optimization
				$categories = array('selected' => array(), 'unselected' => array());
				$results = Db::getInstance()->ExecuteS('
				SELECT name, id_category as id
				FROM '._DB_PREFIX_.'category_lang pl
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
				foreach ($results as $row)
					$categories[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
				Context::getContext()->smarty->assign('product_rule_itemlist', $categories);
				$choose_content = Context::getContext()->smarty->fetch('cart_rules/product_rule_itemlist.tpl');
				Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
				break;
			default:
				die;
		}

		return Context::getContext()->smarty->fetch('cart_rules/product_rule.tpl');
	}

	public function ajaxProcess()
	{
		if (Tools::isSubmit('newProductRule'))
			die ($this->getProductRuleDisplay(Tools::getValue('product_rule_id'), Tools::getValue('product_rule_type')));

		if (Tools::isSubmit('customerFilter'))
		{
			$q = trim(Tools::getValue('q'));
			$customers = Db::getInstance()->ExecuteS('
			SELECT `id_customer`, `email`, CONCAT(`firstname`, \' \', `lastname`) as cname
			FROM `'._DB_PREFIX_.'customer`
			WHERE `deleted` = 0 AND is_guest = 0 AND active = 1
			AND (
				`id_customer` = '.(int)$q.'
				OR `email` LIKE "%'.pSQL($q).'%"
				OR `firstname` LIKE "%'.pSQL($q).'%"
				OR `lastname` LIKE "%'.pSQL($q).'%"
			)
			ORDER BY `firstname`, `lastname` ASC
			LIMIT 50');
			die(Tools::jsonEncode($customers));
		}
		// Both product filter (free product and product discount) search for products
		if (Tools::isSubmit('giftProductFilter') || Tools::isSubmit('reductionProductFilter'))
		{
			$products = Product::searchByName(Context::getContext()->language->id, trim(Tools::getValue('q')));
			die(Tools::jsonEncode($products));
		}
	}

	public function getProductRulesDisplay($cartRule)
	{
		$i = 1;
		$productRulesArray = array();
		if (Tools::getValue('product_restriction') && is_array($array = Tools::getValue('product_rule')) && count($array))
		{
			foreach ($array as $id)
			{
				$productRulesArray[] = $this->getProductRuleDisplay(
					$i++,
					Tools::getValue('product_rule_'.$id.'_type'),
					(int)Tools::getValue('product_rule_'.$id.'_quantity'),
					Tools::getValue('product_rule_select_'.$id)
				);
			}
		}
		else
		{
			foreach ($cartRule->getProductRules() as $productRule)
				$productRulesArray[] = $this->getProductRuleDisplay($i++, $productRule['type'], $productRule['quantity'], $productRule['values']);
		}
		return $productRulesArray;
	}

	public function renderForm()
	{
		$back = Tools::safeOutput(Tools::getValue('back', ''));
		if (empty($back))
			$back = self::$currentIndex.'&token='.$this->token;

		$this->toolbar_btn['save-and-stay'] = array(
			'href' => '#',
			'desc' => $this->l('Save and Stay')
		);

		// Todo: change for "Media" version
		$this->addJs(_PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.js');
		$this->addJs(_PS_JS_DIR_.'jquery/plugins/autocomplete/jquery.autocomplete.js');
		$this->addCss(_PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css');
		$this->addCss(_PS_JS_DIR_.'jquery/plugins/autocomplete/jquery.autocomplete.css');

		$current_object = $this->loadObject(true);

		// All the filter are prefilled with the correct information
		$customer_filter = '';
		if (Validate::isUnsignedId($current_object->id_customer) AND
			$customer = new Customer($current_object->id_customer) AND
			Validate::isLoadedObject($customer))
			$customer_filter = $customer->firstname.' '.$customer->lastname.' ('.$customer->email.')';

		$gift_product_filter = '';
		if (Validate::isUnsignedId($current_object->gift_product) AND
			$product = new Product($current_object->gift_product, false, Context::getContext()->language->id) AND
			Validate::isLoadedObject($product))
			$gift_product_filter = trim($product->reference.' '.$product->name);

		$reduction_product_filter = '';
		if (Validate::isUnsignedId($current_object->reduction_product) AND
			$product = new Product($current_object->reduction_product, false, Context::getContext()->language->id) AND
			Validate::isLoadedObject($product))
			$reduction_product_filter = trim($product->reference.' '.$product->name);

		$product_rules = $this->getProductRulesDisplay($current_object);

		Context::getContext()->smarty->assign(
			array(
				'show_toolbar' => true,
				'toolbar_btn' => $this->toolbar_btn,
				'toolbar_fix' => $this->toolbar_fix,
				'title' => $this->l('Payment : Cart Rules '),
				'languages' => Language::getLanguages(),
				'defaultDateFrom' => date('Y-m-d H:00:00'),
				'defaultDateTo' => date('Y-m-d H:00:00', strtotime('+1 year')),
				'customerFilter' => $customer_filter,
				'giftProductFilter' => $gift_product_filter,
				'reductionProductFilter' => $reduction_product_filter,
				'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
				'defaultLanguage' => Configuration::get('PS_LANG_DEFAULT'),
				'currencies' => Currency::getCurrencies(),
				'countries' => $current_object->getAssociatedRestrictions('country', 1),
				'carriers' => $current_object->getAssociatedRestrictions('carrier', 1),
				'groups' => $current_object->getAssociatedRestrictions('group', 0),
				'cart_rules' => $current_object->getAssociatedRestrictions('cart_rule', 1),
				'product_rules' => $product_rules,
				'product_rules_counter' => count($product_rules),
				'attribute_groups' => AttributeGroup::getAttributesGroups(Context::getContext()->language->id),
				'currentIndex' => self::$currentIndex,
				'currentToken' => $this->token,
				'currentObject' => $current_object,
				'currentTab' => $this
			)
		);

		$this->content .= Context::getContext()->smarty->fetch('cart_rules/form.tpl');

		$this->addJqueryUI('ui.datepicker');
		return parent::renderForm();
	}

	public function displayAjaxSearchCartRuleVouchers()
	{
		$found = false;
		if ($vouchers = CartRule::getCartsRuleByCode(Tools::getValue('q'), (int)Context::getContext()->cookie->id_lang))
			$found = true;
		echo Tools::jsonEncode(array('found' => $found, 'vouchers' => $vouchers));
	}

}