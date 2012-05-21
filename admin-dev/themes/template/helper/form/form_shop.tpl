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

<script type="text/javascript">
$().ready(function() {ldelim}
	// Click on "all shop"
	$('.input_all_shop').click(function() {ldelim}
		var checked = $(this).attr('checked');
		$('.input_group_shop').attr('checked', checked);
		$('.input_shop').attr('checked', checked);
	{rdelim});

	// Click on a group shop
	$('.input_group_shop').click(function() {ldelim}
		$('.input_shop[value='+$(this).val()+']').attr('checked', $(this).attr('checked'));
		check_all_shop();
	{rdelim});

	// Click on a shop
	$('.input_shop').click(function() {ldelim}
		check_group_shop_status($(this).val());
		check_all_shop();
	{rdelim});

	// Initialize checkbox
	$('.input_shop').each(function(k, v) {ldelim}
		check_group_shop_status($(v).val());
		check_all_shop();
	{rdelim});
{rdelim});

function check_group_shop_status(id_group) {ldelim}
	var groupChecked = true;
	$('.input_shop[value='+id_group+']').each(function(k, v) {ldelim}
		if (!$(v).attr('checked'))
			groupChecked = false;
	{rdelim});
	$('.input_group_shop[value='+id_group+']').attr('checked', groupChecked);
{rdelim}

function check_all_shop() {ldelim}
	var allChecked = true;
	$('.input_group_shop').each(function(k, v) {ldelim}
		if (!$(v).attr('checked'))
			allChecked = false;
		{rdelim});
	$('.input_all_shop').attr('checked', allChecked);
{rdelim}
</script>

<div class="assoShop">
	<table class="table" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<th>{if $input.type == 'group_shop'}{l s='Group shop'}{else}{l s='Shop'}{/if}</th>
		</tr>
		<tr {if $input.type == 'group_shop'}class="alt_row"{/if}>
			<td>
				<label class="t"><input class="input_all_shop" type="checkbox" /> {if $input.type == 'group_shop'}{l s='All group shops'}{else}{l s='All shops'}{/if}</label>
			</td>
		</tr>
		{foreach $input.values as $groupID => $groupData}
			{if ($input.type == 'group_shop' && ((isset($fields_value.shop[$groupID]) && in_array($form_id, $fields_value.shop[$groupID])) || !$form_id))}
				{assign var=groupChecked value=true}
			{else}
				{assign var=groupChecked value=false}
			{/if}
			<tr {if $input.type == 'shop'}class="alt_row"{/if}>
				<td>
					<img style="vertical-align:middle;" alt="" src="../img/admin/lv2_b.gif" />
					<label class="t">
						<input class="input_group_shop"
							type="checkbox"
							name="checkBoxGroupShopAsso_{$table}_{$form_id}_{$groupID}"
							value="{$groupID}"
							{if $groupChecked} checked="checked"{/if} />
						{$groupData['name']}
					</label>
				</td>
			</tr>
	
			{if $input.type == 'shop'}
				{assign var=j value=0}
				{foreach $groupData['shops'] as $shopID => $shopData}
					{if ((isset($fields_value.shop[$shopID]) && in_array($form_id, $fields_value.shop[$shopID])) || !$form_id)}
						{assign var=checked value=true}
					{else}
						{assign var=checked value=false}
					{/if}
					<tr>
						<td>
							<img style="vertical-align:middle;" alt="" src="../img/admin/lv3_{if $j < count($groupData['shops']) - 1}b{else}f{/if}.png" />
							<label class="child">
								<input class="input_shop"
									type="checkbox"
									value="{$groupID}"
									name="checkBoxShopAsso_{$table}_{$form_id}_{$shopID}"
									id="checkedBox_{$shopID}"
									{if $checked} checked="checked"{/if} />
								{$shopData['name']}
							</label>
						</td>
					</tr>
					{assign var=j value=$j+1}
				{/foreach}
			{/if}
		{/foreach}
	</table>
</div>