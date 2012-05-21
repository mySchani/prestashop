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
*  @version  Release: $Revision: 10626 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* Assign product price *}
{if ($order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC)}
	{assign var=product_price value=($product['unit_price_tax_excl'] + $product['ecotax'])}
{else}
	{assign var=product_price value=$product['unit_price_tax_incl']}
{/if}

{if ($product['product_quantity'] > $product['customizationQuantityTotal'])}
<tr{if isset($product.image) && $product.image->id && isset($product.image_size)} height="{$product['image_size'][1] + 7}"{/if}>
	<td align="center">{if isset($product.image) && $product.image->id}{$product.image_tag}{/if}</td>
	<td><a href="index.php?controller=adminproducts&id_product={$product['product_id']}&updateproduct&token={getAdminToken tab='AdminProducts'}">
		<span class="productName">{$product['product_name']}</span><br />
		{if $product.product_reference}{l s='Ref:'} {$product.product_reference}<br />{/if}
		{if $product.product_supplier_reference}{l s='Ref Supplier:'} {$product.product_supplier_reference}{/if}
	</a></td>
	<td align="center">
		<span class="product_price_show">{displayPrice price=$product_price currency=$currency->id}</span>
		{if $can_edit}
		<span class="product_price_edit" style="display:none;">
			<input type="hidden" name="product_id_order_detail" class="edit_product_id_order_detail" value="{$product['id_order_detail']}" />
			{if $currency->sign % 2}{$currency->sign}{/if}<input type="text" name="product_price_tax_excl" class="edit_product_price_tax_excl edit_product_price" value="{Tools::ps_round($product['unit_price_tax_excl'], 2)}" size="5" /> {if !($currency->sign % 2)}{$currency->sign}{/if} {l s='tax excl.'}<br />
			{if $currency->sign % 2}{$currency->sign}{/if}<input type="text" name="product_price_tax_incl" class="edit_product_price_tax_incl edit_product_price" value="{Tools::ps_round($product['unit_price_tax_incl'], 2)}" size="5" /> {if !($currency->sign % 2)}{$currency->sign}{/if} {l s='tax incl.'}
		</span>
		{/if}
	</td>
	<td align="center" class="productQuantity">
		<span class="product_quantity_show">{$product['product_quantity']}</span>
		{if $can_edit}
		<span class="product_quantity_edit" style="display:none;">
			<input type="text" name="product_quantity" class="edit_product_quantity" value="{$product['product_quantity']}" size="2" />
		</span>
		{/if}
	</td>
	{if ($order->hasBeenPaid())}<td align="center" class="productQuantity">{$product['product_quantity_refunded']}</td>{/if}
	{if ($order->hasBeenDelivered())}<td align="center" class="productQuantity">{$product['product_quantity_return']}</td>{/if}
	<td align="center" class="productQuantity product_stock">{StockManagerFactory::getManager()->getProductRealQuantities($product['product_id'], $product['product_attribute_id'], null, true)}</td>
	<td align="center" class="total_product">
		{displayPrice price=(Tools::ps_round($product_price, 2) * ($product['product_quantity'] - $product['customizationQuantityTotal'])) currency=$currency->id}
	</td>
	<td colspan="2" style="display: none;" class="add_product_fields">&nbsp;</th>
	<td align="center" class="cancelCheck standard_refund_fields" style="background-color:rgb(232, 237, 194);display:none">
		<input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="{$product['product_quantity_return']}" />
		<input type="hidden" name="totalQty" id="totalQty" value="{$product['product_quantity']}" />
		<input type="hidden" name="productName" id="productName" value="{$product['product_name']}" />
	{if ((!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN')) AND (int)($product['product_quantity_return']) < (int)($product['product_quantity']))}
		<input type="checkbox" name="id_order_detail[{$k}]" id="id_order_detail[{$k}]" value="{$product['id_order_detail']}" onchange="setCancelQuantity(this, {$product['id_order_detail']}, {$product['product_quantity_in_stock'] - $product['customizationQuantityTotal'] - $product['product_quantity_reinjected']})" {if ($product['product_quantity_return'] + $product['product_quantity_refunded'] >= $product['product_quantity'])}disabled="disabled" {/if}/>
	{else}
		--
	{/if}
	</td>
	<td class="cancelQuantity standard_refund_fields" style="background-color:rgb(232, 237, 194);display:none">
	{if ($product['product_quantity_return'] + $product['product_quantity_refunded'] >= $product['product_quantity'])}
		<input type="hidden" name="cancelQuantity[{$k}]" value="0" />
	{elseif (!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN'))}
		<input type="text" id="cancelQuantity_{$product['id_order_detail']}" name="cancelQuantity[{$k}]" size="2" onclick="selectCheckbox(this);" value="" />
	{/if}

	{if $product['customizationQuantityTotal']}
		{assign var=productQuantity value=($product['product_quantity']-$product['customizationQuantityTotal'])}
	{else}
		{assign var=productQuantity value=$product['product_quantity']}
	{/if}

	{if ($order->hasBeenDelivered())}
		{$product['product_quantity_refunded']}/{$productQuantity-$product['product_quantity_refunded']}
	{elseif ($order->hasBeenPaid())}
		{$product['product_quantity_return']}/{$productQuantity}
	{else}
		0/{$productQuantity}
	{/if}
	</td>
	<td class="partial_refund_fields" style="text-align:right;background-color:rgb(232, 237, 194);display:none"><input type="text" size="3" name="partialRefundProduct[{$k}]" /> &euro;</td>
	{if $can_edit}
	<td class="product_invoice" colspan="2" style="display: none;text-align:center;">
		{if $order->hasBeenPaid()}
		<select name="product_invoice" class="edit_product_invoice">
			{foreach from=$invoices_collection item=invoice}
			<option value="{$invoice->id}" {if $invoice->id == $product['id_order_invoice']}selected="selected"{/if}>#{Configuration::get('PS_INVOICE_PREFIX', $current_id_lang)}{'%06d'|sprintf:$invoice->number}</option>
			{/foreach}
		</select>
		{else}
		&nbsp;
		{/if}
	</td>
	<td class="product_action" style="text-align:right">
		<a href="#" class="edit_product_change_link"><img src="../img/admin/edit.gif" alt="{l s='Edit'}" /></a>
		<input type="submit" class="button" name="submitProductChange" value="{l s='Update'}"  style="display: none;" />
		<a href="#" class="cancel_product_change_link" style="display: none;"><img src="../img/admin/disabled.gif" alt="{l s='Cancel'}" /></a>
		<a href="#" class="delete_product_line"><img src="../img/admin/delete.gif" alt="{l s='Delete'}" /></a>
	</td>
	{/if}
</tr>
{/if}