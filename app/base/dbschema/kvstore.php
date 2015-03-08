<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['kvstore']=array (
  'columns' => 
  array (
    'id' => array(
        'type' => 'number',
        'pkey' => true,
        'extra' => 'auto_increment',
    ),
    'prefix' => array(
        'type'=>'varchar(255)',
        'required'=>true,
    ),
    'key' => array(
        'type'=>'varchar(255)',
        'required'=>true,
    ),
    'value' => array(
        'type'=>'serialize',
    ),
    'dateline' => array(
        'type'=>'time',
    ),
    'ttl' => array(
        'type'=>'time',
        'default' => 0,
    ),
  ),
  'index' => 
  array (
    'ind_prefix' => 
    array (
      'columns' => 
      array (
        0 => 'prefix',
      ),
    ),
    'ind_key' => 
    array (
      'columns' => 
      array (
        0 => 'key',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 41137 $',
  'ignore_cache' => true,
);
