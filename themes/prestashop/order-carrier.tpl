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
*  @version  Release: $Revision: 7444 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="carrier_area">
{if !$opc}
	<script type="text/javascript">
	//<![CDATA[
		var orderProcess = 'order';
		var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
		var currencyRate = '{$currencyRate|floatval}';
		var currencyFormat = '{$currencyFormat|intval}';
		var currencyBlank = '{$currencyBlank|intval}';
		var txtProduct = "{l s='product'}";
		var txtProducts = "{l s='products'}";

	var msg = "{l s='You must agree to the terms of service before continuing.' js=1}";
	{literal}
	function acceptCGV()
	{
		if ($('#cgv').length && !$('input#cgv:checked').length)
		{
			alert(msg);
			return false;
		}
		else
			return true;
	}
	{/literal}
	//]]>
	</script>
{else}
	<script type="text/javascript">
		var txtFree = "{l s='Free!'}";
	</script>
{/if}

{if !$virtual_cart && $giftAllowed && $cart->gift == 1}
<script type="text/javascript">
{literal}
// <![CDATA[
	$('document').ready( function(){
		if ($('input#gift').is(':checked'))
			$('p#gift_div').show();
	});
//]]>
{/literal}
</script>
{/if}

{if !$opc}
{capture name=path}{l s='Shipping'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{/if}

{if !$opc}<h1>{l s='Shipping'}</h1>{else}<h2>2. {l s='Delivery methods'}</h2>{/if}

{if !$opc}
{assign var='current_step' value='shipping'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

<form id="form" action="{$link->getPageLink('order', true, NULL, "multi-shipping={$multi_shipping}")}" method="post" onsubmit="return acceptCGV();">
{else}
<div id="opc_delivery_methods" class="opc-main-block">
	<div id="opc_delivery_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}

{if $conditions AND $cms_id}
	<h3 class="condition_title">{l s='Terms of service'}</h3>
	<p class="checkbox">
		<input type="checkbox" name="cgv" id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
		<label for="cgv">{l s='I agree to the terms of service and adhere to them unconditionally.'}</label> <a href="{$link_conditions}" class="iframe">{l s='(read)'}</a>
	</p>
	<script type="text/javascript">$('a.iframe').fancybox();</script>
{/if}

{if $virtual_cart}
	<input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
{else}
	<h3 class="carrier_title">{l s='Choose your delivery method'}</h3>
	
	<div id="HOOK_BEFORECARRIER">{if isset($carriers)}{$HOOK_BEFORECARRIER}{/if}</div>
	{if isset($isVirtualCart) && $isVirtualCart}
	<p class="warning">{l s='No carrier needed for this order'}</p>
	{else}
	{if $recyclablePackAllowed}
	<p class="checkbox">
		<input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} />
		<label for="recyclable">{l s='I agree to receive my order in recycled packaging'}.</label>
	</p>
	{/if}
	<div class="delivery_options_address">
	{if isset($delivery_option_list)}
		{foreach $delivery_option_list as $id_address => $option_list}
			<h3>{$address_collection[$id_address]->alias}</h3>
			<div class="delivery_options">
			{foreach $option_list as $key => $option}
				<div class="delivery_option {if ($option@index % 2)}alternate_{/if}item">
					<input class="delivery_option_radio" type="radio" name="delivery_option[{$id_address}]" {if $opc}onclick="updateCarrierSelectionAndGift();"{/if} id="delivery_option_{$id_address}_{$option@index}" value="{$key}" {if $delivery_option[$id_address] == $key}checked="checked"{/if} />
					<label for="delivery_option_{$id_address}_{$option@index}">
						<table class="resume">
							<tr>
								<td class="delivery_option_logo">
									{* If there is only one carrier, show the logo of the carrier *}
									{if $option.unique_carrier}
										{foreach $option.carrier_list as $carrier}
											{if $carrier.logo}
												<img src="{$carrier.logo}" alt="{$carrier.instance->name}"/>
											{else}
												{$carrier.instance->name}
											{/if}
										{/foreach}
									{else}
										{$carrier.instance->name}
									{/if}
								</td>
								<td>
								{if $option.is_best_grade}
									{if $option.is_best_price}
									<div class="delivery_option_best delivery_option_icon">{l s='The best price and grade'}</div>
									{else}
									<div class="delivery_option_fast delivery_option_icon">{l s='The faster'}</div>
									{/if}
								{else}
									{if $option.is_best_price}
									<div class="delivery_option_best_price delivery_option_icon">{l s='The best price'}</div>
									{/if}
								{/if}
								</td>
								<td>
								<div class="delivery_option_price">
									{if $option.total_price_with_tax}
										{if $use_taxes == 1}
											{convertPrice price=$option.total_price_with_tax} {l s='(tax incl.)'}
										{else}
											{convertPrice price=$option.total_price_without_tax} {l s='(tax excl.)'}
										{/if}
									{else}
										{l s='Free!'}
									{/if}
								</div>
								</td>
							</tr>
						</table>
							<table class="delivery_option_carrier">
								{foreach $option.carrier_list as $carrier}
									<tr>
										<td>
										{if $carrier.logo}
											<img src="{$carrier.logo}" alt="{$carrier.instance->name}"/>
										{/if}
									</td>
									<td>
										{$carrier.instance->name}
									</td>
									<td>
										{if isset($carrier.instance->delay[$cookie->id_lang])}
											{$carrier.instance->delay[$cookie->id_lang]}
										{/if}
									</td>
									</tr>
								{/foreach}
							</table>
					</label>
				</div>
			{/foreach}
			</div>
		{/foreach}
	{/if}
	</div>
	<div style="display: none;" id="extra_carrier"></div>
	
		{if $giftAllowed}
		<h3 class="gift_title">{l s='Gift'}</h3>
		<p class="checkbox">
			<input type="checkbox" name="gift" id="gift" value="1" {if $cart->gift == 1}checked="checked"{/if} />
			<label for="gift">{l s='I would like the order to be gift-wrapped.'}</label>
			<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			{if $gift_wrapping_price > 0}
				({l s='Additional cost of'}
				<span class="price" id="gift-price">
					{if $priceDisplay == 1}{convertPrice price=$total_wrapping_tax_exc_cost}{else}{convertPrice price=$total_wrapping_cost}{/if}
				</span>
				{if $use_taxes}{if $priceDisplay == 1} {l s='(tax excl.)'}{else} {l s='(tax incl.)'}{/if}{/if})
			{/if}
		</p>
		<p id="gift_div" class="textarea">
			<label for="gift_message">{l s='If you wish, you can add a note to the gift:'}</label>
			<textarea rows="5" cols="35" id="gift_message" name="gift_message">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
		</p>
		{/if}
	{/if}
{/if}

{if !$opc}
	<p class="cart_navigation submit">
		<input type="hidden" name="step" value="3" />
		<input type="hidden" name="back" value="{$back}" />
		{if !$is_guest}
			{if $back}
				<a href="{$link->getPageLink('order', true, NULL, "step=1&back={$back}&multi-shipping={$multi_shipping}")}" title="{l s='Previous'}" class="button">&laquo; {l s='Previous'}</a>
			{else}
				<a href="{$link->getPageLink('order', true, NULL, "step=1&multi-shipping={$multi_shipping}")}" title="{l s='Previous'}" class="button">&laquo; {l s='Previous'}</a>
			{/if}
		{else}
				<a href="{$link->getPageLink('order', true, NULL, "multi-shipping={$multi_shipping}")}" title="{l s='Previous'}" class="button">&laquo; {l s='Previous'}</a>
		{/if}
		<input type="submit" name="processCarrier" value="{l s='Next'} &raquo;" class="exclusive" />
	</p>
</form>
{else}
	<h3>{l s='Leave a message'}</h3>
	<div>
		<p>{l s='If you would like to add a comment about your order, please write it below.'}</p>
		<p><textarea cols="120" rows="3" name="message" id="message">{if isset($oldMessage)}{$oldMessage}{/if}</textarea></p>
	</div>
</div>
{/if}
</div>
