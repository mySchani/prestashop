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

	$(document).ready(function() {
		$('input').keypress(function(e) { 
			var code = null; 
			code = (e.keyCode ? e.keyCode : e.which);
			return (code == 13) ? false : true;
		});
	});

</script>

{if isset($obj->id)}

	<h4>{l s='Attachment'}</h4>
	<div class="separation"></div>
	<fieldset>
		<label>{l s='Filename:'} </label>
		<div class="margin-form translatable">
			{foreach $languages as $language}
				<div class="lang_{$language.id_lang}" style="{if $language.id_lang != $default_form_language}display:none;{/if}float: left;">
					<input type="text" name="attachment_name_{$language.id_lang}" value="{$attachment_name[$language.id_lang]}" />
				</div>
			{/foreach}
			<sup>&nbsp;*</sup>
			<p class="preference_description">{l s='Maximum 32 characters.'}</p>
		</div>
		<div class="clear">&nbsp;</div>
		<label>{l s='Description:'} </label>
		<div class="margin-form translatable">
			{foreach $languages as $language}
				<div id="attachment_description_{$language.id_lang}" style="display: {if $language.id_lang == $default_form_language}block{else}none{/if}; float: left;">
					<textarea name="attachment_description_{$language.id_lang}">{$attachment_description[$language.id_lang]}</textarea>
				</div>
			{/foreach}
		</div>
		<div class="clear">&nbsp;</div>
		<label>{l s='File'}</label>
		<div class="margin-form">
			<p><input type="file" name="attachment_file" /></p>
			<p class="preference_description">{l s='Upload file from your computer'} ({$PS_ATTACHMENT_MAXIMUM_SIZE} {l s='Mo maximum'})</p>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="margin-form">
			<input type="submit" value="{l s='Add a new attachment file'}" name="submitAddAttachments" class="button" />
		</div>
		<div class="small"><sup>*</sup> {l s='Required field'}</div>
	</fieldset>
	<div class="clear">&nbsp;</div>
	<table>
		<tr>
			<td>
				<p>{l s='Attachments for this product:'}</p>
				<select multiple id="selectAttachment1" name="attachments[]" style="width:300px;height:160px;">
					{foreach $attach1 as $attach}
						<option value="{$attach.id_attachment}">{$attach.name}</option>
					{/foreach}
				</select><br /><br />
			<a href="#" id="removeAttachment" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
			{l s='Remove'} &gt;&gt;
		</a>
	</td>
	<td style="padding-left:20px;">
	<p>{l s='Available attachments:'}</p>
	<select multiple id="selectAttachment2" style="width:300px;height:160px;">
		{foreach $attach2 as $attach}
			<option value="{$attach.id_attachment}">{$attach.name}</option>
		{/foreach}
	</select><br /><br />
			<a href="#" id="addAttachment" style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
					&lt;&lt; {l s='Add'}
				</a>
			</div>
			</td>
		</tr>
	</table>
	<div class="clear">&nbsp;</div>
	<input type="hidden" name="arrayAttachments" id="arrayAttachments" value="{foreach $attach1 as $attach}{$attach.id_attachment},{/foreach}" />
	<input type="submit" name="submitAttachments" id="submitAttachments" value="{l s='Update attachments'}" class="button" />
	
	
	<script type="text/javascript">
		//displayFlags(languages, id_language, allowEmployeeFormLang);
		$(document).ready(function() {

			$("#addAttachment").live('click', function() {
				$("#selectAttachment2 option:selected").each(function(){
					var val = $('#arrayAttachments').val();
					var tab = val.split(',');
					for (var i=0; i < tab.length; i++)
						if (tab[i] == $(this).val())
							return false;
					$('#arrayAttachments').val(val+$(this).val()+',');
				});
				return !$("#selectAttachment2 option:selected").remove().appendTo("#selectAttachment1");
			});
			$("#removeAttachment").live('click', function() {
				$("#selectAttachment1 option:selected").each(function(){
					var val = $('#arrayAttachments').val();
					var tab = val.split(',');
					var tabs = '';
					for (var i=0; i < tab.length; i++)
						if (tab[i] != $(this).val())
						{
							tabs = tabs+','+tab[i];
							$('#arrayAttachments').val(tabs);
						}
				});
				return !$("#selectAttachment1 option:selected").remove().appendTo("#selectAttachment2");
			});
			$("#product").submit(function() {
				$("#selectAttachment1 option").each(function(i) {
					$(this).attr("selected", "selected");
				});
			});
		});
	</script>

{/if}
