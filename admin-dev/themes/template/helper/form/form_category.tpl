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
{if count($categories) && isset($categories)}
	<script type="text/javascript">
		var inputName = '{$categories.input_name}';
		var use_radio = {if $categories.use_radio}1{else}0{/if};
		var selectedCat = '{$categories.selected_cat}';
		var selectedLabel = '{$categories.trads.selected}';
		var home = '{$categories.trads.Home}';
		var use_radio = {if $categories.use_radio}1{else}0{/if};
	</script>

	<div style="background-color:#F4E6C9; width:99%;padding:5px 0 5px 5px;">
		<a href="#" id="collapse_all" >{$categories.trads['Collapse All']}</a>
		 - <a href="#" id="expand_all" >{$categories.trads['Expand All']}</a>
		{if !$categories.use_radio}
		 - <a href="#" id="check_all" >{$categories.trads['Check All']}</a>
		 - <a href="#" id="uncheck_all" >{$categories.trads['Uncheck All']}</a>
		 {/if}
		{if $categories.use_search}
			<span style="margin-left:20px">
				{$categories.trads.search} :
				<form method="post" id="filternameForm">
					<input type="text" name="search_cat" id="search_cat">
				</form>
			</span>
		{/if}
	</div>

	{assign var=home_is_selected value=false}

	{foreach $categories.selected_cat AS $cat}
		{if is_array($cat)}
			{if $cat.id_category != 1}
				<input {if in_array($cat.id_category, $categories.disabled_categories)}disabled="disabled"{/if} type="hidden" name="{$categories.input_name}" value="{$cat.id_category}" >
			{else}
				{assign var=home_is_selected value=true}
			{/if}
		{else}
			{if $cat != 1}
				<input {if in_array($cat, $categories.disabled_categories)}disabled="disabled"{/if} type="hidden" name="{$categories.input_name}" value="{$cat}" >
			{else}
				{assign var=home_is_selected value=true}
			{/if}
		{/if}
	{/foreach}
	<ul id="categories-treeview" class="filetree">
		<li id="1" class="hasChildren">
			<span class="folder">
				<input type="{if !$categories.use_radio}checkbox{else}radio{/if}"
						name="{$categories.input_name}"
						value="1"
						{if $home_is_selected}checked{/if}
						onclick="clickOnCategoryBox($(this));" />
				{$categories.trads.Home}
			</span>
			<ul>
				<li><span class="placeholder">&nbsp;</span></li>
		  	</ul>
		</li>
	</ul>
{/if}
