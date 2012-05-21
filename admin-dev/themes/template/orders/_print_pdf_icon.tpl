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
*  @version  Release: $Revision: 9589 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* Generate HTML code for printing Invoice Icon with link *}
<span style="width:20px; margin-right:5px;">
{if (($order_state->invoice && $order->invoice_number) && $tr['product_number'])}
	<a href="pdf.php?id_order={$order->id}&pdf"><img src="../img/admin/tab-invoice.gif" alt="invoice" /></a>
{else}
	-
{/if}
</span>

{* Generate HTML code for printing Delivery Icon with link *}
<span style="width:20px;">
{if ($order_state->delivery && $order->delivery_number)}
	<a href="pdf.php?id_delivery={$order->delivery_number}"><img src="../img/admin/delivery.gif" alt="delivery" /></a>
{else}
	-
{/if}
</span>