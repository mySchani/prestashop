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
*  @version  Release: $Revision: 11575 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
	$(document).ready(function() {
		$('input').keypress(function(e) { 
			var code = null; 
			code = (e.keyCode ? e.keyCode : e.which);
			return (code == 13) ? false : true;
		});

		searchCategory();
	});

</script>

<div class="Associations">
	<h4>{l s='Associations'}</h4>
	<div class="separation"></div>
		<div id="no_default_category" class="hint">
		{l s='Please check a category in order to select the default category.'}
	</div>
	<table>
		<tr>
			<td class="col-left">
				<label for="category_block">{l s='Associated categories:'}</label>
			</td>
			<td class="col-right">
				<div id="category_block">{$category_tree}</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-left">
				<label for="id_category_default">{l s='Default category:'}</label>
			</td>
			<td class="col-right">
				<select id="id_category_default" name="id_category_default">
					{foreach from=$selected_cat item=cat}
						<option value="{$cat.id_category}" {if $product->id_category_default == $cat.id_category}selected="selected"{/if} >{$cat.name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
	</table>
	{if $feature_shop_active}
		<div class="separation"></div>
		{* @todo use asso_shop from Helper *}
		<label>{l s='Shop association:'}</label>
		{$displayAssoShop}
	{/if}

<div class="separation"></div>
	<table>
		<tr>
			<td class="col-left"><label>{l s='Accessories:'}</label></td>
			<td style="padding-bottom:5px;">
				<input type="hidden" name="inputAccessories" id="inputAccessories" value="{foreach from=$accessories item=accessory}{$accessory.id_product}-{/foreach}" />
				<input type="hidden" name="nameAccessories" id="nameAccessories" value="{foreach from=$accessories item=accessory}{$accessory.name|htmlentitiesUTF8}¤{/foreach}" />

				<div id="ajax_choose_product">
					<p style="clear:both;margin-top:0;">
						<input type="text" value="" id="product_autocomplete_input" />
						{l s='Begin typing the first letters of the product name, then select the product from the drop-down list'}
					</p>
					<p class="preference_description">{l s='(Do not forget to Save the product afterward)'}</p>
					<!--<img onclick="$(this).prev().search();" style="cursor: pointer;" src="../img/admin/add.gif" alt="{l s='Add an accessory'}" title="{l s='Add an accessory'}" />-->
				</div>
				<div id="divAccessories">
					{* @todo : donot use 3 foreach, but assign var *}
					{foreach from=$accessories item=accessory}
						{$accessory.name|htmlentitiesUTF8}{if !empty($accessory.reference)}{$accessory.reference}{/if} 
						<span onclick="delAccessory({$accessory.id_product});" style="cursor: pointer;">
							<img src="../img/admin/delete.gif" class="middle" alt="" />
						</span><br />
					{/foreach}
				</div>
			</td>
		</tr>
		<tr>
		<br />
		<td class="col-left"><label>{l s='Manufacturer:'}</label></td>
		<td style="padding-bottom:5px;">
		<select name="id_manufacturer" id="id_manufacturer">
		<option value="0">-- {l s='Choose (optional)'} --</option>
		{if $product->id_manufacturer}
		<option value="{$product->id_manufacturer}" selected="selected">{$product->manufacturer_name}</option>
		{/if}
		<option disabled="disabled">----------</option>
		</select>&nbsp;&nbsp;&nbsp;
		<a href="{$link->getAdminLink('AdminManufacturers')}&addmanufacturer" class="confirm_leave">
		<img src="../img/admin/add.gif" alt="{l s='Create'}" title="{l s='Create'}" /> <b>{l s='Create'}</b>
		</a>
		</td>
		</tr>

	</table>
</div>

<script type="text/javascript">
	var formProduct;
	var accessories = new Array();
	urlToCall = null;
	/* function autocomplete */
	$(document).ready(function() {
		$('#product_autocomplete_input')
			.autocomplete('ajax_products_list.php', {
				minChars: 1,
				autoFill: true,
				max:20,
				matchContains: true,
				mustMatch:true,
				scroll:false,
				cacheLength:0,
				formatItem: function(item) {
					return item[1]+' - '+item[0];
				}
			}).result(addAccessory);

		$('#product_autocomplete_input').setOptions({
			extraParams: {
				excludeIds : getAccessorieIds()
			}
		});

		getManufacturers();
	});

	function getAccessorieIds()
	{
		var ids = {$product->id}+',';
		ids += $('#inputAccessories').val().replace(/\\-/g,',').replace(/\\,$/,'');
		ids = ids.replace(/\,$/,'');

		return ids;
	}
</script>