<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='Jxwinter';
$setting['version']='v1.0.0';
$setting['order']=18;
$setting['name']='一张广告图片';
$setting['catalog'] = '广告挂件';
$setting['description'] = '可自定义展示一张图片,并且可以为其加上连接';
$setting['usual'] = '0';
$setting['stime'] ='2012-04-10';
$setting['userinfo'] = '图片地址可使用上传图片，也可使用远程图片，更可使用%THEME%/images/***.jpg写法调用模板内部图片。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
