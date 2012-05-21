<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision: 12460 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

include_once(dirname(__FILE__).'/MailAlert.php');

class MailAlerts extends Module
{
	private $_html = '';

	private $_merchant_mails;
	private $_merchant_order;
	private $_merchant_oos;
	private $_customer_qty;

	const __MA_MAIL_DELIMITOR__ = ',';

	public function __construct()
	{
		$this->name = 'mailalerts';
		$this->tab = 'administration';
		$this->version = '2.3';
		$this->author = 'PrestaShop';

		parent::__construct();
		
		if ($this->id)
			$this->init();

		$this->displayName = $this->l('Mail alerts');
		$this->description = $this->l('Sends e-mail notifications to customers and merchants.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete all customer notifications?');
	}
	
	private function init()
	{
		$this->_merchant_mails = strval(Configuration::get('MA_MERCHANT_MAILS'));
		$this->_merchant_order = (int)Configuration::get('MA_MERCHANT_ORDER');
		$this->_merchant_oos = (int)Configuration::get('MA_MERCHANT_OOS');
		$this->_customer_qty = (int)Configuration::get('MA_CUSTOMER_QTY');
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('actionValidateOrder')
			|| !$this->registerHook('actionUpdateQuantity')
			|| !$this->registerHook('actionProductOutOfStock')
			|| !$this->registerHook('displayCustomerAccount')
			|| !$this->registerHook('displayMyAccountBlock')
			|| !$this->registerHook('actionProductUpdate')
			|| !$this->registerHook('actionProductDelete')
			|| !$this->registerHook('actionProductAttributeDelete')
			|| !$this->registerHook('actionProductAttributeUpdate')
			|| !$this->registerHook('displayHeader')
		)
			return false;

		Configuration::updateValue('MA_MERCHANT_ORDER', 1);
		Configuration::updateValue('MA_MERCHANT_OOS', 1);
		Configuration::updateValue('MA_CUSTOMER_QTY', 1);
		Configuration::updateValue('MA_MERCHANT_MAILS', Configuration::get('PS_SHOP_EMAIL'));
		Configuration::updateValue('MA_LAST_QTIES', (int)Configuration::get('PS_LAST_QTIES'));

		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.MailAlert::$definition['table'].'` (
				`id_customer` int(10) unsigned NOT NULL,
				`customer_email` varchar(128) NOT NULL,
				`id_product` int(10) unsigned NOT NULL,
				`id_product_attribute` int(10) unsigned NOT NULL,
				`id_shop` int(10) unsigned NOT NULL,
				PRIMARY KEY  (`id_customer`,`customer_email`,`id_product`,`id_product_attribute`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

		if (!Db::getInstance()->Execute($sql))
			return false;

		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('MA_MERCHANT_ORDER');
		Configuration::deleteByName('MA_MERCHANT_OOS');
		Configuration::deleteByName('MA_CUSTOMER_QTY');
		Configuration::deleteByName('MA_MERCHANT_MAILS');
		Configuration::deleteByName('MA_LAST_QTIES');

		if (!Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.MailAlert::$definition['table']))
			return false;

