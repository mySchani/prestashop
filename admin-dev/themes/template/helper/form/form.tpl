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

{if $show_toolbar}
	<div class="toolbar-placeholder">
		<div class="toolbarBox {if $toolbar_fix}toolbarHead{/if}">
				{include file="toolbar.tpl" toolbar_btn=$toolbar_btn}
				<div class="pageTitle">
				<h3>
					{block name=pageTitle}
						<span id="current_obj" style="font-weight: normal;">{$title|default:'&nbsp;'}</span>
					{/block}
				</h3>
				</div>
		</div>
	</div>
	<div class="leadin">{block name="leadin"}{/block}</div>
{/if}

{if isset($fields.title)}<h2>{$fields.title}</h2>{/if}
{block name="defaultForm"}
<form id="{$table}_form" class="defaultForm" action="{$current}&{$submit_action}=1&token={$token}" method="post" enctype="multipart/form-data" {if isset($style)}style="{$style}"{/if}>
	{if $form_id}
		<input type="hidden" name="id_{$table}" id="id_{$table}" value="{$form_id}" />
	{/if}
	{foreach $fields as $f => $fieldset}
		<fieldset id="fieldset_{$f}">
			{foreach $fieldset.form as $key => $field}
				{if $key == 'legend'}
					<legend>
						{if isset($field.image)}<img src="{$field.image}" alt="{$field.title}" />{/if}
						{$field.title}
					</legend>
				{elseif $key == 'description'}
					<p class="description">{$field}</p>
				{elseif $key == 'input'}
					{foreach $field as $input}
						{if $input.name == 'id_state'}
							<div id="contains_states" {if $contains_states}style="display:none;"{/if}>
						{/if}
						{block name="label"}
							{if isset($input.label)}
								<label>{$input.label} </label>
							{/if}
						{/block}
						{if $input.type == 'hidden'}
							<input type="hidden" name="{$input.name}" id="{$input.name}" value="{$fields_value[$input.name]}" />
						{else}
						{block name="start_field_block"}
							<div class="margin-form">
						{/block}
							{if $input.type == 'text' || $input.type == 'tags'}
								{if isset($input.lang)}
									<div class="translatable">
										{foreach $languages as $language}
											<div class="lang_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
												{if $input.type == 'tags'}
													{literal}
													<script type="text/javascript">
														$().ready(function () {
															var input_id = '{/literal}{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}{literal}';
															$('#'+input_id).tagify({addTagPrompt: '{/literal}{l s='add tag'}{literal}'});
															$({/literal}'#{$table}{literal}_form').submit( function() {
																$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
														    });
														});
													</script>
													{/literal}
												{/if}
												<input type="text"
														name="{$input.name}_{$language.id_lang}"
														id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"
														value="{$fields_value[$input.name][$language.id_lang]}"
														class="{if $input.type == 'tags'}tagify {/if}"
														{if isset($input.size)}size="{$input.size}"{/if}
														{if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
														{if isset($input.class)}class="{$input.class}"{/if}
														{if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
														{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
												{if isset($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
											</div>
										{/foreach}
									</div>
								{else}
									{if $input.type == 'tags'}
										{literal}
										<script type="text/javascript">
											$().ready(function () {
												var input_id = '{/literal}{if isset($input.id)}{$input.id}{else}{$input.name}{/if}{literal}';
												$('#'+input_id).tagify();
												$('#'+input_id).tagify({addTagPrompt: '{/literal}{l s='Add tag'}{literal}'});
												$({/literal}'#{$table}{literal}_form').submit( function() {
													$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
											    });
											});
										</script>
										{/literal}
									{/if}
									<input type="text"
											name="{$input.name}"
											id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
											value="{$fields_value[$input.name]}"
											class="{if $input.type == 'tags'}tagify {/if}"
											{if isset($input.size)}size="{$input.size}"{/if}
											{if isset($input.maxlength)}maxlength="{$input.maxlength}"{/if}
											{if isset($input.class)}class="{$input.class}"{/if}
											{if isset($input.readonly) && $input.readonly}readonly="readonly"{/if}
											{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
									{if isset($input.suffix)}{$input.suffix}{/if}
									{if isset($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
								{/if}
							{elseif $input.type == 'select'}
								{if isset($input.options.query) && !$input.options.query && isset($input.empty_message)}
									{$input.empty_message}
									{$input.required = false}
									{$input.desc = null}
								{else}
									<select name="{$input.name}"
											id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
											{if isset($input.multiple)}multiple="multiple" {/if}
											{if isset($input.size)}size="{$input.size}"{/if}
											{if isset($input.onchange)}onchange="{$input.onchange}"{/if}>
										{if isset($input.options.default)}
											<option value="{$input.options.default.value}">{$input.options.default.label}</option>
										{/if}
										{if isset($input.options.optiongroup)}
											{foreach $input.options.optiongroup.query AS $optiongroup}
												<optgroup label="{$optiongroup[$input.options.optiongroup.label]}">
													{foreach $optiongroup[$input.options.options.query] as $option}
														<option value="{$option[$input.options.options.id]}"
															{if isset($input.multiple)}
																{foreach $fields_value[$input.name] as $field_value}
																	{if $field_value == $option[$input.options.options.id]}selected="selected"{/if}
																{/foreach}
															{else}
																{if $fields_value[$input.name] == $option[$input.options.options.id]}selected="selected"{/if}
															{/if}
														>{$option[$input.options.options.name]|escape:'htmlall':'UTF-8'}</option>
													{/foreach}
												</optgroup>
											{/foreach}
										{else}
											{foreach $input.options.query AS $option}
												<option value="{$option[$input.options.id]}"
													{if isset($input.multiple)}
														{foreach $fields_value[$input.name] as $field_value}
															{if $field_value == $option[$input.options.id]}selected="selected"{/if}
														{/foreach}
													{else}
														{if $fields_value[$input.name] == $option[$input.options.id]}selected="selected"{/if}
													{/if}
												>{$option[$input.options.name]|escape:'htmlall':'UTF-8'}</option>
											{/foreach}
										{/if}
									</select>
									{if isset($input.hint)}<span class="hint" name="help_box">{$input.hint}<span class="hint-pointer">&nbsp;</span></span>{/if}
								{/if}
							{elseif $input.type == 'radio'}
								{foreach $input.values as $value}
									<input type="radio"
											name="{$input.name}"
											id="{$value.id}"
											value="{$value.value|escape:'htmlall':'UTF-8'}"
											{if $fields_value[$input.name] == $value.value}checked="checked"{/if}
											{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
									<label {if isset($input.class)}class="{$input.class}"{/if} for="{$value.id}">
									 {if isset($input.is_bool) && $input.is_bool == true}
									 	{if $value.value == 1}
									 		<img src="../img/admin/enabled.gif" alt="{$value.label}" title="{$value.label}" />
									 	{else}
									 		<img src="../img/admin/disabled.gif" alt="{$value.label}" title="{$value.label}" />
									 	{/if}
									 {else}
									 	{$value.label}
									 {/if}
									</label>
									{if isset($input.br) && $input.br}<br />{/if}
									{if isset($value.p) && $value.p}<p>{$value.p}</p>{/if}
								{/foreach}
							{elseif $input.type == 'textarea'}
								{if isset($input.lang)}
									<div class="translatable">
										{foreach $languages as $language}
											<div class="lang_{$language.id_lang}" id="{$input.name}_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
												<textarea cols="{$input.cols}" rows="{$input.rows}" name="{$input.name}_{$language.id_lang}" {if isset($input.autoload_rte) && $input.autoload_rte}class="autoload_rte"{/if} >{$fields_value[$input.name][$language.id_lang]}</textarea>
											</div>
										{/foreach}
									</div>
								{else}
									<textarea name="{$input.name}" id="{$input.name}" cols="{$input.cols}" rows="{$input.rows}">{$fields_value[$input.name]}</textarea>
								{/if}
							{elseif $input.type == 'checkbox'}
								{foreach $input.values.query as $value}
									{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]}
									<input type="checkbox"
										name="{$id_checkbox}"
										id="{$id_checkbox}"
										{if isset($value.val)}value="{$value.val}"{/if}
										{if isset($fields_value[$id_checkbox]) && $fields_value[$id_checkbox]}checked="checked"{/if} />
									<label for="{$id_checkbox}" class="t"><strong>{$value[$input.values.name]}</strong></label><br />
								{/foreach}
							{elseif $input.type == 'file'}
								{if isset($input.display_image) && $input.display_image}
									{if isset($fields_value.image) && $fields_value.image}
										<div id="image">
											{$fields_value.image}
											<p align="center">{l s='File size'} {$fields_value.size}kb</p>
											<a href="{$current}&id_category={$form_id}&token={$token}&deleteImage=1">
												<img src="../img/admin/delete.gif" alt="{l s='Delete'}" /> {l s='Delete'}
											</a>
										</div><br />
									{/if}
								{/if}
								<input type="file" name="{$input.name}" {if isset($input.id)}id="{$input.id}"{/if} />
							{elseif $input.type == 'password'}
								<input type="password"
										name="{$input.name}"
										size="{$input.size}"
										value="" />
							{elseif $input.type == 'birthday'}
								{foreach $input.options as $key => $select}
									<select name="{$key}">
										<option value="">-</option>
										{if $key == 'months'}
											{foreach $select as $k => $v}
												<option value="{$k}" {if $k == $fields_value[$key]}selected="selected"{/if}>{$v}</option>
											{/foreach}
										{else}
											{foreach $select as $v}
												<option value="{$v}" {if $v == $fields_value[$key]}selected="selected"{/if}>{$v}</option>
											{/foreach}
										{/if}
									</select>
								{/foreach}
							{elseif $input.type == 'group'}
								{assign var=groups value=$input.values}
								{include file='helper/form/form_group.tpl'}
							{elseif $input.type == 'shop' OR $input.type == 'group_shop'}
								{include file='helper/form/form_shop.tpl' input=$input fields_value=$fields_value}
							{elseif $input.type == 'categories'}
								{include file='helper/form/form_category.tpl' categories=$input.values}
							{elseif $input.type == 'asso_shop' && isset($asso_shop) && $asso_shop}
									{$asso_shop}
							{elseif $input.type == 'color'}
								<input type="color"
									size="{$input.size}"
									data-hex="true"
									{if isset($input.class)}class="{$input.class}"
									{else}class="color mColorPickerInput"{/if}
									name="{$input.name}"
									value="{$fields_value[$input.name]}" />
							{elseif $input.type == 'date'}
								<input type="text"
									size="{$input.size}"
									data-hex="true"
									{if isset($input.class)}class="{$input.class}"
									{else}class="datepicker"{/if}
									name="{$input.name}"
									value="{$fields_value[$input.name]}" />
							{elseif $input.type == 'free'}
								{$fields_value[$input.name]}
							{/if}
							{if isset($input.required) && $input.required} <sup>*</sup>{/if}
							{if isset($input.desc)}
								<p class="clear">
									{if is_array($input.desc)}
										{foreach $input.desc as $p}
											{if is_array($p)}
												<span id="{$p.id}">{$p.text}</span><br />
											{else}
												{$p}<br />
											{/if}
										{/foreach}
									{else}
										{$input.desc}
									{/if}
								</p>
							{/if}
							{if isset($languages)}<div class="clear"></div>{/if}
						{block name="end_field_block"}</div>{/block}
						{/if}
						{if $input.name == 'id_state'}
							</div>
						{/if}
					{/foreach}
				{elseif $key == 'submit'}
					<div class="margin-form">
						<input type="submit"
							id="{if isset($field.id)}{$field.id}{else}{$table}_form_submit_btn{/if}"
							value="{$field.title}"
							name="{if isset($field.name)}{$field.name}{else}{$submit_action}{/if}{if isset($field.stay) && $field.stay}AndStay{/if}"
							{if isset($field.class)}class="{$field.class}"{/if} />
					</div>
				{elseif $key == 'desc'}
					<p class="clear">
						{if is_array($field)}
							{foreach $field as $k => $p}
								{if is_array($p)}
									<span id="{$p.id}">{$p.text}</span><br />
								{else}
									{$p}
									{if isset($field[$k+1])}<br />{/if}
								{/if}
							{/foreach}
						{else}
							{$field}
						{/if}
					</p>
				{/if}
				{block name="other_input"}{/block}
			{/foreach}
			{if $required_fields}
				<div class="small"><sup>*</sup> {l s='Required field'}</div>
			{/if}
		</fieldset>
		{block name="other_fieldsets"}{/block}
		{if isset($fields[$f+1])}<br class="clear" />{/if}
	{/foreach}
</form>
{/block}
{block name="after"}{/block}
{if isset($tinymce) && $tinymce}
	<script type="text/javascript">

	var iso = '{$iso}';
	var pathCSS = '{$smarty.const._THEME_CSS_DIR_}';
	var ad = '{$ad}';

	$(document).ready(function(){

		tinySetup();

		{block name="autoload_tinyMCE"}
			// change each by click to load only on click
			$(".autoload_rte").each(function(e){
				tinySetup({
					mode :"exact",
					editor_selector :"autoload_rte",
					elements : $(this).attr("id"),
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull|cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,undo,redo",
					theme_advanced_buttons2 : "link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,charmap,media,|,ltr,rtl,|,fullscreen",
					theme_advanced_buttons3 : "",
					theme_advanced_buttons4 : "",
				});
			})
		{/block}
	});
	</script>
{/if}
{if $firstCall}
	<script type="text/javascript">
		var vat_number = {$vat_number};
		var module_dir = '{$smarty.const._MODULE_DIR_}';
		var id_language = {$defaultFormLanguage};
		var languages = new Array();

		$(document).ready(function() {

			{foreach $languages as $k => $language}
				languages[{$k}] = {
					id_lang: {$language.id_lang},
					iso_code: '{$language.iso_code}',
					name: '{$language.name}',
					is_default: "{$language.is_default}"
				};
			{/foreach}
			// we need allowEmployeeFormLang var in ajax request
			allowEmployeeFormLang = {$allowEmployeeFormLang};
			displayFlags(languages, id_language, allowEmployeeFormLang);

			{if isset($fields_value.id_state)}
				if ($('#id_country') && $('#id_state'))
				{
					ajaxStates({$fields_value.id_state});
					$('#id_country').change(function() {
						ajaxStates();
					});
				}
			{/if}

			if ($(".datepicker").length > 0)
				$(".datepicker").datepicker({
					prevText: '',
					nextText: '',
					dateFormat: 'yy-mm-dd'
				});

		});
	{block name="script"}{/block}
	</script>
{/if}
