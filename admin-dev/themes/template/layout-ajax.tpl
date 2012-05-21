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
*  @version  Release: $Revision: 10591 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if isset($json)}
{
	"status":"{$status}",
	"confirmations" : {$confirmations},
	"informations" : {$informations},
	"error" : {$errors},
	"warnings" : {$warnings},
	"content" : {$page}
}
{else}

	{if isset($conf)}
		<div class="conf">
			{$conf}
		</div>
	{/if}

	{if count($errors)} {* @todo what is ??? AND $this->_includeContainer *}
		<div class="error">
			<span style="float:right"><a id="hideError" href=""><img alt="X" src="../img/admin/close.png" /></a></span><img src="../img/admin/error2.png" />
			{if count($errors) == 1}
				{$errors[0]}
			{else}
				{$errors|count} {l s='errors'}
				<br/>
				<ol>
					{foreach $errors AS $error}
						<li>{$error}</li>
					{/foreach}
				</ol>
			{/if}
		</div>
	{/if}

	{if isset($informations) && count($informations) && $informations}
		<div class="hint clear" style="display:block;">
			{foreach $informations as $info}
				{$info}<br />
			{/foreach}
		</div><br />
	{/if}

	{if isset($confirmations) && count($confirmations) && $confirmations}
		<div class="conf" style="display:block;">
			{foreach $confirmations as $confirm}
				{$confirm}<br />
			{/foreach}
		</div><br />
	{/if}

	{if count($warnings)}
		<div class="warn">
			<span style="float:right">
				<a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png" /></a>
			</span>
			<img src="../img/admin/warn2.png" />
			{if count($warnings) > 1}
				{l s='There are'} {count($warnings)} {l s='warnings'}
			{else}
				{l s='There is'} {count($warnings)} {l s='warning'}
			{/if}
			<span style="margin-left:20px;" id="labelSeeMore">
				<a id="linkSeeMore" href="#" style="text-decoration:underline">{l s='Click here to see more'}</a>
				<a id="linkHide" href="#" style="text-decoration:underline;display:none">{l s='Hide warning'}</a>
			</span>
			<ul style="display:none;" id="seeMore">
			{foreach $warnings as $warning}
				<li>{$warning}</li>
			{/foreach}
			</ul>
		</div>
	{/if}
	{$page}
{/if}
