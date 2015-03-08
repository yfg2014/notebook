<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package featurecsone
 * @author mabaineng@shopex.cn
 */
class featurecsone_shopfilter
{	
	/**
	 * to_node过滤
	 * @param string $method
	 * @param string $obj_shop_filter
	 */
	function filter($method, &$obj_shop_filter)
	{
		if( $method == 'store.user.add' || $method == 'store.user.update' ) {
			$obj_shop_filter = array_merge($obj_shop_filter, array(
						'node_type' => 'ecos.csone',
					));
		}
	}
}