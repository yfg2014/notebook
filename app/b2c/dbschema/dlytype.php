<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['dlytype']=array (
  'columns' => 
  array (
    'dt_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('配送ID'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'dt_name' => 
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('配送方式'),
      'width' => 180,
      'editable' => true,
      'in_list' => true,
      'is_title' => true,
      'default_in_list' => true,
    ),
    'has_cod' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('货到付款'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'firstunit'=>
    array(
        'type' => 'number',
        'editable' => false,
        'required' => true,
        'default' => 0,

    ),
    'continueunit'=>array(
        'type' => 'number',
        'editable' => false,
        'required' => true,
        'default' => 0
    ),
    'is_threshold'=>array(
        'type' => 
          array (
            '0' => app::get('b2c')->_('不启用'),
            '1' => app::get('b2c')->_('启用'),
          ),
          'editable' => false,
          'default' => '0',
    ),
    'threshold'=>array(
      'type' => 'longtext',
      'label'=> app::get('b2c')->_('临界值'),
      'required' => false,
      'default' => '',
      'editable' => false,
    ),
    'protect' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('物流保价'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'protect_rate' => 
    array (
      'type' => 'float(6,3)',
      'editable' => false,
    ),
    'minprice' => 
    array (
      'type' => 'float(10,2)',
      'default' => '0.00',
      'required' => true,
      'editable' => false,
    ),
    'setting'=>array(
      'type' => 
      array (
        '0' => '0',
        '1' => '1',
      ),
      'editable' => false,
      'default' => '1',
    ),

    'def_area_fee'=>array(
        'type'=>'bool',
        'default'=>'false',
        'label'=>app::get('b2c')->_('按地区设置配送费用时，是否启用默认配送费用'),
        'required' => false,
        'editable' => false,
    ),

    'firstprice'=>array(
       'type' => 'float(10,2)',
      'default' => '0.00',
      'required' => false,
      'editable' => false,
    ),

    'continueprice'=>array(
      'type' => 'float(10,2)',
      'default' => '0.00',
      'required' => false,
      'editable' => false,    
    ),


    'dt_discount'=>array(
      'type' => 'float(10,2)',
      'default' => '0.00',
      'required' => false,
      'editable' => false,    
    ),
        
    'dt_expressions' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'dt_useexp' => 
    array (
      'type' => 'bool',
      'editable' => false,
      'default' => 'false',
    ),

    'corp_id' => 
    array (
        'type' => 'number',
        'editable' => false,
        'required' => false
    ),

    'dt_status' => 
    array (
      'type' => 
      array (
        '0' => app::get('b2c')->_('关闭'),
        '1' => app::get('b2c')->_('启用'),
      ),
      'label' => app::get('b2c')->_('状态'),
      'width' => 75,
      'editable' => false,
      'default' => '1',
      'in_list' => true,
      'default_in_list' => true,
    ),

    'detail' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'area_fee_conf' => 
    array (
      'type' => 'longtext',
      'required' => false,
      'default' => '',
      'editable' => false,
    ),
    'ordernum' => 
    array (
      'type' => 'smallint(4)',
      'default' => 0,
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

  ),
  'comment' => app::get('b2c')->_('商店配送方式表'),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'version' => '$Rev$',
);
