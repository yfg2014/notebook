<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
    $setting['auther'] = 'kxgsy163@163.com';
    $setting['name'] = app::get('content')->_('APP文章挂件');
    $setting['version']    = '20100607';
    $setting['catalog']    = app::get('content')->_('文章挂件');
    $setting['description']    = '';
    $setting['usual']    = '0';
    $setting['stime'] = '2010-06-20';
    $setting['template']=array(
        'default.html'=>app::get('content')->_('默认')
    );
    $setting['limit'] = 5;          //节点下显文章数
    $setting['lv'] = 2;             //深度
    $setting['styleart'] = 0;       //文章样式统一
    $setting['shownode'] = 1;       //是否显示节点名称
    $setting['node_id']  = 1;       //默认节点
    $selectmaps = kernel::single('content_article_node')->get_selectmaps();
    array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---无---')));
    $setting['selectmaps'] = $selectmaps;
    
    $setting['select_order']['order_type'] = array('pubtime'=>'发布时间');
    $setting['select_order']['order'] = array('asc'=>'升序','desc'=>'降序');
    
    
    //print_r($setting);//exit;
?>
