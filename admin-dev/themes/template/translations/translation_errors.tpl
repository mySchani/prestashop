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
	{l s='Errors to translate'} : <b>{$count}</b>
	{$limit_warning}
	{if !$suoshin_exceeded}
		<form method="post" id="{$table}_form" action="{$url_submit}" class="form">
			{*{$auto_translate}$*}
			<input type="hidden" name="lang" value="{$lang}" />
			<input type="submit" id="{$table}_form_submit_btn" name="submitTranslations{$type|ucfirst}" value="{l s='Update translations'}" class="button" />
			<br /><br />
			<table cellpadding="0" cellspacing="0" class="table">
			{foreach $errorsArray as $key => $value}
				<tr {if empty($value)}style="background-color:#FBB"{else}{cycle values='class="alt_row",'}{/if}>
					<td>{$key|stripslashes}</td>
					<td style="width: 430px">= <input type="text" name="{$key|md5}" value="{$value|regex_replace:'#"#':'&quot;'|stripslashes}" style="width: 380px"></td>
				</tr>
			{/foreach}
			</table>
		</form>
	{/if}

{/block}