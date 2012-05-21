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
*  @version  Release: $Revision: 7331 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ProductControllerCore extends FrontController
{
	protected $product;

	public function setMedia()
	{
		parent::setMedia();

		$this->addCSS(_THEME_CSS_DIR_.'product.css');
		$this->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
		$this->addJqueryPlugin(array('fancybox', 'idTabs', 'scrollTo', 'serialScroll'));
		$this->addJS(array(
						_THEME_JS_DIR_.'tools.js',
						_THEME_JS_DIR_.'product.js')
						);

		if (Configuration::get('PS_DISPLAY_JQZOOM') == 1)
			$this->addJqueryPlugin('jqzoom');
	}

	public function canonicalRedirection($canonical_url = '')
	{
		if (Validate::isLoadedObject($this->product))
			parent::canonicalRedirection($this->context->link->getProductLink($this->product));
	}

	/**
	 * Initialize product controller
	 * @see FrontController::init()
	 */
	public function init()
	{
		parent::init();

		if ($id_product = (int)Tools::getValue('id_product'))
			$this->product = new Product($id_product, true, $this->context->language->id);

		if (!Validate::isLoadedObject($this->product))
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
		else
			$this->canonicalRedirection();

		if (Pack::isPack((int)$this->product->id) && !Pack::isInStock((int)$this->product->id))
			$this->product->quantity = 0;

		$this->product->description = $this->transformDescriptionWithImg($this->product->description);

		if (!Validate::isLoadedObject($this->product))
			$this->errors[] = Tools::displayError('Product not found');
		else
		{
			/*
			 * If the product is associated to the shop
			 * and is active or not active but preview mode (need token + file_exists)
			 * allow showing the product
			 * In all the others cases => 404 "Product is no longer available"
			 */
			if (!$this->product->isAssociatedToShop()
			|| ((!$this->product->active && ((Tools::getValue('adtoken') != Tools::encrypt('PreviewProduct'.$this->product->id))
			|| !file_exists(_PS_ROOT_DIR_.'/'.Tools::getValue('ad').'/ajax.php')))))
			{
				header('HTTP/1.1 404 page not found');
				$this->errors[] = Tools::displayError('Product is no longer available.');
			}
			else if (!$this->product->checkAccess(isset($this->context->customer) ? $this->context->customer->id : 0))
				$this->errors[] = Tools::displayError('You do not have access to this product.');
		}
	}

	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		if (!$this->errors)
		{
			// Assign to the tempate the id of the virtuale product. "0" if the product is not downloadable.
			$this->context->smarty->assign('virtual', ProductDownload::getIdFromIdProduct((int)$this->product->id));

			// If the product is not active, it's the admin preview mode
			if (!$this->product->active)
				$this->context->smarty->assign('adminActionDisplay', true);

			// Product pictures management
			require_once('images.inc.php');
			$this->context->smarty->assign('customizationFormTarget', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

			if (Tools::isSubmit('submitCustomizedDatas'))
			{
				// If cart has not been saved, we need to do it so that customization fields can have an id_cart
				// We check that the cookie exists first to avoid ghost carts
				if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()]))
				{
					$this->context->cart->add();
					$this->context->cookie->id_cart = (int)$this->context->cart->id;
				}
				$this->pictureUpload($this->product, $this->context->cart);
				$this->textRecord($this->product, $this->context->cart);
				$this->formTargetFormat();
			}
			else if (Tools::getIsset('deletePicture') && !$this->context->cart->deletePictureToProduct($this->product->id, Tools::getValue('deletePicture')))
				$this->errors[] = Tools::displayError('An error occurred while deleting the selected picture');

			$files = $this->context->cart->getProductCustomization($this->product->id, Product::CUSTOMIZE_FILE, true);
			$pictures = array();
			foreach ($files as $file)
				$pictures['pictures_'.$this->product->id.'_'.$file['index']] = $file['value'];

			$texts = $this->context->cart->getProductCustomization($this->product->id, Product::CUSTOMIZE_TEXTFIELD, true);
			$text_fields = array();
			foreach ($texts as $text_field)
				$text_fields['textFields_'.$this->product->id.'_'.$text_field['index']] = str_replace('<br />', "\n", $text_field['value']);
			$this->context->smarty->assign(array(
				'pictures' => $pictures,
				'textFields' => $text_fields));

			// Assign template vars related to the category + execute hooks related to the category
			$this->assignCategory();
			// Assign template vars related to the price and tax
			$this->assignPriceAndTax();

			// Assign template vars related to the images
			$this->assignImages();
			// Assign attribute groups to the template
			$this->assignAttributesGroups();

			// Assign attributes combinations to the template
			$this->assignAttributesCombinations();

			// Pack management
			$pack_items = $this->product->cache_is_pack ? Pack::getItemTable($this->product->id, $this->context->language->id, true) : array();
			$this->context->smarty->assign('packItems', $pack_items);
			$this->context->smarty->assign('packs', Pack::getPacksTable($this->product->id, $this->context->language->id, true, 1));

			if (isset($category->id) && $category->id)
				$return_link = Tools::safeOutput($this->context->link->getCategoryLink($category));
			else
				$return_link = 'javascript: history.back();';
			$this->context->smarty->assign(array(
				'customizationFields' => ($this->product->customizable) ? $this->product->getCustomizationFields($this->context->language->id) : false,
				'accessories' => $this->product->getAccessories($this->context->language->id),
				'return_link' => $return_link,
				'product' => $this->product,
				'product_manufacturer' => new Manufacturer((int)$this->product->id_manufacturer, $this->context->language->id),
				'token' => Tools::getToken(false),
				'features' => $this->product->getFrontFeatures($this->context->language->id),
				'attachments' => (($this->product->cache_has_attachments) ? $this->product->getAttachments($this->context->language->id) : array()),
				'allow_oosp' => $this->product->isAvailableWhenOutOfStock((int)$this->product->out_of_stock),
				'last_qties' =>  (int)Configuration::get('PS_LAST_QTIES'),
				'HOOK_EXTRA_LEFT' => Hook::exec('extraLeft'),
				'HOOK_EXTRA_RIGHT' => Hook::exec('extraRight'),
				'HOOK_PRODUCT_OOS' => Hook::exec('productOutOfStock', array('product' => $this->product)),
				'HOOK_PRODUCT_ACTIONS' => Hook::exec('productActions'),
				'HOOK_PRODUCT_TAB' =>  Hook::exec('productTab'),
				'HOOK_PRODUCT_TAB_CONTENT' =>  Hook::exec('productTabContent'),
				'display_qties' => (int)Configuration::get('PS_DISPLAY_QTIES'),
				'display_ht' => !Tax::excludeTaxeOption(),
				'currencySign' => $this->context->currency->sign,
				'currencyRate' => $this->context->currency->conversion_rate,
				'currencyFormat' => $this->context->currency->format,
				'currencyBlank' => $this->context->currency->blank,
				'jqZoomEnabled' => Configuration::get('PS_DISPLAY_JQZOOM'),
				'ENT_NOQUOTES' => ENT_NOQUOTES,
				'outOfStockAllowed' => (int)Configuration::get('PS_ORDER_OUT_OF_STOCK')
			));
		}

		$this->context->smarty->assign('errors', $this->errors);

		$this->setTemplate(_PS_THEME_DIR_.'product.tpl');
	}

	/**
	 * Assign price and tax to the template
	 */
	protected function assignPriceAndTax()
	{
		$id_customer = (isset($this->context->customer) ? (int)$this->context->customer->id : 0);
		$id_group = (isset($this->context->customer) ? $this->context->customer->id_default_group : _PS_DEFAULT_CUSTOMER_GROUP_);
		$id_country = (int)$id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT');

		$group_reduction = GroupReduction::getValueForProduct($this->product->id, $id_group);
		if ($group_reduction == 0)
			$group_reduction = Group::getReduction((int)$this->context->cookie->id_customer) / 100;

		// Tax
		$tax = (float)$this->product->getTaxesRate(new Address((int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
		$this->context->smarty->assign('tax_rate', $tax);

		$product_price_with_tax = Product::getPriceStatic($this->product->id, true, null, 6);
		if (Product::$_taxCalculationMethod == PS_TAX_INC)
			$product_price_with_tax = Tools::ps_round($product_price_with_tax, 2);
		$product_price_without_eco_tax = (float)$product_price_with_tax - $this->product->ecotax;

		$ecotax_rate = (float)Tax::getProductEcotaxRate($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
		$ecotax_tax_amount = Tools::ps_round($this->product->ecotax, 2);
		if (Product::$_taxCalculationMethod == PS_TAX_INC && (int)Configuration::get('PS_TAX'))
			$ecotax_tax_amount = Tools::ps_round($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);

		$id_currency = (int)$this->context->cookie->id_currency;
		$id_product = (int)$this->product->id;
		$id_shop = $this->context->shop->getID(true);

		$quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, null, true);
		foreach($quantity_discounts as &$quantity_discount)
			if ($quantity_discount['id_product_attribute'])
			{
				$combination = new Combination((int)$quantity_discount['id_product_attribute']);
				$attributes = $combination->getAttributesName((int)$this->context->language->id);
				foreach ($attributes as $attribute)
					$quantity_discount['attributes'] = $attribute['name'].' - ';
				$quantity_discount['attributes'] = rtrim($quantity_discount['attributes'], ' - ');
			}

		$product_price = $this->product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false);
		$address = new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
		$this->context->smarty->assign(array(
			'quantity_discounts' => $this->formatQuantityDiscounts($quantity_discounts, $product_price, (float)$tax),
			'ecotax_tax_inc' => $ecotax_tax_amount,
			'ecotax_tax_exc' => Tools::ps_round($this->product->ecotax, 2),
			'ecotaxTax_rate' => $ecotax_rate,
			'productPriceWithoutEcoTax' => (float)$product_price_without_eco_tax,
			'group_reduction' => (1 - $group_reduction),
			'no_tax' => Tax::excludeTaxeOption() || !$this->product->getTaxesRate($address),
			'ecotax' => (!count($this->errors) && $this->product->ecotax > 0 ? Tools::convertPrice((float)$this->product->ecotax) : 0),
			'tax_enabled' => Configuration::get('PS_TAX')
		));
	}

	/**
	 * Assign template vars related to images
	 */
	protected function assignImages()
	{
		$images = $this->product->getImages((int)$this->context->cookie->id_lang);
		$product_images = array();
		foreach ($images as $k => $image)
		{
			if ($image['cover'])
			{
				$this->context->smarty->assign('mainImage', $images[0]);
				$cover = $image;
				$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['id_image']) : $image['id_image']);
				$cover['id_image_only'] = (int)$image['id_image'];
			}
			$product_images[(int)$image['id_image']] = $image;
		}
		if (!isset($cover))
			$cover = array('id_image' => $this->context->language->iso_code.'-default', 'legend' => 'No picture', 'title' => 'No picture');
		$size = Image::getSize('large');
		$this->context->smarty->assign(array(
			'have_image' => Product::getCover((int)Tools::getValue('id_product')),
			'cover' => $cover,
			'imgWidth' => (int)$size['width'],
			'mediumSize' => Image::getSize('medium'),
			'largeSize' => Image::getSize('large'),
			'homeSize' => Image::getSize('home'),
			'col_img_dir' => _PS_COL_IMG_DIR_));
		if (count($product_images))
			$this->context->smarty->assign('images', $product_images);
	}

	/**
	 * Assign template vars related to attribute groups and colors
	 */
	protected function assignAttributesGroups()
	{
		$colors = array();
		$groups = array();

		// @todo (RM) should only get groups and not all declination ?
		$attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
		if (is_array($attributes_groups) && $attributes_groups)
		{
			$combination_images = $this->product->getCombinationImages($this->context->language->id);

			foreach ($attributes_groups as $k => $row)
			{
				// Color management
				if ((isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg')))
				{
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
					if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
						$colors[$row['id_attribute']]['attributes_quantity'] = 0;
					$colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
				}
				if (!isset($groups[$row['id_attribute_group']]))
					$groups[$row['id_attribute_group']] = array(
						'name' => $row['public_group_name'],
						'group_type' => $row['group_type'],
						'default' => -1,
					);

				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
				if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
					$groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];

				if ($row['available_date'] != '0000-00-00 00:00:00' && $row['available_date'] != '0000-00-00')
					$available_date = Tools::displayDate($row['available_date'], $this->context->language->id);
				else
					$available_date = $row['available_date'];

				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
				$combinations[$row['id_product_attribute']]['price'] = (float)$row['price'];
				Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
				$combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
				$combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
				$combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
				$combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
				$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
				$combinations[$row['id_product_attribute']]['available_date'] = $available_date;

				if (isset($combination_images[$row['id_product_attribute']][0]['id_image']))
					$combinations[$row['id_product_attribute']]['id_image'] = $combination_images[$row['id_product_attribute']][0]['id_image'];
				else
					$combinations[$row['id_product_attribute']]['id_image'] = -1;
			}
			// wash attributes list (if some attributes are unavailables and if allowed to wash it)
			if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
			{
				foreach ($groups as &$group)
					foreach ($group['attributes_quantity'] as $key => &$quantity)
						if (!$quantity)
							unset($group['attributes'][$key]);

				foreach ($colors as $key => $color)
					if (!$color['attributes_quantity'])
						unset($colors[$key]);
			}
			foreach ($combinations as $id_product_attribute => $comb)
			{
				$attribute_list = '';
				foreach ($comb['attributes'] as $id_attribute)
					$attribute_list .= '\''.(int)$id_attribute.'\',';
				$attribute_list = rtrim($attribute_list, ',');
				$combinations[$id_product_attribute]['list'] = $attribute_list;
			}
			$this->context->smarty->assign(array(
				'groups' => $groups,
				'combinations' => $combinations,
				'colors' => (count($colors)) ? $colors : false,
				'combinationImages' => $combination_images));
		}
	}

	/**
	 * Get and assign attributes combinations informations
	 */
	protected function assignAttributesCombinations()
	{
		$attributes_combinations = Product::getAttributesInformationsByProduct($this->product->id);
		foreach ($attributes_combinations as &$ac)
			foreach ($ac as &$val)
				$val = str_replace('-', '_', Tools::link_rewrite($val));
		$this->context->smarty->assign('attributesCombinations', $attributes_combinations);
	}

	/**
	 * Assign template vars related to category
	 */
	protected function assignCategory()
	{
		$category = false;
		if (isset($_SERVER['HTTP_REFERER'])
		&& !strstr($_SERVER['HTTP_REFERER'], Tools::getHttpHost()) // Assure us the previous page was one of the shop
		&& preg_match('!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!', $_SERVER['HTTP_REFERER'], $regs))
		{
			// If the previous page was a category and is a parent category of the product use this category as parent category
			if (isset($regs[2]) && is_numeric($regs[2]))
			{
				if (Product::idIsOnCategoryId((int)$this->product->id, array('0' => array('id_category' => (int)$regs[2]))))
					$category = new Category($regs[2], (int)$this->context->cookie->id_lang);
			}
			else if (isset($regs[5]) && is_numeric($regs[5]))
			{
				if (Product::idIsOnCategoryId((int)$this->product->id, array('0' => array('id_category' => (int)$regs[5]))))
					$category = new Category($regs[5], (int)$this->context->cookie->id_lang);
			}
		}
		else
			// Set default product category
			$category = new Category($this->product->id_category_default, (int)$this->context->cookie->id_lang);

		// Assign category to the template
		if ($category !== false && Validate::isLoadedObject($category))
		{
			$this->context->smarty->assign(array(
				'path' => Tools::getPath($category->id, $this->product->name, true),
				'category' => $category,
				'subCategories' => $category->getSubCategories($this->context->language->id, true),
				'id_category_current' => (int)$category->id,
				'id_category_parent' => (int)$category->id_parent,
				'return_category_name' => Tools::safeOutput($category->name)
			));
		}
		else
			$this->context->smarty->assign('path', Tools::getPath((int)$this->product->id_category_default, $this->product->name));

		$this->context->smarty->assign('categories', Category::getHomeCategories($this->context->language->id));
		$this->context->smarty->assign(array('HOOK_PRODUCT_FOOTER' => Hook::exec('productFooter', array('product' => $this->product, 'category' => $category))));
	}

	public function transformDescriptionWithImg($desc)
	{
		$reg = '/{img-([0-9]+)-(left|right)-([a-z]+)}/';
		while (preg_match($reg, $desc, $matches))
		{
			$link_lmg = $this->context->link->getImageLink($this->product->link_rewrite, $this->product->id.'-'.$matches[1], $matches[3]);
			$class = $matches[2] == 'left' ? 'class="imageFloatLeft"' : 'class="imageFloatRight"';
			$html_img = '<img src="'.$link_lmg.'" alt="" '.$class.'/>';
			$desc = str_replace($matches[0], $html_img, $desc);
		}
		return $desc;
	}

	public function pictureUpload(Product $product, Cart $cart)
	{
		if (!$field_ids = $this->product->getCustomizationFieldIds())
			return false;
		$authorized_file_fields = array();
		foreach ($field_ids as $field_id)
			if ($field_id['type'] == Product::CUSTOMIZE_FILE)
				$authorized_file_fields[(int)$field_id['id_customization_field']] = 'file'.(int)$field_id['id_customization_field'];
		$indexes = array_flip($authorized_file_fields);
		foreach ($_FILES as $field_name => $file)
			if (in_array($field_name, $authorized_file_fields) && isset($file['tmp_name']) && !empty($file['tmp_name']))
			{
				$file_name = md5(uniqid(rand(), true));
				if ($error = checkImage($file, (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE')))
					$this->errors[] = $error;

				$product_picture_width = (int)Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
				$product_picture_height = (int)Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
				if ($error || (!$tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS') || !move_uploaded_file($file['tmp_name'], $tmp_name)))
					return false;
				/* Original file */
				else if (!imageResize($tmp_name, _PS_UPLOAD_DIR_.$file_name))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				/* A smaller one */
				else if (!imageResize($tmp_name, _PS_UPLOAD_DIR_.$file_name.'_small', $product_picture_width, $product_picture_height))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				else if (!chmod(_PS_UPLOAD_DIR_.$file_name, 0777) || !chmod(_PS_UPLOAD_DIR_.$file_name.'_small', 0777))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				else
				{
					// Store customization in database
					$cart->addPictureToProduct($this->product->id, $indexes[$field_name], Product::CUSTOMIZE_FILE, $file_name);
				}
				unlink($tmp_name);
			}
		return true;
	}

	public function textRecord(Product $product, Cart $cart)
	{
		if (!$field_ids = $this->product->getCustomizationFieldIds())
			return false;
		$authorized_text_fields = array();
		foreach ($field_ids as $field_id)
			if ($field_id['type'] == Product::CUSTOMIZE_TEXTFIELD)
				$authorized_text_fields[(int)$field_id['id_customization_field']] = 'textField'.(int)$field_id['id_customization_field'];
		$indexes = array_flip($authorized_text_fields);
		foreach ($_POST as $field_name => $value)
			if (in_array($field_name, $authorized_text_fields) && !empty($value))
			{
				if (!Validate::isMessage($value))
					$this->errors[] = Tools::displayError('Invalid message');
				else
					$cart->addTextFieldToProduct($this->product->id, $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value);
			}
			else if (in_array($field_name, $authorized_text_fields) && empty($value))
				$cart->deleteCustomizationToProduct((int)$this->product->id, $indexes[$field_name]);
	}

	public function formTargetFormat()
	{
		$customization_form_target = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
		foreach ($_GET as $field => $value)
			if (strncmp($field, 'group_', 6) == 0)
				$customization_form_target = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customization_form_target);
		if (isset($_POST['quantityBackup']))
			$this->context->smarty->assign('quantityBackup', (int)$_POST['quantityBackup']);
		$this->context->smarty->assign('customizationFormTarget', $customization_form_target);
	}

	public function formatQuantityDiscounts($specific_prices, $price, $tax_rate)
	{
		foreach ($specific_prices as $key => &$row)
		{
			$row['quantity'] = &$row['from_quantity'];
			if ($row['price'] != 0) // The price may be directly set
			{
				$cur_price = (Product::$_taxCalculationMethod == PS_TAX_EXC ? $row['price'] : $row['price'] * (1 + $tax_rate / 100));

				if ($row['reduction_type'] == 'amount')
					if (Product::$_taxCalculationMethod == PS_TAX_INC)
						$cur_price = $cur_price - $row['reduction'];
					else
						$cur_price = $cur_price - ($row['reduction'] / (1 + $tax_rate / 100));
				else
					$cur_price = $cur_price * ( 1 - ($row['reduction']));

				$row['real_value'] = $price - $cur_price;
			}
			else
			{
				if ($row['reduction_type'] == 'amount')
				{
					$reduction_amount = $row['reduction'];
					if (!$row['id_currency'])
						$reduction_amount = Tools::convertPrice($reduction_amount, $this->context->currency->id);
					$row['real_value'] = Product::$_taxCalculationMethod == PS_TAX_INC ? $row['reduction'] : $row['reduction'] / (1 + $tax_rate / 100);
				}
				else
					$row['real_value'] = $row['reduction'] * 100;
			}
			$row['nextQuantity'] = (isset($specific_prices[$key + 1]) ? (int)$specific_prices[$key + 1]['from_quantity'] : -1);
		}
		return $specific_prices;
	}
}
