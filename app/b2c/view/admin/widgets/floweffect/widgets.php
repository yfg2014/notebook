<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting['author']='dreamdream';
$setting['name']=app::get('b2c')->_('商品滑动展示');
$setting['version']='135711';

$setting['stime']='2008-8-8';

$setting['catalog']=app::get('b2c')->_('广告相关');

$setting['usual']    = '0';

$setting['description']    = app::get('b2c')->_('本版块与“商品旋转展示”和“商品翻转展示”的使用基本上是一样的。这三个版块(widget)的最大特点就是前台是以flash形式展现的。特别美观。').'<br><br>'.app::get('b2c')->_('如需查看该版块的使用说明，请').'<a href="http://www.shopex.cn/bbs/read.php?tid-64202.html" target="_blank">'.app::get('b2c')->_('点击这里').'</a>。';

$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

?>