		return parent::uninstall();
	}

	public function getContent()
	{
		$this->_postProcess();

		$this->_html = '<h2>'.$this->displayName.'</h2>';
		$this->_html .= $this->_displayForm();

		return $this->_html;
	}

	private function _postProcess()
	{
		$errors = array();

		if (Tools::isSubmit('submitMACustomer'))
		{
			if (!Configuration::updateValue('MA_CUSTOMER_QTY', (int)Tools::getValue('mA_customer_qty')))
				$errors[] = $this->l('Cannot update settings');
		}
		else if (Tools::isSubmit('submitMAMerchant'))
		{
			$emails = strval(Tools::getValue('ma_merchant_mails'));

			if (!$emails || empty($emails))
				$errors[] = $this->l('Please type one (or more) e-mail address');
			else
			{
				$emails = explode("\n", $emails);
				foreach ($emails as $k => $email)
				{
					$email = trim($email);
					if (!empty($email) && !Validate::isEmail($email))
					{
						$errors[] = $this->l('Invalid e-mail:').' '.$email;
						break;
					}
					else if (!empty($email) && sizeof($email))
						$emails[$k] = $email;
					else
						unset($emails[$k]);
				}
				$emails = implode(self::__MA_MAIL_DELIMITOR__, $emails);
				if (!Configuration::updateValue('MA_MERCHANT_MAILS', strval($emails)))
					$errors[] = $this->l('Cannot update settings');
				elseif (!Configuration::updateValue('MA_MERCHANT_ORDER', (int)Tools::getValue('mA_merchand_order')))
					$errors[] = $this->l('Cannot update settings');
				elseif (!Configuration::updateValue('MA_MERCHANT_OOS', (int)Tools::getValue('mA_merchand_oos')))
					$errors[] = $this->l('Cannot update settings');
				elseif (!Configuration::updateValue('MA_LAST_QTIES', (int)Tools::getValue('MA_LAST_QTIES')))
					$errors[] = $this->l('Cannot update settings');
			}
		}

		if (sizeof($errors))
			echo $this->displayError(implode('<br />', $errors));

		$this->init();
	}

	public function _displayForm()
	{
		return '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Customer notification').'</legend>
				<label>'.$this->l('Product availability:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_customer_qty" name="mA_customer_qty" '.(Tools::getValue('mA_customer_qty', $this->_customer_qty) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_customer_qty" class="t">'.$this->l('Gives the customer the option of receiving a notification for an available product if this one is out of stock.').'</label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitMACustomer" class="button" />
				</div>
			</fieldset>
		</form>
		<br />
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" />'.$this->l('Merchant notification').'</legend>
				<label>'.$this->l('New order:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_merchand_order" name="mA_merchand_order" '.(Tools::getValue('mA_merchand_order', $this->_merchant_order) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_merchand_order" class="t">'.$this->l('Receive a notification if a new order is made').'</label>
				</div>
				<label>'.$this->l('Out of stock:').' </label>
				<div class="margin-form">
					<input type="checkbox" value="1" id="mA_merchand_oos" name="mA_merchand_oos" '.(Tools::getValue('mA_merchand_oos', $this->_merchant_oos) == 1 ? 'checked' : '').'>
					&nbsp;<label for="mA_merchand_oos" class="t">'.$this->l('Receive a notification if the quantity of a product is below the alert threshold').'</label>
				</div>
				<label>'.$this->l('Alert threshold:').'</label>
				<div class="margin-form">
					<input type="text" name="MA_LAST_QTIES" value="'.(Tools::getValue('MA_LAST_QTIES') != NULL ? (int)(Tools::getValue('MA_LAST_QTIES')) : Configuration::get('MA_LAST_QTIES')).'" size="3" />
					<p>'.$this->l('Quantity for which a product is regarded as out of stock').'</p>
				</div>
				<label>'.$this->l('Send to these e-mail addresses:').' </label>
				<div class="margin-form">
					<div style="float:left; margin-right:10px;">
						<textarea name="ma_merchant_mails" rows="10" cols="30">'.Tools::getValue('ma_merchant_mails', str_replace(self::__MA_MAIL_DELIMITOR__, "\n", $this->_merchant_mails)).'</textarea>
					</div>
					<div style="float:left;">
						'.$this->l('One e-mail address per line').'<br />
						'.$this->l('e.g.,').' bob@example.com
					</div>
				</div>
				<div style="clear:both;">&nbsp;</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitMAMerchant" class="button" />
				</div>
			</fieldset>
		</form>';
	}

	public function hookActionValidateOrder($params)
	{
		if (!$this->_merchant_order OR empty($this->_merchant_mails))
			return;

		// Getting differents vars
		$id_lang = (int)Context::getContext()->language->id;
	 	$currency = $params['currency'];
		$configuration = Configuration::getMultiple(array('PS_SHOP_EMAIL', 'PS_MAIL_METHOD', 'PS_MAIL_SERVER', 'PS_MAIL_USER', 'PS_MAIL_PASSWD', 'PS_SHOP_NAME'));
		$order = $params['order'];
		$customer = $params['customer'];
		$delivery = new Address((int)$order->id_address_delivery);
		$invoice = new Address((int)$order->id_address_invoice);
		$order_date_text = Tools::displayDate($order->date_add, (int)$id_lang);
		$carrier = new Carrier((int)$order->id_carrier);
		$message = $order->getFirstMessage();

		if (!$message || empty($message))
			$message = $this->l('No message');

		$itemsTable = '';

		$products = $params['order']->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas((int)$params['cart']->id);
		Product::addCustomizationPrice($products, $customizedDatas);
		foreach ($products as $key => $product)
		{
			$unit_price = $product['product_price_wt'];
			$price = $product['total_price'];

			$customizationText = '';
			if (isset($customizedDatas[$product['product_id']][$product['product_attribute_id']]))
			{

				foreach ($customizedDatas[$product['product_id']][$product['product_attribute_id']] AS $customization)
				{
					if (isset($customization['datas'][_CUSTOMIZE_TEXTFIELD_]))
						foreach ($customization['datas'][_CUSTOMIZE_TEXTFIELD_] as $text)
							$customizationText .= $text['name'].':'.' '.$text['value'].'<br />';

					if (isset($customization['datas'][_CUSTOMIZE_FILE_]))
						$customizationText .= sizeof($customization['datas'][_CUSTOMIZE_FILE_]) .' '. Tools::displayError('image(s)').'<br />';

					$customizationText .= '---<br />';
				}

				$customizationText = rtrim($customizationText, '---<br />');
			}

			$itemsTable .=
				'<tr style="background-color:'.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
					<td style="padding:0.6em 0.4em;">'.$product['product_reference'].'</td>
					<td style="padding:0.6em 0.4em;"><strong>'.$product['product_name'].(isset($product['attributes_small']) ? ' '.$product['attributes_small'] : '').(!empty($customizationText) ? '<br />'.$customizationText : '').'</strong></td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice($unit_price, $currency, false).'</td>
					<td style="padding:0.6em 0.4em; text-align:center;">'.(int)($product['product_quantity']).'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">'.Tools::displayPrice(($unit_price * $product['product_quantity']), $currency, false).'</td>
				</tr>';
		}
		foreach ($params['order']->getCartRules() as $discount)
		{
			$itemsTable .=
			'<tr style="background-color:#EBECEE;">
					<td colspan="4" style="padding:0.6em 0.4em; text-align:right;">'.$this->l('Voucher code:').' '.$discount['name'].'</td>
					<td style="padding:0.6em 0.4em; text-align:right;">-'.Tools::displayPrice($discount['value'], $currency, false).'</td>
			</tr>';
		}
		if ($delivery->id_state)
			$delivery_state = new State((int)$delivery->id_state);
		if ($invoice->id_state)
			$invoice_state = new State((int)$invoice->id_state);

		// Filling-in vars for email
		$template = 'new_order';
		$subject = $this->l('New order', $id_lang).' - '.sprintf('%06d', $order->id);
		$templateVars = array(
			'{firstname}' => $customer->firstname,
			'{lastname}' => $customer->lastname,
			'{email}' => $customer->email,
			'{delivery_block_txt}' => MailAlert::getFormatedAddress($delivery, "\n"),
			'{invoice_block_txt}' => MailAlert::getFormatedAddress($invoice, "\n"),
			'{delivery_block_html}' => MailAlert::getFormatedAddress($delivery, "<br />", array(
							'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
							'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>')),
			'{invoice_block_html}' => MailAlert::getFormatedAddress($invoice, "<br />", array(
							'firstname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>',
							'lastname'	=> '<span style="color:#DB3484; font-weight:bold;">%s</span>')),
			'{delivery_company}' => $delivery->company,
			'{delivery_firstname}' => $delivery->firstname,
			'{delivery_lastname}' => $delivery->lastname,
			'{delivery_address1}' => $delivery->address1,
			'{delivery_address2}' => $delivery->address2,
			'{delivery_city}' => $delivery->city,
			'{delivery_postal_code}' => $delivery->postcode,
			'{delivery_country}' => $delivery->country,
			'{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
			'{delivery_phone}' => $delivery->phone,
			'{delivery_other}' => $delivery->other,
			'{invoice_company}' => $invoice->company,
			'{invoice_firstname}' => $invoice->firstname,
			'{invoice_lastname}' => $invoice->lastname,
			'{invoice_address2}' => $invoice->address2,
			'{invoice_address1}' => $invoice->address1,
			'{invoice_city}' => $invoice->city,
			'{invoice_postal_code}' => $invoice->postcode,
			'{invoice_country}' => $invoice->country,
			'{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
			'{invoice_phone}' => $invoice->phone,
			'{invoice_other}' => $invoice->other,
			'{order_name}' => sprintf("%06d", $order->id),
			'{shop_name}' => Configuration::get('PS_SHOP_NAME'),
			'{date}' => $order_date_text,
			'{carrier}' => (($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name),
			'{payment}' => Tools::substr($order->payment, 0, 32),
			'{items}' => $itemsTable,
			'{total_paid}' => Tools::displayPrice($order->total_paid, $currency),
			'{total_products}' => Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency),
			'{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency),
			'{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency),
			'{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency),
			'{currency}' => $currency->sign,
			'{message}' => $message
		);

		$iso = Language::getIsoById($id_lang);

		if (file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.txt') && file_exists(dirname(__FILE__).'/mails/'.$iso.'/'.$template.'.html'))
			Mail::Send($id_lang, $template, $subject, $templateVars, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, $configuration['PS_SHOP_EMAIL'], $configuration['PS_SHOP_NAME'], NULL, NULL, dirname(__FILE__).'/mails/');
	}

	public function hookActionProductOutOfStock($params)
	{
		if (!$this->_customer_qty)
			return ;

		$id_product_attribute = 0;
		$id_product = (int)$params['product']->id;
		$id_customer = (int)Context::getContext()->customer->id;

		if (!(int)Context::getContext()->customer->isLogged())
			$this->context->smarty->assign('email', 1);
		else if (MailAlert::customerHasNotification($id_customer, $id_product, $id_product_attribute))
			return ;
		
		$this->context->smarty->assign(array(
							'id_product' => $id_product,
							'id_product_attribute' => $id_product_attribute));

		return $this->display(__FILE__, 'product.tpl');
	}

	public function hookActionUpdateQuantity($params)
	{
		$product = $params['product'];
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		if ((int)$product->quantity <= (int)Configuration::get('MA_LAST_QTIES') &&
		    !(!$this->_merchant_oos || empty($this->_merchant_mails)) && StockAvailable::dependsOnStock($product->id))
		{
			$templateVars = array('{qty}' => (int)$product->quantity,
					      '{last_qty}' => (int)Configuration::get('MA_LAST_QTIES'),
					      '{product}' => strval((int)$product->name).(isset($product->attributes_small) ? ' '.$product->attributes_small : ''));
			
			$iso = Language::getIsoById($id_lang);
			
			if ($product->active == 1)
				if (file_exists(dirname(__FILE__).'/mails/'.$iso.'/productoutofstock.txt') && file_exists(dirname(__FILE__).'/mails/'.$iso.'/productoutofstock.html'))
					Mail::Send($id_lang, 'productoutofstock', Mail::l('Product out of stock', $id_lang), $templateVars, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');
		}
		if ($this->_customer_qty && $product->quantity > 0)
			MailAlert::sendCustomerAlert((int)$product->id, (int)$params['attribute_id']);
	}

	public function hookActionProductUpdate($params)
	{
		/* We specify 0 as an id_product_attribute because this hook is called when the main product is updated */
		if ($this->_customer_qty && $product->quantity > 0)
			MailAlert::sendCustomerAlert((int)$params['product']->id, 0);
	}

	public function hookActionProductAttributeUpdate($params)
	{
		$sql = 'SELECT `id_product`, `quantity`
			FROM `'._DB_PREFIX_.'stock_available`
			WHERE `id_product_attribute` = '.(int)$params['id_product_attribute'];

		$result = Db::getInstance()->getRow($sql);

		if ($this->_customer_qty && $result['quantity'] > 0)
			MailAlert::sendCustomerAlert((int)$result['id_product'], (int)$params['id_product_attribute']);
	}
	
	public function hookDisplayCustomerAccount($params)
	{
		return $this->_customer_qty ? $this->display(__FILE__, 'my-account.tpl') : NULL;
	}
	
	public function hookDisplayMyAccountBlock($params)
	{
		return $this->hookDisplayCustomerAccount($params);
	}
	
	public function hookActionProductDelete($params)
	{
		$sql = 'DELETE FROM `'._DB_PREFIX_.MailAlert::$definition['table'].'`
			WHERE `id_product` = '.(int)$params['product']->id;
		
		Db::getInstance()->Execute($sql);
	}
	
	public function hookActionAttributeDelete($params)
	{
		if ($params['deleteAllAttributes'])
			$sql = 'DELETE FROM `'._DB_PREFIX_.MailAlert::$definition['table'].'`
				WHERE `id_product` = '.(int)$params['id_product'];
		else
			$sql = 'DELETE FROM `'._DB_PREFIX_.MailAlert::$definition['table'].'`
				WHERE `id_product_attribute` = '.(int)$params['id_product_attribute'].'
				AND `id_product` = '.(int)$params['id_product'];

		Db::getInstance()->Execute($sql);
	}

	public function hookDisplayHeader($params)
	{
	    $this->context->controller->addCSS($this->_path.'mailalerts.css', 'all');
	}
}
