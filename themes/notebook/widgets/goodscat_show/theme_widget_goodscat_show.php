<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_goodscat_show(&$setting,&$render){

      $data = b2c_widgets::load('GoodsCat')->getGoodsCatMap('',true); //新数据接口
      return $data;
                                        
    }
?>
