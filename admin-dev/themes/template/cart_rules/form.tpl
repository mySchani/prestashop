<div>
 	<div class="productTabs">
		<ul class="tab">
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_informations" href="javascript:displayCartRuleTab('informations');">{l s='Informations'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_conditions" href="javascript:displayCartRuleTab('conditions');">{l s='Conditions'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_actions" href="javascript:displayCartRuleTab('actions');">{l s='Actions'}</a>
			</li>
		</ul>
	</div>
</div>
<form action="{$currentIndex}&token={$currentToken}&addcart_rule" id="cart_rule_form" method="post">
	{if $currentObject->id}<input type="hidden" name="id_cart_rule" value="{$currentObject->id|intval}" />{/if}
	<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
	<div id="cart_rule_informations" class="cart_rule_tab">
		<h4>{l s='Cart rule informations'}</h4>
		<div class="separation"></div>
		{include file='cart_rules/informations.tpl'}
	</div>
	<div id="cart_rule_conditions" class="cart_rule_tab">
		<h4>{l s='Cart rule conditions'}</h4>
		<div class="separation"></div>
		{include file='cart_rules/conditions.tpl'}
	</div>
	<div id="cart_rule_actions" class="cart_rule_tab">
		<h4>{l s='Cart rule actions'}</h4>
		<div class="separation"></div>
		{include file='cart_rules/actions.tpl'}
	</div>
	<div class="separation"></div>
	<div style="text-align:center">
		<input type="submit" value="{l s='Save'}" class="button" name="submitAddcart_rule" />
		<input type="submit" value="{l s='Save and stay'}" class="button" name="submitAddcart_ruleAndStay" />
	</div>
</form>
<script type="text/javascript">
	var product_rules_counter = {if isset($product_rules_counter)}{$product_rules_counter}{else}0{/if};
	var currentToken = '{$currentToken}';
	var currentFormTab = '{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape}{else}informations{/if}';
	
	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k}] = {
			id_lang: {$language.id_lang},
			iso_code: '{$language.iso_code}',
			name: '{$language.name}'
		};
	{/foreach}
	displayFlags(languages, {$defaultLanguage});
</script>
<script type="text/javascript" src="themes/template/cart_rules/form.js"></script>