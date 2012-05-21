{extends file="helper/form/form.tpl"}
{block name="end_field_block"}
	{if ($input.type == "description")}
		<p>
			{$input.text}
		</p>
	{/if}
	</div>
{/block}
{block name="after"}
<script type="text/javascript">
	startingData = new Array();
	{foreach from=$products item=product key=key}
		startingData[{$key}] = new Array(
			'{$product.details->name}', 
			'{$product.id_product}', 
			{$product.x_axis},
			{$product.y_axis},
			{$product.zone_width},
			{$product.zone_height});
	{/foreach}
</script>
{/block}
