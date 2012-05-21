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
*  @version  Release: $Revision: 9771 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

				<table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom:10px;" class="table" id="">
					<col width="20px">
					<col width="40px">
					<col>
					<col width="150px">
					</colgroup>
					<thead>
						<tr class="nodrag nodrop">
							<th class="center">
								<input type="checkbox" onclick="" class="noborder" name="checkme"><br>
								<!-- TODO
									<a href="#"><img border="0" src="../img/admin/down.gif"></a>
									<a href="#"><img border="0" src="../img/admin/up_d.gif"></a>
								-->
							</th>
							<th class="center"></th>
							<th>{l s='Module name'}</th>
							<th></th>
						</tr>			
					<tbody>
					{foreach from=$modules item=module}
						<tr>
							<td><input type="checkbox" name="modules" value="{$module->name}" class="noborder"></td>
							<td><img class="imgm" alt="" src="{if isset($module->image)}{$module->image}{else}../modules/{$module->name}/{$module->logo}{/if}"></td>
							<td>
								<div class="moduleDesc" id="anchor{$module->name|ucfirst}">
									<h3>{$module->displayName}{if isset($module->id) && $module->id gt 0}<span class="setup{if isset($module->active) && $module->active eq 0} off{/if}">{l s='Installed'}</span>{else}<span class="setup non-install">{l s='Not installed'}</span>{/if}</h3>
									<div class="metadata">
										{if isset($module->author) && !empty($module->author)}
										<dl class="">
											<dt>{l s='Developed by'} :</dt>
											<dd>{$module->author|truncate:20:'...'}</dd>|
										</dl>
										{/if}
										<dl class="">
											<dt>{l s='Version'} :</dt>
											<dd>{$module->version} {if isset($module->version_addons)}({l s='Update'} {$module->version_addons} {l s='available on PrestaShop Addons'}){/if}</dd>|
										</dl>
										<dl class="">
											<dt>{l s='Category'} :</dt>
											<dd>{$module->categoryName}</dd>
										</dl>
									</div>
									<p class="desc">{l s='Description'} : {$module->description}</p>
									{if isset($module->message)}<div class="conf">{$module->message}</div>{/if}
									<div class="row-actions-module">
										{$module->optionsHtml}
									</div>
								</div>
							</td>
							<td><a href="{if isset($module->id) && $module->id gt 0}{$module->options.uninstall_url}{else}{$module->options.install_url}{/if}" class="button installed"><span>{if isset($module->id) && $module->id gt 0}{l s='Uninstall'}{else}{l s='Install'}{/if}</span></a></td>
						</tr>
					{/foreach}
					</tbody>
				</table>

