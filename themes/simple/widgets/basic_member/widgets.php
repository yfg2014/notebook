<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='jxwinter';
$setting['name']='会员注册/登录';
$setting['version']='1.0.0';
$setting['stime']='2012-4-10';
$setting['catalog']='系统基础挂件';
$setting['usual'] = '0';
$setting['description']='本挂件无需参数设置，添加本挂件到模板页面对应插槽上即可使用。';
$setting['userinfo']='您只需配置欢迎词即可。';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
?>
