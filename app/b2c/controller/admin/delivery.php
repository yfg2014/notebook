<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_delivery extends desktop_controller{

    var $workground = 'ectools.workground.order';

    function index(){
        $this->finder('b2c_mdl_delivery',array(
            'title'=>app::get('b2c')->_('发货单'),'allow_detail_popup'=>true,
            'params'=>array(
                'bill_type' => 'delivery',
            )
            ));
    }
    

    function addnew(){
        echo __FILE__.':'.__LINE__;
    }

}
