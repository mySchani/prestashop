{*
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
*  @version  Release: $Revision: 8971 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

	<fieldset style="width:230px">
		<legend>
			<img src="../img/admin/navigation.png" /> {l s='Navigation'}
		</legend>
		{if count($modules)}
			{foreach $modules as $module}
				{if $module_instance[$module.name]}
					<h4>
						<img src="../modules/{$module.name}/logo.gif" />
						<a href="{$current}&token={$token}&module={$module.name}">{$module_instance[$module.name]->displayName}</a>
					</h4>
				{/if}
			{/foreach}
		{else}
			{l s='No module installed'}
		{/if}
	</fieldset>
	<div class="clear space">&nbsp;</div>
</div>

