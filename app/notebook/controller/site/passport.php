<?php
/*
 * 前台登陆控制器
 * 
 * 
 */

class notebook_ctl_site_passport extends site_controller {
    
    //构造函数
    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        //开启session
        kernel::single('base_session')->start();
    }
    
    //默认方法，显示登录页面
   function index() {
        $url = $this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'member'));
        if(isset($_SESSION['username']))
            $this->splash('success',$url,app::get('notebook')->_('您已经是登陆状态，不需要重新登陆'));
        $this->gen_login_form();
        $this->page('member-login.html');
    }

    //登陆验证方法
    private function gen_login_form(){
        $url = $this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'index'));
        //实例化登录方式类
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));  
        $auth->set_appid($this->app->app_id);
        $pagedata['singup_url'] = $this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'reg'));//注册地址
        $pagedata['loginName'] = $_COOKIE['loginName'];
        #设置回调函数地址
        $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'notebook',
        'ctl'=>'site_passport','act'=>'post_login','arg'=>base64_encode($url)))));
        //设置验证后返回地址，已经base64_encode编码
        foreach(kernel::servicelist('passport') as $k=>$passport){
            if($auth->is_module_valid($k)){  //验证登录方式是否开启
            $this->pagedata['passports'][] = array(
                'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(), //登陆框名
                'html'=>$passport->get_login_form($auth, 'notebook', 'member-login.html', $pagedata), //生成登录框
                );
            }
        }
    }

    //生成样验证码
    function gen_vcode(){
        $vcode = kernel::single('base_vcode'); //创建验证码实例
        $vcode->length(4);            //验证码数字长度
        $vcode->verify_key($this->app->app_id);//验证码key
        $vcode->display(); //显示验证码
    }
    
    //显示注册页面
    function reg(){
        $this->page('reg.html');
    }
    
    //注册信息保存
    function regindb(){
        $data = array(
                'member_user'=>$_POST['user'],
                'member_password'=>md5($_POST['password']),
                'member_time'=>time(),
                'member_email'=>$_POST['email'],
            );
        $this->app->model('member')->insert($data);//插入到notebook_member表中
        unset($data['member_user']);
        unset($data['member_password']);
        unset($data['member_time']);
        unset($data['member_email']);

        $data = array(
                'account_type'=>'member',
                'login_name'=>$_POST['user'],
                'login_password'=>md5($_POST['password']),
                'createtime'=>time(),
            );
        $a = app::get('pam')->model('account')->insert($data);//插入到pam_account表中
        unset($data);

        $this->splash('success',$this->gen_url(array('app'=>'notebook',
                'ctl'=>'site_passport','act'=>'index')),app::get('notebook')->_('注册成功！'));
    }
    
    
    function post_login($url=null) {
        $url = base64_decode($url);
        $member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        if($member_id && $_SESSION['last_error'] == ''){
        //此通过Pam验证 如有必要可以再验证是否在member中存在
           $username = $this->app->model('member')->getUsername($member_id);//调用
       $_SESSION['username'] = $username;
       $this->splash('success',$this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'member')),app::get('notebook')->_('登录成功'));
        }else{
             $msg = $_SESSION['error']?$_SESSION['error']:app::get('notebook')->_('页面已过期,操作失败!');
             unset($_SESSION['error']);
             $this->splash('failed',$this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'index')),app::get('notebook')->_($msg));
        }
    }

    function member() {
        $url = $this->gen_url(array('app'=>'notebook','ctl'=>'site_passport','act'=>'index'));
        if(!isset($_SESSION['username']))
            $this->splash('failed',$url,app::get('notebook')->_('你还没登录，请登录！'));
        $this->page('member.html');
    }
    
    //会员退出 
    function logout() {
      unset($_SESSION['account'][$this->type]);
      unset($_SESSION['last_error']);
      unset($_SESSION['username']);
      unset($_SESSION['login_time']);
      $this->splash('success',$this->gen_url(array('app'=>'notebook',
                'ctl'=>'site_passport','act'=>'index')),app::get('notebook')->_('你已退出系统'));
    }

    
    
}
