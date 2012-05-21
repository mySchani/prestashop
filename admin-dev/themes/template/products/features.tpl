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
*  @version  Release: $Revision: 11775 $
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
	});

</script>

{if isset($product->id)}
	
	<h4>{l s='Assign features to this product:'}</h4>
	<div class="separation"></div>
				<ul>
					<li>{l s='You can specify a value for each relevant feature regarding this product, empty fields will not be displayed.'}</li>
					<li>{l s='You can either set a specific value, or select among existing pre-defined values you added previously.'}</li>
				</ul>
			</td>
		</tr>
	</table>
	<br />
	<table border="0" cellpadding="0" cellspacing="0" class="table" style="width:900px;">
		<tr>
			<th height="30px">{l s='Feature'}</td>
			<th style="width:30%">{l s='Pre-defined value'}</td>
			<th style="width:40%"><u>{l s='or'}</u> {l s='Customized value'}</td>
		</tr>
	</table>
	{foreach from=$available_features item=available_feature}
	<table cellpadding="5" style="background-color:#fff; width: 900px; padding:10px 5px; border:1px solid #ccc; border-top:none;">
	<tr>
		<td>{$available_feature.name}</td>
		<td style="width: 30%">
		{if sizeof($available_feature.featureValues)}
			<select id="feature_{$available_feature.id_feature}_value" name="feature_{$available_feature.id_feature}_value"
				onchange="$('.custom_{$available_feature.id_feature}_').val('');">
				<option value="0">---&nbsp;</option>
					{foreach from=$available_feature.featureValues item=value}
						<option value="{$value.id_feature_value}"{if $available_feature.current_item == $value.id_feature_value}selected="selected"{/if} >
							{$value.value|truncate:40}&nbsp;
						</option>
					{/foreach}
	
			</select>
		{else}
			<input type="hidden" name="feature_{$available_feature.id_feature}_value" value="0" />
				<span style="font-size: 10px; color: #666;">{l s='N/A'} -
				<a href="{$link->getAdminLink('AdminFeatures')}&amp;addfeature_value&id_feature={$available_feature.id_feature}"
				 style="color: #666; text-decoration: underline;" class="confirm_leave">{l s='Add pre-defined values first'}</a>
			</span>
		{/if}
		</td>
		<td style="width:40%" class="translatable">
		{foreach from=$languages key=k item=language}
			<div class="lang_{$language.id_lang}" style="{if $language.id_lang != $default_form_language}display:none;{/if}float: left;">
			<textarea class="custom_{$available_feature.id_feature}_" name="custom_{$available_feature.id_feature}_{$language.id_lang}" cols="40" rows="1"
				onkeyup="if (isArrowKey(event)) return ;$('#feature_{$available_feature.id_feature}_value').val(0);" >{$available_feature.val[$k].value|htmlentitiesUTF8|default:""}</textarea>
			</div>
		{/foreach}
		</td>
	</tr>
	
	{foreachelse}
		<tr><td colspan="3" style="text-align:center;">{l s='No features defined'}</td></tr>
	{/foreach}
	
	</table>
	<div class="separation"></div>
	<div>
		<a href="{$link->getAdminLink('AdminFeatures')}&amp;addfeature" class="confirm_leave button">
			<img src="../img/admin/add.gif" alt="new_features" title="{l s='Add a new feature'}" />&nbsp;{l s='Add a new feature'}
		</a>
	</div>
	
	<script type="text/javascript">
		displayFlags(languages, id_language, allowEmployeeFormLang);
	</script>

{/if}
