<?php
function set_stock_available()
{
	//Get all products with positive quantity
	$resource = Db::getInstance(_PS_USE_SQL_SLAVE_)->query('
		SELECT quantity, id_product, out_of_stock
		FROM `'._DB_PREFIX_.'product`
		WHERE `active` = 1
	');

	while ($row = Db::getInstance()->nextRow($resource))
	{
		$quantity = 0;

		//Try to get product attribues
		$attributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT quantity, id_product_attribute
			FROM `'._DB_PREFIX_.'product_attribute`
			WHERE `id_product` = '.(int)$row['id_product']
		);

		//Add each attribute to stock_available
		foreach ($attributes as $attribute)
		{
			// add to global quantity
			$quantity += $attribute['quantity'];

			//add stock available for attributes
			Db::getInstance()->execute('
				INSERT INTO `'._DB_PREFIX_.'stock_available`
				(`id_product`, `id_product_attribute`, `id_shop`, `id_group_shop`, `quantity`, `depends_on_stock`, `out_of_stock`)
				VALUES
				("'.(int)$row['id_product'].'", "'.(int)$attribute['id_product_attribute'].'", "1", "0", "'.(int)$attribute['quantity'].'", "0", "'.(int)$row['out_of_stock'].'")
			');
		}

		if (count($attributes) == 0)
			$quantity = (int)$row['quantity'];

		if ($quantity == 0)
			continue;

		//Add stock available for product
		Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'stock_available`
			(`id_product`, `id_product_attribute`, `id_shop`, `id_group_shop`, `quantity`, `depends_on_stock`, `out_of_stock`)
			VALUES
			("'.(int)$row['id_product'].'", "0", "1", "0", "'.(int)$quantity.'", "0", "'.(int)$row['out_of_stock'].'")
		');
	}
}