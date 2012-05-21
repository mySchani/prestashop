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
*  @version  Release: $Revision: 13052 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
	
	<h2>{l s='Language'} : {$lang} - {$translation_type}</h2>
	{l s='Expressions to translate'} : <b>{$count}</b>.
	<div class="hint" style="display:block;">{l s='Click on the titles to open fieldsets'}.</div><br />
	{$limit_warning}
	{if !$suoshin_exceeded}
		<form method="post" id="{$table}_form" action="{$url_submit}" class="form">
		{$toggle_button}
		<input type="hidden" name="lang" value="{$lang}" />
		<input type="submit" id="{$table}_form_submit_btn" name="submitTranslations{$type|ucfirst}" value="{l s='Update translations'}" class="button" />
		<br />
	
		{if count($modules_translations) > 1}
			<h3 style="padding:0;margin:0;">{l s='List of Themes - Click to access theme translation:'}</h3>
			<ul style="list-style-type:none;padding:0;margin:0 0 10px 0;">
				{foreach array_keys($modules_translations) as $theme}
					<li><a href="#{$theme}" class="link">- {if $theme === 'default'}{l s='default'}{else}$theme{/if}</a></li>
				{/foreach}
			</ul>
		{/if}
	
		{foreach $modules_translations as $theme_name => $theme}
			<h2>&gt;{l s='Theme:'} <a name="{$theme_name}">{if $theme_name === $default_theme_name}{l s='default'}{else}{$theme_name}{/if} </h2>
			{foreach $theme as $module_name => $module}
				<h3>{l s='Module:'} <a name="{$module_name}" style="font-style:italic">{$module_name}</a></h3>
				{foreach $module as $template_name => $newLang}
					{if !empty($newLang)}
						{$occurrences = $newLang|array_count_values}
						{if isset($occurrences[''])}
							{$missing_translations = $occurrences['']}
						{else}
							{$missing_translations = 0}
						{/if}
						<fieldset>
							<legend style="cursor : pointer" onclick="$('#{$theme_name}_{$module_name}_{$template_name}').slideToggle();">{if $theme_name === 'default'}{l s='default'}{else}{$theme_name}{/if} - {$template_name}
								<font color="blue">{$newLang|count}</font> {l s='expressions'} (<font color="red">{$missing_translations}</font>)
							</legend>
							<div name="{$type}_div" id="{$theme_name}_{$module_name}_{$template_name}" style="display:{if $missing_translations}block{else}none{/if}">
								<table cellpadding="2">
									{foreach $newLang as $key => $value}
										<tr>
											<td style="width: 40%">{$key|stripslashes}</td>
											<td>= 
												{* Prepare name string for md5() *}
												{capture assign="name"}{strtolower($module_name)}_{strtolower($theme_name)}_{strtolower($template_name)}_{md5($key)}{/capture}
												{if $key|strlen < $textarea_sized}
													<input type="text" 
														style="width: 450px" 
														name="{$name|md5}" 
														value="{$value|regex_replace:'#"#':'&quot;'|stripslashes}" />
												{else}
													<textarea rows="{($key|strlen / $textarea_sized)|intval}" 
														style="width: 450px" 
														name="{$name|md5}">{$value|regex_replace:'#"#':'&quot;'|stripslashes}</textarea>
												{/if}
											</td>
										</tr>
									{/foreach}
								</table>
							</div>
						</fieldset><br />
					{/if}
				{/foreach}
			{/foreach}
		{/foreach}
	{/if}

{/block}