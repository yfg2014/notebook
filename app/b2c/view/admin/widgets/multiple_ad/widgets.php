<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

$setting['author']      = 'ShopEx';
$setting['name']        = '广告-多功能轮播广告';
$setting['version']     = '1.0';
$setting['order']       = '30';
$setting['catalog']     = app::get('b2c')->_('广告相关');
$setting['description'] = app::get('b2c')->_('集合翻页按钮、数字按钮、文字按钮和图片按钮于一体的多功能图片轮播广告插件。强大的自定义功能，可自选按钮样式、位置以及图片轮播方式等；支持为每张广告图片设置链接。');
$setting['template']    = array( 'default.html' => app::get('b2c')->_('默认') );
$setting['stime']       = '2010-11-18 12:13:30';
