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

class AdminLanguagesControllerCore extends AdminController
{
	public function __construct()
	{
	 	$this->table = 'lang';
		$this->className = 'Language';
	 	$this->lang = false;
		$this->deleted = false;

		$this->requiredDatabase = true;

		$this->context = Context::getContext();

 		$this->fieldImageSettings = array(
 			array(
 				'name' => 'flag',
 				'dir' => 'l'
 			),
 			array(
 				'name' => 'no-picture',
 				'dir' => 'p'
 			)
 		);

		$this->fieldsDisplay = array(
			'id_lang' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'flag' => array(
				'title' => $this->l('Logo'),
				'align' => 'center',
				'image' => 'l',
				'orderby' => false,
				'search' => false
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 120
			),
			'iso_code' => array(
				'title' => $this->l('ISO code'),
				'width' => 70,
				'align' => 'center'
			),
			'language_code' => array(
				'title' => $this->l('Language code'),
				'width' => 70,
				'align' => 'center'
			),
			'date_format_lite' => array(
				'title' => $this->l('Date format')
			),
			'date_format_full' => array(
				'title' => $this->l('Date format (full)')
			),
			'active' => array(
				'title' => $this->l('Enabled'),
				'align' => 'center',
				'active' => 'status',
				'type' => 'bool'
			)
		);

		$this->options = array(
			'general' => array(
				'title' =>	$this->l('Languages options'),
				'fields' =>	array(
					'PS_LANG_DEFAULT' => array(
						'title' => $this->l('Default language:'),
						'desc' => $this->l('The default language used in shop'),
						'cast' => 'intval',
						'type' => 'select',
						'identifier' => 'id_lang',
						'list' => Language::getlanguages(false)
					)
				),
				'submit' => array()
			)
		);

		parent::__construct();
	}

	public function initList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');

	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
	 	$this->specificConfirmDelete = $this->l('When you delete a language, ALL RELATED TRANSLATIONS IN THE DATABASE WILL BE DELETED, are you sure you want to delete this language?', __CLASS__, true, false);

