<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_member_create
{
    function __construct( &$app )
    {
        $this->app = $app;
    }
    /**
     * 注册后触发，如满足注册营销的配置条件，则给于相应的优惠：送券｜送积分｜送预存款
     *
     * @param int $member_id
     * @return void
     */
    public function registerActive( $member_id ) {
        $o = kernel::single("proregister_setting");
        $setting = $o->getSetting();
        if( time()<$setting['stime'] ) return true;  //小于开始时间
        if( time()>$setting['etime'] ) return true;  //大于结束时间
        if( !$o->checkStatus() ) return true;        //活动未开启
        
        //送优惠券
        if( $setting['getcoupon'] ) {  
            kernel::single('proregister_promotion_getcoupon')->promotion( $member_id,$setting['getcoupon'] );
        }
        
        //送积分
        if( $setting['getscore'] ) {  
            kernel::single('proregister_promotion_getscore')->promotion( $member_id,$setting['getscore'] );
        }
        
        //送预存款
        if( $setting['getadvance'] ) {  
            kernel::single('proregister_promotion_getadvance')->promotion( $member_id,$setting['getadvance'] );
        }
    }
}
