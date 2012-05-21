{*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @version  Release: $Revision: 10428 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<table>
	<tr>
		<td style="text-align: center; font-size: 6pt; color: #444">
			{$shop_address|escape:'htmlall':'UTF-8'}<br />

			{if !empty($shop_phone) OR !empty($shop_fax)}
				{l s='For more assistance, contact Support:' pdf='true'}<br />
				{if !empty($shop_phone)}
					Tel: {$shop_phone|escape:'htmlall':'UTF-8'}
				{/if}

				{if !empty($shop_fax)}
					Fax: {$shop_fax|escape:'htmlall':'UTF-8'}
				{/if}
				<br />
			{/if}
            
            {if isset($shop_details)}
                {$shop_details|escape:'htmlall':'UTF-8'}<br />
            {/if}

            {if isset($free_text)}
    			{$free_text|escape:'htmlall':'UTF-8'}<br />
            {/if}
		</td>
	</tr>
</table>

