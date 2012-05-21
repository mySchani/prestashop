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
*  @version  Release: $Revision: 8754 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

function update_feature_detachable_cache()
{
	Configuration::updateGlobalValue('PS_SPECIFIC_PRICE_FEATURE_ACTIVE', (int)SpecificPrice::isCurrentlyUsed('specific_price'));
	Configuration::updateGlobalValue('PS_SCENE_FEATURE_ACTIVE', (int)Scene::isCurrentlyUsed('scene', true));
	Configuration::updateGlobalValue('PS_VIRTUAL_PROD_FEATURE_ACTIVE', (int)ProductDownload::isCurrentlyUsed('product_download', true));
	Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', (int)Customization::isCurrentlyUsed());
	Configuration::updateGlobalValue('PS_DISCOUNT_FEATURE_ACTIVE', (int)Discount::isCurrentlyUsed('discount', true));
	Configuration::updateGlobalValue('PS_GROUP_FEATURE_ACTIVE', (int)Group::isCurrentlyUsed());
	Configuration::updateGlobalValue('PS_PACK_FEATURE_ACTIVE', (int)Pack::isCurrentlyUsed());
	Configuration::updateGlobalValue('PS_ALIAS_FEATURE_ACTIVE', (int)Alias::isCurrentlyUsed('alias', true));
}
