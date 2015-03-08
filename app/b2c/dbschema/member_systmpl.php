<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_systmpl']=array (
  'columns' => 
  array (
    'tmpl_name' => array (
       'type' => 'varchar(100)',
        'pkey' => true,
      'required' => true,
    ),
    'content' => array(
        'type'=>'longtext',
        'label' =>app::get('b2c')->_('内容'),
        'default' => 0,
    ),
    'edittime' => array (
      'type' => 'int(10) ',
      'required' => true,
    ),
    'active' => array(
        'type'=>"enum('true', 'false')",
        'default' => 'true',      
    ),
   
  ),   
  'comment' => app::get('b2c')->_('信息表'),
   'engine' => 'innodb',
   'version' => '$Rev$',
);
