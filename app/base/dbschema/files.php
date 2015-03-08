<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['files']=array (
  'columns' => 
  array (
    'file_id' => array('type'=>'number','pkey'=>true,'extra' => 'auto_increment'),
    'file_path' => array('type'=>'varchar(255)'),
    'file_type' =>array('type'=>array('private'=>'','public'=>''),'default'=>'public'),
    'last_change_time' => array('type'=>'last_modify'),
  ),
  'version' => '$Rev: 41137 $',
);