		$this->displayWarning($this->l('When you delete a language, all related translations in the database will be deleted.'));
		return parent::initList();
	}

	public function initForm()
	{
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Languages'),
				'image' => '../img/admin/world.gif'
			),
			'input' => array(
				array(
					'type' => 'hidden',
					'name' => 'ps_version'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 8,
					'maxlength' => 32,
					'required' => true
				),
				array(
					'type' => 'text',
					'label' => $this->l('ISO code:'),
					'name' => 'iso_code',
					'required' => true,
					'size' => 4,
					'maxlength' => 2,
					'desc' => $this->l('2-letter ISO code (e.g., fr, en, de)')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Language code:'),
					'name' => 'language_code',
					'required' => true,
					'size' => 10,
					'maxlength' => 5,
					'desc' => $this->l('Full language code (e.g., en-us, pt-br)')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Date format:'),
					'name' => 'date_format_lite',
					'required' => true,
					'size' => 15,
					'desc' => $this->l('Date format, lite (e.g., Y-m-d, d/m/Y)')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Date format (full):'),
					'name' => 'date_format_full',
					'required' => true,
					'size' => 25,
					'desc' => $this->l('Date format, full (e.g., Y-m-d H:i:s, d/m/Y H:i)')
				),
				array(
					'type' => 'file',
					'label' => $this->l('Flag:'),
					'name' => 'flag',
					'required' => true,
					'desc' => $this->l('Upload country flag from your computer')
				),
				array(
					'type' => 'file',
					'label' => $this->l('"No-picture" image:'),
					'name' => 'no-picture',
					'required' => true,
					'desc' => $this->l('Image displayed when "no picture found"')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Is RTL language:'),
					'name' => 'is_rtl',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'is_rtl_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('To active if this language is a right to left language').' '.
							$this->l('(Experimental: your theme must be compliant with RTL language)')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Status:'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Allow or disallow this language to be selected by the customer')
				),
				array(
					'type' => 'special',
					'name' => 'resultCheckLangPack',
					'text' => $this->l('Check if a language pack is available for this ISO code...'),
					'img' => 'ajax-loader.gif'
				)
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Shop association:'),
				'name' => 'checkBoxShopAsso',
				'values' => Shop::getTree()
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('   Save   '),
			'class' => 'button'
		);

		if (!($obj = $this->loadObject(true)))
			return;

		if ($obj->id && !$obj->checkFiles())
		{
			$this->fields_form['new'] = array(
				'legend' => array(
					'title' => $this->l('Warning'),
					'image' => '../img/admin/warning.gif'
				),
				'list_files' => array(
					array(
						'label' => $this->l('Translations files:'),
						'files' => Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'tr', true)
					),
					array(
						'label' => $this->l('Theme files:'),
						'files' => Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'theme', true)
					),
					array(
						'label' => $this->l('Mail files:'),
						'files' => Language::getFilesList($obj->iso_code, _THEME_NAME_, false, false, 'mail', true)
					)
				)
			);
		}

		$this->fields_value = array(
			'ps_version' => _PS_VERSION_
		);

		$this->addJS(_PS_JS_DIR_.'checkLangPack.js');

		return parent::initForm();
	}

	public function postProcess()
	{
		if (isset($_GET['delete'.$this->table]))
		{
			if ($this->tabAccess['delete'] === '1')
		 	{
				if (Validate::isLoadedObject($object = $this->loadObject()) && isset($this->fieldImageSettings))
				{
					// English is needed by the system (ex. translations)
					if ($object->id == Language::getIdByIso('en'))
						$this->_errors[] = $this->l('You cannot delete the English language as it is a system requirement, you can only deactivate it.');
					if ($object->id == Configuration::get('PS_LANG_DEFAULT'))
						$this->_errors[] = $this->l('you cannot delete the default language');
					else if ($object->id == $this->context->language->id)
						$this->_errors[] = $this->l('You cannot delete the language currently in use. Please change languages before deleting.');
					else if ($this->deleteNoPictureImages((int)Tools::getValue('id_lang')) && $object->delete())
						Tools::redirectAdmin(self::$currentIndex.'&conf=1'.'&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.'</b> '.
						Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else if (Tools::getValue('submitDel'.$this->table) && isset($_POST[$this->table.'Box']))
		{
		 	if ($this->tabAccess['delete'] === '1')
			{
				if (in_array(Configuration::get('PS_LANG_DEFAULT'), $_POST[$this->table.'Box']))
					$this->_errors[] = $this->l('you cannot delete the default language');
				else if (in_array($this->context->language->id, $_POST[$this->table.'Box']))
					$this->_errors[] = $this->l('you cannot delete the language currently in use, please change languages before deleting');
				else
				{
				 	foreach ($_POST[$this->table.'Box'] as $language)
				 		$this->deleteNoPictureImages($language);
					parent::postProcess();
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else if (Tools::isSubmit('submitAddlang'))
		{
			/* New language */
			if ((int)Tools::getValue('id_'.$this->table) == 0)
			{
				if ($this->tabAccess['add'] === '1')
				{
					if (isset($_POST['iso_code']) && !empty($_POST['iso_code']) && Validate::isLanguageIsoCode(Tools::getValue('iso_code')) && Language::getIdByIso($_POST['iso_code']))
						$this->_errors[] = Tools::displayError('This ISO code is already linked to another language.');
					if ((!empty($_FILES['no-picture']['tmp_name']) || !empty($_FILES['flag']['tmp_name'])) && Validate::isLanguageIsoCode(Tools::getValue('iso_code')))
					{
						if ($_FILES['no-picture']['error'] == UPLOAD_ERR_OK)
							$this->copyNoPictureImage(strtolower(Tools::getValue('iso_code')));
						// class AdminTab deal with every $_FILES content, don't do that for no-picture
						unset($_FILES['no-picture']);
						parent::postProcess();
					}
					else
					{
						$this->validateRules();
						$this->_errors[] = Tools::displayError('Flag and No-Picture image fields are required.');
					}
				}
				else
					$this->_errors[] = Tools::displayError('You do not have permission to add here.');
			}
			/* Language edition */
			else
			{
				if ($this->tabAccess['edit'] === '1')
				{
					if (( isset($_FILES['no-picture']) && !$_FILES['no-picture']['error'] || isset($_FILES['flag']) && !$_FILES['flag']['error'])
						&& Validate::isLanguageIsoCode(Tools::getValue('iso_code')))
					{
						if ($_FILES['no-picture']['error'] == UPLOAD_ERR_OK)
							$this->copyNoPictureImage(strtolower(Tools::getValue('iso_code')));
						// class AdminTab deal with every $_FILES content, don't do that for no-picture
						unset($_FILES['no-picture']);
						parent::postProcess();
					}

					if (!Validate::isLoadedObject($object = $this->loadObject()))
						$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					if ((int)$object->id == (int)Configuration::get('PS_LANG_DEFAULT') && (int)$_POST['active'] != (int)$object->active)
						$this->_errors[] = Tools::displayError('You cannot change the status of the default language.');
					else
						parent::postProcess();

					$this->validateRules();
				}
				else
					$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			}
		}
		else if (isset($_GET['status'.$this->table]) && isset($_GET['id_lang']))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (!Validate::isLoadedObject($object = $this->loadObject()))
					$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
				if ((int)$object->id == (int)Configuration::get('PS_LANG_DEFAULT'))
					$this->_errors[] = Tools::displayError('You cannot change the status of the default language.');
				else
					return parent::postProcess();
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		else
			return parent::postProcess();
	}

	/**
	 * Copy a no-product image
	 *
	 * @param string $language Language iso_code for no-picture image filename
	 */
	public function copyNoPictureImage($language)
	{
		if (isset($_FILES['no-picture']) && $_FILES['no-picture']['error'] === 0)
			if ($error = checkImage($_FILES['no-picture'], Tools::getMaxUploadSize()))
				$this->_errors[] = $error;
			else
			{
				if (!$tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS') || !move_uploaded_file($_FILES['no-picture']['tmp_name'], $tmp_name))
					return false;
				if (!imageResize($tmp_name, _PS_IMG_DIR_.'p/'.$language.'.jpg'))
					$this->_errors[] = Tools::displayError('An error occurred while copying no-picture image to your product folder.');
				if (!imageResize($tmp_name, _PS_IMG_DIR_.'c/'.$language.'.jpg'))
					$this->_errors[] = Tools::displayError('An error occurred while copying no-picture image to your category folder.');
				if (!imageResize($tmp_name, _PS_IMG_DIR_.'m/'.$language.'.jpg'))
					$this->_errors[] = Tools::displayError('An error occurred while copying no-picture image to your manufacturer folder');
				else
				{
					$images_types = ImageType::getImagesTypes('products');
					foreach ($images_types as $k => $image_type)
					{
						if (!imageResize($tmp_name, _PS_IMG_DIR_.'p/'.$language.'-default-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']))
							$this->_errors[] = Tools::displayError('An error occurred while resizing no-picture image to your product directory.');
						if (!imageResize($tmp_name, _PS_IMG_DIR_.'c/'.$language.'-default-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']))
							$this->_errors[] = Tools::displayError('An error occurred while resizing no-picture image to your category directory.');
						if (!imageResize($tmp_name, _PS_IMG_DIR_.'m/'.$language.'-default-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']))
							$this->_errors[] = Tools::displayError('An error occurred while resizing no-picture image to your manufacturer directory.');
					}
				}
				unlink($tmp_name);
			}
	}

	/**
	 * deleteNoPictureImages will delete all default image created for the language id_language
	 *
	 * @param string $id_language
	 * @return boolean true if no error
	 */
	private function deleteNoPictureImages($id_language)
	{
	 	$language = Language::getIsoById($id_language);
		$images_types = ImageType::getImagesTypes('products');
		$dirs = array(_PS_PROD_IMG_DIR_, _PS_CAT_IMG_DIR_, _PS_MANU_IMG_DIR_, _PS_SUPP_IMG_DIR_, _PS_MANU_IMG_DIR_);
		foreach ($dirs as $dir)
		{
			foreach ($images_types as $k => $image_type)
				if (file_exists($dir.$language.'-default-'.stripslashes($image_type['name']).'.jpg'))
					if (!unlink($dir.$language.'-default-'.stripslashes($image_type['name']).'.jpg'))
						$this->_errors[] = Tools::displayError('An error occurred during image deletion.');

			if (file_exists($dir.$language.'.jpg'))
				if (!unlink($dir.$language.'.jpg'))
					$this->_errors[] = Tools::displayError('An error occurred during image deletion.');
		}

		return !count($this->_errors) ? true : false;
	}

	protected function copyFromPost(&$object, $table)
	{
		if ($object->id && ($object->iso_code != $_POST['iso_code']))
			if (Validate::isLanguageIsoCode($_POST['iso_code']))
				$object->moveToIso($_POST['iso_code']);
		parent::copyFromPost($object, $table);
	}

	public function beforeUpdateOptions()
	{
		$lang = new Language((int)Tools::getValue('PS_LANG_DEFAULT'));
		if (!$lang->active)
			$this->_errors[] = Tools::displayError('You cannot set this language as default language because it\'s disabled');
	}
}


