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
*  @version  Release: $Revision: 10355 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminCmsContentControllerCore extends AdminController
{
	/** @var object adminCMSCategories() instance */
	private $adminCMSCategories;

	/** @var object adminCMS() instance */
	private $adminCMS;

	/** @var object Category() instance for navigation*/
	private static $_category = NULL;

	public function __construct()
	{
		/* Get current category */
		$id_cms_category = (int)(Tools::getValue('id_cms_category', Tools::getValue('id_cms_category_parent', 1)));
		self::$_category = new CMSCategory($id_cms_category);
		if (!Validate::isLoadedObject(self::$_category))
			die('Category cannot be loaded');

		$this->table = array('cms_category', 'cms');
		$this->adminCMSCategories = new AdminCmsCategoriesController();
		$this->adminCMS = new AdminCmsController();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentCMSCategory()
	{
		return self::$_category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminCMSCategories->tabAccess = $this->tabAccess;
		$this->adminCMS->tabAccess = $this->tabAccess;
		return $result;
	}


	public function displayErrors()
	{
		parent::displayErrors();
		$this->adminCMS->displayErrors();
		$this->adminCMSCategories->displayErrors();
	}

	public function initContent()
	{
		$this->adminCMSCategories->token = $this->token;
		$this->adminCMS->token = $this->token;

		if ($this->display == 'edit_category')
			$this->content .= $this->adminCMSCategories->initForm();
		elseif ($this->display == 'edit_page')
			$this->content .= $this->adminCMS->initForm();
		elseif ($this->display == 'view_page'){}
		else
		{
			$id_cms_category = (int)(Tools::getValue('id_cms_category'));
			if (!$id_cms_category)
				$id_cms_category = 1;

			// CMS categories breadcrumb
			$cms_tabs = array('cms_category', 'cms');
			// Cleaning links
			$catBarIndex = self::$currentIndex;
			foreach ($cms_tabs AS $tab)
				if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway'))
					$catBarIndex = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', self::$currentIndex);

			$this->content .= $this->adminCMSCategories->initList();
			$this->adminCMS->id_cms_category = $id_cms_category;
			$this->content .= $this->adminCMS->initList();
			$this->context->smarty->assign(array(
				'cms_breadcrumb' => getPath($catBarIndex, $id_cms_category,'','','cms'),
			));
		}

		$this->context->smarty->assign(array(
			'content' => $this->content
		));
	}

	public function postProcess()
	{
		if (((Tools::isSubmit('submitAddcms_category') || Tools::isSubmit('submitAddcms_categoryAndStay')) && sizeof($this->adminCMSCategories->_errors))
			|| isset($_GET['updatecms_category'])
			|| isset($_GET['addcms_category']))
			$this->display = 'edit_category';
		elseif (((Tools::isSubmit('submitAddcms') || Tools::isSubmit('submitAddcmsAndStay')) && sizeof($this->adminCMS->_errors))
			|| isset($_GET['updatecms'])
			|| isset($_GET['addcms']))
			$this->display = 'edit_page';
		else
		{
			$this->display = 'list';
			$this->id_cms_category = (int)(Tools::getValue('id_cms_category'));
		}

		if (Tools::isSubmit('submitDelcms')
			|| Tools::isSubmit('previewSubmitAddcmsAndPreview')
			|| Tools::isSubmit('submitAddcms')
			|| isset($_GET['deletecms'])
			|| Tools::isSubmit('viewcms')
			|| (Tools::isSubmit('statuscms') && Tools::isSubmit('id_cms')) && (Tools::isSubmit('position') && !Tools::isSubmit('id_cms_category_to_move')))
			$this->adminCMS->postProcess();
		elseif(Tools::isSubmit('submitDelcms_category')
			|| Tools::isSubmit('submitAddcms_categoryAndBackToParent')
			|| Tools::isSubmit('submitAddcms_category')
			|| isset($_GET['deletecms_category'])
			|| (Tools::isSubmit('statuscms_category') && Tools::isSubmit('id_cms_category'))
			|| (Tools::isSubmit('position') && Tools::isSubmit('id_cms_category_to_move')))
			$this->adminCMSCategories->postProcess();
	}
}
