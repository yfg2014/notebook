<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_gtype{


    var $column_control = '操作';
    function column_control($row){
        return '<a href=\'index.php?app=b2c&ctl=admin_goods_type&act=set&p[0]='.$row['type_id'].'\'" target="dialog::{ title:\''.app::get('b2c')->_('编辑商品类型').'\', width:800, height:470}">'.app::get('b2c')->_('编辑').'</a>';
    }


}
