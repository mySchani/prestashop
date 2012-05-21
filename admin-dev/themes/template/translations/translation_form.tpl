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
*  @version  Release: $Revision: 11429 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helper/view/view.tpl"}

{block name="override_tpl"}

	<h2>{l s='Language'} : {$lang} - {$translation_type}</h2>
	{l s='Expressions to translate'} : <b>{$count}</b>. {l s='Click on the titles to open fieldsets'}.<br /><br />
	<p>{l s='total missing expresssions:'} {$missing_translations|array_sum} </p>
	{$limit_warning}
	{if !$suoshin_exceeded}
		<form method="post" id="{$table}_form" action="{$url_submit}" class="form">
		{$toggle_button}
		{*{include file="translations/auto_translate.tpl"}*}
		<input type="hidden" name="lang" value="{$lang}" />
		<input type="submit" id="{$table}_form_submit_btn" name="submitTranslations{$type|ucfirst}" value="{l s='Update translations'}" class="button" />
		<br /><br />
		{foreach $tabsArray as $k => $newLang}
			{if !empty($newLang)}
				<fieldset>
					<legend style="cursor : pointer" onclick="$('#{$k}-tpl').slideToggle();">
						{$k} - <font color="blue">{$newLang|count}</font> {l s='expressions'}
						{if isset($missing_translations[$k])}(<font color="red">{$missing_translations[$k]} {l s='missing'}</font>){/if}
					</legend>
					<div name="{$type}_div" id="{$k}-tpl" style="display:{if isset($missing_translations[$k])}block{else}none{/if}">
					<table cellpadding="2">
					{foreach $newLang as $key => $value}
						<tr>
							<td style="width: 40%">{$key|stripslashes}</td>
							<td>= {*todo : md5 is already calculated in AdminTranslationsController*}
								{if $key|strlen < $textarea_sized}
									<input type="text" 
										style="width: 450px" 
										name="{if in_array($type, array('front', 'fields'))}{$k}_{$key|md5}{else}{$k}{$key|md5}{/if}" 
										value="{$value|regex_replace:'/"/':'&quot;'|stripslashes}" />
								{else}
									<textarea rows="{($key|strlen / $textarea_sized)|intval}" 
										style="width: 450px" 
									name="{if in_array($type, array('front', 'fields'))}{$k}_{$key|md5}{else}{$k}{$key|md5}{/if}"
									>{$value|regex_replace:'/"/':'&quot;'|stripslashes}</textarea>
								{/if}
							</td>
						</tr>
					{/foreach}
					</table>
					</div>
				</fieldset><br />
			{/if}
		{/foreach}
	{/if}

{/block}