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
*  @version  Release: $Revision: 7465 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if !$opc}
	<script type="text/javascript">
	// <![CDATA[
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var txtProduct = "{l s='product'}";
	var txtProducts = "{l s='products'}";
	// ]]>
	</script>

	{capture name=path}{l s='Your payment method'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
{/if}

{if !$opc}<h1>{l s='Choose your payment method'}</h1>{else}<h2>3. {l s='Choose your payment method'}</h2>{/if}

{if !$opc}
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

	{include file="$tpl_dir./errors.tpl"}
{else}
	<div id="opc_payment_methods" class="opc-main-block">
		<div id="opc_payment_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}

<div id="HOOK_TOP_PAYMENT">{$HOOK_TOP_PAYMENT}</div>

{if $HOOK_PAYMENT}
	{if !$opc}
<div id="order-detail-content" class="table_block">
	<table id="cart_summary" class="std">
		<thead>
			<tr>
				<th class="cart_product first_item">{l s='Product'}</th>
				<th class="cart_description item">{l s='Description'}</th>
				<th class="cart_ref item">{l s='Ref.'}</th>
				<th class="cart_availability item">{l s='Avail.'}</th>
				<th class="cart_unit item">{l s='Unit price'}</th>
				<th class="cart_quantity item">{l s='Qty'}</th>
				<th class="cart_total last_item">{l s='Total'}</th>
			</tr>
		</thead>
		<tfoot>
			{if $use_taxes}
				{if $priceDisplay}
					<tr class="cart_total_price">
						<td colspan="6">{l s='Total products'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_product">{displayPrice price=$total_products}</td>
					</tr>
				{else}
					<tr class="cart_total_price">
						<td colspan="6">{l s='Total products'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_product">{displayPrice price=$total_products_wt}</td>
					</tr>
				{/if}
			{else}
				<tr class="cart_total_price">
					<td colspan="6">{l s='Total products:'}</td>
					<td class="price" id="total_product">{displayPrice price=$total_products}</td>
				</tr>
			{/if}
			<tr class="cart_total_voucher" {if $total_discounts == 0}style="display: none;"{/if}>
				<td colspan="6">
				{if $use_taxes}
					{if $priceDisplay}
						{l s='Total vouchers'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}
					{else}
						{l s='Total vouchers'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}
					{/if}
				{else}
					{l s='Total vouchers:'}
				{/if}
				</td>
				<td class="price-discount" id="total_discount">
				{if $use_taxes}
					{if $priceDisplay}
						{displayPrice price=$total_discounts_tax_exc}
					{else}
						{displayPrice price=$total_discounts}
					{/if}
				{else}
					{displayPrice price=$total_discounts_tax_exc}
				{/if}
				</td>
			</tr>
			<tr class="cart_total_voucher" {if $total_wrapping == 0}style="display: none;"{/if}>
				<td colspan="6">
				{if $use_taxes}
					{if $priceDisplay}
						{l s='Total gift-wrapping'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}
					{else}
						{l s='Total gift-wrapping'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}
					{/if}
				{else}
					{l s='Total gift-wrapping:'}
				{/if}
				</td>
				<td class="price-discount" id="total_wrapping">
				{if $use_taxes}
					{if $priceDisplay}
						{displayPrice price=$total_wrapping_tax_exc}
					{else}
						{displayPrice price=$total_wrapping}
					{/if}
				{else}
					{displayPrice price=$total_wrapping_tax_exc}
				{/if}
				</td>
			</tr>
			{if $use_taxes}
				{if $priceDisplay}
					<tr class="cart_total_delivery" {if $shippingCost <= 0} style="display:none;"{/if}>
						<td colspan="6">{l s='Total shipping'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
					</tr>
				{else}
					<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
						<td colspan="6">{l s='Total shipping'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
					</tr>
				{/if}
			{else}
				<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
					<td colspan="6">{l s='Total shipping:'}</td>
					<td class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
				</tr>
			{/if}

			{if $use_taxes}
			<tr class="cart_total_price">
				<td colspan="6">
					{if $display_tax_label}
						{l s='Total (tax excl.):'}
					{else}
						{l s='Subtotal:'}
					{/if}
				</td>
				<td class="price" id="total_price_without_tax">{displayPrice price=$total_price_without_tax}</td>
			</tr>
			<tr class="cart_total_tax">
				<td colspan="6">
					{if $display_tax_label}
						{l s='Total tax:'}
					{else}
						{l s='Estimated Sales Tax:'}
					{/if}
				</td>
				<td class="price" id="total_tax">{displayPrice price=$total_tax}</td>
			</tr>
			<tr class="cart_total_price">
				<td colspan="6">
					{if $display_tax_label}
						{l s='Total (tax incl.):'}
					{else}
						{l s='Total:'}
					{/if}
				</td>
				<td class="price" id="total_price">{displayPrice price=$total_price}</td>
			</tr>
			{else}
			<tr class="cart_total_price">
				<td colspan="6">{l s='Total:'}</td>
				<td class="price" id="total_price">{displayPrice price=$total_price_without_tax}</td>
			</tr>
			{/if}
			{*<tr class="cart_free_shipping" {if $free_ship <= 0 || $isVirtualCart} style="display: none;" {/if}>
					<td colspan="6" style="white-space: normal;">{l s='Remaining amount to be added to your cart in order to obtain free shipping:'}</td>
					<td id="free_shipping" class="price">{displayPrice price=$free_ship}</td>
			</tr>*}
		</tfoot>
		<tbody>
		{foreach from=$products item=product name=productLoop}
			{assign var='productId' value=$product.id_product}
			{assign var='productAttributeId' value=$product.id_product_attribute}
			{assign var='quantityDisplayed' value=0}
			{assign var='cannotModify' value=1}
			{* Display the product line *}
			{include file="$tpl_dir./shopping-cart-product-line.tpl"}
			{* Then the customized datas ones*}
			{if isset($customizedDatas.$productId.$productAttributeId)}
				{foreach from=$customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] key='id_customization' item='customization'}
					<tr id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" class="alternate_item cart_item">
						<td colspan="5">
							{foreach from=$customization.datas key='type' item='datas'}
								{if $type == $CUSTOMIZE_FILE}
									<div class="customizationUploaded">
										<ul class="customizationUploaded">
											{foreach from=$datas item='picture'}<li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>{/foreach}
										</ul>
									</div>
								{elseif $type == $CUSTOMIZE_TEXTFIELD}
									<ul class="typedText">
										{foreach from=$datas item='textField' name='typedText'}<li>{if $textField.name}{$textField.name}{else}{l s='Text #'}{$smarty.foreach.typedText.index+1}{/if}{l s=':'} {$textField.value}</li>{/foreach}
									</ul>
								{/if}
							{/foreach}
						</td>
						<td class="cart_quantity">
							{if isset($cannotModify) AND $cannotModify == 1}
								<span style="float:left">{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}</span>
							{else}
								<div style="float:right">
									<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart', true, NULL, "delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}")}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" title="{l s='Delete this customization'}" width="11" height="13" class="icon" /></a>
								</div>
								<div id="cart_quantity_button" style="float:left">
								<a rel="nofollow" class="cart_quantity_up" id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart', true, NULL, "add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}")}" title="{l s='Add'}"><img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add'}" width="14" height="9" /></a><br />
								{if $product.minimal_quantity < ($customization.quantity -$quantityDisplayed) OR $product.minimal_quantity <= 1}
								<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart', true, NULL, "add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}")}" title="{l s='Subtract'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract'}" width="14" height="9" />
								</a>
								{else}
								<a class="cart_quantity_down" style="opacity: 0.3;" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="#" title="{l s='Subtract'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract'}" width="14" height="9" />
								</a>
								{/if}
								</div>
								<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_hidden"/>
								<input size="2" type="text" value="{$customization.quantity}" class="cart_quantity_input" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}"/>
							{/if}
						</td>
						<td class="cart_total"></td>
					</tr>
					{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
				{/foreach}
				{* If it exists also some uncustomized products *}
				{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl"}{/if}
			{/if}
		{/foreach}
		</tbody>
	{if sizeof($discounts)}
		<tbody>
		{foreach from=$discounts item=discount name=discountLoop}
			<tr class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
				<td class="cart_discount_name" colspan="2">{$discount.name}</td>
				<td class="cart_discount_description" colspan="3">{$discount.description}</td>
				<td class="cart_discount_delete"><a href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="icon" width="11" height="13" /></a></td>
				<td class="cart_discount_price"><span class="price-discount">
					{if $discount.value_real > 0}
						{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
					{/if}
				</span></td>
			</tr>
		{/foreach}
		</tbody>
	{/if}
	</table>
</div>
{/if}
	{if $opc}<div id="opc_payment_methods-content">{/if}
		<div id="HOOK_PAYMENT">{$HOOK_PAYMENT}</div>
	{if $opc}</div>{/if}
{else}
	<p class="warning">{l s='No payment modules have been installed.'}</p>
{/if}

{if !$opc}
	<p class="cart_navigation"><a href="{$link->getPageLink('order', true, NULL, "step=2")}" title="{l s='Previous'}" class="button">&laquo; {l s='Previous'}</a></p>
{else}
	</div>
{/if}

