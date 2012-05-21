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
*  @version  Release: $Revision: 8971 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminDeliverySlipControllerCore extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'delivery';

		$this->context = Context::getContext();

		$this->options = array(
			'general' => array(
				'title' =>	$this->l('Delivery slips options'),
				'fields' =>	array(
					'PS_DELIVERY_PREFIX' => array(
						'title' => $this->l('Delivery prefix:'),
						'desc' => $this->l('Prefix used for delivery slips'),
						'size' => 6,
						'type' => 'textLang'
					),
					'PS_DELIVERY_NUMBER' => array(
						'title' => $this->l('Delivery number:'),
						'desc' => $this->l('The next delivery slip will begin with this number, and then increase with each additional slip'),
						'size' => 6,
						'type' => 'text'
					)
				),
				'submit' => array()
			)
		);

		parent::__construct();
	}

	public function initForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Print PDF delivery slips'),
				'image' => '../img/t/AdminPdf.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('From:'),
					'name' => 'date_from',
					'size' => 20,
					'maxlength' => 10,
					'required' => true,
					'desc' => $this->l('Format: 2007-12-31 (inclusive)')
				),
				array(
					'type' => 'text',
					'label' => $this->l('To:'),
					'name' => 'date_to',
					'size' => 20,
					'maxlength' => 10,
					'required' => true,
					'desc' => $this->l('Format: 2008-12-31 (inclusive)')
				)
			),
			'submit' => array(
				'title' => $this->l('Generate PDF file'),
				'class' => 'button'
			)
		);

		$this->fields_value = array(
			'date_from' => date('Y-m-d'),
			'date_to' => date('Y-m-d')
		);

		return parent::initForm();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAdddelivery'))
		{
			if (!Validate::isDate(Tools::getValue('date_from')))
				$this->_errors[] = Tools::displayError('Invalid from date');
			if (!Validate::isDate(Tools::getValue('date_to')))
				$this->_errors[] = Tools::displayError('Invalid end date');
			if (!count($this->_errors))
			{
				$orders = Order::getOrdersIdByDate(Tools::getValue('date_from'), Tools::getValue('date_to'), null, 'delivery');
				if (count($orders))
					Tools::redirectAdmin('pdf.php?deliveryslips='.urlencode(serialize($orders)).'&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('No delivery slip found for this period');
			}
		}
		else
			parent::postProcess();
	}

	public function initContent()
	{
		$this->content .= $this->initForm().'<br />';
		$this->show_toolbar = false;
		$this->content .= $this->initOptions();

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}
}


