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
	
	{$tinyMCE}
	
	<h2>{l s='Language'} : {$lang} - {$translation_type}</h2>
		<div class="hint" style="display:block;">{l s='Click on the titles to open fieldsets'}.</div><br />
	{if !$suoshin_exceeded}
		<form method="post" id="{$table}_form" action="{$url_submit}" class="form">
		{$toggle_button}
		<input type="hidden" name="lang" value="{$lang}" />
		<input type="hidden" name="type" value="{$type}" />
		<input type="submit" id="{$table}_form_submit_btn" name="submitTranslations{$type|ucfirst}" value="{l s='Update translations'}" class="button" />
		{*<input type="submit" name="submitTranslations{$type|ucfirst}AndStay" value="{l s='Update and stay'}" class="button" />*}
	
		<h2>{l s='Core e-mails:'}</h2>
		{$mail_content}

		<h2>{l s='Modules e-mails:'}</h2>
		{foreach $module_mails as $module_name => $mails}
			{$mails['display']}
		{/foreach}
		
		{if !empty($theme_mails)}
			<h2>{l s='Themes e-mails:'}</h2>
			{$bool_title = false}
			{foreach $theme_mails as $theme_or_module_name => $mails}
				{if $theme_or_module_name != 'theme_mail' && !$bool_title}
					{$bool_title = true}
					<h2>{l s='E-mails modules in theme:'}</h2>
				{/if}
				{$mails['display']}
			{/foreach}
		{/if}
		</form>
	{/if}

{/block}