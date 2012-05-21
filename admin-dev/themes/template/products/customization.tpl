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
	<h4>{l s='Add or modify customizable properties'}</h4>
	
	<div class="separation"></div><br />
	<table cellpadding="5" style="width:100%">
		<tr>
			<td style="width:150px;text-align:right;padding-right:10px;font-weight:bold;vertical-align:top;" valign="top">{l s='File fields:'}</td>
			<td style="padding-bottom:5px;">
				<input type="text" name="uploadable_files" id="uploadable_files" size="4" value="{$uploadable_files}" />
				<p class="preference_description">{l s='Number of upload file fields displayed'}</p>
			</td>
		</tr>
		<tr>
			<td style="width:150px;text-align:right;padding-right:10px;font-weight:bold;vertical-align:top;" valign="top">{l s='Text fields:'}</td>
			<td style="padding-bottom:5px;">
				<input type="text" name="text_fields" id="text_fields" size="4" value="{$text_fields}" />
				<p class="preference_description">{l s='Number of text fields displayed'}</p>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;">
				<input type="submit" name="submitCustomizationConfiguration" value="{l s='Update settings'}" class="button" onclick="this.form.action += '&addproduct&tabs=5';" />
			</td>
		</tr>
		{if $has_file_labels}
			<tr>
				<td colspan="2"><div class="separation"></div></td>
			</tr>
			<tr>
				<td style="width:150px" valign="top">{l s='Files fields:'}</td>
				<td>
					{$display_file_labels}
				</td>
			</tr>
		{/if}
		{if $has_text_labels}
			<tr>
				<td colspan="2"><div class="separation"></div></td>
			</tr>
			<tr>
				<td style="width:150px" valign="top">{l s='Text fields:'}</td>
				<td>
					{$display_text_labels}
				</td>
			</tr>
		{/if}
		<tr>
			<td colspan="2" style="text-align:center;">
				{if $has_file_labels || $has_text_labels}
					<input type="submit" name="submitProductCustomization" id="submitProductCustomization" value="{l s='Save labels'}" class="button" onclick="this.form.action += '&addproduct&tabs=5';" style="margin-top: 9px" />
				{/if}
			</td>
		</tr>
	</table>
{/if}