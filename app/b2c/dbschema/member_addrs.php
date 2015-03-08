<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_addrs']=array (
  'columns' => 
  array (
    'addr_id' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'name' => 
    array (
      'is_title' => true,
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'lastname' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'firstname' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'area' => 
    array (
      'type' => 'region',
      'editable' => false,
    ),
    'addr' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
    ),
    'zip' => 
    array (
      'type' => 'varchar(20)',
      'sdfpath'=>'zipcode',
      'editable' => false,
    ),
    'tel' => 
    array (
      'type' => 'varchar(50)',
      'sdfpath' => 'phone/telephone',
      'editable' => false,
    ),
    'mobile' => 
    array (
        'type' => 'varchar(50)',
        'sdfpath' => 'phone/mobile',
        'editable' => false,
    ),
    'def_addr' => 
    array (
      'type' => 'tinyint(1)',
      'sdfpath' => 'default',
      'default' => 0,
      'editable' => false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42752 $',
);
