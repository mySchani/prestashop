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

	<div class="toolbar-placeholder">
		<div class="toolbarBox toolbarHead">
	
			<ul class="cc_button">
				<li>
					<a id="desc-module-new" class="toolbar_btn" href="#top_container" onclick="$('#module_install').slideToggle();" title="Add new">
						<span class="process-icon-new-module" ></span>
						<div>Add new module</div>
					</a>
				</li>
				<li>
					<a id="desc-module-addon-new" class="toolbar_btn" href="{$addonsUrl}" title="Add new">
						<span class="process-icon-new-module-addon" ></span>
						<div>Add new via Addons</div>
					</a>
				</li>
			</ul>

			<div class="pageTitle">
				<h3><span id="current_obj" style="font-weight: normal;"><span class="breadcrumb item-0">Module</span> : <span class="breadcrumb item-1">Liste de modules</span></span></h3>
			</div>

		</div>
	</div>


	<div id="module_install" style="width:500px;margin-top:5px;{if !isset($smarty.post.downloadflag)}display: none;{/if}">
		<fieldset>
			<legend><img src="../img/admin/add.gif" alt="{l s='Add a new module'}" class="middle" /> {l s='Add a new module'}</legend>
			<p>{'The module must be either a zip file or a tarball.'}</p>
			<div style="float:left;margin-right:50px">
				<form action="{$currentIndex}&token={$token}" method="post" enctype="multipart/form-data">
					<label style="width: 100px">{l s='Module file'}</label>
					<div class="margin-form" style="padding-left: 140px">
						<input type="file" name="file" />
						<p>{l s='Upload the module from your computer.'}</p>
					</div>
					<div class="margin-form" style="padding-left: 140px">
						<input type="submit" name="download" value="{l s='Upload this module'}" class="button" />
					</div>
				</form>
			</div>
		</fieldset>
		<br />
	</div>

	{if !isset($logged_on_addons)}
		<!--start addons login-->
		<div class="filter-module" id="addons_login_div">
			<p>{l s='You have a PrestaShop Addons account ?'}</p>
			<form id="addons_login_form" method="post">
				<label>{l s='Login Addons'} :</label> <input type="text" value="" id="username_addons" autocomplete="off" class="ac_input">
				<label>{l s= 'Password Addons'} :</label> <input type="password" value="" id="password_addons" autocomplete="off" class="ac_input">
				<input type="submit" class="button" id="addons_login_button" value="{l s='Log in'}">
				<span id="addons_loading" style="color:red"></span>
			</form>

		</div>
		<!--end addons login-->
	{/if}

