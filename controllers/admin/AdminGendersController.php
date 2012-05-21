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

class AdminGendersController extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'gender';
		$this->className = 'Gender';
		$this->lang = true;
		$this->requiredDatabase = true;

		$this->addRowAction('edit');
		$this->addRowAction('delete');

		$this->context = Context::getContext();

		if (!Tools::getValue('realedit'))
			$this->deleted = false;

	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		$this->default_image_height = 16;
		$this->default_image_width = 16;

		$this->fieldImageSettings = array(
			'name' => 'image',
			'dir' => 'genders'
		);

		$this->fieldsDisplay = array(
			'id_gender' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 150,
				'filter_key' => 'b!name'
			),
			'type' => array(
				'title' => $this->l('Type'),
				'width' => 100,
				'orderby' => false,
				'type' => 'select',
				'list' => array(
					0 => $this->l('Male'),
					1 => $this->l('Female'),
					2 => $this->l('Neutral')
				),
				'filter_key' => 'a!type',
				'callback' => 'displayGenderType',
				'callback_object' => $this,
			),
			'image' => array(
				'title' => $this->l('Image'),
				'align' => 'center',
				'image' => 'genders',
				'orderby' => false,
				'search' => false
			)
		);

		parent::__construct();
	}

	public function initForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Gender'),
				'image' => '../img/admin/tab-genders.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'lang' => true,
					'size' => 33,
					'hint' => $this->l('Invalid characters:').' 0-9!<>,;?=+()@#"�{}_$%:',
					'required' => true
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Type:'),
					'name' => 'type',
					'required' => false,
					'class' => 't',
					'values' => array(
						array(
							'id' => 'type_male',
							'value' => 0,
							'label' => $this->l('Male')
						),
						array(
							'id' => 'type_female',
							'value' => 1,
							'label' => $this->l('Female')
						),
						array(
							'id' => 'type_neutral',
							'value' => 2,
							'label' => $this->l('Neutral')
						)
					)
				),
				array(
					'type' => 'file',
					'label' => $this->l('Image:'),
					'name' => 'image',
					'value' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('Image Width:'),
					'name' => 'img_width',
					'size' => 4,
					'desc' => $this->l('Image width in pixel. "0" to use original size')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Image Height:'),
					'name' => 'img_height',
					'size' => 4,
					'desc' => $this->l('Image height in pixel. "0" to use original size')
				)
			),
			'submit' => array(
				'title' => $this->l('   Save   '),
				'class' => 'button'
			)
		);

		if (!($obj = $this->loadObject(true)))
			return;

		$this->fields_value = array(
			'img_width' => $this->default_image_width,
			'img_height' => $this->default_image_height,
			'image' => $obj->getImage()
		);

		return parent::initForm();
	}

	public function displayGenderType($value, $tr)
	{
		return $this->fieldsDisplay['type']['list'][$value];
	}

	protected function postImage($id)
	{
		if (isset($this->fieldImageSettings['name']) && isset($this->fieldImageSettings['dir']))
		{
			if (!Validate::isInt(Tools::getValue('img_width')) || !Validate::isInt(Tools::getValue('img_height')))
				$this->_errors[] = Tools::displayError('Width and height must be a numeric');
			else
			{
				if ((int)Tools::getValue('img_width') > 0 && (int)Tools::getValue('img_height') > 0)
				{
					$width = (int)Tools::getValue('img_width');
					$height = (int)Tools::getValue('img_height');
				}
				else
				{
					$width = null;
					$height = null;
				}
				return $this->uploadImage($id, $this->fieldImageSettings['name'], $this->fieldImageSettings['dir'].'/', false, $width, $height);
			}
		}
		return !count($this->_errors) ? true : false;
	}
}

