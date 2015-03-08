<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_other_menu($setting,&$smarty){
  
  foreach($setting['top_link_title'] as $tk=>$tv){
        $res[$tk]['top_link_title'] = $tv;
        $res[$tk]['top_link_url'] = $setting['top_link_url'][$tk];
  }
  //  print_r($res);exit;
  return $res;
}
?>
