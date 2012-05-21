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
{extends file="../helper/form/form.tpl"}

{block name="label"}
	{if $input.type == 'select' && $input.name == 'country[]'}
		<div id="add_new_tax_rule" style="display:none">
			<label>{$input.label} </label>
	{else}
		{if isset($input.label)}
			<label
				{if $input.name == 'states[]'}
					 id="state-label" style="display: none;"
				{elseif $input.name == 'zipcode'}
					 id="zipcode-label"
				{/if}
				>{$input.label} </label>
		{/if}
	{/if}
{/block}

{block name="start_field_block"}
	<div class="margin-form"
		{if $input.name == 'states[]'}
			  id="state-select" style="display: none;"
		{/if}
	>
{/block}

{block name="end_field_block"}
	{if $input.type == 'submit'}
			</div>
	{/if}
	</div>
{/block}

{block name="script"}

		$(document).ready(function() {

			$('#country').click(function() {
				populateStates($(this).val(), '');
			});

			$('span.process-icon-new').parent().click(function() {
				initForm();
				$('#add_new_tax_rule').slideToggle();
			});
			
			$('table.tax_rule').find('a.edit').attr('href', '#');

		});
	
		function populateStates(id_country, id_state)
		{
			if ($("#country option:selected").size() > 1)
			{
				$("#zipcode-label").hide();
				$("#zipcode").hide();
	
				$("#state-select").hide();
				$("#state-label").hide();
			} else {
				$.ajax({
					url: "ajax.php",
					cache: false,
					data: "ajaxStates=1&id_country="+id_country+"&id_state="+id_state+"&empty_value={l s='All'}",
					success: function(html){
						if (html == "false")
						{
							$("#state-label").hide();
							$("#state-select").hide();
							$("#states").html('');
						}
						else
						{
							$("#state-label").show();
							$("#state-select").show();
							$("#states").html(html);
						}
					}
				});

				$("#zipcode-label").show();
				$("#zipcode").show();
			}
		}				
	
		function loadTaxRule(id_tax_rule)
		{
			$.ajax({
		        type: 'POST',
				url: 'ajax.php',
		        async: true,
		        dataType: 'json',
				data: 'ajaxStates=1&ajaxUpdateTaxRule=1&id_tax_rule='+id_tax_rule,
				success: function(data){
					$('#add_new_tax_rule').show();
					$('#id_tax_rule').val(data.id);
					$('#country').val(data.id_country);
					$('#state').val(data.id_state);
	
					zipcode = 0;
					if (data.zipcode_from != 0)
					{
						zipcode = data.zipcode_from;
	
						if (data.zipcode_to != 0)
							zipcode = zipcode +"-"+data.zipcode_to
					}
	
					$('#zipcode').val(zipcode);
					$('#behavior').val(data.behavior);
					$('#id_tax').val(data.id_tax);
					$('#description').val(data.description);
	
					populateStates(data.id_country, data.id_state);
				}
			});
		}
	
		function initForm()
		{
			$('#id_tax_rule').val('');
			$('#country').val(0);
			$('#state').val(0);
			$('#zipcode').val(0);
			$('#behavior').val(0);
			$('#tax').val(0);
			$('#description').val('');
	
			populateStates(0,0);
		}
{/block}