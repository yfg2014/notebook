<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_frontpage extends site_controller{
    //todo
    protected $member = array();
    function __construct(&$app){
        parent::__construct($app);
    }

    function verify_member(){
        kernel::single('base_session')->start();
        if($this->app->member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
            $obj_member = &$this->app->model('members');
            $data = $obj_member->select()->columns('member_id')->where('member_id = ?',$this->app->member_id)->instance()->fetch_one();
            if($data){
                //登陆受限检测
                $res = $this->loginlimit($this->app->member_id,$redirect);
                if($res){
                    $this->redirect($redirect);
                }else{
                    return true;
                }
            }else{
                $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
            }
        }else{
            $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
        }

    }
    /**
    * loginlimit-登陆受限检测
    *
    * @param      none
    * @return     void
    */
    function loginlimit($mid,&$redirect) {
        $services = kernel::servicelist('loginlimit.check');
        if ($services){
            foreach ($services as $service){
                $redirect = $service->checklogin($mid);
            }
        }
        return $redirect?true:false;
    }//End Function

    function bind_member($member_id){
        $obj_member = app::get('b2c')->model('members');
        $data = $obj_member->dump($member_id,'*',array(':account@pam'=>array('*')));
        $service = kernel::service('pam_account_login_name');
        if(is_object($service)){
            if(method_exists($service,'get_login_name')){
                $data['pam_account']['login_name'] = $service->get_login_name($data['pam_account']);
            }
        }
        $secstr = $obj_member->gen_secret_str($data['member_id']);
        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',$secstr,0);
        $this->set_cookie('loginName',$data['pam_account']['login_name'],0);
        $this->set_cookie('UNAME',$data['pam_account']['login_name'],0);
        $this->set_cookie('MLV',$data['member_lv']['member_group_id'],0);
        $this->set_cookie('CUR',$data['currency'],0);
        $this->set_cookie('LANG',$data['lang'],0);
        $this->set_cookie('S[MEMBER]',$member_id,0);
    }

    public function _check_verify_member($member_id=0)
    {
        if (isset($member_id) && $member_id)
        {
            $arr_member = $this->get_current_member();
            if ($member_id != $arr_member['member_id'])
            {
                $this->begin();
                $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    public function get_current_member()
    {
        if($this->member) return $this->member;
        $obj_members = $this->app->model('members');
		$this->member = $obj_members->get_current_member();
        //登陆受限检测
        if(is_array($this->member)){
            $minfo = $this->member;
            $mid = $minfo['member_id'];
            $res = $this->loginlimit($mid,$redirect);
            if($res){
                $this->redirect($redirect);
            }
        }
        return $this->member;
    }

    function set_cookie($name,$value,$expire=false,$path=null){
        if(!$this->cookie_path){
            $this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
            $this->cookie_life =  $this->app->getConf('system.cookie.life');
        }
        $this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
        $expire = $expire === false ? time()+$this->cookie_life : $expire;
        setcookie($name,$value,$expire,$this->cookie_path);
        $_COOKIE[$name] = $value;
    }

    function check_login(){
        kernel::single('base_session')->start();
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
            return true;
        }
        else{
            return false;
        }
    }
    /*获取当前登录会员的会员等级*/
    function get_current_member_lv()
    {
        kernel::single('base_session')->start();
        if($member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
           $member_lv_row = app::get("pam")->model("account")->db->selectrow("select member_lv_id from sdb_b2c_members where member_id=".intval($member_id));
           return $member_lv_row ? $member_lv_row['member_lv_id'] : -1;
        }
        else{
            return -1;
        }
    }
    function setSeo($app,$act,$args=null){
        $seo = kernel::single('site_seo_base')->get_seo_conf($app,$act,$args);
        $this->title = $seo['seo_title'];
        $this->keywords = $seo['seo_keywords'];
        $this->description = $seo['seo_content'];
        $this->nofollow = $seo['seo_nofollow'];
        $this->noindex = $seo['seo_noindex'];
    }//End Function

    function get_member_fav($member_id=null){
        $obj_member_goods = $this->app->model('member_goods');
		return $obj_member_goods->get_member_fav($member_id);
    }


}
