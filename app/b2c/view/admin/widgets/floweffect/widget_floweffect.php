<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function widget_floweffect(&$setting,&$smarty){

    $gimage = &app::get('image')->model('image');
    $o = &app::get('b2c')->model('goods');

    parse_str( $setting['adjunct']['items'][0],$filter);
    $list = $o->getList('*',$filter,0,$setting['limit']?$setting['limit']:10);

    $aTmp = $list;
    foreach($aTmp as $k=>$v){
        if( $v['image_default_id'] ){
        $aPicture =$gimage->dump($v['image_default_id'],'s_url');
        $list[$k]['picture'] = $base_url= app::get('b2c')->base_url().$aPicture['s_url'];
        }
        unset($list[$k]['image_default']);
        unset($list[$k]['thumbnail_pic']);
        unset($list[$k]['pdt_desc']);
    }
    unset($aTmp);
    return array('xml'=>kernel::single('site_utility_xml')->array2xml(array('products'=>&$list)),'count'=>$count,'haystack'=>time());
}

?>
