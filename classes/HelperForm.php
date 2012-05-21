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
*  @version  Release: $Revision: 9194 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class HelperFormCore extends Helper
{
	public $id;
	public $first_call = true;

	/** @var array of forms fields */
	protected $fields_form = array();

	/** @var array values ​​of form fields */
	public $fields_value = array();

	public $table;

	/** @var string if not null, a title will be added on that list */
	public $title = null;

	/** @var string Used to override default 'submitAdd' parameter in form action attribute */
	public $submit_action = '';

	public $token;
	public $languages = null;
	public $default_form_language = null;
	public $allow_employee_form_lang = null;

	public function __construct()
	{
		$this->base_folder = 'helper/form/';
		$this->base_tpl = 'form.tpl';
		parent::__construct();
	}

	public function generateForm($fields_form)
	{
		$this->fields_form = $fields_form;
		return $this->generate();
	}

	public function generate()
	{
		$this->tpl = $this->createTemplate($this->base_tpl);
		if ($this->submit_action == '')
			$this->submit_action = 'submitAdd'.$this->table;

		/* TODO : replace call method displayAssoShop() by form_shop.tpl */
		if (isset($this->fields_form[0]['form']['asso_shop']) && Shop::isFeatureActive())
			if ($this->fields_form[0]['asso_shop'] == 'group')
				$asso_shop = $this->displayAssoShop('group_shop');
			else if ($this->fields_form[0]['form']['asso_shop'] == 'shop')
				$asso_shop = $this->displayAssoShop();

		$this->context->controller->addJS(_PS_JS_DIR_.'form.js');

		$categories = true;
		$color = true;
		$date = true;
		$tinymce = true;
		foreach ($this->fields_form as $fieldset)
			if (isset($fieldset['form']['input']))
				foreach ($fieldset['form']['input'] as $key => $params)
				{
					switch ($params['type'])
					{
						case 'categories':
							if ($categories)
							{
								// Added Jquery plugin treeview (css and js files)
								$this->context->controller->addJqueryPlugin('treeview');
	
								// Added JS files
								$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/treeview/jquery.treeview.async.js');
								$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/treeview/jquery.treeview.edit.js');
								$this->context->controller->addJS(_PS_JS_DIR_.'admin-categories-tree.js');
	
								if (isset($params['use_search']) && $params['use_search'])
									$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/autocomplete/jquery.autocomplete.js');
								$categories = false;
							}
							break;
						case 'color':
							if ($color)
							{
								// Added JS file
								$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/jquery.colorpicker.js');
								$color = false;
							}
							break;
						case 'date':
							if ($date)
							{
								$this->context->controller->addJqueryUI('ui.datepicker');
								$date = false;
							}
							break;
						case 'textarea':
							if ($tinymce)
							{
								$iso = $this->context->language->iso_code;
								$this->tpl_vars['iso'] = file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en';
								$this->tpl_vars['path_css'] = _THEME_CSS_DIR_;
								$this->tpl_vars['ad'] = dirname($_SERVER['PHP_SELF']);
								$this->tpl_vars['tinymce'] = true;
	
								$this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
								$this->context->controller->addJS(_PS_JS_DIR_.'tinymce.inc.js');
								$tinymce = false;
							}
							break;
					}
				}

		$this->tpl->assign(array(
			'title' => $this->title,
			'toolbar_btn' => $this->toolbar_btn,
			
			'ps_help_context' => $this->ps_help_context,
			'class_name' => get_class($this->context->controller),
			'iso_user' => $this->context->language->id,
			'country_iso_code' => $this->context->country->iso_code,
			'version' => _PS_VERSION_,

			'show_toolbar' => $this->show_toolbar,
			'toolbar_fix' => $this->toolbar_fix,
			'submit_action' => $this->submit_action,
			'firstCall' => $this->first_call,
			'current' => $this->currentIndex,
			'token' => $this->token,
			'table' => $this->table,
			'languages' => $this->languages,
			'defaultFormLanguage' => $this->default_form_language,
			'allowEmployeeFormLang' => $this->allow_employee_form_lang,
			'form_id' => $this->id,
			'fields' => $this->fields_form,
			'fields_value' => $this->fields_value,
			'required_fields' => $this->getFieldsRequired(),
			'vat_number' => file_exists(_PS_MODULE_DIR_.'vatnumber/ajax.php'),
			'module_dir' => _MODULE_DIR_,
			'contains_states' => (isset($this->fields_value['id_country']) && isset($this->fields_value['id_state'])) ? Country::containsStates($this->fields_value['id_country']) : null,
			'asso_shop' => isset($asso_shop) ? $asso_shop : null
		));
		return parent::generate();
	}

	/**
	 * Return true if there are required fields
	 */
	public function getFieldsRequired()
	{
		foreach ($this->fields_form as $fieldset)
			if (isset($fieldset['form']['input']))
				foreach ($fieldset['form']['input'] as $input)
					if (array_key_exists('required', $input) && $input['required'])
						return true;

		return false;
	}
}
