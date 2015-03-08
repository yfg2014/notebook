<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='jxwinter';
$setting['name']='商店logo挂件';
$setting['version']='1.0.0';
$setting['stime']='2012-4-10';
$setting['catalog']='系统基础挂件';
$setting['usual'] = '0';
$setting['description']='方便的展示商店logo。';
$setting['userinfo']='您只需配置logo图片的文字描述即可。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
