<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting['author']='jxwinter';
$setting['name']='当前位置面包屑';
$setting['version']='v1.0.0';
$setting['vary'] = '*';
$setting['catalog']='系统基础挂件';
$setting['usual']    = '1';
$setting['description'] = '本版块（widget）是显示商品分类的路径。没有特殊参数需要设置，添加本版块到模板页面上的相应插槽里即可。';
$setting['userinfo'] ='*'; 
$setting['stime']='2012-04-12';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
