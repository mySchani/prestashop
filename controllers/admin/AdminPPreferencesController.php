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
*  @version  Release: $Revision: 7465 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminPPreferencesControllerCore extends AdminController
{
	public function __construct()
	{
		$this->className = 'Configuration';
		$this->table = 'configuration';

		parent::__construct();

		$this->options = array(
			'products' => array(
				'title' =>	$this->l('Products'),
				'icon' =>	'tab-orders',
				'fields' =>	array(
					'PS_CATALOG_MODE' => array(
						'title' => $this->l('Catalog mode:'),
						'desc' => $this->l('When active, all features for shopping will be disabled'),
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool'
					),
		 			'PS_ORDER_OUT_OF_STOCK' => array(
		 				'title' => $this->l('Allow ordering out-of-stock product:'),
		 				'desc' => $this->l('Add to cart button is hidden when product is unavailable'),
		 				'validation' => 'isBool',
		 				'cast' => 'intval',
		 				'required' => false,
		 				'type' => 'bool'
					),
					'PS_STOCK_MANAGEMENT' => array(
						'title' => $this->l('Enable stock management:'),
						'desc' => '',
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool',
						'js' => array(
							'on' => 'onchange="stockManagementActivationAuthorization()"',
							'off' => 'onchange="stockManagementActivationAuthorization()"'
						)
					),
					'PS_ADVANCED_STOCK_MANAGEMENT' => array(
						'title' => $this->l('Enable advanced stock management:'),
						'desc' => $this->l('Allows you to manage a physical stock, warehouses and supply orders.'),
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_DISPLAY_QTIES' => array(
						'title' => $this->l('Display available quantities on product page:'),
						'desc' => '',
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool'
					),
					'PS_DISPLAY_JQZOOM' => array(
						'title' => $this->l('Enable JqZoom instead of Thickbox on product page:'),
						'desc' => '',
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool'
					),
					'PS_DISP_UNAVAILABLE_ATTR' => array(
						'title' => $this->l('Display unavailable product attributes on product page:'),
						'desc' => '',
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool'
					),
					'PS_ATTRIBUTE_CATEGORY_DISPLAY' => array(
						'title' => $this->l('Display "add to cart" button when product has attributes:'),
						'desc' => $this->l('Display or hide the "add to cart" button on category pages for products
							that have attributes to force customers to see the product detail'),
						'validation' => 'isBool',
						'cast' => 'intval',
						'type' => 'bool'
					),
					'PS_COMPARATOR_MAX_ITEM' => array(
						'title' => $this->l('Max items in the comparator:'),
						'desc' => $this->l('Set to 0 to disable this feature'),
						'validation' => 'isUnsignedId',
						'required' => true,
						'cast' => 'intval',
						'type' => 'text'
					),
					'PS_PURCHASE_MINIMUM' => array(
						'title' => $this->l('Minimum purchase total required in order to validate order:'),
						'desc' => $this->l('Set to 0 to disable this feature'),
						'validation' => 'isFloat',
						'cast' => 'floatval',
						'type' => 'price'
					),
					'PS_LAST_QTIES' => array(
						'title' => $this->l('Display last quantities when qty is lower than:'),
						'desc' => $this->l('Set to 0 to disable this feature'),
						'validation' => 'isUnsignedId',
						'required' => true,
						'cast' => 'intval',
						'type' => 'text'
					),
					'PS_NB_DAYS_NEW_PRODUCT' => array(
						'title' => $this->l('Number of days during which the product is considered \'new\':'),
						'validation' => 'isUnsignedInt',
						'cast' => 'intval',
						'type' => 'text'
					),
					'PS_CART_REDIRECT' => array(
						'title' => $this->l('Re-direction after adding product to cart:'),
						'desc' => $this->l('Concerns only the non-AJAX version of the cart'),
						'cast' => 'intval',
						'show' => true,
						'required' => false,
						'type' => 'radio',
						'validation' => 'isBool',
						'choices' => array(
							0 => $this->l('previous page'),
							1 => $this->l('cart summary')
						)
					),
					'PS_PRODUCTS_PER_PAGE' => array(
						'title' => $this->l('Products per page:'),
						'desc' => $this->l('Products displayed per page. Default is 10.'),
						'validation' => 'isUnsignedInt',
						'cast' => 'intval',
						'type' => 'text'
					),
					'PS_PRODUCTS_ORDER_BY' => array(
						'title' => $this->l('Default order by:'),
						'desc' => $this->l('Default order by for product list'),
						'type' => 'select',
						'list' => array(
							array('id' => '0', 'name' => $this->l('Product name')),
							array('id' => '1', 'name' => $this->l('Product price')),
							array('id' => '2', 'name' => $this->l('Product added date')),
							array('id' => '4', 'name' => $this->l('Position inside category')),
							array('id' => '5', 'name' => $this->l('Manufacturer')),
							array('id' => '3', 'name' => $this->l('Product modified date'))
						),
						'identifier' => 'id'
					),
					'PS_PRODUCTS_ORDER_WAY' => array(
						'title' => $this->l('Default order way:'),
						'desc' => $this->l('Default order way for product list'),
						'type' => 'select',
						'list' => array(
							array(
								'id' => '0',
								'name' => $this->l('Ascending')
							),
							array(
								'id' => '1',
								'name' => $this->l('Descending')
							)
						),
						'identifier' => 'id'
					),
					'PS_PRODUCT_SHORT_DESC_LIMIT' => array(
						'title' => $this->l('Short description max size'),
						'desc' => $this->l('Set the maximum size of product short description'),
						'validation' => 'isInt',
						'cast' => 'intval',
						'type' => 'text'
					),
					'PS_IMAGE_GENERATION_METHOD' => array(
						'title' => $this->l('Image generated by:'),
						'validation' => 'isUnsignedId',
						'required' => false,
						'cast' => 'intval',
						'type' => 'select',
						'list' => array(
							array(
								'id' => '0',
								'name' => $this->l('auto')
							),
							array(
								'id' => '1',
								'name' => $this->l('width')
							),
							array(
								'id' => '2',
								'name' => $this->l('height')
							)
						),
						'identifier' => 'id',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_PRODUCT_PICTURE_MAX_SIZE' => array(
						'title' => $this->l('Maximum size of product pictures:'),
						'desc' => $this->l('The maximum size of pictures uploadable by customers (in Bytes)'),
						'validation' => 'isUnsignedId',
						'required' => true,
						'cast' => 'intval',
						'type' => 'text',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_PRODUCT_PICTURE_WIDTH' => array(
						'title' => $this->l('Product pictures width:'),
						'desc' => $this->l('The maximum width of pictures uploadable by customers'),
						'validation' => 'isUnsignedId',
						'required' => true,
						'cast' => 'intval',
						'type' => 'text',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_PRODUCT_PICTURE_HEIGHT' => array(
						'title' => $this->l('Product pictures height:'),
						'desc' => $this->l('The maximum height of pictures uploadable by customers'),
						'validation' => 'isUnsignedId',
						'required' => true,
						'cast' => 'intval',
						'type' => 'text',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_LEGACY_IMAGES' => array(
						'title' => $this->l('Use the legacy image filesystem:'),
						'desc' => $this->l('This should be set to yes unless you successfully moved images in Preferences > Images tab'),
						'validation' => 'isBool',
						'cast' => 'intval',
						'required' => false,
						'type' => 'bool',
						'visibility' => Shop::CONTEXT_ALL
					),
					'PS_QTY_DISCOUNT_ON_COMBINATION' => array(
						'title' => $this->l('Quantity discounts based on:'),
						'desc' => $this->l('How to calculate quantity discounts'),
						'cast' => 'intval',
						'show' => true,
						'required' => false,
						'type' => 'radio',
						'validation' => 'isBool',
						'choices' => array(
							0 => $this->l('Products'),
							1 => $this->l('Combinations')
						)
					)
				),
				'bottom' => '<script type="text/javascript">stockManagementActivationAuthorization();</script>',
				'submit' => array()
			),
		);
	}

	public function beforeUpdateOptions()
	{
		if (!Tools::getValue('PS_STOCK_MANAGEMENT'))
		{
			$_POST['PS_ORDER_OUT_OF_STOCK'] = 1;
			$_POST['PS_DISPLAY_QTIES'] = 0;
		}

		// if advanced stock management is disabled, updates concerned tables
		if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') == 1 &&
			(int)Tools::getValue('PS_ADVANCED_STOCK_MANAGEMENT') == 0)
		{
			Db::getInstance()->execute(
				'UPDATE `'._DB_PREFIX_.'product`
				SET `advanced_stock_management` = 0
				WHERE `advanced_stock_management` = 1');

			Db::getInstance()->execute(
				'UPDATE `'._DB_PREFIX_.'stock_available`
				SET `depends_on_stock` = 0, `quantity` = 0
				WHERE `depends_on_stock` = 1');
		}
	}
}
