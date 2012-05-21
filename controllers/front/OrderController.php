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
*  @version  Release: $Revision: 7095 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class OrderControllerCore extends ParentOrderController
{
	public $step;

	/**
	 * Initialize order controller
	 * @see FrontController::init()
	 */
	public function init()
	{
		global $isVirtualCart, $orderTotal;

		parent::init();

		$this->step = (int)(Tools::getValue('step'));
		if (!$this->nbProducts)
			$this->step = -1;

		// If some products have disappear
		if (!$this->context->cart->checkQuantities())
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('An item in your cart is no longer available for this quantity, you cannot proceed with your order.');
		}

		// Check minimal amount
		$currency = Currency::getCurrency((int)$this->context->cart->id_currency);

		$orderTotal = $this->context->cart->getOrderTotal();
		$minimal_purchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
		if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimal_purchase && $this->step != -1)
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimal_purchase, $currency).
			' '.Tools::displayError('is required in order to validate your order.');
		}

		if (!$this->context->customer->isLogged(true) && in_array($this->step, array(1, 2, 3)))
			Tools::redirect('index.php?controller=authentication&back='.urlencode('order.php&step='.$this->step.'&multi-shipping='.(int)Tools::getValue('multi-shipping')).'&multi-shipping='.(int)Tools::getValue('multi-shipping'));

		if (Tools::getValue('multi-shipping') == 1)
			$this->context->smarty->assign('multi_shipping', true);
		else
			$this->context->smarty->assign('multi_shipping', false);

		if ($this->context->customer->id)
			$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
		else
			$this->context->smarty->assign('address_list', array());
	}

	public function postProcess()
	{
		// Update carrier selected on preProccess in order to fix a bug of
		// block cart when it's hooked on leftcolumn
		if ($this->step == 3 && Tools::isSubmit('processCarrier'))
			$this->processCarrier();
	}

	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		global $isVirtualCart;

		if ($this->nbProducts)
			$this->context->smarty->assign('virtual_cart', $isVirtualCart);

		// 4 steps to the order
		switch ((int)$this->step)
		{
			case -1;
				$this->context->smarty->assign('empty', 1);
				$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
			break;

			case 1:
				$this->_assignAddress();
				$this->processAddressFormat();
				if (Tools::getValue('multi-shipping') == 1)
				{
					$this->_assignSummaryInformations();
					$this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping.tpl');
				}
				else
					$this->setTemplate(_PS_THEME_DIR_.'order-address.tpl');
			break;

			case 2:
				if (Tools::isSubmit('processAddress'))
					$this->processAddress();
				$this->autoStep();
				$this->_assignCarrier();
				$this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
			break;

			case 3:
				// Test that the conditions (so active) were accepted by the customer
				$cgv = Tools::getValue('cgv');
				if (Configuration::get('PS_CONDITIONS') && (!Validate::isBool($cgv) || $cgv == false))
					Tools::redirect('index.php?controller=order&step=2');

				$this->autoStep();

				// Bypass payment step if total is 0
				if (($id_order = $this->_checkFreeOrder()) && $id_order)
				{
					if ($this->context->customer->is_guest)
					{
						$email = $this->context->customer->email;
						$this->context->customer->mylogout(); // If guest we clear the cookie for security reason
						Tools::redirect('index.php?controller=guest-tracking&id_order='.(int)$id_order.'&email='.urlencode($email));
					}
					else
						Tools::redirect('index.php?controller=history');
				}
				$this->_assignPayment();
				// assign some informations to display cart
				$this->_assignSummaryInformations();
				$this->setTemplate(_PS_THEME_DIR_.'order-payment.tpl');
			break;

			default:
				$this->_assignSummaryInformations();
				$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
			break;
		}

		$this->context->smarty->assign(array(
			'currencySign' => $this->context->currency->sign,
			'currencyRate' => $this->context->currency->conversion_rate,
			'currencyFormat' => $this->context->currency->format,
			'currencyBlank' => $this->context->currency->blank,
		));
		parent::initContent();
	}

	private function processAddressFormat()
	{
		$addressDelivery = new Address((int)$this->context->cart->id_address_delivery);
		$addressInvoice = new Address((int)$this->context->cart->id_address_invoice);

		$invoiceAddressFields = AddressFormat::getOrderedAddressFields($addressInvoice->id_country, false, true);
		$deliveryAddressFields = AddressFormat::getOrderedAddressFields($addressDelivery->id_country, false, true);

		$this->context->smarty->assign(array(
			'inv_adr_fields' => $invoiceAddressFields,
			'dlv_adr_fields' => $deliveryAddressFields));
	}

	/**
	 * Order process controller
	 */
	public function autoStep()
	{
		global $isVirtualCart;

		if ($this->step >= 2 && (!$this->context->cart->id_address_delivery || !$this->context->cart->id_address_invoice))
			Tools::redirect('index.php?controller=order&step=1');
		
		if ($this->step > 2 && !$isVirtualCart && count($this->context->cart->getDeliveryOptionList()) == 0)
			Tools::redirect('index.php?controller=order&step=2');
		
		$delivery = new Address((int)$this->context->cart->id_address_delivery);
		$invoice = new Address((int)$this->context->cart->id_address_invoice);

		if ($delivery->deleted || $invoice->deleted)
		{
			if ($delivery->deleted)
				unset($this->context->cart->id_address_delivery);
			if ($invoice->deleted)
				unset($this->context->cart->id_address_invoice);
			Tools::redirect('index.php?controller=order&step=1');
		}
	}

	/*
	 * Manage address
	 */
	public function processAddress()
	{
		if (!Tools::getValue('multi-shipping'))
			$this->context->cart->setNoMultishipping();

		// Add checking for all addresses

		if (!Tools::isSubmit('id_address_delivery') || !Address::isCountryActiveById((int)Tools::getValue('id_address_delivery')))
			$this->errors[] = Tools::displayError('This address is not in a valid area.');
		else
		{
			$this->context->cart->id_address_delivery = (int)Tools::getValue('id_address_delivery');
			$this->context->cart->id_address_invoice = Tools::isSubmit('same') ? $this->context->cart->id_address_delivery : (int)Tools::getValue('id_address_invoice');
			if (!$this->context->cart->update())
				$this->errors[] = Tools::displayError('An error occurred while updating your cart.');

			if (Tools::isSubmit('message'))
				$this->_updateMessage(Tools::getValue('message'));
		}

		if ($this->errors)
		{
			if (Tools::getValue('ajax'))
				die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
			$this->step = 1;
		}

		if ($this->ajax)
			die(true);
	}

	/**
	 * Carrier step
	 */
	protected function processCarrier()
	{
		global $orderTotal;
		parent::_processCarrier();

		if (count($this->errors))
		{
			$this->context->smarty->assign('errors', $this->errors);
			$this->_assignCarrier();
			$this->step = 2;
			$this->displayContent();
			include(dirname(__FILE__).'/../footer.php');
			exit;
		}
		$orderTotal = $this->context->cart->getOrderTotal();
	}

	/**
	 * Address step
	 */
	protected function _assignAddress()
	{
		parent::_assignAddress();

		if (Tools::getValue('multi-shipping'))
			$this->context->cart->autosetProductAddress();

		$this->context->smarty->assign('cart', $this->context->cart);
		if ($this->context->customer->is_guest)
			Tools::redirect('index.php?controller=order&step=2');
	}

	/**
	 * Carrier step
	 */
	protected function _assignCarrier()
	{
		if (!isset($this->context->customer->id))
			die(Tools::displayError('Fatal error: No customer'));
		// Assign carrier
		parent::_assignCarrier();
		// Assign wrapping and TOS
		$this->_assignWrappingAndTOS();

		$this->context->smarty->assign('is_guest', (isset($this->context->customer->is_guest) ? $this->context->customer->is_guest : 0));
	}

	/**
	 * Payment step
	 */
	protected function _assignPayment()
	{
		global $orderTotal;

		// Redirect instead of displaying payment modules if any module are grefted on
		Hook::backBeforePayment('order.php?step=3');

		/* We may need to display an order summary */
		$this->context->smarty->assign($this->context->cart->getSummaryDetails());
		$this->context->smarty->assign(array(
			'total_price' => (float)($orderTotal),
			'taxes_enabled' => (int)(Configuration::get('PS_TAX'))
		));
		$this->context->cart->checkedTOS = '1';

		parent::_assignPayment();
	}
}

