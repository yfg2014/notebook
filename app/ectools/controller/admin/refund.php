<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_ctl_admin_refund extends desktop_controller{
    
	public function __construct($app)
	{
		parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
	}
	
    public function index(){
        $this->finder('ectools_mdl_refunds',array(

            'title'=>app::get('ectools')->_('退款单'),'allow_detail_popup'=>true,

            'actions'=>array(
                            
                        )
            ));
    }
}
