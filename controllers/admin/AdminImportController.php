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

@ini_set('max_execution_time', 0);
/** No max line limit since the lines can be more than 4096. Performance impact is not significant. */
define('MAX_LINE_SIZE', 0);

/** Used for validatefields diying without user friendly error or not */
define('UNFRIENDLY_ERROR', false);

/** this value set the number of columns visible on each page */
define('MAX_COLUMNS', 6);

/** correct Mac error on eof */
@ini_set('auto_detect_line_endings', '1');

class AdminImportControllerCore extends AdminController
{
	public static $column_mask;

	public $entities = array();

	public $available_fields = array();

	public $required_fields = array('name');

	public static $default_values = array();

	public static $validators = array(
		'active' => array('AdminImportController', 'getBoolean'),
		'tax_rate' => array('AdminImportController', 'getPrice'),
		 /** Tax excluded */
		'price_tex' => array('AdminImportController', 'getPrice'),
		 /** Tax included */
		'price_tin' => array('AdminImportController', 'getPrice'),
		'reduction_price' => array('AdminImportController', 'getPrice'),
		'reduction_percent' => array('AdminImportController', 'getPrice'),
		'wholesale_price' => array('AdminImportController', 'getPrice'),
		'ecotax' => array('AdminImportController', 'getPrice'),
		'name' => array('AdminImportController', 'createMultiLangField'),
		'description' => array('AdminImportController', 'createMultiLangField'),
		'description_short' => array('AdminImportController', 'createMultiLangField'),
		'meta_title' => array('AdminImportController', 'createMultiLangField'),
		'meta_keywords' => array('AdminImportController', 'createMultiLangField'),
		'meta_description' => array('AdminImportController', 'createMultiLangField'),
		'link_rewrite' => array('AdminImportController', 'createMultiLangField'),
		'available_now' => array('AdminImportController', 'createMultiLangField'),
		'available_later' => array('AdminImportController', 'createMultiLangField'),
		'category' => array('AdminImportController', 'split'),
		'online_only' => array('AdminImportController', 'getBoolean')
	);

	public function __construct()
	{
		$this->entities = array_flip(array(
			$this->l('Categories'),
			$this->l('Products'),
			$this->l('Combinations'),
			$this->l('Customers'),
			$this->l('Addresses'),
			$this->l('Manufacturers'),
			$this->l('Suppliers'),
			$this->l('SupplyOrders'),
		));

		switch ((int)Tools::getValue('entity'))
		{
			case $this->entities[$this->l('Combinations')]:
				$this->required_fields = array(
					'id_product',
					'group',
					'attribute'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id_product' => array('label' => $this->l('Product ID').'*'),
					'group' => array(
						'label' => $this->l('Group (Name:Position)').'*',
						'help' => $this->l('Position of the group attribute.')
					),
					'attribute' => array(
						'label' => $this->l('Attribute (Value:Position)').'*',
						'help' => $this->l('Position of the attribute in the attribute group.')
					),
					'reference' => array('label' => $this->l('Reference')),
					'supplier_reference' => array('label' => $this->l('Supplier reference')),
					'ean13' => array('label' => $this->l('EAN13')),
					'upc' => array('label' => $this->l('UPC')),
					'wholesale_price' => array('label' => $this->l('Wholesale price')),
					'price' => array('label' => $this->l('Price')),
					'ecotax' => array('label' => $this->l('Ecotax')),
					'quantity' => array('label' => $this->l('Quantity')),
					'weight' => array('label' => $this->l('Weight')),
					'default_on' => array('label' => $this->l('Default')),
					'image_position' => array(
						'label' => $this->l('Image position'),
						'help' => $this->l('Position of the product image to use for this combination. If you use this field, leave image URL empty.')
					),
					'image_url' => array('label' => $this->l('Image URL')),
					'delete_existing_images' => array(
						'label' => $this->l('Delete existing images (0 = no, 1 = yes)'),
						'help' => $this->l('If you do not specify this column and you specify the column images,
							all images of the product will be replaced by those specified in the import file')
					),
				);

				self::$default_values = array(
					'reference' => '',
					'supplier_reference' => '',
					'ean13' => '',
					'upc' => '',
					'wholesale_price' => 0,
					'price' => 0,
					'ecotax' => 0,
					'quantity' => 0,
					'weight' => 0,
					'default_on' => 0
				);
			break;

			case $this->entities[$this->l('Categories')]:
				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'parent' => array('label' => $this->l('Parent category')),
					'description' => array('label' => $this->l('Description')),
					'meta_title' => array('label' => $this->l('Meta-title')),
					'meta_keywords' => array('label' => $this->l('Meta-keywords')),
					'meta_description' => array('label' => $this->l('Meta-description')),
					'link_rewrite' => array('label' => $this->l('URL rewritten')),
					'image' => array('label' => $this->l('Image URL')),
				);

				self::$default_values = array(
					'active' => '1',
					'parent' => '1',
					'link_rewrite' => ''
				);
			break;

			case $this->entities[$this->l('Products')]:
				self::$validators['image'] = array(
					'AdminImportController',
					'split'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'category' => array('label' => $this->l('Categories (x,y,z...)')),
					'price_tex' => array('label' => $this->l('Price tax excl.')),
					'price_tin' => array('label' => $this->l('Price tax incl.')),
					'id_tax_rules_group' => array('label' => $this->l('Tax rules id')),
					'wholesale_price' => array('label' => $this->l('Wholesale price')),
					'on_sale' => array('label' => $this->l('On sale (0/1)')),
					'reduction_price' => array('label' => $this->l('Discount amount')),
					'reduction_percent' => array('label' => $this->l('Discount percent')),
					'reduction_from' => array('label' => $this->l('Discount from (yyyy-mm-dd)')),
					'reduction_to' => array('label' => $this->l('Discount to (yyyy-mm-dd)')),
					'reference' => array('label' => $this->l('Reference #')),
					'supplier_reference' => array('label' => $this->l('Supplier reference #')),
					'supplier' => array('label' => $this->l('Supplier')),
					'manufacturer' => array('label' => $this->l('Manufacturer')),
					'ean13' => array('label' => $this->l('EAN13')),
					'upc' => array('label' => $this->l('UPC')),
					'ecotax' => array('label' => $this->l('Ecotax')),
					'weight' => array('label' => $this->l('Weight')),
					'quantity' => array('label' => $this->l('Quantity')),
					'description_short' => array('label' => $this->l('Short description')),
					'description' => array('label' => $this->l('Description')),
					'tags' => array('label' => $this->l('Tags (x,y,z...)')),
					'meta_title' => array('label' => $this->l('Meta-title')),
					'meta_keywords' => array('label' => $this->l('Meta-keywords')),
					'meta_description' => array('label' => $this->l('Meta-description')),
					'link_rewrite' => array('label' => $this->l('URL rewritten')),
					'available_now' => array('label' => $this->l('Text when in-stock')),
					'available_later' => array('label' => $this->l('Text if back-order allowed')),
					'available_for_order' => array('label' => $this->l('Available for order')),
					'date_add' => array('label' => $this->l('Date add product')),
					'show_price' => array('label' => $this->l('Show price')),
					'image' => array('label' => $this->l('Image URLs (x,y,z...)')),
					'delete_existing_images' => array(
						'label' => $this->l('Delete existing images (0 = no, 1 = yes)'),
						'help' => $this->l('If you do not specify this column and you specify the column images,
							all images of the product will be replaced by those specified in the import file')
					),
					'features' => array('label' => $this->l('Feature(Name:Value:Position)'),
						'help' => $this->l('Position of the feature.')),
					'online_only' => array('label' => $this->l('Only available online')),
					'condition' => array('label' => $this->l('Condition')),
					'shop' => array(
						'label' => $this->l('ID / Name of shop'),
						'help' => $this->l('Ignore this field if you don\'t use multishop tool. If you leave this field empty, default shop will be used'),
					),
				);

				self::$default_values = array(
					'id_category' => array(1),
					'id_category_default' => 1,
					'active' => '1',
					'quantity' => 0,
					'price' => 0,
					'id_tax_rules_group' => 0,
					'description_short' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
					'link_rewrite' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
					'online_only' => 0,
					'condition' => 'new',
					'shop' => Configuration::get('PS_SHOP_DEFAULT'),
					'date_add' => date('Y-m-d H:i:s'),
					'condition' => 'new',
				);
			break;

			case $this->entities[$this->l('Customers')]:
				//Overwrite required_fields AS only email is required whereas other entities
				$this->required_fields = array('email', 'passwd', 'lastname', 'firstname');

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active  (0/1)')),
					'id_gender' => array('label' => $this->l('Gender ID (Mr = 1, Ms = 2, else 0)')),
					'email' => array('label' => $this->l('E-mail *')),
					'passwd' => array('label' => $this->l('Password *')),
					'birthday' => array('label' => $this->l('Birthday (yyyy-mm-dd)')),
					'lastname' => array('label' => $this->l('Lastname *')),
					'firstname' => array('label' => $this->l('Firstname *')),
					'newsletter' => array('label' => $this->l('Newsletter (0/1)')),
					'optin' => array('label' => $this->l('Opt in (0/1)')),
					'id_shop' => array(
						'label' => $this->l('ID / Name of shop'),
						'help' => $this->l('Ignore this field if you don\'t use multishop tool. If you leave this field empty, default shop will be used'),
					),
				);

				self::$default_values = array(
					'active' => '1',
					'id_shop' => Configuration::get('PS_SHOP_DEFAULT'),
				);
			break;

			case $this->entities[$this->l('Addresses')]:
				//Overwrite required_fields
				$this->required_fields = array(
					'lastname',
					'firstname',
					'address1',
					'postcode',
					'country',
					'city'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'alias' => array('label' => $this->l('Alias *')),
					'active' => array('label' => $this->l('Active  (0/1)')),
					'customer_email' => array('label' => $this->l('Customer e-mail')),
					'manufacturer' => array('label' => $this->l('Manufacturer')),
					'supplier' => array('label' => $this->l('Supplier')),
					'company' => array('label' => $this->l('Company')),
					'lastname' => array('label' => $this->l('Lastname *')),
					'firstname' => array('label' => $this->l('Firstname *')),
					'address1' => array('label' => $this->l('Address 1 *')),
					'address2' => array('label' => $this->l('Address 2')),
					'postcode' => array('label' => $this->l('Postcode*/ Zipcode*')),
					'city' => array('label' => $this->l('City *')),
					'country' => array('label' => $this->l('Country *')),
					'state' => array('label' => $this->l('State')),
					'other' => array('label' => $this->l('Other')),
					'phone' => array('label' => $this->l('Phone')),
					'phone_mobile' => array('label' => $this->l('Mobile Phone')),
					'vat_number' => array('label' => $this->l('VAT number')),
				);

				self::$default_values = array(
					'alias' => 'Alias',
					'postcode' => 'X'
				);
			break;

			case $this->entities[$this->l('Manufacturers')]:
			case $this->entities[$this->l('Suppliers')]:
				//Overwrite validators AS name is not MultiLangField
				self::$validators = array(
					'description' => array('AdminImportController', 'createMultiLangField'),
					'short_description' => array('AdminImportController', 'createMultiLangField'),
					'meta_title' => array('AdminImportController', 'createMultiLangField'),
					'meta_keywords' => array('AdminImportController', 'createMultiLangField'),
					'meta_description' => array('AdminImportController', 'createMultiLangField'),
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'description' => array('label' => $this->l('Description')),
					'short_description' => array('label' => $this->l('Short description')),
					'meta_title' => array('label' => $this->l('Meta-title')),
					'meta_keywords' => array('label' => $this->l('Meta-keywords')),
					'meta_description' => array('label' => $this->l('Meta-description')),
					'shop' => array(
						'label' => $this->l('ID / Name of group shop'),
						'help' => $this->l('Ignore this field if you don\'t use multishop tool. If you leave this field empty, default shop will be used'),
					),
				);

				self::$default_values = array(
					'shop' => Shop::getGroupFromShop(Configuration::get('PS_SHOP_DEFAULT')),
				);
			break;
			// @since 1.5.0
			case $this->entities[$this->l('SupplyOrders')]:
				// required fields
				$this->required_fields = array(
					'id_supplier',
					'id_warehouse',
					'reference',
					'date_delivery_expected',
				);
				// available fields
				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'id_supplier' => array('label' => $this->l('Supplier ID *')),
					'id_lang' => array('label' => $this->l('Lang ID')),
					'id_warehouse' => array('label' => $this->l('Warehouse ID *')),
					'id_currency' => array('label' => $this->l('Currency ID *')),
					'reference' => array('label' => $this->l('Supply Order Reference *')),
					'date_delivery_expected' => array('label' => $this->l('Delivery Date (Y-M-D)*')),
					'discount_rate' => array('label' => $this->l('Discount Rate')),
				);
				// default values
				self::$default_values = array(
					'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
					'id_currency' => Currency::getDefaultCurrency()->id,
					'discount_rate' => '0',
				);
		}

