<?php
class openid_ctl_site_trust extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
    }


    //信任登陆回调函数(token_url)
    function callback(){
            app::get('openid')->setConf('trust_token',$_GET['token']);
            $callback = kernel::single('pam_callback');
            $params['module'] = 'openid_passport_trust';
            $params['type'] = pam_account::get_account_type('b2c');
            $back_url = $this->gen_url(array('app'=>'openid','ctl'=>'site_trust','act'=>'post_login','full'=>1));
            $params['redirect'] = base64_encode($back_url);
            $callback->login($params);
            if($result_m['redirect_url']){
                echo "script>window.location=decodeURIComponent('".$result_m['redirect_url']."');</script>";
                exit;
            }else{
                echo "<script>top.window.location='".$back_url."'</script>";
                exit;
            }
    }


    //pam登陆后处理(保存信任登陆返回的信息)
    function post_login(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        $member_id = $_SESSION['account'][pam_account::get_account_type('b2c')];
        if($member_id){
            $obj_mem = app::get('b2c')->model('members');
            $obj_openid = app::get('openid')->model('openid');
            $member_point = app::get('b2c')->model('member_point');
            $member_data = $obj_mem->dump($member_id);
            $lv_model = app::get('b2c')->model('member_lv');
            $member_lv_id = $lv_model->get_default_lv();
            $result = kernel::single('openid_denglu')->get_user();
            $data = array(
                'member_id' => $member_id,
                'member_lv_id' => $member_lv_id,
                'email' => $result['data']['email'],
                'name'=> empty($result['data']['nickname']) ? $result['data']['realname'] : $result['data']['nickname'],
                'addr' => $result['data']['address'],
                'sex' => $this->gender($result['data']['gender']),
                'trust_name' => empty($result['data']['nickname'])?$result['data']['realname']:$result['data']['nickname'],
            );
             $save= array(
                'member_id' => $member_id,
                'openid' => $result['data']['openid'],
                'provider_code' => $result['data']['provider_code'],
                'provider_openid' => $result['data']['provider_openid'],
                'avatar' => $result['data']['avatar'],
                'email' => $result['data']['email'],
                'address' => $result['data']['address'],
                'gender' => $result['data']['gender'],
                'nickname' => $result['data']['nickname'],
                'realname' => $result['data']['realname'],
            );
            if(!$member_data){
                $data['regtime'] = time(); //注册时间
                if($obj_mem->insert($data)){
                    $obj_openid->insert($save);
                    $this->bind_member($member_id);
                    $this->splash('success',$url,app::get('b2c')->_('登录成功'));
                }else{
                    $this->splash('failed',$url,app::get('b2c')->_('登录失败,请联系商店管理员'));
                }
            }else{
                if($obj_mem->update($data,array('member_id'=>$member_id))){
                    $obj_openid->update($save,array('openid'=>$save['openid']));
                }
                $sdf = $obj_mem->dump($member_id);
                $obj_order = app::get('b2c')->model('orders');
                $msg = kernel::single('b2c_message_msg');
                $sdf['order_num'] = count($obj_order->getList('order_id',array('member_id' => $member_id)));
                $sdf['unreadmsg'] = count($msg->getList('*',array('to_id' => $member_id,'has_sent' => 'true','for_comment_id' => 'all','mem_read_status' => 'false')));
                unset($msg);
                if(app::get('b2c')->getConf('site.level_switch')==1){
                    $sdf['member_lv']['member_group_id'] = $obj_mem->member_lv_chk($sdf['member_lv']['member_group_id'],$sdf['experience']);
                }
                if(app::get('b2c')->getConf('site.level_switch')==0 && app::get('b2c')->getConf('site.level_point') == 1){
                    $sdf['member_lv']['member_group_id'] = $member_point->member_lv_chk($member_id,$sdf['member_lv']['member_group_id'],$sdf['score']['total']);
                }
                $obj_mem->save($sdf);
                $this->bind_member($member_id);
                $this->splash('success',$url,app::get('b2c')->_('登录成功'));
            }
        }else{
            $this->splash('failed',kernel::base_url(1),app::get('b2c')->_('参数错误'));
        }
    }

    function gender($gender){
        if($gender == '0'){
            return '2';
        }elseif($gender == '2'){
            return '0';
        }else{
            return $gender;
        }
    }
}
?>
