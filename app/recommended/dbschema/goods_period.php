<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['goods_period'] = array (
    'columns' => array (
        'primary_goods_id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
        ),
        'secondary_goods_id' => array(
            'type' => 'varchar(200)',
        ),
        'last_modified' => array(
            'type' => 'time',
            'required' => true,
        ),
    ),
    
    'index' => array(
        'ind_goods_id' => array(
            'columns' => array(
                0 => 'primary_goods_id',
            ),
        ),
    ),
);