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
*  @version  Release: $Revision: 9596 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helper/view/view.tpl"}

{block name="override_tpl"}
	
	<h2>{if $customer->id}{$customer->firstname} {$customer->lastname}{else}{l s='Guest'}{/if} - {l s='Cart #'}{$cart->id|string_format:"%06d"} {l s='from'} {$cart->date_upd}</h2>
	
	<br />
	<div style="float: left;">
	<fieldset style="width: 400px">
		<legend><img src="../img/admin/tab-customers.gif" />{l s='Customer information'}</legend>
		<span style="font-weight: bold; font-size: 14px;">
		{if $customer->id}
			<a href={$link->getAdminLink('AdminCustomers')}&id_customer={$customer->id}&viewcustomer">{$customer->firstname} {$customer->lastname}</a></span> {l s='#'}{$customer->id}<br />
			<a href="mailto:{$customer->email}">{$customer->email}</a>	<br /><br />
			{l s='Account registered:'} {dateFormat date=$customer->date_add}<br />
			{l s='Valid orders placed:'} <b>{$customer_stats.nb_orders}</b><br />
			{l s='Total paid since registration:'} <b>{displayWtPriceWithCurrency price=$customer_stats.total_orders currency=$currency}</b><br />
		{else}
			{l s='Guest not registered'}
		{/if}
		</span>
	</fieldset>
	</div>
	<div style="float: left; margin-left: 40px">
	<fieldset style="width: 400px">
		<legend><img src="../img/admin/cart.gif" /> {l s='Order information'}</legend>
		<span style="font-weight: bold; font-size: 14px;">
		{if $order->id}
			<a href="{$link->getAdminLink('AdminOrders')}&id_order={$order->id}&vieworder"> {l s='Order #'}{$order->id|string_format:"%06d"}</a></span>
			<br /><br />
			{l s='Made on:'} {dateFormat date=$order->date_add}<br /><br /><br /><br />
		{else}
			{l s='No order created from this cart'}</span>
			<p><a href="{$link->getAdminLink('AdminOrders')}&id_cart={$cart->id}&addorder">{l s='Create an order with this cart'}</a></p>
		{/if}
	</fieldset>
	</div>
	<br style="clear:both;" />
	<fieldset style="margin-top:25px; width: 715px; ">
	<legend><img src="../img/admin/cart.gif" alt="{l s='Products'}" />{l s='Cart summary'}</legend>
	<div style="float:left;">
		<table style="width: 700px;" cellspacing="0" cellpadding="0" class="table" id="orderProducts">
		<thead>
			<tr>
			<th align="center" style="width: 60px">&nbsp;</th>
			<th>{l s='Product'}</th>
			<th style="width: 80px; text-align: center">{l s='UP'}</th>
			<th style="width: 20px; text-align: center">{l s='Qty'}</th>
			<th style="width: 30px; text-align: center">{l s='Stock'}</th>
			<th style="width: 90px; text-align: right; font-weight:bold;">{l s='Total'}</th>
		</tr>
		<tbody>
		{foreach from=$products item='product'}
			{if isset($customized_datas[$product.id_product][$product.id_product_attribute])}
				<tr>
					<td align="center">{$product.image}</td>
					<td><a href="{$link->getAdminLink('AdminProducts')}&id_product={$product.id_product}&updateproduct">
								<span class="productName">{$product.name}</span>{if isset($product.attributes)}<br />{$product.attributes}{/if}<br />
							{if $product.reference}$this->l('Ref:') {$product.reference}{/if}
							{if $product.reference && $product.supplier_reference} / {$product.supplier_reference}{/if}
						</a>
					</td>
					<td align="center">{displayWtPriceWithCurrency price=$product.price_wt currency=$currency}</td>
					<td align="center" class="productQuantity">{$product.customizationQuantityTotal}</td>
					<td align="center" class="productQuantity">{$product.qty_in_stock}</td>
					<td align="right">{displayWtPriceWithCurrency price=$product.total_customization_wt currency=$currency}</td>
				</tr>
				{foreach from=$customized_datas[$product.id_product][$product.id_product_attribute] item='customization'}
				<tr>
					<td colspan="2">
					{foreach from=$customization.datas key='type' item='datas'}
						{if $type == constant('Product::CUSTOMIZE_FILE')}
							<ul style="margin: 0; padding: 0; list-style-type: none;">
							{foreach from=$datas key='index' item='data'}
									<li style="display: inline; margin: 2px;">
										<a href="displayImage.php?img={$data.value}&name={$order->id}-file{$smarty.foreach.count.index}" target="_blank">
										<img src="{$pic_dir}{$data.value}_small" alt="" /></a>
									</li>
							{/foreach}
							</ul>
						{elseif $type == constant('Product::CUSTOMIZE_TEXTFIELD')}
							<ul style="margin-bottom: 4px; padding: 0; list-style-type: none;">
							{foreach from=$datas key='index' item='data'}
								<li>{if $data.name}{$data.name}{else}{l s='Text #'}{$smarty.foreach.count.index}{/if}{l s=':'}<b>{$data.value}</b></li>
							{/foreach}
							</ul>
						{/if}
					{/foreach}
					</td>
					<td align="center"></td>
					<td align="center" class="productQuantity">{$customization.quantity}</td>
					<td align="center" class="productQuantity"></td>
					<td align="center"></td>
				</tr>
				{/foreach}
			{/if}
			
			{if $product.cart_quantity > $product.customizationQuantityTotal}
				<tr>
					<td align="center">{$product.image}</td>
					<td>
						<a href="{$link->getAdminLink('AdminProducts')}&id_product={$product.id_product}&updateproduct">
						<span class="productName">{$product.name}</span>{if isset($product.attributes)}<br />{$product.attributes}{/if}<br />
						{if $product.reference}{l s='Ref:'} {$product.reference}{/if}
						{if $product.reference && $product.supplier_reference} / {$product.supplier_reference}{/if}
						</a>
					</td>
					<td align="center">{displayWtPriceWithCurrency price=$product.product_price currency=$currency}</td>
					<td align="center" class="productQuantity">{math equation='x - y' x=$product.cart_quantity y=$product.customizationQuantityTotal}</td>
					<td align="center" class="productQuantity">{$product.qty_in_stock}</td>
					<td align="right">{displayWtPriceWithCurrency price=$product.product_total currency=$currency}</td>
				</tr>
			{/if}
		{/foreach}
		<tr class="cart_total_product">
			<td colspan="5">{l s='Total products:'}</td>
			<td class="price bold right">{displayWtPriceWithCurrency price=$total_products currency=$currency}</td>
		</tr>
	
		{if $total_discounts != 0}
			<tr class="cart_total_voucher">
				<td colspan="5">{l s='Total vouchers:'}</td>
				<td class="price-discount bold right">{displayWtPriceWithCurrency price=$total_discounts currency=$currency}</td>
			</tr>
		{/if}
		{if $total_wrapping > 0}
			<tr class="cart_total_voucher">
				<td colspan="5">{l s='Total gift-wrapping:'}</td>
				<td class="price-discount bold right">{displayWtPriceWithCurrency price=$total_wrapping currency=$currency}</td>
			</tr>
		{/if}
		{if $cart->getOrderTotal(true, Cart::ONLY_SHIPPING) > 0}
			<tr class="cart_total_delivery">
				<td colspan="5">{l s='Total shipping:'}</td>
				<td class="price bold right">{displayWtPriceWithCurrency price=$total_shipping currency=$currency}</td>
			</tr>
		{/if}
		<tr class="cart_total_price">
			<td colspan="5" class="bold">{l s='Total:'}</td>
			<td class="price bold right">{displayWtPriceWithCurrency price=$total_price currency=$currency}</td>
		</tr>
	</table>
	
	{if $discounts}
		<table cellspacing="0" cellpadding="0" class="table" style="width:280px; margin:15px 0px 0px 420px;">
		<tr>
			<th><img src="../img/admin/coupon.gif" alt="{l s='Discounts'}" />{l s='Discount name'}</th>
			<th align="center" style="width: 100px">{l s='Value'}</th>
		</tr>
		{foreach from=$discounts item='discount'}
			<tr>
				<td><a href="{$link->getAdminLink('AdminDiscounts')}&id_discount={$discount.id_discount}&updatediscount">{$discount.name}</a></td>
				<td align="center">- {displayWtPriceWithCurrency price=$discount.value_real currency=$currency}</td>
			</tr>
		{/foreach}
	</table>
	{/if}
	<div style="float:left; margin-top:15px;">
	{l s='According to the group of this customer, prices are printed:'} <b>{if $order->getTaxCalculationMethod() == $smarty.const.PS_TAX_EXC}{l s='tax excluded'}{else}{l s='tax included'}{/if}</b>
	</div></div>
	
	</fieldset>
	<div class="clear" style="height:20px;">&nbsp;</div>
{/block}