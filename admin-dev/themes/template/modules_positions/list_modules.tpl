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
*  @version  Release: $Revision: 10453 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
	var token = '{$token}';
	var come_from = 'AdminModulesPositions';
</script>
<script type="text/javascript" src="../js/admin-dnd.js"></script>

{if $show_toolbar}
	<div class="toolbar-placeholder">
		<div class="toolbarBox {if $toolbar_fix}toolbarHead{/if}">
			{include file="toolbar.tpl" toolbar_btn=$toolbar_btn}
			<div class="pageTitle">
				<h3>{block name=pageTitle}
					<span id="current_obj" style="font-weight: normal;">{$title|default:'&nbsp;'}</span>
					{/block}
				</h3>
			</div>
		</div>
	</div>
{/if}

<form>
	{l s='Show'} :
	<select id="show_modules" onChange="autoUrl('show_modules', '{$url_show_modules}')">
		<option value="all">{l s='All modules'}&nbsp;</option>
		<option>---------------</option>

		{foreach $modules as $module}
			<option value="{$module->id|intval}" {if $display_key == $module->id}selected="selected"{/if}>{$module->displayName}</option>
		{/foreach}
	</select>
	<br /><br />
	<input type="checkbox" id="hook_position" onclick="autoUrlNoList('hook_position', '{$url_show_invisible}')" {if $hook_position}checked="checked"{/if} />&nbsp;
	<label class="t" for="hook_position">{l s='Display non-positionable hook'}</label>
</form>

<fieldset style="width:250px;float:right"><legend>{l s='Live edit'}</legend>
{if $live_edit}
	<p>{l s='You have to select a shop to use live edit'}</p>
{else}
	<p>{l s='By clicking here you will be redirected to the front office of your shop to move and delete modules directly.'}</p>
		<br>
		<a href="{$url_live_edit}" target="_blank" class="button">{l s='Run LiveEdit'}</a>
{/if}
</fieldset>

<form method="post" action="{$url_submit}">
<div id="unhook_button_position_top">
	<input class="button floatr" type="submit" name="unhookform" value="{l s='Unhook the selection'}"/></div>

{if !$can_move}
	<br /><div><b>{l s='If you want to order / move following data, please go in shop context (select a shop in shop list)'}</b></div>
{/if}
{foreach $hooks as $hook}
	<a name="{$hook['name']}"/>
	<table cellpadding="0" cellspacing="0" class="table width3 space {if $hook['module_count'] >= 2} tableDnD{/if}" id="{$hook['id_hook']}">
	<tr class="nodrag nodrop"><th colspan="4">{$hook['title']} - <span style="color: red">{$hook['module_count']}</span> {if $hook['module_count'] > 1}{l s='modules'}{else}{l s='module'}{/if}
	{if $hook['module_count'] && $can_move}
		<input type="checkbox" id="Ghook{$hook['id_hook']}" class="floatr" style="margin-right: 2px;" onclick="hookCheckboxes({$hook['id_hook']}, 0, this)"/>
	{/if}
	{if !empty($hook['description'])}
		&nbsp;<span style="font-size:0.8em; font-weight: normal">[{$hook['description']}]</span>
	{/if}
	<sub style="color:grey;"><i>({l s='Technical name: '}{$hook['name']})</i></sub></th></tr>
	{if $hook['module_count']}
		{foreach $hook['modules'] as $position => $module}
			<tr id="{$hook['id_hook']}_{$module['instance']->id}" {cycle values='class="alt_row",'} style="height: 42px;">
			{if !$display_key}
				<td class="positions" width="40">{$module@iteration}</td>
				<td {if $can_move && $hook['module_count'] >= 2} class="dragHandle"{/if} id="td_{$hook['id_hook']}_{$module['instance']->id}" width="40">
					{if $can_move}
						<a {if {$module@iteration} == 1} style="display: none;"{/if} href="{$current}&id_module={$module['instance']->id}&id_hook={$hook['id_hook']}&direction=0&token={$token}&changePosition#{$hook['name']}">
							<img src="../img/admin/up.gif" alt="{l s='Up'}" title="{l s='Up'}" />
						</a><br />
						<a {if {$module@iteration} == count($hook['modules'])} style="display: none;"{/if} href="{$current}&id_module={$module['instance']->id}&id_hook={$hook['id_hook']}&direction=1&token={$token}&changePosition#{$hook['name']}">
							<img src="../img/admin/down.gif" alt="{l s='Down'}" title="{l s='Down'}" />
						</a>
					{/if}
				</td>
				<td style="padding-left: 10px;"><label class="lab_modules_positions" for="mod{$hook['id_hook']}_{$module['instance']->id}">
			{else}
				<td style="padding-left: 10px;" colspan="3"><label class="lab_modules_positions" for="mod{$hook['id_hook']}_{$module['instance']->id}">
			{/if}
			<img src="../modules/{$module['instance']->name}/logo.gif" alt="{$module['instance']->name|stripslashes}" /> <strong>{$module['instance']->displayName|stripslashes}</strong>
				{if $module['instance']->version} v{if $module['instance']->version|intval == $module['instance']->version}{sprintf('%.1f', $module['instance']->version)}{else}{$module['instance']->version|floatval}{/if}{/if}<br />{$module['instance']->description}
			</label></td>
				<td width="60">
					<a href="{$current}&id_module={$module['instance']->id}&id_hook={$hook['id_hook']}&editGraft{if $display_key}&show_modules={$display_key}{/if}&token={$token}">
						<img src="../img/admin/edit.gif" border="0" alt="{l s='Edit'}" title="{l s='Edit'}" />
					</a>
					<a href="{$current}&id_module={$module['instance']->id}&id_hook={$hook['id_hook']}&deleteGraft{if $display_key}&show_modules={$display_key}{/if}&token={$token}">
						<img src="../img/admin/delete.gif" border="0" alt="{l s='Delete'}" title="{l s='Delete'}" />
					</a>
					<input type="checkbox" id="mod{$hook['id_hook']}_{$module['instance']->id}" class="hook{$hook['id_hook']}" onclick="hookCheckboxes({$hook['id_hook']}, 1, this)" name="unhooks[]" value="{$hook['id_hook']}_{$module['instance']->id}"/>
				</td>
			</tr>
		{/foreach}
	{else}
		<tr><td colspan="4">{l s='No module for this hook'}</td></tr>
	{/if}
	</table>
{/foreach}
<div id="unhook_button_position_bottom"><input class="button floatr" type="submit" name="unhookform" value="{l s='Unhook the selection'}"/></div></form>