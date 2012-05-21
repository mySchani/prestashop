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
*  @version  Release: $Revision: 11862 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminOrdersControllerCore extends AdminController
{
	public $toolbar_title;

	public function __construct()
	{
		$this->table = 'order';
		$this->className = 'Order';
		$this->lang = false;
		$this->addRowAction('view');

		$this->deleted = false;
		$this->requiredDatabase = false;
		$this->context = Context::getContext();

		$this->_select = '
			a.id_order AS id_pdf,
			CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
			osl.`name` AS `osname`,
			os.`color`,
			IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new,
			(SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = a.`id_order` GROUP BY `id_order`) AS product_number';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
		LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = a.`id_order`)
		LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
		LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
		$this->_where = 'AND oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = a.`id_order` GROUP BY moh.`id_order`)';
		$this->_orderBy = '`id_order`';
		$this->_orderWay = 'DESC'; // FIXME

		$statesArray = array();
		$states = OrderState::getOrderStates((int)$this->context->language->id);

		foreach ($states AS $state)
			$statesArray[$state['id_order_state']] = $state['name'];

		$this->fieldsDisplay = array(
		'id_order' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'reference' => array('title' => $this->l('Reference'), 'align' => 'center', 'width' => 65),
		'new' => array('title' => $this->l('New'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'filter_key' => 'new', 'tmpTableFilter' => true, 'icon' => array(0 => 'blank.gif', 1 => 'news-new.gif'), 'orderby' => false),
		'customer' => array('title' => $this->l('Customer'), 'filter_key' => 'customer', 'tmpTableFilter' => true),
		'total_paid_tax_incl' => array('title' => $this->l('Total'), 'width' => 70, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'type' => 'price', 'currency' => true),
		'payment' => array('title' => $this->l('Payment'), 'width' => 100),
		'osname' => array('title' => $this->l('Status'), 'color' => 'color', 'width' => 230, 'type' => 'select', 'list' => $statesArray, 'filter_key' => 'os!id_order_state', 'filter_type' => 'int'),
		'date_add' => array('title' => $this->l('Date'), 'width' => 150, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!date_add'),
		'id_pdf' => array('title' => $this->l('PDF'), 'width' => 35, 'align' => 'center', 'callback' => 'printPDFIcons', 'orderby' => false, 'search' => false, 'remove_onclick' => true));

		$this->shopLinkType = 'shop';
		$this->shopShareDatas = Shop::SHARE_ORDER;

		// Save context (in order to apply cart rule)
		$order = new Order((int)Tools::getValue('id_order'));
		$this->context->cart = new Cart($order->id_cart);
		$this->context->customer = new Customer($order->id_customer);

		parent::__construct();
	}

	public function renderForm()
	{
		parent::renderForm();
		$this->addJqueryPlugin(array('autocomplete', 'fancybox', 'typewatch'));
		$cart = new Cart((int)Tools::getValue('id_cart'));
		$this->context->smarty->assign(array(
			'recyclable_pack' => (int)Configuration::get('PS_RECYCLABLE_PACK'),
			'gift_wrapping' => (int)Configuration::get('PS_GIFT_WRAPPING'),
			'cart' => $cart,
			'currencies' => Currency::getCurrencies(),
			'langs' => Language::getLanguages(true, Context::getContext()->shop->id),
			'payment_modules' => PaymentModule::getInstalledPaymentModules(),
			'order_states' => OrderState::getOrderStates((int)Context::getContext()->cookie->id_lang)));
		$this->content .= $this->context->smarty->fetch('orders/form.tpl');
	}

	public function initToolbar()
	{
		if ($this->display == 'view')
		{
			$order = new Order((int)Tools::getValue('id_order'));
			if ($order->hasBeenDelivered()) $type = $this->l('Return products');
			elseif ($order->hasBeenPaid()) $type = $this->l('Standard refund');
			else $type = $this->l('Cancel products');

			if (!$order->hasBeenDelivered())
				$this->toolbar_btn['new'] = array(
					'short' => 'Create',
					'href' => '#',
					'desc' => $this->l('Add a product'),
					'class' => 'add_product'
				);
			$this->toolbar_btn['standard_refund'] = array(
				'short' => 'Create',
				'href' => '',
				'desc' => $type,
				'class' => 'process-icon-standardRefund'
			);
			$this->toolbar_btn['partial_refund'] = array(
				'short' => 'Create',
				'href' => '',
				'desc' => $this->l('Partial refund'),
				'class' => 'process-icon-partialRefund'
			);
		}
		return parent::initToolbar();
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUI('ui.datepicker');
		if ($this->tabAccess['edit'] == 1 && $this->display == 'view')
		{
			$this->addJS(_PS_JS_DIR_.'admin_order.js');
			$this->addJS(_PS_JS_DIR_.'tools.js');
			$this->addJqueryPlugin('autocomplete');
		}
	}

	public function printPDFIcons($id_order, $tr)
	{
		$order = new Order($id_order);
		$order_state = OrderHistory::getLastOrderState($id_order);
		if (!Validate::isLoadedObject($order_state) OR !Validate::isLoadedObject($order))
			die(Tools::displayError('Invalid objects'));

		$this->context->smarty->assign(array(
			'order' => $order,
			'order_state' => $order_state,
			'tr' => $tr
		));

		return $this->context->smarty->fetch('orders/_print_pdf_icon.tpl');
	}

	public function postProcess()
	{
		/* Update shipping number */
		if (Tools::isSubmit('submitShippingNumber') AND ($id_order = (int)Tools::getValue('id_order')) AND Validate::isLoadedObject($order = new Order($id_order)))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isUrl(Tools::getValue('tracking_number')))
				{
					// update shipping number
					$order->shipping_number = Tools::getValue('tracking_number');
					if ($order->update())
					{
						// Update order_carrier
						$id_order_invoice = Tools::getValue('id_order_invoice');
						$id_carrier = Tools::getValue('id_carrier');
						$id_order_carrier = Db::getInstance()->getValue('
							SELECT `id_order_carrier`
							FROM `'._DB_PREFIX_.'order_carrier`
							WHERE `id_order` = '.(int)$order->id.
							' AND `id_carrier` = '.(int)$id_carrier.
							($id_order_invoice ? ' AND `id_order_invoice` = '.(int)$id_order_invoice : ''));
						$order_carrier = new OrderCarrier($id_order_carrier);
						$order_carrier->tracking_number = pSQL(Tools::getValue('tracking_number'));
						$order_carrier->update();

						global $_LANGMAIL;
						$customer = new Customer((int)$order->id_customer);
						$carrier = new Carrier((int)$order->id_carrier);
						if (!Validate::isLoadedObject($customer))
							throw new PrestashopException('Can\'t load Customer object');
						if (!Validate::isLoadedObject($carrier))
							throw new PrestashopException('Can\'t load Carrier object');
						$templateVars = array(
							'{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
							'{firstname}' => $customer->firstname,
							'{lastname}' => $customer->lastname,
							'{id_order}' => (int)$order->id
						);
						@Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit', (int)$order->id_lang), $templateVars,
							$customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL,
							_PS_MAIL_DIR_, true);
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
					}
					else
						$this->_errors[] = Tools::displayError('An error occured on updating of order');
				}
				else
					$this->_errors[] = Tools::displayError('Shipping number is incorrect');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		/* Change order state, add a new entry in order history and send an e-mail to the customer if needed */
		elseif (Tools::isSubmit('submitState') AND ($id_order = (int)(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order)))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$_GET['view'.$this->table] = true;
				if (!$newOrderStatusId = (int)(Tools::getValue('id_order_state')))
					$this->_errors[] = Tools::displayError('Invalid new order status');
				else
				{
					$history = new OrderHistory();
					$history->id_order = (int)$id_order;
					$history->id_employee = (int)$this->context->employee->id;
					$history->changeIdOrderState((int)($newOrderStatusId), (int)($id_order));
					$order = new Order((int)$order->id);
					$carrier = new Carrier((int)($order->id_carrier), (int)($order->id_lang));
					$templateVars = array();
					if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') AND $order->shipping_number)
						$templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
					else if ($history->id_order_state == Configuration::get('PS_OS_CHEQUE'))
						$templateVars = array(
							'{cheque_name}' => (Configuration::get('CHEQUE_NAME') ? Configuration::get('CHEQUE_NAME') : ''),
							'{cheque_address_html}' => (Configuration::get('CHEQUE_ADDRESS') ? nl2br(Configuration::get('CHEQUE_ADDRESS')) : ''));
					elseif ($history->id_order_state == Configuration::get('PS_OS_BANKWIRE'))
						$templateVars = array(
							'{bankwire_owner}' => (Configuration::get('BANK_WIRE_OWNER') ? Configuration::get('BANK_WIRE_OWNER') : ''),
							'{bankwire_details}' => (Configuration::get('BANK_WIRE_DETAILS') ? nl2br(Configuration::get('BANK_WIRE_DETAILS')) : ''),
							'{bankwire_address}' => (Configuration::get('BANK_WIRE_ADDRESS') ? nl2br(Configuration::get('BANK_WIRE_ADDRESS')) : ''));
					if ($history->addWithemail(true, $templateVars))
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$id_order.'&vieworder'.'&token='.$this->token);
					$this->_errors[] = Tools::displayError('An error occurred while changing the status or was unable to send e-mail to the customer.');
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		/* Add a new message for the current order and send an e-mail to the customer if needed */
		elseif (isset($_POST['submitMessage']))
		{
			$_GET['view'.$this->table] = true;
			if ($this->tabAccess['edit'] === '1')
			{
				if (!($id_order = (int)(Tools::getValue('id_order'))) OR !($id_customer = (int)(Tools::getValue('id_customer'))))
					$this->_errors[] = Tools::displayError('An error occurred before sending message');
				elseif (!Tools::getValue('message'))
					$this->_errors[] = Tools::displayError('Message cannot be blank');
				else
				{
					/* Get message rules and and check fields validity */
					$rules = call_user_func(array('Message', 'getValidationRules'), 'Message');
					foreach ($rules['required'] AS $field)
						if (($value = Tools::getValue($field)) == false AND (string)$value != '0')
							if (!Tools::getValue('id_'.$this->table) OR $field != 'passwd')
								$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is required.');
					foreach ($rules['size'] AS $field => $maxLength)
						if (Tools::getValue($field) AND Tools::strlen(Tools::getValue($field)) > $maxLength)
							$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is too long.').' ('.$maxLength.' '.Tools::displayError('chars max').')';
					foreach ($rules['validate'] AS $field => $function)
						if (Tools::getValue($field))
							if (!Validate::$function(htmlentities(Tools::getValue($field), ENT_COMPAT, 'UTF-8')))
								$this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is invalid.');
					if (!sizeof($this->_errors))
					{
						$order = new Order((int)(Tools::getValue('id_order')));
						$customer = new Customer((int)$order->id_customer);
						//check if a thread already exist
						$id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($customer->email, $order->id);
						$cm = new CustomerMessage();
						if (!$id_customer_thread)
						{
							$ct = new CustomerThread();
							$ct->id_contact = 0;
							$ct->id_customer = (int)$order->id_customer;
							$ct->id_shop = (int)$this->context->shop->getId(true);
							$ct->id_order = (int)$order->id;
							$ct->id_lang = (int)$this->context->language->id;
							$ct->email = $customer->email;
							$ct->status = 'open';
							$ct->token = Tools::passwdGen(12);
							$ct->add();
						}
						else
							$ct = new CustomerThread((int)$id_customer_thread);
						$cm->id_customer_thread = $ct->id;
						$cm->id_employee = (int)$this->context->employee->id;
						$cm->message = htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8');
						$cm->private = Tools::getValue('visibility');
						if (!$cm->add())
							$this->_errors[] = Tools::displayError('An error occurred while sending message.');
						elseif ($cm->private)
							Tools::redirectAdmin(self::$currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
						elseif (Validate::isLoadedObject($customer = new Customer($id_customer)))
						{
							if (Validate::isLoadedObject($order))
							{
								$varsTpl = array(
									'{lastname}' => $customer->lastname,
									'{firstname}' => $customer->firstname,
									'{id_order}' => $order->id,
									'{message}' => (Configuration::get('PS_MAIL_TYPE') == 2 ? $cm->message : Tools::nl2br($cm->message))
								);
								if (@Mail::Send((int)$order->id_lang, 'order_merchant_comment',
									Mail::l('New message regarding your order', (int)$order->id_lang), $varsTpl, $customer->email,
									$customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, true))
									Tools::redirectAdmin(self::$currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
							}
						}
						$this->_errors[] = Tools::displayError('An error occurred while sending e-mail to customer.');
					}
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}

		/* Partial refund from order */
		elseif (Tools::isSubmit('partialRefund') AND Validate::isLoadedObject($order = new Order((int)(Tools::getValue('id_order')))))
		{
			$amount = 0;
			$order_detail_list = array();
			foreach ($_POST['partialRefundProduct'] as $id_order_detail => $amount_detail)
				if (isset($amount_detail) && !empty($amount_detail))
				{
					$amount += $amount_detail;
					$order_detail_list[$id_order_detail]['quantity'] = (int)$_POST['partialRefundProductQuantity'][$id_order_detail];
					$order_detail_list[$id_order_detail]['amount'] = (float)$amount_detail;
				}
			$shipping_cost_amount = (float)(Tools::getValue('partialRefundShippingCost'));
			if ($shipping_cost_amount > 0)
				$amount += $shipping_cost_amount;

			if ($amount > 0)
			{
				if (!OrderSlip::createPartialOrderSlip($order, $amount, $shipping_cost_amount, $order_detail_list))
					$this->_errors[] = Tools::displayError('Cannot generate partial credit slip');
			}
			else
				$this->_errors[] = Tools::displayError('You have to write an amount if you want to do a partial credit slip');

			// Redirect if no errors
			if (!sizeof($this->_errors))
				Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=24&token='.$this->token);
		}

		/* Cancel product from order */
		elseif (Tools::isSubmit('cancelProduct') AND Validate::isLoadedObject($order = new Order((int)(Tools::getValue('id_order')))))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				$productList = Tools::getValue('id_order_detail');
				$customizationList = Tools::getValue('id_customization');
				$qtyList = Tools::getValue('cancelQuantity');
				$customizationQtyList = Tools::getValue('cancelCustomizationQuantity');

				$full_product_list = $productList;
				$full_quantity_list = $qtyList;

				if ($customizationList)
				{
					foreach ($customizationList as $key => $id_order_detail)
					{
						$full_product_list[$id_order_detail] = $id_order_detail;
						$full_quantity_list[$id_order_detail] = $customizationQtyList[$key];
					}
				}

				if ($productList OR $customizationList)
				{
					if ($productList)
					{
						$id_cart = Cart::getCartIdByOrderId($order->id);
						$customization_quantities = Customization::countQuantityByCart($id_cart);

						foreach ($productList AS $key => $id_order_detail)
						{
							$qtyCancelProduct = abs($qtyList[$key]);
							if (!$qtyCancelProduct)
								$this->_errors[] = Tools::displayError('No quantity selected for product.');

							// check actionable quantity
							$order_detail = new OrderDetail($id_order_detail);
							$customization_quantity = 0;
							if (array_key_exists($order_detail->product_id, $customization_quantities) && array_key_exists($order_detail->product_attribute_id, $customization_quantities[$order_detail->product_id]))
								$customization_quantity =  (int) $customization_quantities[$order_detail->product_id][$order_detail->product_attribute_id];

							if (($order_detail->product_quantity - $customization_quantity - $order_detail->product_quantity_refunded - $order_detail->product_quantity_return) < $qtyCancelProduct)
								$this->_errors[] = Tools::displayError('Invalid quantity selected for product.');

						}
					}
					if ($customizationList)
					{
						$customization_quantities = Customization::retrieveQuantitiesFromIds(array_keys($customizationList));

						foreach ($customizationList AS $id_customization => $id_order_detail)
						{
							$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
							$customization_quantity = $customization_quantities[$id_customization];

							if (!$qtyCancelProduct)
								$this->_errors[] = Tools::displayError('No quantity selected for product.');

							if ($qtyCancelProduct > ($customization_quantity['quantity'] - ($customization_quantity['quantity_refunded'] + $customization_quantity['quantity_returned'])))
								$this->_errors[] = Tools::displayError('Invalid quantity selected for product.');
						}
					}

					if (!sizeof($this->_errors) AND $productList)
						foreach ($productList AS $key => $id_order_detail)
						{
							$qty_cancel_product = abs($qtyList[$key]);
							$order_detail = new OrderDetail((int)($id_order_detail));

							// Reinject product
							if (!$order->hasBeenDelivered() OR ($order->hasBeenDelivered() AND Tools::isSubmit('reinjectQuantities')))
							{
								$reinjectable_quantity = (int)$order_detail->product_quantity - (int)$order_detail->product_quantity_reinjected;
								$quantity_to_reinject = $qty_cancel_product > $reinjectable_quantity ? $reinjectable_quantity : $qty_cancel_product;

								// @since 1.5.0 : Advanced Stock Management
								$product_to_inject = new Product($order_detail->product_id, false, $this->context->language->id, $order->id_shop);

								$product = new Product($order_detail->product_id);

								if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
									&& $product->advanced_stock_management
									&& $order_detail->id_warehouse != 0
								)
								{
									$mvts = StockMvt::getNegativeStockMvts(
										$order_detail->id_order,
										$order_detail->product_id,
										$order_detail->product_attribute_id,
										$quantity_to_reinject);

									foreach ($mvts as $mvt)
									{
										$manager->addProduct(
											$order_detail->product_id,
											$order_detail->product_attribute_id,
											new Warehouse($mvt['id_warehouse']),
											$mvt['physical_quantity'],
											null,
											$mvt['price_te'],
											true
										);
									}
									StockAvailable::synchronize($order_detail->product_id);
								}
								else if ($order_detail->id_warehouse == 0)
								{
									StockAvailable::updateQuantity($order_detail->product_id,
																   $order_detail->product_attribute_id,
																   $quantity_to_reinject,
																   $order->id_shop);
								}
								else
									$this->_errors[] = Tools::displayError('Cannot re-stock product');
							}

							// Delete product
							$orderDetail = new OrderDetail((int)($id_order_detail));
							if (!$order->deleteProduct($order, $orderDetail, $qtyCancelProduct))
								$this->_errors[] = Tools::displayError('An error occurred during deletion of the product.').' <span class="bold">'.$orderDetail->product_name.'</span>';
							Hook::exec('cancelProduct', array('order' => $order, 'id_order_detail' => $id_order_detail));
						}
					if (!sizeof($this->_errors) AND $customizationList)
						foreach ($customizationList AS $id_customization => $id_order_detail)
						{
							$orderDetail = new OrderDetail((int)($id_order_detail));
							$qtyCancelProduct = abs($customizationQtyList[$id_customization]);
							if (!$order->deleteCustomization($id_customization, $qtyCancelProduct, $orderDetail))
								$this->_errors[] = Tools::displayError('An error occurred during deletion of product customization.').' '.$id_customization;
						}
					// E-mail params
					if ((isset($_POST['generateCreditSlip']) OR isset($_POST['generateDiscount'])) AND !sizeof($this->_errors))
					{
						$customer = new Customer((int)($order->id_customer));
						$params['{lastname}'] = $customer->lastname;
						$params['{firstname}'] = $customer->firstname;
						$params['{id_order}'] = $order->id;
					}

					// Generate credit slip
					if (isset($_POST['generateCreditSlip']) AND !sizeof($this->_errors))
					{
						if (!OrderSlip::createOrderSlip($order, $full_product_list, $full_quantity_list, isset($_POST['shippingBack'])))
							$this->_errors[] = Tools::displayError('Cannot generate credit slip');
						else
						{
							Hook::exec('orderSlip', array('order' => $order, 'productList' => $full_product_list, 'qtyList' => $full_quantity_list));
							@Mail::Send((int)$order->id_lang, 'credit_slip', Mail::l('New credit slip regarding your order', $order->id_lang),
							$params, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL,
							_PS_MAIL_DIR_, true);
						}
					}

					// Generate voucher
					if (isset($_POST['generateDiscount']) AND !sizeof($this->_errors))
					{
						// @todo generate a voucher using cartrules
						if (true || !$voucher = Discount::createOrderDiscount($order, $full_product_list, $full_quantity_list, $this->l('Credit Slip concerning the order #'), isset($_POST['shippingBack'])))
							$this->_errors[] = Tools::displayError('Cannot generate voucher');
						else
						{
							$currency = $this->context->currency;
							$params['{voucher_amount}'] = Tools::displayPrice($voucher->value, $currency, false);
							$params['{voucher_num}'] = $voucher->name;
							@Mail::Send((int)$order->id_lang, 'voucher', Mail::l('New voucher regarding your order', (int)$order->id_lang),
							$params, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL,
							NULL, _PS_MAIL_DIR_, true);
						}
					}
				}
				else
					$this->_errors[] = Tools::displayError('No product or quantity selected.');

				// Redirect if no errors
				if (!sizeof($this->_errors))
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=24&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (isset($_GET['messageReaded']))
			Message::markAsReaded($_GET['messageReaded'], $this->context->employee->id);
		else if (Tools::isSubmit('submitAddPayment'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order = new Order(Tools::getValue('id_order'));
				$amount = str_replace(',', '.', Tools::getValue('payment_amount'));
				$currency = new Currency(Tools::getValue('payment_currency'));
				if ($order->hasInvoice())
					$order_invoice = new OrderInvoice(Tools::getValue('payment_invoice'));
				else
					$order_invoice = null;
				if (!Validate::isLoadedObject($order))
					$this->_errors[] = Tools::displayError('Order can\'t be found');
				elseif (!Validate::isPrice($amount))
					$this->_errors[] = Tools::displayError('Amount is invalid');
				elseif (!Validate::isString(Tools::getValue('payment_method')))
					$this->_errors[] = Tools::displayError('Payment method is invalid');
				elseif (!Validate::isString(Tools::getValue('payment_transaction_id')))
					$this->_errors[] = Tools::displayError('Transaction ID is invalid');
				elseif (!Validate::isLoadedObject($currency))
					$this->_errors[] = Tools::displayError('Currency is invalid');
				elseif ($order->hasInvoice() && !Validate::isLoadedObject($order_invoice))
					$this->_errors[] = Tools::displayError('Invoice is invalid');
				elseif (!Validate::isDate(Tools::getValue('payment_date')))
					$this->_errors[] = Tools::displayError('Date is invalid');
				else
				{
					if (!$order->addOrderPayment($amount, Tools::getValue('payment_method'), Tools::getValue('payment_transaction_id'), $currency, Tools::getValue('payment_date'), $order_invoice))
						$this->_errors[] = Tools::displayError('An error occured on adding of order payment');
					else
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitEditNote'))
		{
			$id_order_invoice = (int)Tools::getValue('id_order_invoice');
			$note = Tools::getValue('note');
			$order_invoice = new OrderInvoice($id_order_invoice);
			if (Validate::isLoadedObject($order_invoice) && Validate::isCleanHtml($note))
			{
				if ($this->tabAccess['edit'] === '1')
				{
					$order_invoice->note = $note;
					if ($order_invoice->save())
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order_invoice->id_order.'&vieworder&conf=4&token='.$this->token);
					else
						$this->_errors[] = Tools::displayError('Unable to save invoice note.');
				}
				else
					$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			}
			else
				$this->_errors[] = Tools::displayError('Unable to load invoice for edit note.');
		}
		elseif (Tools::isSubmit('submitAddOrder') == 1 && ($id_cart = Tools::getValue('id_cart')) && ($module_name = pSQL(Tools::getValue('payment_module_name'))) && ($id_order_state = Tools::getValue('id_order_state')))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$payment_module = Module::getInstanceByName($module_name);
				$cart = new Cart((int)$id_cart);
				$payment_module->validateOrder((int)$cart->id, (int)$id_order_state, $cart->getOrderTotal(true, Cart::BOTH), $payment_module->displayName, $this->l(sprintf('Manual order - ID Employee :%1', (int)Context::getContext()->cookie->id_employee)));
				if ($payment_module->currentOrder)
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$payment_module->currentOrder.'&vieworder'.'&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add here.');
		}
		elseif (Tools::isSubmit('submitAddressShipping') || Tools::isSubmit('submitAddressInvoice'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$address = new Address(Tools::getValue('id_address'));
				if (Validate::isLoadedObject($address))
				{
					// Update the address on order
					$order = new Order(Tools::getValue('id_order'));
					if (Tools::isSubmit('submitAddressShipping'))
						$order->id_address_delivery = $address->id;
					elseif (Tools::isSubmit('submitAddressInvoice'))
						$order->id_address_invoice = $address->id;
					$order->update();
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayErrror('This address can\'t be loaded');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitChangeCurrency'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order = new Order(Tools::getValue('id_order'));
				if (!Validate::isLoadedObject($order))
					throw new PrestashopException('Con\'t load Order object');

				if (Tools::getValue('new_currency') != $order->id_currency && !$order->valid)
				{
					$old_currency = new Currency($order->id_currency);
					$currency = new Currency(Tools::getValue('new_currency'));
					if (!Validate::isLoadedObject($currency))
						throw new PrestashopException('Can\'t load Currency object');

					// Update order detail amount
					foreach($order->getOrderDetailList() as $row)
					{
						$order_detail = new OrderDetail($row['id_order_detail']);
						$order_detail->product_price = Tools::convertPriceFull($order_detail->product_price, $old_currency, $currency);
						$order_detail->reduction_amount = Tools::convertPriceFull($order_detail->reduction_amount, $old_currency, $currency);
						$order_detail->unit_price_tax_incl = Tools::convertPriceFull($order_detail->unit_price_tax_incl, $old_currency, $currency);
						$order_detail->unit_price_tax_excl = Tools::convertPriceFull($order_detail->unit_price_tax_excl, $old_currency, $currency);
						$order_detail->total_price_tax_incl = Tools::convertPriceFull($order_detail->product_price, $old_currency, $currency);
						$order_detail->total_price_tax_excl = Tools::convertPriceFull($order_detail->product_price, $old_currency, $currency);
						$order_detail->group_reduction = Tools::convertPriceFull($order_detail->product_price, $old_currency, $currency);
						$order_detail->product_quantity_discount = Tools::convertPriceFull($order_detail->product_price, $old_currency, $currency);

						$order_detail->update();
					}

					// Update order payment amount
					foreach($order->getOrderPaymentCollection() as $payment)
					{
						$payment->id_currency = (int)$currency->id;
						$payment->amount = Tools::convertPriceFull((float)$payment->amount, $old_currency, $currency);
						$payment->update();
					}

					$id_order_carrier = Db::getInstance()->getRow('
						SELECT `id_order_carrier`
						FROM `'._DB_PREFIX_.'order_carrier`
						WHERE `id_order` = '.(int)$order->id);
					$order_carrier = new OrderCarrier($id_order_carrier);
					$order_carrier->shipping_cost_tax_excl = (float)Tools::convertPriceFull($order_carrier['shipping_cost_tax_excl'], $old_currency, $currency);
					$order_carrier->shipping_cost_tax_incl = (float)Tools::convertPriceFull($order_carrier['shipping_cost_tax_incl'], $old_currency, $currency);
					$order_carrier->update();

					// Update order amount
					$order->total_discounts = Tools::convertPriceFull($order->total_discounts, $old_currency, $currency);
					$order->total_discounts_tax_incl = Tools::convertPriceFull($order->total_discounts_tax_incl, $old_currency, $currency);
					$order->total_discounts_tax_excl = Tools::convertPriceFull($order->total_discounts_tax_excl, $old_currency, $currency);
					$order->total_paid = Tools::convertPriceFull($order->total_paid, $old_currency, $currency);
					$order->total_paid_tax_incl = Tools::convertPriceFull($order->total_paid_tax_incl, $old_currency, $currency);
					$order->total_paid_tax_excl = Tools::convertPriceFull($order->total_discounts_tax_excl, $old_currency, $currency);
					$order->total_paid_real = Tools::convertPriceFull($order->total_paid_real, $old_currency, $currency);
					$order->total_products = Tools::convertPriceFull($order->total_products, $old_currency, $currency);
					$order->total_products_wt = Tools::convertPriceFull($order->total_products_wt, $old_currency, $currency);
					$order->total_shipping = Tools::convertPriceFull($order->total_shipping, $old_currency, $currency);
					$order->total_shipping_tax_incl = Tools::convertPriceFull($order->total_shipping_tax_incl, $old_currency, $currency);
					$order->total_shipping_tax_excl = Tools::convertPriceFull($order->total_shipping_tax_excl, $old_currency, $currency);
					$order->total_wrapping = Tools::convertPriceFull($order->total_wrapping, $old_currency, $currency);
					$order->total_wrapping_tax_incl = Tools::convertPriceFull($order->total_wrapping_tax_incl, $old_currency, $currency);
					$order->total_wrapping_tax_excl = Tools::convertPriceFull($order->total_wrapping_tax_excl, $old_currency, $currency);

					// Update currency in order
					$order->id_currency = $currency->id;

					$order->update();
				}
				else
					$this->_errors[] = Tools::displayError('You can\'t change the currency');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::isSubmit('submitGenerateInvoice'))
		{
			$order = new Order(Tools::getValue('id_order'));
			if (!Validate::isLoadedObject($order))
				throw new PrestashopException('Order can\'t be loaded');

			if ($order->hasInvoice())
				$this->_errors[] = Tools::displayError('This order has already invoice');
			else
			{
				$order->setInvoice();
				Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
			}
		}
		elseif (Tools::isSubmit('submitDeleteVoucher'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				$order_cart_rule = new OrderCartRule(Tools::getValue('id_order_cart_rule'));
				$order = new Order(Tools::getValue('id_order'));
				if (Validate::isLoadedObject($order_cart_rule) && Validate::isLoadedObject($order) && $order_cart_rule->id_order == $order->id)
				{
					if ($order_cart_rule->id_order_invoice)
					{
						$order_invoice = new OrderInvoice($order_cart_rule->id_order_invoice);
						if (!ValidateCore::isLoadedObject($order_invoice))
							throw new PrestashopException('Can\'t load Order Invoice object');

						// Update amounts of Order Invoice
						$order_invoice->total_discount_tax_excl -= $order_cart_rule->value_tax_excl;
						$order_invoice->total_discount_tax_incl -= $order_cart_rule->value;

						$order_invoice->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
						$order_invoice->total_paid_tax_incl += $order_cart_rule->value;

						// Update Order Invoice
						$order_invoice->update();
					}

					// Update amounts of order
					$order->total_discounts -= $order_cart_rule->value;
					$order->total_discounts_tax_incl -= $order_cart_rule->value;
					$order->total_discounts_tax_excl -= $order_cart_rule->value_tax_excl;

					$order->total_paid += $order_cart_rule->value;
					$order->total_paid_tax_incl += $order_cart_rule->value;
					$order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;

					// Delete Order Cart Rule and update Order
					$order_cart_rule->delete();
					$order->update();
					Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayError('Can\'t edit this Order Cart Rule');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		elseif (Tools::getValue('submitNewVoucher'))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!Tools::getValue('discount_name'))
					$this->_errors[] = Tools::displayError('You must specify a name in order to create a new discount');
				else
				{
					$order = new Order(Tools::getValue('id_order'));
					if (!Validate::isLoadedObject($order))
						throw new PrestashopException('Can\'t load Order object');

					if ($order->hasInvoice())
					{
						// If the discount is for only one invoice
						if (!Tools::isSubmit('discount_all_invoices'))
						{
							$order_invoice = new OrderInvoice(Tools::getValue('discount_invoice'));
							if (!Validate::isLoadedObject($order_invoice))
								throw new PrestashopException('Can\'t load Order Invoice object');
						}
					}

					$cart_rules = array();
					switch (Tools::getValue('discount_type'))
					{
						// Percent type
						case 1:
							if (Tools::getValue('discount_value') < 100)
							{
								if (isset($order_invoice))
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round($order_invoice->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
									$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round($order_invoice->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
								elseif ($order->hasInvoice())
								{
									$order_invoices_collection = $order->getInvoicesCollection();
									foreach($order_invoices_collection as $order_invoice)
									{
										$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round($order_invoice->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
										$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round($order_invoice->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);

										// Update OrderInvoice
										$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
									}
								}
								else
								{
									$cart_rules[0]['value_tax_incl'] = Tools::ps_round($order->total_paid_tax_incl * Tools::getValue('discount_value') / 100, 2);
									$cart_rules[0]['value_tax_excl'] = Tools::ps_round($order->total_paid_tax_excl * Tools::getValue('discount_value') / 100, 2);
								}
							}
							else
								$this->_errors[] = Tools::displayError('Discount value is invalid');
							break;
						// Amount type
						case 2:
							if (isset($order_invoice))
							{
								if (Tools::getValue('discount_value') > $order_invoice->total_paid_tax_incl)
									$this->_errors[] = Tools::displayError('Discount value is superior than the order invoice total');
								else
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
									$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							elseif ($order->hasInvoice())
							{
								$order_invoices_collection = $order->getInvoicesCollection();
								foreach($order_invoices_collection as $order_invoice)
								{
									if (Tools::getValue('discount_value') > $order_invoice->total_paid_tax_incl)
										$this->_errors[] = Tools::displayError('Discount value is superior than the order invoice total (Invoice: ').$order_invoice->getInvoiceNumberFormatted(Context::getContext()->language->id).')';
									else
									{
										$cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
										$cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);

										// Update OrderInvoice
										$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
									}
								}
							}
							else
							{
								if (Tools::getValue('discount_value') > $order->total_paid_tax_incl)
									$this->_errors[] = Tools::displayError('Discount value is superior than the order total');
								else
								{
									$cart_rules[0]['value_tax_incl'] = Tools::ps_round(Tools::getValue('discount_value'), 2);
									$cart_rules[0]['value_tax_excl'] = Tools::ps_round(Tools::getValue('discount_value') / (1 + ($order->getTaxesAverageUsed() / 100)), 2);
								}
							}
							break;
						// Free shipping type
						case 3:
							if (isset($order_invoice))
							{
								if ($order_invoice->total_shipping_tax_incl > 0)
								{
									$cart_rules[$order_invoice->id]['value_tax_incl'] = $order_invoice->total_shipping_tax_incl;
									$cart_rules[$order_invoice->id]['value_tax_excl'] = $order_invoice->total_shipping_tax_excl;

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							elseif ($order->hasInvoice())
							{
								$order_invoices_collection = $order->getInvoicesCollection();
								foreach($order_invoices_collection as $order_invoice)
								{
									if ($order_invoice->total_shipping_tax_incl <= 0)
										continue;
									$cart_rules[$order_invoice->id]['value_tax_incl'] = $order_invoice->total_shipping_tax_incl;
									$cart_rules[$order_invoice->id]['value_tax_excl'] = $order_invoice->total_shipping_tax_excl;

									// Update OrderInvoice
									$this->applyDiscountOnInvoice($order_invoice, $cart_rules[$order_invoice->id]['value_tax_incl'], $cart_rules[$order_invoice->id]['value_tax_excl']);
								}
							}
							break;
						default:
							$this->_errors[] = Tools::displayError('Discount type is invalid');
					}

					$res = true;
					foreach($cart_rules as $id_order_invoice => $cart_rule)
					{
						// Create OrderCartRule
						$order_cart_rule = new OrderCartRule();
						$order_cart_rule->id_order = $order->id;
						$order_cart_rule->id_order_invoice = $id_order_invoice;
						$order_cart_rule->name = Tools::getValue('discount_name');
						$order_cart_rule->value = $cart_rule['value_tax_incl'];
						$order_cart_rule->value_tax_excl = $cart_rule['value_tax_excl'];
						$res &= $order_cart_rule->add();

						$order->total_discounts += $order_cart_rule->value;
						$order->total_discounts_tax_incl += $order_cart_rule->value;
						$order->total_discounts_tax_excl += $order_cart_rule->value_tax_excl;
						$order->total_paid -= $order_cart_rule->value;
						$order->total_paid_tax_incl -= $order_cart_rule->value;
						$order->total_paid_tax_excl -= $order_cart_rule->value_tax_excl;
					}

					// Update Order
					$res &= $order->update();

					if ($res)
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
					else
						$this->_errors[] = Tools::displayError('An error occured on OrderCartRule creation');
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}

		parent::postProcess();
	}

	public function renderView()
	{
		$order = new Order(Tools::getValue('id_order'));
		if (!Validate::isLoadedObject($order))
			throw new PrestashopException('object can\'t be loaded');

		$customer = new Customer($order->id_customer);
		$carrier = new Carrier($order->id_carrier);
		$products = $this->getProducts($order);

		// Carrier module call
		$carrier_module_call = null;
		if ($carrier->is_module)
		{
			$module = Module::getInstanceByName($carrier->external_module_name);
			if (method_exists($module, 'displayInfoByCart'))
				$carrier_module_call = call_user_func(array($module, 'displayInfoByCart'), $order->id_cart);
		}

		// Retrieve addresses informations
		$addressInvoice = new Address($order->id_address_invoice, $this->context->language->id);
		if (Validate::isLoadedObject($addressInvoice) AND $addressInvoice->id_state)
			$invoiceState = new State((int)($addressInvoice->id_state));

		if ($order->id_address_invoice == $order->id_address_delivery)
		{
			$addressDelivery = $addressInvoice;
			if (isset($invoiceState))
				$deliveryState = $invoiceState;
		}
		else
		{
			$addressDelivery = new Address($order->id_address_delivery, $this->context->language->id);
			if (Validate::isLoadedObject($addressDelivery) AND $addressDelivery->id_state)
				$deliveryState = new State((int)($addressDelivery->id_state));
		}

		$this->toolbar_title = $this->l('Order #').sprintf('%06d', $order->id).' ('.$order->reference.') - '.$customer->firstname.' '.$customer->lastname;

		// gets warehouses to ship products, if and only if advanced stock management is activated
		$warehouse_list = null;

		$order_details = $order->getOrderDetailList();
		foreach ($order_details as $order_detail)
		{
			$product = new Product($order_detail['product_id']);

			if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
				&& $product->advanced_stock_management
			)
			{
				$warehouses = Warehouse::getWarehousesByProductId($order_detail['product_id'], $order_detail['product_attribute_id']);
				foreach ($warehouses as $warehouse)
				{
					if (!isset($warehouse_list[$warehouse['id_warehouse']]))
						$warehouse_list[$warehouse['id_warehouse']] = $warehouse;
				}
			}
		}

		$payment_methods = array();
		foreach(PaymentModule::getInstalledPaymentModules() as $payment)
		{
			$module = Module::getInstanceByName($payment['name']);
			if (Validate::isLoadedObject($module))
				$payment_methods[] = $module->displayName;
		}

		// Smarty assign
		$this->tpl_view_vars = array(
			'order' => $order,
			'cart' => new Cart($order->id),
			'customer' => $customer,
			'customer_addresses' => $customer->getAddresses($this->context->language->id),
			'addresses' => array(
				'delivery' => $addressDelivery,
				'deliveryState' => isset($deliveryState) ? $deliveryState : null,
				'invoice' => $addressInvoice,
				'invoiceState' => isset($invoiceState) ? $invoiceState : null
			),
			'customerStats' => $customer->getStats(),
			'products' => $products,
			'discounts' => $order->getCartRules(),
			'orders_total_paid_tax_incl' => $order->getOrdersTotalPaid(), // Get the sum of total_paid_tax_incl of the order with similar reference
			'total_paid' => $order->getTotalPaid(),
			'returns' => OrderReturn::getOrdersReturn($order->id_customer, $order->id),
			'orderMessages' => OrderMessage::getOrderMessages($order->id_lang),
			'messages' => Message::getMessagesByOrderId($order->id, true),
			'carrier' => new Carrier($order->id_carrier),
			'history' => $order->getHistory($this->context->language->id),
			'states' => OrderState::getOrderStates($this->context->language->id),
			'warehouse_list' => $warehouse_list,
			'sources' => ConnectionsSource::getOrderSources($order->id),
			'currentState' => OrderHistory::getLastOrderState($order->id),
			'currency' => new Currency($order->id_currency),
			'currencies' => Currency::getCurrencies(),
			'previousOrder' => $order->getPreviousOrderId(),
			'nextOrder' => $order->getNextOrderId(),
			'currentIndex' => self::$currentIndex,
			'carrierModuleCall' => $carrier_module_call,
			'iso_code_lang' => $this->context->language->iso_code,
			'id_lang' => $this->context->language->id,
			'can_edit' => ($this->tabAccess['edit'] == 1),
			'current_id_lang' => $this->context->language->id,
			'invoices_collection' => $order->getInvoicesCollection(),
			'not_paid_invoices_collection' => $order->getNotPaidInvoicesCollection(),
			'payment_methods' => $payment_methods,
			'HOOK_INVOICE' => Hook::exec('invoice', array('id_order' => $order->id)),
			'HOOK_ADMIN_ORDER' => Hook::exec('adminOrder', array('id_order' => $order->id))
		);

		return parent::renderView();
	}

	public function ajaxProcessSearchCustomers()
	{
		if ($customers = Customer::searchByName(pSQL(Tools::getValue('customer_search'))))
			$to_return = array('customers' => $customers,
									'found' => true);
		else
			$to_return = array('found' => false);
		$this->content = Tools::jsonEncode($to_return);
	}

	public function ajaxProcessSearchProducts()
	{
		$currency = new Currency((int)Tools::getValue('id_currency'));
		if ($products = Product::searchByName((int)$this->context->language->id, pSQL(Tools::getValue('product_search'))))
		{
			foreach ($products AS &$product)
			{
				// Formatted price
				$product['formatted_price'] = Tools::displayPrice(Tools::convertPrice($product['price_tax_incl'], $currency), $currency);
				// Concret price
				$product['price_tax_incl'] = Tools::ps_round(Tools::convertPrice($product['price_tax_incl'], $currency), 2);
				$product['price_tax_excl'] = Tools::ps_round(Tools::convertPrice($product['price_tax_excl'], $currency), 2);
				$productObj = new Product((int)$product['id_product'], false, (int)$this->context->language->id);
				$combinations = array();
				$attributes = $productObj->getAttributesGroups((int)$this->context->language->id);
				$product['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct((int)$product['id_product'], 0, (int)$this->context->shop->getID());
				// Tax rate for this customer
				if (Tools::isSubmit('id_address'))
					$product['tax_rate'] = $productObj->getTaxesRate(new Address(Tools::getValue('id_address')));

				$product['warehouse_list'] = array();

				foreach($attributes AS $attribute)
				{
					if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
						$combinations[$attribute['id_product_attribute']]['attributes'] = '';
					$combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';
					$combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
					$combinations[$attribute['id_product_attribute']]['default_on'] = $attribute['default_on'];
					if (!isset($combinations[$attribute['id_product_attribute']]['price']))
					{
						$price_tax_incl = Product::getPriceStatic((int)$product['id_product'], true, $attribute['id_product_attribute']);
						$price_tax_excl = Product::getPriceStatic((int)$product['id_product'], false, $attribute['id_product_attribute']);
						$combinations[$attribute['id_product_attribute']]['price_tax_incl'] =  Tools::ps_round(Tools::convertPrice($price_tax_incl, $currency), 2);
						$combinations[$attribute['id_product_attribute']]['price_tax_excl'] =  Tools::ps_round(Tools::convertPrice($price_tax_excl, $currency), 2);
						$combinations[$attribute['id_product_attribute']]['formatted_price'] =  Tools::displayPrice(Tools::convertPrice($price_tax_incl, $currency), $currency);
					}
					if (!isset($combinations[$attribute['id_product_attribute']]['qty_in_stock']))
						$combinations[$attribute['id_product_attribute']]['qty_in_stock']= StockAvailable::getQuantityAvailableByProduct((int)$product['id_product'], $attribute['id_product_attribute'], (int)$this->context->shop->getID());

					if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int)$product['advanced_stock_management'] == 1)
						$product['warehouse_list'][$attribute['id_product_attribute']] = Warehouse::getProductWarehouseList($product['id_product'], $attribute['id_product_attribute']);
					else
						$product['warehouse_list'][$attribute['id_product_attribute']] = array();

					$product['stock'][$attribute['id_product_attribute']] = Product::getRealQuantity($product['id_product'], $attribute['id_product_attribute']);

				}

				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int)$product['advanced_stock_management'] == 1)
					$product['warehouse_list'][0] = Warehouse::getProductWarehouseList($product['id_product']);
				else
					$product['warehouse_list'][0] = array();

				$product['stock'][0] = Product::getRealQuantity($product['id_product'], 0, 0);


				foreach ($combinations AS &$combination)
					$combination['attributes'] = rtrim($combination['attributes'], ' - ');
				$product['combinations'] = $combinations;
			}
			$to_return = array(
				'products' => $products,
				'found' => true
			);
		}
		else
			$to_return = array('found' => false);

		$this->content = Tools::jsonEncode($to_return);
	}

	public function ajaxProcessSendMailValidateOrder()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$errors = array();
			$cart = new Cart((int)Tools::getValue('id_cart'));
			if (Validate::isLoadedObject($cart))
			{
				$customer = new Customer((int)$cart->id_customer);
				if (Validate::isLoadedObject($customer))
				{
					$mailVars = array(
						'{order_link}' => Context::getContext()->link->getPageLink('order', false, (int)$cart->id_lang, 'step=3&recover_cart='.(int)$cart->id.'&token_cart='.md5(_COOKIE_KEY_.'recover_cart_'.(int)$cart->id)),
						'{firstname}' => $customer->firstname,
						'{lastname}' => $customer->lastname
					);
					if (Mail::Send((int)$cart->id_lang, 'backoffice_order', Mail::l('Process the payment of your order', (int)$cart->id_lang), $mailVars, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL,_PS_MAIL_DIR_, true))
						die(Tools::jsonEncode(array('errors' => false, 'result' => $this->l('The mail was sent to your customer.'))));
				}
			}
			$this->content = Tools::jsonEncode(array('errors' => true, 'result' => $this->l('Error in sending the email to your customer.')));
		}
	}

	public function ajaxProcessAddProductOnOrder()
	{
		// Load object
		$order = new Order(Tools::getValue('id_order'));
		if (!Validate::isLoadedObject($order))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Order object')
			)));

		if ($order->hasBeenDelivered())
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t add a product on delivered order')
			)));

		$product_informations = $_POST['add_product'];
		if (isset($_POST['add_invoice']))
			$invoice_informations = $_POST['add_invoice'];
		else
			$invoice_informations = array();
		$product = new Product($product_informations['product_id'], false, $order->id_lang);
		if (!Validate::isLoadedObject($product))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Product object')
			)));

		if (isset($product_informations['product_attribute_id']) && $product_informations['product_attribute_id'])
		{
			$combination = new Combination($product_informations['product_attribute_id']);
			if (!Validate::isLoadedObject($combination))
				die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Combination object')
			)));
		}

		// Total method
		$total_method = Cart::BOTH_WITHOUT_SHIPPING;

		// Create new cart
		$cart = new Cart();
		$cart->id_group_shop = $order->id_group_shop;
		$cart->id_shop = $order->id_shop;
		$cart->id_customer = $order->id_customer;
		$cart->id_carrier = $order->id_carrier;
		$cart->id_address_delivery = $order->id_address_delivery;
		$cart->id_address_invoice = $order->id_address_invoice;
		$cart->id_currency = $order->id_currency;
		$cart->id_customer = $order->id_customer;
		$cart->id_lang = $order->id_lang;
		$cart->id_carrier = $order->id_carrier;
		$cart->secure_key = $order->secure_key;

		// Save new cart
		$cart->add();

		// Save context (in order to apply cart rule)
		$this->context->cart = $cart;
		$this->context->customer = new Customer($order->id_customer);

		$use_taxes = ($order->getTaxCalculationMethod() == PS_TAX_INC);

		$initial_prodcut_price_tax_incl = Product::getPriceStatic($product->id, $use_taxes, isset($combination) ? $combination->id : null, 2, null, false, true, 1,
			false, $order->id_customer, $cart->id, $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		// Creating specific price if needed
		if ($product_informations['product_price_tax_incl'] != $initial_prodcut_price_tax_incl)
		{
			$specific_price = new SpecificPrice();
			$specific_price->id_shop = 0;
			$specific_price->id_group_shop = 0;
			$specific_price->id_currency = 0;
			$specific_price->id_country = 0;
			$specific_price->id_group = 0;
			$specific_price->id_customer = $order->id_customer;
			$specific_price->id_product = $product->id;
			if (isset($combination))
				$specific_price->id_product_attribute = $combination->id;
			else
				$specific_price->id_product_attribute = 0;
			$specific_price->price = $product_informations['product_price_tax_excl'];
			$specific_price->from_quantity = 1;
			$specific_price->reduction = 0;
			$specific_price->reduction_type = 'amount';
			$specific_price->from = '0000-00-00 00:00:00';
			$specific_price->to = '0000-00-00 00:00:00';
			$specific_price->add();
		}

		// Add product to cart
		$cart->updateQty($product_informations['product_quantity'], $product->id, isset($combination) ? $combination->id : null, false, 'up', new Shop($cart->id_shop));

		// If order is valid, we can create a new invoice or edit an existing invoice
		if ($order->hasInvoice())
		{
			$order_invoice = new OrderInvoice($product_informations['invoice']);
			// Create new invoice
			if ($order_invoice->id == 0)
			{
				// If we create a new invoice, we calculate shipping cost
				$total_method = Cart::BOTH;
				// Create Cart rule in order to make free shipping
				if (isset($invoice_informations['free_shipping']) && $invoice_informations['free_shipping'])
				{
					$cart_rule = new CartRule();
					$cart_rule->id_customer = $order->id_customer;
					$cart_rule->name = array(
						Configuration::get('PS_LANG_DEFAULT') => $this->l('[Generated] CartRule for Free Shipping')
					);
					$cart_rule->date_from = date('Y-m-d H:i:s', time());
					$cart_rule->date_to = date('Y-m-d H:i:s', time() + 24 * 3600);
					$cart_rule->quantity = 1;
					$cart_rule->quantity_per_user = 1;
					$cart_rule->minimum_amount_currency = $order->id_currency;
					$cart_rule->reduction_currency = $order->id_currency;
					$cart_rule->free_shipping = true;
					$cart_rule->active = 1;
					$cart_rule->add();

					// Add cart rule to cart and in order
					$cart->addCartRule($cart_rule->id);
					$values = array(
						'tax_incl' => $cart_rule->getContextualValue(true),
						'tax_excl' => $cart_rule->getContextualValue(false)
					);
					$order->addCartRule($cart_rule->id, $cart_rule->name[Configuration::get('PS_LANG_DEFAULT')], $values);
				}

				$order_invoice->id_order = $order->id;
				if ($order_invoice->number)
					Configuration::updateValue('PS_INVOICE_START_NUMBER', false);
				else
					$order_invoice->number = Order::getLastInvoiceNumber() + 1;

				$order_invoice->total_paid_tax_excl = Tools::ps_round((float)$cart->getOrderTotal(false, $total_method), 2);
				$order_invoice->total_paid_tax_incl = Tools::ps_round((float)$cart->getOrderTotal($use_taxes, $total_method), 2);
				$order_invoice->total_products = (float)$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
				$order_invoice->total_products_wt = (float)$cart->getOrderTotal($use_taxes, Cart::ONLY_PRODUCTS);
				$order_invoice->total_shipping_tax_excl = (float)$cart->getTotalShippingCost(null, false);
				$order_invoice->total_shipping_tax_incl = (float)$cart->getTotalShippingCost();

				$order_invoice->total_wrapping_tax_excl = abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
				$order_invoice->total_wrapping_tax_incl = abs($cart->getOrderTotal($use_taxes, Cart::ONLY_WRAPPING));

				// Update current order field, only shipping because other field is updated later
				$order->total_shipping += $order_invoice->total_shipping_tax_incl;
				$order->total_shipping_tax_excl += $order_invoice->total_shipping_tax_excl;
				$order->total_shipping_tax_incl += ($use_taxes) ? $order_invoice->total_shipping_tax_incl : $order_invoice->total_shipping_tax_excl;

				$order->total_wrapping += abs($cart->getOrderTotal($use_taxes, Cart::ONLY_WRAPPING));
				$order->total_wrapping_tax_excl += abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
				$order->total_wrapping_tax_incl += abs($cart->getOrderTotal($use_taxes, Cart::ONLY_WRAPPING));
				$order_invoice->add();

				$order_carrier = new OrderCarrier();
				$order_carrier->id_order = (int)$order->id;
				$order_carrier->id_carrier = (int)$order->id_carrier;
				$order_carrier->id_order_invoice = (int)$order_invoice->id;
				$order_carrier->weight = (float)$cart->getTotalWeight();
				$order_carrier->shipping_cost_tax_excl = (float)$order_invoice->total_shipping_tax_excl;
				$order_carrier->shipping_cost_tax_incl = ($use_taxes) ? (float)$order_invoice->total_shipping_tax_incl : (float)$order_invoice->total_shipping_tax_excl;
				$order_carrier->add();
			}
			// Update current invoice
			else
			{
				$order_invoice->total_paid_tax_excl += Tools::ps_round((float)($cart->getOrderTotal(false, $total_method)), 2);
				$order_invoice->total_paid_tax_incl += Tools::ps_round((float)($cart->getOrderTotal($use_taxes, $total_method)), 2);
				$order_invoice->total_products += (float)$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
				$order_invoice->total_products_wt += (float)$cart->getOrderTotal($use_taxes, Cart::ONLY_PRODUCTS);
				$order_invoice->total_shipping_tax_excl += (float)$cart->getTotalShippingCost(null, false);
				$order_invoice->total_shipping_tax_incl += (float)$cart->getTotalShippingCost(null, $use_taxes);
				$order_invoice->total_wrapping_tax_excl += abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
				$order_invoice->total_wrapping_tax_incl += abs($cart->getOrderTotal($use_taxes, Cart::ONLY_WRAPPING));
				$order_invoice->update();
			}
		}

		// Create Order detail information
		$order_detail = new OrderDetail();
		$order_detail->createList($order, $cart, OrderHistory::getLastOrderState($order->id), $cart->getProducts(), (isset($order_invoice) ? $order_invoice->id : 0), $use_taxes, (int)Tools::getValue('add_product_warehouse'));

		// update totals amount of order
		$order->total_products += (float)$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
		$order->total_products_wt += (float)$cart->getOrderTotal($use_taxes, Cart::ONLY_PRODUCTS);

		$order->total_paid += Tools::ps_round((float)($cart->getOrderTotal(true, $total_method)), 2);
		$order->total_paid_tax_excl += Tools::ps_round((float)($cart->getOrderTotal(false, $total_method)), 2);
		$order->total_paid_tax_incl += Tools::ps_round((float)($cart->getOrderTotal($use_taxes, $total_method)), 2);

		// discount
		$order->total_discounts += (float)abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));
		$order->total_discounts_tax_excl += (float)abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS));
		$order->total_discounts_tax_incl += (float)abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));

		// Save changes of order
		$order->update();

		// Delete specific price if exists
		if (isset($specific_price))
			$specific_price->delete();

		$products = $this->getProducts($order);
		// Get the last product
		$product = $products[max(array_keys($products))];

		// Get invoices collection
		$invoice_collection = $order->getInvoicesCollection();

		$invoice_array = array();
		foreach($invoice_collection as $invoice)
		{
			$invoice->name = $invoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
			$invoice_array[] = $invoice;
		}

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign(array(
			'product' => $product,
			'order' => $order,
			'currency' => new Currency($order->id_currency),
			'can_edit' => $this->tabAccess['edit'],
			'invoices_collection' => $invoice_collection,
			'current_id_lang' => Context::getContext()->language->id,
			'link' => Context::getContext()->link,
			'currentIndex' => self::$currentIndex
		));

		die(Tools::jsonEncode(array(
			'result' => true,
			'view' => $this->context->smarty->fetch('orders/_product_line.tpl'),
			'can_edit' => $this->tabAccess['add'],
			'order' => $order,
			'invoices' => $invoice_array,
			'documents_html' => $this->context->smarty->fetch('orders/_documents.tpl'),
			'shipping_html' => $this->context->smarty->fetch('orders/_shipping.tpl'),
			'discount_form_html' => $this->context->smarty->fetch('orders/_discount_form.tpl')
		)));
	}

	public function ajaxProcessLoadProductInformation()
	{
		$order_detail = new OrderDetail(Tools::getValue('id_order_detail'));
		if (!Validate::isLoadedObject($order_detail))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load OrderDetail object')
			)));

		$product = new Product($order_detail->product_id);
		if (!Validate::isLoadedObject($product))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Product object')
			)));

		$address = new Address(Tools::getValue('id_address'));
		if (!Validate::isLoadedObject($address))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Address object')
			)));

		die(Tools::jsonEncode(array(
			'result' => true,
			'product' => $product,
			'tax_rate' => $product->getTaxesRate($address),
			'price_tax_incl' => Product::getPriceStatic($product->id, true, $order_detail->product_attribute_id, 2),
			'price_tax_excl' => Product::getPriceStatic($product->id, false, $order_detail->product_attribute_id, 2)
		)));
	}

	public function ajaxProcessEditProductOnOrder()
	{
		// Return value
		$res = true;

		$order = new Order(Tools::getValue('id_order'));
		$order_detail = new OrderDetail(Tools::getValue('product_id_order_detail'));
		if (Tools::isSubmit('product_invoice'))
			$order_invoice = new OrderInvoice(Tools::getValue('product_invoice'));

		$this->doEditProductValidation($order_detail, $order, isset($order_invoice) ? $order_invoice : null);

		$product_price_tax_incl = Tools::ps_round(Tools::getValue('product_price_tax_incl'), 2);
		$product_price_tax_excl = Tools::ps_round(Tools::getValue('product_price_tax_excl'), 2);
		$total_products_tax_incl = $product_price_tax_incl * Tools::getValue('product_quantity');
		$total_products_tax_excl = $product_price_tax_excl * Tools::getValue('product_quantity');

		// Calculate differences of price (Before / After)
		$diff_price_tax_incl = $total_products_tax_incl - $order_detail->total_price_tax_incl;
		$diff_price_tax_excl = $total_products_tax_excl - $order_detail->total_price_tax_excl;

		// Apply change on OrderInvoice
		if (isset($order_invoice))
			// If OrderInvoice to use is different, we update the old invoice and new invoice
			if ($order_detail->id_order_invoice != $order_invoice->id)
			{
				$old_order_invoice = new OrderInvoice($order_detail->id_order_invoice);
				// We remove cost of products
				$old_order_invoice->total_products -= $order_detail->total_price_tax_excl;
				$old_order_invoice->total_products_wt -= $order_detail->total_price_tax_incl;

				$old_order_invoice->total_paid_tax_excl -= $order_detail->total_price_tax_excl;
				$old_order_invoice->total_paid_tax_incl -= $order_detail->total_price_tax_incl;

				$res &= $old_order_invoice->update();
				// TODO remove invoice if no item ?

				$order_invoice->total_products += $order_detail->total_price_tax_excl;
				$order_invoice->total_products_wt += $order_detail->total_price_tax_incl;

				$order_invoice->total_paid_tax_excl += $order_detail->total_price_tax_excl;
				$order_invoice->total_paid_tax_incl += $order_detail->total_price_tax_incl;

				$order_detail->id_order_invoice = $order_invoice->id;
			}

		if ($diff_price_tax_incl != 0 && $diff_price_tax_excl != 0)
		{
			$order_detail->unit_price_tax_excl = $product_price_tax_excl;
			$order_detail->unit_price_tax_incl = $product_price_tax_incl;

			$order_detail->total_price_tax_incl += $diff_price_tax_incl;
			$order_detail->total_price_tax_excl += $diff_price_tax_excl;

			if (isset($order_invoice))
			{
				// Apply changes on OrderInvoice
				$order_invoice->total_products += $diff_price_tax_excl;
				$order_invoice->total_products_wt += $diff_price_tax_incl;

				$order_invoice->total_paid_tax_excl += $diff_price_tax_excl;
				$order_invoice->total_paid_tax_incl += $diff_price_tax_incl;
			}

			// Apply changes on Order
			$order = new Order($order_detail->id_order);
			$order->total_products += $diff_price_tax_excl;
			$order->total_products_wt += $diff_price_tax_incl;

			$order->total_paid += $diff_price_tax_incl;
			$order->total_paid_tax_excl += $diff_price_tax_excl;
			$order->total_paid_tax_incl += $diff_price_tax_incl;

			$res &= $order->update();
		}

		$order_detail->product_quantity = Tools::getValue('product_quantity');
		// Save order detail
		$res &= $order_detail->update();
		// Save order invoice
		if (isset($order_invoice))
			 $res &= $order_invoice->update();

		$products = $this->getProducts($order);
		// Get the last product
		$product = $products[$order_detail->id];

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign(array(
			'product' => $product,
			'order' => $order,
			'currency' => new Currency($order->id_currency),
			'can_edit' => $this->tabAccess['edit'],
			'invoices_collection' => $invoice_collection,
			'current_id_lang' => Context::getContext()->language->id,
			'link' => Context::getContext()->link,
			'current_index' => self::$currentIndex
		));

		// Get invoices collection
		$invoice_collection = $order->getInvoicesCollection();

		$invoice_array = array();
		foreach($invoice_collection as $invoice)
		{
			$invoice->name = $invoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
			$invoice_array[] = $invoice;
		}

		if (!$res)
			die(Tools::jsonEncode(array(
				'result' => $res,
				'error' => Tools::displayError('Error occured on edition of this product line')
			)));

		die(Tools::jsonEncode(array(
			'result' => $res,
			'view' => $this->context->smarty->fetch('orders/_product_line.tpl'),
			'can_edit' => $this->tabAccess['add'],
			'invoices_collection' => $invoice_collection,
			'order' => $order,
			'invoices' => $invoice_array,
			'documents_html' => $this->context->smarty->fetch('orders/_documents.tpl'),
			'shipping_html' => $this->context->smarty->fetch('orders/_shipping.tpl')
		)));
	}

	public function ajaxProcessDeleteProductLine()
	{
		$res = true;

		$order_detail = new OrderDetail(Tools::getValue('id_order_detail'));
		$order = new Order(Tools::getValue('id_order'));

		$this->doDeleteProductLinveValidation($order_detail, $order);

		// Update OrderInvoice of this OrderDetail
		if ($order_detail->id_order_invoice != 0)
		{
			$order_invoice = new OrderInvoice($order_detail->id_order_invoice);
			$order_invoice->total_paid_tax_excl -= $order_detail->total_price_tax_incl;
			$order_invoice->total_paid_tax_incl -= $order_detail->total_price_tax_excl;
			$order_invoice->total_products -= $order_detail->total_price_tax_incl;
			$order_invoice->total_products_wt -= $order_detail->total_price_tax_excl;
			$res &= $order_invoice->update();
		}

		// Update Order
		$order->total_paid -= $order_detail->total_price_tax_incl;
		$order->total_paid_tax_incl -= $order_detail->total_price_tax_incl;
		$order->total_paid_tax_excl -= $order_detail->total_price_tax_excl;
		$order->total_products -= $order_detail->total_price_tax_incl;
		$order->total_products_wt -= $order_detail->total_price_tax_excl;

		$res &= $order->update();

		// Delete OrderDetail
		$res &= $order_detail->delete();

		if (!$res)
			die(Tools::jsonEncode(array(
				'result' => $res,
				'error' => Tools::displayError('Error occured on deletion of this product line')
			)));

		// Get invoices collection
		$invoice_collection = $order->getInvoicesCollection();

		$invoice_array = array();
		foreach($invoice_collection as $invoice)
		{
			$invoice->name = $invoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
			$invoice_array[] = $invoice;
		}

		// Assign to smarty informations in order to show the new product line
		$this->context->smarty->assign(array(
			'order' => $order,
			'currency' => new Currency($order->id_currency),
			'invoices_collection' => $invoice_collection,
			'current_id_lang' => Context::getContext()->language->id,
			'link' => Context::getContext()->link,
			'current_index' => self::$currentIndex
		));

		die(Tools::jsonEncode(array(
			'result' => $res,
			'order' => $order,
			'invoices' => $invoice_array,
			'documents_html' => $this->context->smarty->fetch('orders/_documents.tpl'),
			'shipping_html' => $this->context->smarty->fetch('orders/_shipping.tpl')
		)));
	}

	protected function doEditProductValidation(OrderDetail $order_detail, Order $order, OrderInvoice $order_invoice = null)
	{
		if (!Validate::isLoadedObject($order_detail))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Order Detail object')
			)));

		if (!empty($order_invoice) && !Validate::isLoadedObject($order_invoice))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Invoice object')
			)));

		if (!Validate::isLoadedObject($order))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Order object')
			)));

		if ($order_detail->id_order != $order->id)
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t edit this Order Detail for this order')
			)));

		// We can't edit a delivered order
		if ($order->hasBeenDelivered())
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t edit an delivered order')
			)));

		if (!empty($order_invoice) && $order_invoice->id_order != Tools::getValue('id_order'))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t use this invoice for this order')
			)));

		// Clean price
		$product_price_tax_incl = str_replace(',', '.', Tools::getValue('product_price_tax_incl'));
		$product_price_tax_excl = str_replace(',', '.', Tools::getValue('product_price_tax_excl'));

		if (!Validate::isPrice($product_price_tax_incl) || !Validate::isPrice($product_price_tax_excl))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Invalid price')
			)));

		if (!Validate::isUnsignedInt(Tools::getValue('product_quantity')))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Invalid quantity')
			)));
	}

	protected function doDeleteProductLinveValidation(OrderDetail $order_detail, Order $order)
	{
		if (!Validate::isLoadedObject($order_detail))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Order Detail object')
			)));

		if (!Validate::isLoadedObject($order))
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t load Order object')
			)));

		if ($order_detail->id_order != $order->id)
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t delete this Order Detail for this order')
			)));

		// We can't edit a delivered order
		if ($order->hasBeenDelivered())
			die(Tools::jsonEncode(array(
				'result' => false,
				'error' => Tools::displayError('Can\'t edit an delivered order')
			)));
	}

	protected function getProducts($order)
	{
		$products = $order->getProducts();

		foreach ($products as &$product)
		{
			if ($product['image'] != null)
			{
				$name = 'product_mini_'.(int)$product['product_id'].(isset($product['product_attribute_id']) ? '_'.(int)$product['product_attribute_id'] : '').'.jpg';
				// generate image cache, only for back office
				$product['image_tag'] = cacheImage(_PS_IMG_DIR_.'p/'.$product['image']->getExistingImgPath().'.jpg', $name, 45, 'jpg');
				$product['image_size'] = getimagesize(_PS_TMP_IMG_DIR_.$name);
			}
		}

		return $products;
	}

	protected function applyDiscountOnInvoice($order_invoice, $value_tax_incl, $value_tax_excl)
	{
		// Update OrderInvoice
		$order_invoice->total_discount_tax_incl += $value_tax_incl;
		$order_invoice->total_discount_tax_excl += $value_tax_excl;
		$order_invoice->total_paid_tax_incl -= $value_tax_incl;
		$order_invoice->total_paid_tax_excl -= $value_tax_excl;
		$order_invoice->update();
	}
}

