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

class AdminSlipControllerCore extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'order_slip';
		$this->className = 'OrderSlip';

		$this->fieldsDisplay = array(
			'id_order_slip' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
 			),
			'id_order' => array(
				'title' => $this->l('ID Order'),
				'width' => 75,
				'align' => 'center'
 			),
			'date_add' => array(
				'title' => $this->l('Date issued'),
				'width' => 60,
				'type' => 'date',
				'align' => 'right'
 			)
 		);

		$this->optionTitle = $this->l('Slip');

		parent::__construct();
	}

	public function initList()
	{
		$this->addRowAction('edit');
	 	$this->addRowAction('delete');
	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		return parent::initList();
	}

	public function initForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Print a PDF'),
				'image' => '../img/admin/pdf.gif'
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
				'class' => 'button',
				'id' => 'submitPrint'
			)
		);

		$this->fields_value = array(
			'date_from' => date('Y-m-d'),
			'date_to' => date('Y-m-d')
		);

		$this->show_toolbar = false;
		return parent::initForm();
	}

	public function postProcess()
	{
		if (Tools::getValue('submitAddorder_slip'))
		{
			if (!Validate::isDate(Tools::getValue('date_from')))
				$this->_errors[] = $this->l('Invalid from date');
			if (!Validate::isDate(Tools::getValue('date_to')))
				$this->_errors[] = $this->l('Invalid end date');
			if (!count($this->_errors))
			{
				$order_slips = OrderSlip::getSlipsIdByDate(Tools::getValue('date_from'), Tools::getValue('date_to'));
				if (count($order_slips))
					Tools::redirectAdmin(
						'pdf.php?slips&date_from='.urlencode(Tools::getValue('date_from')).'&date_to='.urlencode(Tools::getValue('date_to')).'&token='.$this->token);
				$this->_errors[] = $this->l('No order slips found for this period');
			}
		}
		else
			return parent::postProcess();
	}

	public function initContent()
	{
		$this->initToolbar();
		$this->content .= $this->initList();
		$this->content .= $this->initForm();

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}

	public function initToolbar()
	{
		$this->toolbar_btn['save-date'] = array(
			'href' => '#',
			'desc' => $this->l('Generate PDF file')
		);
	}
}

