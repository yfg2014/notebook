<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['hasrole']=array (
  'columns' => 
  array (
    'user_id' => 
    array (
      'type' => 'table:users',
      'required' => true,
      'pkey' => true,
    ),
    'role_id' => 
    array (
      'type' => 'table:roles',
      'required' => true,
      'pkey' => true,
    ),
  ),
  'version' => '$Rev: 40654 $',
);

