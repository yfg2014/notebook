<?php
/**
* Power By ShopEx Jxwinter
* Time  2012-04-10  NO.193

*/

function theme_widget_basic_comment($setting,&$smarty){

    $data = b2c_widgets::load('Comment')->getTopComment($setting['limit']);    //通过数据接口取数据
 
    // print_r ($data) ; exit;

    return $data;
}
?>