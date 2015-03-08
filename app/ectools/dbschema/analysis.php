<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['analysis']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'service' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
    ),
    'interval' => 
    array (
      'type' => 
          array (
            'hour' => 'hour',
            'day' => 'day',
          ),
      'required' => true,
    ),
    'modify' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
    ),
  ),
  'engine' => 'innodb',
  'ignore_cache' => true,
);
