<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package featurecsone
 * @author mabaineng@shopex.cn
 */
class featurecsone_member_sync extends b2c_api_rpc_request
{
    function __construct( &$app )
    {
    	$this->app = &app::get('b2c');
        parent::__construct($this->app);
    }
    
    /**
     * 生成会员
     * 
     * @param int $member_id
     * @return void
     */
    public function createActive($member_id)
    {
    	$arr_callback = array('class'=>'featurecsone_member_sync', 'method'=>'callback');
    	return $this->member_rpc_request($member_id, 'store.user.add', $arr_callback, 'Member Create');
    }
    
    /**
     * 修改会员
     *
     * @param int $member_id
     * @return void
     */
    public function modifyActive($member_id)
    {
    	$arr_callback = array('class'=>'featurecsone_member_sync', 'method'=>'callback');
    	return $this->member_rpc_request($member_id, 'store.user.update', $arr_callback, 'Member Update');
    }
    
    /**
     * 和中心交互
     *
     * @param int $member_id
     * @param string $method
     * @param array $arr_callback
     * @param string $title
     * @return void
     */
    public function member_rpc_request( $member_id, $method, $arr_callback, $title ) {
    	$obj_member = $this->app->model('members');    	
    	$member_sdf = $obj_member->dump($member_id,"*",array(':account@pam'=>array('*'), ':member_lv'=>'*'));

        $sex = $member_sdf['profile']['gender'];
        $sex = $sex == 'male' ? '1' : ($sex == 'female' ? '2' : '');
        
        $status = '';
        if( $member_sdf['state'] == '1' )
        	$status = 'inactive';
        elseif( $member_sdf['disabled'] == 'true' )
        	$status = 'reeze';
        else
        	$status = 'normal';

        $area = $member_sdf['contact']['area'];
        $area_array = array();
        if( $area != '' ){
          $area = explode(':', $area);
          if( isset($area[2]) && is_numeric($area[2]) )
          	$area_array = explode('/', $area[1]);
          else
          	$area_array = array($member_sdf['contact']['area']);
        }
        
        $arr_data = array(
                'userid' => $member_sdf['pam_account']['account_id'],
        		'uid' => $member_sdf['pam_account']['login_name'],
        		'nick_name' => $member_sdf['contact']['name'],
        		'sex' => $sex,
        		'buyer_credit' => '',
        		'seller_credit' => '',
        		'location' => json_encode(array(
                                    'zip' => $member_sdf['contact']['zipcode'],
                                    'address' => $member_sdf['contact']['addr'],
                                    'city' => $area_array[1],
                                    'state' => $area_array[0],
                                    'county' => '',
                                    'district' => $area_array[2],
                                    )),
        		'created' => date('Y-m-d H:i:s', $member_sdf['regtime']),
        		'last_visit' => '',
        		'birthday' => $member_sdf['profile']['birthday'],
        		'promoted_type' => '',
        		'status' => $status,
        		'alipay_bind' => '',
        		'consumer_protection' => '',
        		'alipay_account' => '',
        		'alipay_no' => '',
        		'vip_info' => $member_sdf['member_lv']['name'],
        		'email' => $member_sdf['contact']['email'],
        		//'marital' => $member_sdf['wedlock'],
        		'marital' => '',
        		'mobile' => $member_sdf['contact']['phone']['mobile'],
        		'age' => '',
        );

        // 回朔和请求
        parent::request($method, $arr_data, $arr_callback, $title, 1);
        
        return true;
    }
    
    public function callback($result){
    	return ;
    }
}
