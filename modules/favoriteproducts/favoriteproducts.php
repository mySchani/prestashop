<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision: 9178 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class FavoriteProducts extends Module
{
	public function __construct()
	{
		$this->name = 'favoriteproducts';
		$this->tab = 'front_office_features';
		$this->version = 1.0;
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Favorite Products');
		$this->description = $this->l('Display a page with the favorite products of the customer');
	}

	public function install()
	{
			if (!parent::install()
				OR !$this->registerHook('myAccountBlock')
				OR !$this->registerHook('extraLeft')
				OR !$this->registerHook('header'))
					return false;

			if (!Db::getInstance()->execute('
				CREATE TABLE `'._DB_PREFIX_.'favorite_product` (
				`id_favorite_product` int(10) unsigned NOT NULL auto_increment,
				`id_product` int(10) unsigned NOT NULL,
				`id_customer` int(10) unsigned NOT NULL,
				`id_shop` int(10) unsigned NOT NULL,
				`date_add` datetime NOT NULL,
  				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_favorite_product`))
				ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
				return false;

			return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() OR !Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'favorite_product`'))
			return false;
		return true;
	}

	public function hookCustomerAccount($params)
	{
		include_once(dirname(__FILE__).'/FavoriteProduct.php');

		$favoriteProducts = FavoriteProduct::getFavoriteProducts($this->context->customer->id, $this->context->language->id);

		$this->context->smarty->assign(array('favorite_products' => $favoriteProducts));

		return $this->display(__FILE__, 'my-account.tpl');
	}

	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}

	public function hookExtraLeft($params)
	{
		include_once(dirname(__FILE__).'/FavoriteProduct.php');

		$this->context->smarty->assign(array(
			'isCustomerFavoriteProduct' => (FavoriteProduct::isCustomerFavoriteProduct($this->context->customer->id, Tools::getValue('id_product')) ? 1 : 0),
			'isLogged' => (int)$this->context->customer->logged));
		return $this->display(__FILE__, 'favoriteproducts-extra.tpl');
	}

	public function hookHeader($params)
	{
		$this->context->controller->addCSS($this->_path.'favoriteproducts.css', 'all');
	}

}

