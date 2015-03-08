<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_basic_search(&$setting,&$smarty){
    $data['search_key'] = $GLOBALS['runtime']['search_key'];

    foreach($setting['top_link_title'] as $tk=>$tv){
    $res['search'][$tk]['top_link_title'] = $tv;
    $res['search'][$tk]['top_link_url'] = $setting['top_link_url'][$tk];
    }
    $res['search_key'] = $data['search_key'];
    //  print_r($res);exit;
    return $res;
}
?>
