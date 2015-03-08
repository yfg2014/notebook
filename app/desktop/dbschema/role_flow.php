<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['role_flow']=array (
  'columns' => 
  array (
    'role_id' => array (
      'type' => 'table:roles',
      'required' => true,
      'pkey' => true,
    ),
    'flow_id' => array (
      'type' => 'table:flow',
      'required' => true,
      'pkey' => true,
    ),
  ),
  'comment' => app::get('desktop')->_('信息表'),
  'version' => '$Rev$',
);
