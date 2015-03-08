<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['type_brand']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_id' => 
    array (
      'type' => 'table:brand',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
    ),
    'brand_order' => 
    array (
      'type' => 'number',
      'editable' => false,
    ),
  ),
  'version' => '$Rev: 40654 $',
);