		parent::__construct();
	}

	public function renderForm()
	{
		if (!is_writable(_PS_ADMIN_DIR_.'/import/'))
			$this->displayWarning($this->l('directory import on admin directory must be writable (CHMOD 755 / 777)'));

		if (isset($this->warnings) && count($this->warnings))
		{
			$warnings = array();
			foreach ($this->warnings as $warning)
				$warnings[] = $warning;
			$this->displayWarning($warnings);
		}

		$files_to_import = scandir(_PS_ADMIN_DIR_.'/import/');
		uasort($files_to_import, array('AdminImportController', 'usortFiles'));
		foreach ($files_to_import as $k => &$filename)
			//exclude .  ..  .svn and index.php and all hidden files
			if (preg_match('/^\..*|index\.php/i', $filename))
				unset($files_to_import[$k]);
		unset($filename);

		$this->fields_form = array('');

		$this->toolbar_fix = false;
		$this->toolbar_btn = array();

		// adds fancybox
		$this->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
		$this->addJqueryPlugin(array('fancybox'));

		$this->tpl_form_vars = array(
			'module_confirmation' => (Tools::getValue('import')) && (isset($this->warnings) && !count($this->warnings)),
			'path_import' => _PS_ADMIN_DIR_.'/import/',
			'entities' => $this->entities,
			'entity' => Tools::getValue('entity'),
			'files_to_import' => $files_to_import,
			'languages' => Language::getLanguages(false),
			'id_language' => $this->context->language->id,
			'available_fields' => $this->getAvailableFields()
		);

		return parent::renderForm();
	}

	public function renderView()
	{
		$this->addJS(_PS_JS_DIR_.'adminImport.js');

		$glue = Tools::getValue('separator', ';');
		$handle = $this->openCsvFile();
		$nb_column = $this->getNbrColumn($handle, $glue);
		$nb_table = ceil($nb_column / MAX_COLUMNS);

		$res = array();
		foreach ($this->required_fields as $elem)
			$res[] = '\''.$elem.'\'';

		$data = array();
		for ($i = 0; $i < $nb_table; $i++)
			$data[$i] = $this->generateContentTable($i, $nb_column, $handle, $glue);

		$this->tpl_view_vars = array(
			'import_matchs' => Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'import_match'),
			'fields_value' => array(
				'csv' => Tools::getValue('csv'),
				'convert' => Tools::getValue('convert'),
				'entity' => (int)Tools::getValue('entity'),
				'iso_lang' => Tools::getValue('iso_lang'),
				'truncate' => Tools::getValue('truncate'),
				'match_ref' => Tools::getValue('match_ref'),
				'separator' => strval(trim(Tools::getValue('separator'))),
				'multiple_value_separator' => strval(trim(Tools::getValue('multiple_value_separator')))
			),
			'nb_table' => $nb_table,
			'nb_column' => $nb_column,
			'res' => implode(',', $res),
			'max_columns' => MAX_COLUMNS,
			'no_pre_select' => array('price_tin', 'feature'),
			'available_fields' => $this->available_fields,
			'data' => $data
		);

		return parent::renderView();
	}

	public function initToolbar()
	{
		switch ($this->display)
		{
			// @todo defining default buttons
			case 'import':
				// Default cancel button - like old back link
				$back = Tools::safeOutput(Tools::getValue('back', ''));
				if (empty($back))
					$back = self::$currentIndex.'&token='.$this->token;

				$this->toolbar_btn['cancel'] = array(
					'href' => $back,
					'desc' => $this->l('Cancel')
				);
				// Default save button - action dynamically handled in javascript
				$this->toolbar_btn['save-import'] = array(
					'href' => '#',
					'desc' => $this->l('Import CSV data')
				);
				break;
		}
	}

	private function generateContentTable($current_table, $nb_column, $handle, $glue)
	{
		$html = '<table id="table'.$current_table.'" style="display: none;" class="table" cellspacing="0" cellpadding="0">
					<tr>';

		// Header
		for ($i = 0; $i < $nb_column; $i++)
			if (MAX_COLUMNS * (int)$current_table <= $i && (int)$i < MAX_COLUMNS * ((int)$current_table + 1))
				$html .= '<th style="width: '.(900 / MAX_COLUMNS).'px; vertical-align: top; padding: 4px">
							<select onchange="askFeatureName(this, '.$i.');"
								style="width: '.(900 / MAX_COLUMNS).'px;"
								id="type_value['.$i.']"
								name="type_value['.$i.']"
								class="type_value">
								'.$this->getTypeValuesOptions($i).'
							</select>
							<div id="features_'.$i.'" style="display: none;">
								<input style="width: 90px" type="text" name="" id="feature_name_'.$i.'">
								<input type="button" value="ok" onclick="replaceFeature($(\'#feature_name_'.$i.'\').attr(\'name\'), '.$i.');">
							</div>
						</th>';
		$html .= '</tr>';

		self::setLocale();
		for ($current_line = 0; $current_line < 10 && $line = fgetcsv($handle, MAX_LINE_SIZE, $glue); $current_line++)
		{
			/* UTF-8 conversion */
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$html .= '<tr id="table_'.$current_table.'_line_'.$current_line.'" style="padding: 4px">';
			foreach ($line as $nb_c => $column)
				if ((MAX_COLUMNS * (int)$current_table <= $nb_c) && ((int)$nb_c < MAX_COLUMNS * ((int)$current_table + 1)))
					$html .= '<td>'.htmlentities(substr($column, 0, 200), ENT_QUOTES, 'UTF-8').'</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		self::rewindBomAware($handle);
		return $html;
	}

	public function init()
	{
		parent::init();
		if (Tools::isSubmit('submitImportFile'))
			$this->display = 'import';
	}

	public function initContent()
	{
		// toolbar (save, cancel, new, ..)
		$this->initToolbar();
		if ($this->display == 'import')
			$this->content .= $this->renderView();
		else
			$this->content .= $this->renderForm();

		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}

	private static function rewindBomAware($handle)
	{
		// A rewind wrapper that skip BOM signature wrongly
		rewind($handle);
		if (($bom = fread($handle, 3)) != "\xEF\xBB\xBF")
			rewind($handle);
	}

	private static function getBoolean($field)
	{
		return (boolean)$field;
	}

	private static function getPrice($field)
	{
		$field = ((float)str_replace(',', '.', $field));
		$field = ((float)str_replace('%', '', $field));
		return $field;
	}

	private static function split($field)
	{
		if (empty($field))
			return array();

		if (is_null(Tools::getValue('multiple_value_separator')) || trim(Tools::getValue('multiple_value_separator')) == '')
			$separator = ',';
		else
			$separator = Tools::getValue('multiple_value_separator');

		$temp = tmpfile();
		fwrite($temp, $field);
		rewind($temp);
		$tab = fgetcsv($temp, MAX_LINE_SIZE, $separator);
		fclose($temp);
		if (empty($tab) || (!is_array($tab)))
			return array();
		return $tab;

	}

	private static function createMultiLangField($field)
	{
		$languages = Language::getLanguages(false);
		$res = array();
		foreach ($languages as $lang)
			$res[$lang['id_lang']] = $field;
		return $res;
	}

	private function getTypeValuesOptions($nb_c)
	{
		$i = 0;
		$no_pre_select = array('price_tin', 'feature');

		$options = '';
		foreach ($this->available_fields as $k => $field)
		{
			$options .= '<option value="'.$k.'"';
			if ($k === 'price_tin')
				++$nb_c;
			if ($i === ($nb_c + 1) && (!in_array($k, $no_pre_select)))
				$options .= ' selected="selected"';
			$options .= '>'.$field['label'].'</option>';
			++$i;
		}
		return $options;
	}

	/*
	* Return fields to be display AS piece of advise
	*
	* @param $in_array boolean
	* @return string or return array
	*/
	public function getAvailableFields($in_array = false)
	{
		$i = 0;
		$fields = array();
		foreach ($this->available_fields as $k => $field)
		{
			if ($k === 'no')
				continue;
			if ($k === 'price_tin')
				$fields[$i - 1]['label'] = $fields[$i - 1]['label'].' '.$this->l('or').' '.$field['label'];
			else
			{
				if (isset($field['help']))
					$html = '&nbsp;<a href="#" class="info" title="'.$this->l('Info').'|'.$field['help'].'"><img src="'._PS_ADMIN_IMG_.'information.png"></a>';
				else
					$html = '<span style="margin-left:16px"></span>';
				$fields[] = '<div>'.$field['label'].$html.'</div>';
			}
			++$i;
		}
		if ($in_array)
			return $fields;
		else
			return implode("\n\r", $fields);
	}

	private function receiveTab()
	{
		$type_value = Tools::getValue('type_value') ? Tools::getValue('type_value') : array();
		foreach ($type_value as $nb => $type)
			if ($type != 'no')
				self::$column_mask[$type] = $nb;
	}

	public static function getMaskedRow($row)
	{
		$res = array();
		foreach (self::$column_mask as $type => $nb)
			$res[$type] = isset($row[$nb]) ? $row[$nb] : null;
		return $res;
	}

	private static function setDefaultValues(&$info)
	{
		foreach (self::$default_values as $k => $v)
			if (!isset($info[$k]) || $info[$k] == '')
				$info[$k] = $v;
	}

	private static function setEntityDefaultValues(&$entity)
	{
		$members = get_object_vars($entity);
		foreach (self::$default_values as $k => $v)
			if ((array_key_exists($k, $members) && $entity->$k === null) || !array_key_exists($k, $members))
				$entity->$k = $v;
	}

	private static function fillInfo($infos, $key, &$entity)
	{
		if (isset(self::$validators[$key][1]) && self::$validators[$key][1] == 'createMultiLangField' && Tools::getValue('iso_lang'))
		{
			$id_lang = Language::getIdByIso(Tools::getValue('iso_lang'));
			$tmp = call_user_func(self::$validators[$key], $infos);
			foreach ($tmp as $id_lang_tmp => $value)
				if (empty($entity->{$key}[$id_lang_tmp]) || $id_lang_tmp == $id_lang)
					$entity->{$key}[$id_lang_tmp] = $value;
		}
		else
			$entity->{$key} = isset(self::$validators[$key]) ? call_user_func(self::$validators[$key], $infos) : $infos;
		return true;
	}

	public static function arrayWalk(&$array, $funcname, &$user_data = false)
	{
		if (!is_callable($funcname)) return false;

		foreach ($array as $k => $row)
			if (!call_user_func_array($funcname, array($row, $k, $user_data)))
				return false;
		return true;
	}

	/**
	 * copyImg copy an image located in $url and save it in a path
	 * according to $entity->$id_entity .
	 * $id_image is used if we need to add a watermark
	 *
	 * @param int $id_entity id of product or category (set in entity)
	 * @param int $id_image (default null) id of the image if watermark enabled.
	 * @param string $url path or url to use
	 * @param string entity 'products' or 'categories'
	 * @return void
	 */
	private static function copyImg($id_entity, $id_image = null, $url, $entity = 'products')
	{
		$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
		$watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

		switch ($entity)
		{
			default:
			case 'products':
				$image_obj = new Image($id_image);
				$path = $image_obj->getPathForCreation();
			break;
			case 'categories':
				$path = _PS_CAT_IMG_DIR_.(int)$id_entity;
			break;
		}
		$url = str_replace(' ', '%20', trim($url));
		if (file_exists($url) && copy($url, $tmpfile))
		{
			imageResize($tmpfile, $path.'.jpg');
			$images_types = ImageType::getImagesTypes($entity);
			foreach ($images_types as $k => $image_type)
			{
				$theme = (Shop::isFeatureActive() ? '-'.$image_type['id_theme'] : '');
				imageResize($tmpfile, $path.'-'.stripslashes($image_type['name']).$theme.'.jpg', $image_type['width'], $image_type['height']);
			}
			if (in_array($image_type['id_image_type'], $watermark_types))
				Hook::exec('watermark', array('id_image' => $id_image, 'id_product' => $id_entity));
		}
		else
		{
			unlink($tmpfile);
			return false;
		}
		unlink($tmpfile);
		return true;
	}

	public function categoryImport()
	{
		$cat_moved = array();

		$this->receiveTab();
		$handle = $this->openCsvFile();
		$default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			self::setDefaultValues($info);
			$category = new Category();
			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $category);

			if (isset($category->parent) && is_numeric($category->parent))
			{
				if (isset($cat_moved[$category->parent]))
					$category->parent = $cat_moved[$category->parent];
				$category->id_parent = $category->parent;
			}
			else if (isset($category->parent) && is_string($category->parent))
			{
				$category_parent = Category::searchByName($default_language_id, $category->parent, true);
				if ($category_parent['id_category'])
					$category->id_parent =	(int)$category_parent['id_category'];
				else
				{
					$category_to_create = new Category();
					$category_to_create->name = self::createMultiLangField($category->parent);
					$category_to_create->active = 1;
					$category_to_create->id_parent = 1; // Default parent is home for unknown category to create
					if (($field_error = $category_to_create->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $category_to_create->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $category_to_create->add())
						$category->id_parent = $category_to_create->id;
					else
					{
						$this->_errors[] = $category_to_create->name[$default_language_id].(isset($category_to_create->id) ? ' ('.$category_to_create->id.')' : '').
							' '.Tools::displayError('Cannot be saved');
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
							Db::getInstance()->getMsgError();
					}
				}
			}
			if (isset($category->link_rewrite) && !empty($category->link_rewrite[$default_language_id]))
				$valid_link = Validate::isLinkRewrite($category->link_rewrite[$default_language_id]);
			else
				$valid_link = false;

			$bak = $category->link_rewrite[$default_language_id];
			if ((isset($category->link_rewrite) && empty($category->link_rewrite[$default_language_id])) || !$valid_link)
			{
				$category->link_rewrite = Tools::link_rewrite($category->name[$default_language_id]);
				if ($category->link_rewrite == '')
				{
					$category->link_rewrite = 'friendly-url-autogeneration-failed';
					$this->warnings[] = Tools::displayError('URL rewriting failed to auto-generate a friendly URL for: ').$category->name[$default_language_id];
				}
				$category->link_rewrite = self::createMultiLangField($category->link_rewrite);
			}

			if (!$valid_link)
				$this->warnings[] = Tools::displayError('Rewrite link for').' '.$bak.(isset($info['id']) ? ' (ID '.$info['id'].') ' : '').
					' '.Tools::displayError('was re-written as').' '.$category->link_rewrite[$default_language_id];
			$res = false;
			if (($field_error = $category->validateFields(UNFRIENDLY_ERROR, true)) === true &&
				($lang_field_error = $category->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
			{
				$category_already_created = Category::searchByNameAndParentCategoryId(
					$default_language_id,
					$category->name[$default_language_id],
					$category->id_parent
				);

				// If category already in base, get id category back
				if ($category_already_created['id_category'])
				{
					$cat_moved[$category->id] = (int)$category_already_created['id_category'];
					$category->id =	(int)$category_already_created['id_category'];
				}

				/* No automatic nTree regeneration for import */
				$category->doNotRegenerateNTree = true;

				// If id category AND id category already in base, trying to update
				if ($category->id && $category->categoryExists($category->id) && $category->id != 1)
					$res = $category->update();
				if ($category->id == 1)
					$this->_errors[] = Tools::displayError('Root category cannot be modify');
				// If no id_category or update failed
				if (!$res)
					$res = $category->add();
			}
			//copying images of categories
			if (isset($category->image) && !empty($category->image))
				if (!(self::copyImg($category->id, null, $category->image, 'categories')))
					$this->warnings[] = $category->image.' '.Tools::displayError('Cannot be copied');
			// If both failed, mysql error
			if (!$res)
			{
				$this->_errors[] = $info['name'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').' '.Tools::displayError('Cannot be saved');
				$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
					Db::getInstance()->getMsgError();
			}
		}

		/* Import has finished, we can regenerate the categories nested tree */
		Category::regenerateEntireNtree();

		$this->closeCsvFile($handle);
	}

	public function productImport()
	{
		$this->receiveTab();
		$handle = $this->openCsvFile();
		$default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);
			if (array_key_exists('id', $info) && (int)$info['id'] && Product::existsInDatabase((int)$info['id'], 'product'))
			{
				$product = new Product((int)$info['id']);
				$category_data = Product::getProductCategories((int)$product->id);
				foreach ($category_data as $tmp)
					$product->category[] = $tmp;
			}
			else
				$product = new Product();
			self::setEntityDefaultValues($product);
			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $product);

			if ((int)$product->id_tax_rules_group != 0)
			{
				if (Validate::isLoadedObject(new TaxRulesGroup($product->id_tax_rules_group)))
				{
					$address = $this->context->shop->getAddress();
					$tax_manager = TaxManagerFactory::getManager($address, $product->id_tax_rules_group);
                    $product_tax_calculator = $tax_manager->getTaxCalculator();
					$product->tax_rate = $product_tax_calculator->getTotalRate();
				}
				else
					$this->addProductWarning(
						'id_tax_rules_group',
						$product->id_tax_rules_group,
						Tools::displayError('Invalid tax rule group ID, you first need a group with this ID.')
					);
			}
			if (isset($product->manufacturer) && is_numeric($product->manufacturer) && Manufacturer::manufacturerExists((int)$product->manufacturer))
				$product->id_manufacturer = (int)$product->manufacturer;
			else if (isset($product->manufacturer) && is_string($product->manufacturer) && !empty($product->manufacturer))
			{
				if ($manufacturer = Manufacturer::getIdByName($product->manufacturer))
					$product->id_manufacturer = (int)$manufacturer;
				else
				{
					$manufacturer = new Manufacturer();
					$manufacturer->name = $product->manufacturer;
					if (($field_error = $manufacturer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $manufacturer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $manufacturer->add())
						$product->id_manufacturer = (int)$manufacturer->id;
					else
					{
						$this->_errors[] = $manufacturer->name.(isset($manufacturer->id) ? ' ('.$manufacturer->id.')' : '').' '.Tools::displayError('Cannot be saved');
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
							Db::getInstance()->getMsgError();
					}
				}
			}

			if (isset($product->supplier) && is_numeric($product->supplier) && Supplier::supplierExists((int)$product->supplier))
				$product->id_supplier = (int)$product->supplier;
			else if (isset($product->supplier) && is_string($product->supplier) && !empty($product->supplier))
			{
				if ($supplier = Supplier::getIdByName($product->supplier))
					$product->id_supplier = (int)$supplier;
				else
				{
					$supplier = new Supplier();
					$supplier->name = $product->supplier;
					if (($field_error = $supplier->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $supplier->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $supplier->add())
						$product->id_supplier = (int)$supplier->id;
					else
					{
						$this->_errors[] = $supplier->name.(isset($supplier->id) ? ' ('.$supplier->id.')' : '').' '.Tools::displayError('Cannot be saved');
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
							Db::getInstance()->getMsgError();
					}
				}
			}

			if (isset($product->price_tex) && !isset($product->price_tin))
				$product->price = $product->price_tex;
			else if (isset($product->price_tin) && !isset($product->price_tex))
			{
				$product->price = $product->price_tin;
				// If a tax is already included in price, withdraw it from price
				if ($product->tax_rate)
					$product->price = (float)number_format($product->price / (1 + $product->tax_rate / 100), 6, '.', '');
			}
			else if (isset($product->price_tin) && isset($product->price_tex))
				$product->price = $product->price_tex;

			if (isset($product->category) && is_array($product->category) && count($product->category))
			{
				$product->id_category = array(); // Reset default values array

				foreach ($product->category as $value)
				{
					if (is_numeric($value))
					{
						if (Category::categoryExists((int)$value))
							$product->id_category[] = (int)$value;
						else
						{
							$category_to_create = new Category();
							$category_to_create->id = (int)$value;
							$category_to_create->name = self::createMultiLangField($value);
							$category_to_create->active = 1;
							$category_to_create->id_parent = 1; // Default parent is home for unknown category to create
							if (($field_error = $category_to_create->validateFields(UNFRIENDLY_ERROR, true)) === true &&
								($lang_field_error = $category_to_create->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $category_to_create->add())
								$product->id_category[] = (int)$category_to_create->id;
							else
							{
								$this->_errors[] = $category_to_create->name[$default_language_id].(isset($category_to_create->id) ? ' ('.$category_to_create->id.')' : '').
									' '.Tools::displayError('Cannot be saved');
								$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
									Db::getInstance()->getMsgError();
							}
						}
					}
					else if (is_string($value) && !empty($value))
					{
						$category = Category::searchByName($default_language_id, $value, true);
						if ($category['id_category'])
							$product->id_category[] = (int)$category['id_category'];
						else
						{
							$category_to_create = new Category();
							$category_to_create->name = self::createMultiLangField($value);
							$category_to_create->active = 1;
							$category_to_create->id_parent = 1; // Default parent is home for unknown category to create
							if (($field_error = $category_to_create->validateFields(UNFRIENDLY_ERROR, true)) === true &&
								($lang_field_error = $category_to_create->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $category_to_create->add())
								$product->id_category[] = (int)$category_to_create->id;
							else
							{
								$this->_errors[] = $category_to_create->name[$default_language_id].(isset($category_to_create->id) ? ' ('.$category_to_create->id.')' : '').
									' '.Tools::displayError('Cannot be saved');
								$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
									Db::getInstance()->getMsgError();
							}
						}
					}
				}
			}

			$product->id_category_default = isset($product->id_category[0]) ? (int)$product->id_category[0] : '';
			$link_rewrite = (is_array($product->link_rewrite) && count($product->link_rewrite)) ? $product->link_rewrite[$default_language_id] : '';

			$valid_link = Validate::isLinkRewrite($link_rewrite);

			if ((isset($product->link_rewrite[$default_language_id]) && empty($product->link_rewrite[$default_language_id])) || !$valid_link)
			{
				$link_rewrite = Tools::link_rewrite($product->name[$default_language_id]);
				if ($link_rewrite == '')
					$link_rewrite = 'friendly-url-autogeneration-failed';
			}
			if (!$valid_link)
				$this->warnings[] = Tools::displayError('Rewrite link for').' '.$link_rewrite.(isset($info['id']) ? ' (ID '.$info['id'].') ' : '').
					' '.Tools::displayError('was re-written as').' '.$link_rewrite;

			$product->link_rewrite = self::createMultiLangField($link_rewrite);

			$res = false;
			$field_error = $product->validateFields(UNFRIENDLY_ERROR, true);
			$lang_field_error = $product->validateFieldsLang(UNFRIENDLY_ERROR, true);
			if ($field_error === true && $lang_field_error === true)
			{
				// check quantity
				if ($product->quantity == null)
					$product->quantity = 0;

				// If match ref is specified && ref product && ref product already in base, trying to update
				if (Tools::getValue('match_ref') == 1 && $product->reference && Product::existsRefInDatabase($product->reference))
				{
					$datas = Db::getInstance()->getRow('
						SELECT `date_add`, `id_product`
						FROM `'._DB_PREFIX_.'product`
						WHERE `reference` = "'.$product->reference.'"
					');
					$product->id = pSQL($datas['id_product']);
					$product->date_add = pSQL($datas['date_add']);
					$res = $product->update();
				} // Else If id product && id product already in base, trying to update
				else if ($product->id && Product::existsInDatabase((int)$product->id, 'product'))
				{
					$datas = Db::getInstance()->getRow('SELECT `date_add` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$product->id);
					$product->date_add = pSQL($datas['date_add']);
					$res = $product->update();
				}
				// If no id_product or update failed
				if (!$res)
				{
					if (isset($product->date_add) && $product->date_add != '')
						$res = $product->add(false);
					else
					$res = $product->add();
				}
			}
			// If both failed, mysql error
			if (!$res)
			{
				$this->_errors[] = $info['name'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').' '.Tools::displayError('Cannot be saved');
				$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
					Db::getInstance()->getMsgError();

			}
			else
			{
				// Associate product to shop
				if (Shop::isFeatureActive() && $product->shop)
				{
					$product->shop = explode(',', $product->shop);
					$shops = array();
					foreach ($product->shop as $shop)
					{
						$shop = trim($shop);
						if (!is_numeric($shop))
							$shop = Shop::getIdByName($shop);
						$shops[] = $shop;
					}
					$product->associateTo($shops);
				}

				// SpecificPrice (only the basic reduction feature is supported by the import)
				if ((isset($info['reduction_price']) && $info['reduction_price'] > 0) || (isset($info['reduction_percent']) && $info['reduction_percent'] > 0))
				{
					$specific_price = new SpecificPrice();
					$specific_price->id_product = (int)$product->id;
					// @todo multishop specific price import
					$specific_price->id_shop = $this->context->shop->getID(true);
					$specific_price->id_currency = 0;
					$specific_price->id_country = 0;
					$specific_price->id_group = 0;
					$specific_price->price = 0.00;
					$specific_price->from_quantity = 1;
					$specific_price->reduction = (isset($info['reduction_price']) && $info['reduction_price']) ? $info['reduction_price'] : $info['reduction_percent'] / 100;
					$specific_price->reduction_type = (isset($info['reduction_price']) && $info['reduction_price']) ? 'amount' : 'percentage';
					$specific_price->from = (isset($info['reduction_from']) && Validate::isDate($info['reduction_from'])) ? $info['reduction_from'] : '0000-00-00 00:00:00';
					$specific_price->to = (isset($info['reduction_to']) && Validate::isDate($info['reduction_to']))  ? $info['reduction_to'] : '0000-00-00 00:00:00';
					if (!$specific_price->add())
						$this->addProductWarning($info['name'], $product->id, $this->l('Discount is invalid'));
				}

				if (isset($product->tags) && !empty($product->tags))
				{
					// Delete tags for this id product, for no duplicating error
					Tag::deleteTagsForProduct($product->id);

					$tag = new Tag();
					if (!is_array($product->tags))
					{
						$product->tags = self::createMultiLangField($product->tags);
						foreach ($product->tags as $key => $tags)
						{
							$is_tag_added = $tag->addTags($key, $product->id, $tags);
							if (!$is_tag_added)
							{
								$this->addProductWarning($info['name'], $product->id, $this->l('Tags list').' '.$this->l('is invalid'));
								break;
							}
						}

					}
					else
					{
						foreach ($product->tags as $key => $tags)
						{
							$str = '';
							foreach ($tags as $one_tag)
								$str .= $one_tag.',';
							$str = rtrim($str, ',');

							$is_tag_added = $tag->addTags($key, $product->id, $str);
							if (!$is_tag_added)
							{
								$this->addProductWarning($info['name'], $product->id, 'Invalid tag(s) ('.$str.')');
								break;
							}
						}
					}
				}
				//delete existing images if "delete_existing_images" is set to 1
				if (isset($product->delete_existing_images))
					if ((bool)$product->delete_existing_images)
						$product->deleteImages();
				else if (isset($product->image) && is_array($product->image) && count($product->image))
					$product->deleteImages();

				if (isset($product->image) && is_array($product->image) && count($product->image))
				{
					$product_has_images = (bool)Image::getImages($this->context->language->id, (int)$product->id);
					foreach ($product->image as $key => $url)
						if (!empty($url))
						{
							$image = new Image();
							$image->id_product = (int)$product->id;
							$image->position = Image::getHighestPosition($product->id) + 1;
							$image->cover = (!$key && !$product_has_images) ? true : false;
							if (($field_error = $image->validateFields(UNFRIENDLY_ERROR, true)) === true &&
								($lang_field_error = $image->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $image->add())
							{
								if (!self::copyImg($product->id, $image->id, $url))
									$this->warnings[] = Tools::displayError('Error copying image: ').$url;
							}
							else
							{
								$this->warnings[] = (isset($image->id_product) ? ' ('.$image->id_product.')' : '').
									' '.Tools::displayError('Cannot be saved');
								$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
									Db::getInstance()->getMsgError();
							}
						}
				}
				if (isset($product->id_category))
					$product->updateCategories(array_map('intval', $product->id_category));

				// Features import
				$features = get_object_vars($product);
				if (isset($features['features']))
					foreach (explode(',', $features['features']) as $single_feature)
					{
						$tab_feature = explode(':', $single_feature);
						$feature_name = $tab_feature[0];
						$feature_value = $tab_feature[1];
						$position = isset($tab_feature[2]) ? $tab_feature[1]: false;
						$id_feature = Feature::addFeatureImport($feature_name, $position);
						$id_feature_value = FeatureValue::addFeatureValueImport($id_feature, $feature_value);
						Product::addFeatureProductImport($product->id, $id_feature, $id_feature_value);
					}
				// clean feature positions to avoid conflict
				Feature::cleanPositions();
			}
		}
		$this->closeCsvFile($handle);
	}

	public function attributeImport()
	{
		$default_language = Configuration::get('PS_LANG_DEFAULT');
		$groups = array();
		foreach (AttributeGroup::getAttributesGroups($default_language) as $group)
			$groups[$group['name']] = (int)$group['id_attribute_group'];
		$attributes = array();
		foreach (Attribute::getAttributes($default_language) as $attribute)
			$attributes[$attribute['attribute_group'].'_'.$attribute['name']] = (int)$attribute['id_attribute'];

		$this->receiveTab();
		$handle = $this->openCsvFile();
		$fsep = ((is_null(Tools::getValue('multiple_value_separator')) || trim(Tools::getValue('multiple_value_separator')) == '' ) ? ',' : Tools::getValue('multiple_value_separator'));
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);
			$info = array_map('trim', $info);

			self::setDefaultValues($info);

			$product = new Product((int)$info['id_product'], false, $default_language);
			$id_image = null;
			//delete existing images if "delete_existing_images" is set to 1
			if (array_key_exists('delete_existing_images', $info) && $info['delete_existing_images'])
				$product->deleteImages();
			else if (array_key_exists('image_url', $info))
				$product->deleteImages();

			if (isset($info['image_url']) && $info['image_url'])
			{
				$product_has_images = (bool)Image::getImages($this->context->language->id, $product->id);
				$url = $info['image_url'];
				$image = new Image();
				$image->id_product = (int)$product->id;
				$image->position = Image::getHighestPosition($product->id) + 1;
				$image->cover = (!$product_has_images) ? true : false;

				if (($field_error = $image->validateFields(UNFRIENDLY_ERROR, true)) === true &&
					($lang_field_error = $image->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $image->add())
				{
					if (!self::copyImg($product->id, $image->id, $url))
						$this->warnings[] = Tools::displayError('Error copying image: ').$url;
					else
						$id_image = array($image->id);
				}
				else
				{
					$this->warnings[] = (isset($image->id_product) ? ' ('.$image->id_product.')' : '').
						' '.Tools::displayError('Cannot be saved');
					$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').mysql_error();
				}
			}
			else if (isset($info['image_position']) && $info['image_position'])
			{
				$images = $product->getImages($default_language);

				if ($images)
					foreach ($images as $row)
						if ($row['position'] == (int)$info['image_position'])
						{
							$id_image = array($row['id_image']);
							break;
						}
				if (!$id_image)
					$this->warnings[] = sprintf(
						Tools::displayError('No image found for combination with id_product = %s and image position = %s.'),
						$product->id,
						(int)$info['image_position']
					);
			}

			$id_product_attribute = $product->addAttribute(
				(float)$info['price'],
				(float)$info['weight'],
				0,
				(float)$info['ecotax'],
				(int)$info['quantity'],
				$id_image,
				strval($info['reference']),
				strval($info['supplier_reference']),
				strval($info['ean13']),
				(int)$info['default_on'],
				strval($info['upc'])
			);

			$id_attribute_group = 0;
			$group = '';
			foreach (explode($fsep, $info['group']) as $group)
			{
				$tab_group = explode(':', $group);
				$group = $tab_group[0];
				$id_attribute_group = $groups[$group];
				// if position is filled
				if (isset($tab_group[1]))
					$position = $tab_group[1];
				else
					$position = false;
				if (!isset($groups[$group]))
				{
					$obj = new AttributeGroup();
					$obj->is_color_group = false;
					$obj->name[$default_language] = $group;
					$obj->public_name[$default_language] = $group;
					$obj->position = (!$position) ? AttributeGroup::getHigherPosition() + 1 : $position;
					if (($field_error = $obj->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $obj->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
					{
						$obj->add();
						$groups[$group] = $obj->id;
					}
					else
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '');
				}
			}
			foreach (explode($fsep, $info['attribute']) as $attribute)
			{
				$tab_attribute = explode(':', $attribute);
				$attribute = $tab_attribute[0];
				// if position is filled
				if (isset($tab_attribute[1]))
					$position = $tab_attribute[1];
				else
					$position = false;
				if (!isset($attributes[$group.'_'.$attribute]))
				{
					$obj = new Attribute();
					$obj->id_attribute_group = $groups[$group];
					$obj->name[$default_language] = str_replace('\n', '', str_replace('\r', '', $attribute));
					$obj->position = (!$position) ? Attribute::getHigherPosition($groups[$group]) + 1 : $position;
					if (($field_error = $obj->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $obj->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
					{
						$obj->add();
						$attributes[$group.'_'.$attribute] = $obj->id;
					}
					else
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '');

				}
			}
			Db::getInstance()->execute('
				INSERT INTO '._DB_PREFIX_.'product_attribute_combination (id_attribute, id_product_attribute)
				VALUES ('.(int)$attributes[$group.'_'.$attribute].','.(int)$id_product_attribute.')
			');
		}
		$this->closeCsvFile($handle);

		// after insertion, we clean attribute position and group attribute position
		$obj = new Attribute();
		$obj->cleanPositions((int)$id_attribute_group, false);
		AttributeGroup::cleanPositions();
	}

	public function customerImport()
	{
		$this->receiveTab();
		$handle = $this->openCsvFile();
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			self::setDefaultValues($info);
			$customer = new Customer();
			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $customer);

			if ($customer->passwd)
				$customer->passwd = md5(_COOKIE_KEY_.$customer->passwd);

			// Associate product to shop
			if (Shop::isFeatureActive() && $customer->id_shop)
			{
				if (!is_numeric($customer->id_shop))
					$customer->id_shop = Shop::getIdByName($customer->id_shop);
			}
			else
				$customer->id_shop = Configuration::get('PS_SHOP_DEFAULT');

			$res = false;
			if (($field_error = $customer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
				($lang_field_error = $customer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
			{
				$customer->id_group_shop = Shop::getGroupFromShop($customer->id_shop);

				if ($customer->id && $customer->customerIdExists($customer->id))
					$res = $customer->update();
				if (!$res)
					$res = $customer->add();
				if ($res)
					$customer->addGroups(array(1));
			}
			if (!$res)
			{
				$this->_errors[] = $info['email'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').' '.Tools::displayError('Cannot be saved');
				$this->_errors[] = ($field_error !== true ? $field_error : ($lang_field_error !== true ? $lang_field_error : '')).
					Db::getInstance()->getMsgError();
			}
		}
		$this->closeCsvFile($handle);
	}

	public function addressImport()
	{
		$this->receiveTab();
		$default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
		$handle = $this->openCsvFile();
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			self::setDefaultValues($info);
			$address = new Address();
			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $address);

			if (isset($address->country) && is_numeric($address->country))
			{
				if (Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), (int)$address->country))
					$address->id_country = (int)$address->country;
			}
			else if (isset($address->country) && is_string($address->country) && !empty($address->country))
			{
				if ($id_country = Country::getIdByName(null, $address->country))
					$address->id_country = (int)$id_country;
				else
				{
					$country = new Country();
					$country->active = 1;
					$country->name = self::createMultiLangField($address->country);
					$country->id_zone = 0; // Default zone for country to create
					$country->iso_code = strtoupper(substr($address->country, 0, 2)); // Default iso for country to create
					$country->contains_states = 0; // Default value for country to create
					$lang_field_error = $country->validateFieldsLang(UNFRIENDLY_ERROR, true);
					if (($field_error = $country->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $country->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $country->add())
						$address->id_country = (int)$country->id;
					else
					{
						$this->_errors[] = $country->name[$default_language_id].' '.Tools::displayError('Cannot be saved');
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
							Db::getInstance()->getMsgError();
					}
				}
			}

			if (isset($address->state) && is_numeric($address->state))
			{
				if (State::getNameById((int)$address->state))
					$address->id_state = (int)$address->state;
			}
			else if (isset($address->state) && is_string($address->state) && !empty($address->state))
			{
				if ($id_state = State::getIdByName($address->state))
					$address->id_state = (int)$id_state;
				else
				{
					$state = new State();
					$state->active = 1;
					$state->name = $address->state;
					$state->id_country = isset($country->id) ? (int)$country->id : 0;
					$state->id_zone = 0; // Default zone for state to create
					$state->iso_code = strtoupper(substr($address->state, 0, 2)); // Default iso for state to create
					$state->tax_behavior = 0;
					if (($field_error = $state->validateFields(UNFRIENDLY_ERROR, true)) === true &&
						($lang_field_error = $state->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $state->add())
						$address->id_state = (int)$state->id;
					else
					{
						$this->_errors[] = $state->name.' '.Tools::displayError('Cannot be saved');
						$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
							Db::getInstance()->getMsgError();
					}
				}
			}

			if (isset($address->customer_email) && !empty($address->customer_email))
			{
				if (Validate::isEmail($address->customer_email))
				{
					$customer = Customer::customerExists($address->customer_email, true);
					if ($customer)
						$address->id_customer = (int)$customer;
					else
						$this->_errors[] = Db::getInstance()->getMsgError().' '.$address->customer_email.' '.Tools::displayError('does not exist in database').' '.
							(isset($info['id']) ? ' (ID '.$info['id'].')' : '').' '.Tools::displayError('Cannot be saved');
				}
				else
					$this->_errors[] = '"'.$address->customer_email.'" :'.Tools::displayError('Is not a valid Email');
			}

			if (isset($address->manufacturer) && is_numeric($address->manufacturer) && Manufacturer::manufacturerExists((int)$address->manufacturer))
				$address->id_manufacturer = (int)$address->manufacturer;
			else if (isset($address->manufacturer) && is_string($address->manufacturer) && !empty($address->manufacturer))
			{
				$manufacturer = new Manufacturer();
				$manufacturer->name = $address->manufacturer;
				if (($field_error = $manufacturer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
					($lang_field_error = $manufacturer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $manufacturer->add())
					$address->id_manufacturer = (int)$manufacturer->id;
				else
				{
					$this->_errors[] = Db::getInstance()->getMsgError().' '.$manufacturer->name.(isset($manufacturer->id) ? ' ('.$manufacturer->id.')' : '').
						' '.Tools::displayError('Cannot be saved');
					$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
						Db::getInstance()->getMsgError();
				}
			}

			if (isset($address->supplier) && is_numeric($address->supplier) && Supplier::supplierExists((int)$address->supplier))
				$address->id_supplier = (int)$address->supplier;
			else if (isset($address->supplier) && is_string($address->supplier) && !empty($address->supplier))
			{
				$supplier = new Supplier();
				$supplier->name = $address->supplier;
				if (($field_error = $supplier->validateFields(UNFRIENDLY_ERROR, true)) === true &&
					($lang_field_error = $supplier->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $supplier->add())
					$address->id_supplier = (int)$supplier->id;
				else
				{
					$this->_errors[] = Db::getInstance()->getMsgError().' '.$supplier->name.(isset($supplier->id) ? ' ('.$supplier->id.')' : '').
						' '.Tools::displayError('Cannot be saved');
					$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
						Db::getInstance()->getMsgError();
				}
			}

			$res = false;
			if (($field_error = $address->validateFields(UNFRIENDLY_ERROR, true)) === true &&
				($lang_field_error = $address->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
			{
				if ($address->id && $address->addressExists($address->id))
					$res = $address->update();
				if (!$res)
					$res = $address->add();
			}
			if (!$res)
			{
				$this->_errors[] = $info['alias'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').' '.Tools::displayError('Cannot be saved');
				$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
					Db::getInstance()->getMsgError();
			}
		}
		$this->closeCsvFile($handle);
	}

	public function manufacturerImport()
	{
		$this->receiveTab();
		$handle = $this->openCsvFile();
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			self::setDefaultValues($info);

			if (array_key_exists('id', $info) && (int)$info['id'] && Manufacturer::existsInDatabase((int)$info['id'], 'manufacturer'))
				$manufacturer = new Manufacturer((int)$info['id']);
			else
			$manufacturer = new Manufacturer();
			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $manufacturer);

			$res = false;
			if (($field_error = $manufacturer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
				($lang_field_error = $manufacturer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
			{
				if ($manufacturer->id && $manufacturer->manufacturerExists($manufacturer->id))
					$res = $manufacturer->update();
				if (!$res)
					$res = $manufacturer->add();

				if ($res)
				{
					// Associate supplier to group shop
					if (Shop::isFeatureActive() && $manufacturer->shop)
					{
						$manufacturer->shop = explode(',', $manufacturer->shop);
						$shops = array();
						foreach ($manufacturer->shop as $shop)
						{
							$shop = trim($shop);
							if (!is_numeric($shop))
								$shop = GroupShop::getIdByName($shop);
							$shops[] = $shop;
						}
						$manufacturer->associateTo($shops, 'group_shop');
					}
				}
			}

			if (!$res)
			{
				$this->_errors[] = Db::getInstance()->getMsgError().' '.$info['name'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').
					' '.Tools::displayError('Cannot be saved');
				$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '').
					Db::getInstance()->getMsgError();
			}
		}
		$this->closeCsvFile($handle);
	}

	public function supplierImport()
	{
		$this->receiveTab();
		$handle = $this->openCsvFile();
		self::setLocale();
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			self::setDefaultValues($info);

			if (array_key_exists('id', $info) && (int)$info['id'] && Supplier::existsInDatabase((int)$info['id'], 'supplier'))
				$supplier = new Supplier((int)$info['id']);
			else
			$supplier = new Supplier();

			self::arrayWalk($info, array('AdminImportController', 'fillInfo'), $supplier);
			if (($field_error = $supplier->validateFields(UNFRIENDLY_ERROR, true)) === true &&
				($lang_field_error = $supplier->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true)
			{
				$res = false;
				if ($supplier->id && $supplier->supplierExists($supplier->id))
					$res = $supplier->update();
				if (!$res)
					$res = $supplier->add();

				if (!$res)
					$this->_errors[] = Db::getInstance()->getMsgError().' '.$info['name'].(isset($info['id']) ? ' (ID '.$info['id'].')' : '').
						' '.Tools::displayError('Cannot be saved');
				else
				{
					// Associate supplier to group shop
					if (Shop::isFeatureActive() && $supplier->shop)
					{
						$supplier->shop = explode(',', $supplier->shop);
						$shops = array();
						foreach ($supplier->shop as $shop)
						{
							$shop = trim($shop);
							if (!is_numeric($shop))
								$shop = GroupShop::getIdByName($shop);
							$shops[] = $shop;
						}
						$supplier->associateTo($shops, 'group_shop');
					}
				}
			}
			else
			{
				$this->_errors[] = $this->l('Supplier not valid').' ('.$supplier->name.')';
				$this->_errors[] = ($field_error !== true ? $field_error : '').($lang_field_error !== true ? $lang_field_error : '');
			}
		}
		$this->closeCsvFile($handle);
	}

	/**
	 * @since 1.5.0
	 */
	public function supplyOrdersImport()
	{
		// opens CSV & sets locale
		$this->receiveTab();
		$handle = $this->openCsvFile();
		self::setLocale();

		// main loop, for each lines
		for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator')); $current_line++)
		{
			// if convert requested
			if (Tools::getValue('convert'))
				$line = $this->utf8EncodeArray($line);
			$info = self::getMaskedRow($line);

			// sets default values if needed
			self::setDefaultValues($info);

			// if an id is set, instanciates a supply order with this id if possible
			if (array_key_exists('id', $info) && (int)$info['id'] && SupplyOrder::exists((int)$info['id']))
				$supply_order = new Supplier((int)$info['id']);
			// if an reference is set, instanciates a supply order with this reference if possible
			else if (array_key_exists('reference', $info) && $info['reference'] && SupplyOrder::exists(pSQL($info['reference'])))
				$supply_order = SupplyOrder::getSupplyOrderByReference(pSQL($info['reference']));
			else // new supply order
				$supply_order = new SupplyOrder();

			/*
			 * @TODO Checks $info
			 *
			 */

			/**
			 * @TODO If check is OK, sets the Supply Order
			 */
		}

		// closes
		$this->closeCsvFile($handle);
	}

	public function utf8EncodeArray($array)
	{
		if (is_array($array))
			foreach ($array as $key => $value)
				$array[$key] = utf8_encode($value);
		else
			$array = utf8_encode($array);

		return $array;
	}

	private function getNbrColumn($handle, $glue)
	{
		$tmp = fgetcsv($handle, MAX_LINE_SIZE, $glue);
		self::rewindBomAware($handle);
		return count($tmp);
	}

	private static function usortFiles($a, $b)
	{
		$a = strrev(substr(strrev($a), 0, 14));
		$b = strrev(substr(strrev($b), 0, 14));

		if ($a == $b)
			return 0;

		return ($a < $b) ? 1 : -1;
	}

	private function openCsvFile()
	{
		 $handle = fopen(_PS_ADMIN_DIR_.'/import/'.strval(preg_replace('/\.{2,}/', '.', Tools::getValue('csv'))), 'r');

		if (!$handle)
			$this->_errors[] = Tools::displayError('Cannot read the CSV file');

		self::rewindBomAware($handle);

		for ($i = 0; $i < (int)Tools::getValue('skip'); ++$i)
			$line = fgetcsv($handle, MAX_LINE_SIZE, Tools::getValue('separator', ';'));
		return $handle;
	}

	private function closeCsvFile($handle)
	{
		fclose($handle);
	}

	private function truncateTables($case)
	{
		switch ((int)$case)
		{
			case $this->entities[$this->l('Categories')]:
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'category` WHERE id_category != 1');
				Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'category_lang` WHERE id_category != 1');
				Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'category` AUTO_INCREMENT = 2');
				foreach (scandir(_PS_CAT_IMG_DIR_) as $d)
					if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d))
						unlink(_PS_CAT_IMG_DIR_.$d);
				break;
			case $this->entities[$this->l('Products')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'feature_product');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_lang');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'category_product');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_tag');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'image');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'image_lang');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'specific_price');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'specific_price_priority');
				Image::deleteAllImages(_PS_PROD_IMG_DIR_);
				mkdir(_PS_PROD_IMG_DIR_);
				break;
			case $this->entities[$this->l('Customers')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'customer');
				break;
			case $this->entities[$this->l('Addresses')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'address');
				break;
			case $this->entities[$this->l('Combinations')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'attribute_impact');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_attribute`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_attribute_combination`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_attribute_image`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'attribute_group`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'attribute_group_lang`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'attribute`');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'attribute_lang`');
				break;
			case $this->entities[$this->l('Manufacturers')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer_lang');
				foreach (scandir(_PS_MANU_IMG_DIR_) as $d)
					if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d))
						unlink(_PS_MANU_IMG_DIR_.$d);
				break;
			case $this->entities[$this->l('Suppliers')]:
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier');
				Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier_lang');
				foreach (scandir(_PS_SUPP_IMG_DIR_) as $d)
					if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d))
						unlink(_PS_SUPP_IMG_DIR_.$d);
				break;
		}
		Image::clearTmpDir();
		return true;
	}

	public function postProcess()
	{
		/* PrestaShop demo mode */
		if (_PS_MODE_DEMO_)
		{
			$this->_errors[] = Tools::displayError('This functionnality has been disabled.');
			return;
		}
		/* PrestaShop demo mode*/

		if (Tools::isSubmit('submitFileUpload'))
		{
			if (isset($_FILES['file']) && !empty($_FILES['file']['error']))
			{
				switch ($_FILES['file']['error'])
				{
					case UPLOAD_ERR_INI_SIZE:
						$this->_errors[] = Tools::displayError('The uploaded file exceeds the upload_max_filesize directive in php.ini.
							If your server configuration allows it, you may add a directive in your .htaccess, for example:')
						.'<br/><a href="?tab=AdminGenerator&amp;token='
						.Tools::getAdminTokenLite('AdminGenerator').'" >
						<code>php_value upload_max_filesize 20M</code> '.
						Tools::displayError('(clic to open Generator tab)').'</a>';
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$this->_errors[] = Tools::displayError('The uploaded file exceeds the post_max_size directive in php.ini.
							If your server configuration allows it, you may add a directive in your .htaccess, for example:')
						.'<br/><a href="?tab=AdminGenerator&amp;token='
						.Tools::getAdminTokenLite('AdminGenerator').'" >
						<code>php_value post_max_size 20M</code> '.
						Tools::displayError('(clic to open Generator tab)').'</a>';
						break;
					break;
					case UPLOAD_ERR_PARTIAL:
						$this->_errors[] = Tools::displayError('The uploaded file was only partially uploaded.');
						break;
					break;
					case UPLOAD_ERR_NO_FILE:
						$this->_errors[] = Tools::displayError('No file was uploaded');
						break;
					break;
				}
			}
			else if (!file_exists($_FILES['file']['tmp_name']) ||
				!@move_uploaded_file($_FILES['file']['tmp_name'], _PS_ADMIN_DIR_.'/import/'.$_FILES['file']['name'].'.'.date('Ymdhis')))
				$this->_errors[] = $this->l('an error occurred while uploading and copying file');
			else
				Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getValue('token').'&conf=18');
		}
		else if (Tools::getValue('import'))
		{
			if (Tools::getValue('truncate'))
				$this->truncateTables((int)Tools::getValue('entity'));

			switch ((int)Tools::getValue('entity'))
			{
				case $this->entities[$this->l('Categories')]:
					$this->categoryImport();
				break;
				case $this->entities[$this->l('Products')]:
					$this->productImport();
				break;
				case $this->entities[$this->l('Customers')]:
					$this->customerImport();
				break;
				case $this->entities[$this->l('Addresses')]:
					$this->addressImport();
				break;
				case $this->entities[$this->l('Combinations')]:
					$this->attributeImport();
				break;
				case $this->entities[$this->l('Manufacturers')]:
					$this->manufacturerImport();
				break;
				case $this->entities[$this->l('Suppliers')]:
					$this->supplierImport();
				break;
				case $this->entities[$this->l('SupplyOrders')]:
					$this->supplyOrdersImport();
				break;
				default:
					$this->_errors[] = $this->l('no entity selected');
			}
		}

		parent::postProcess();
	}

	public static function setLocale()
	{
		$iso_lang  = trim(Tools::getValue('iso_lang'));
		setlocale(LC_COLLATE, strtolower($iso_lang).'_'.strtoupper($iso_lang).'.UTF-8');
		setlocale(LC_CTYPE, strtolower($iso_lang).'_'.strtoupper($iso_lang).'.UTF-8');
	}

	protected function addProductWarning($product_name, $product_id = null, $message = '')
	{
		$this->warnings[] = $product_name.(isset($product_id) ? ' (ID '.$product_id.')' : '').' '.Tools::displayError($message);
	}
}
