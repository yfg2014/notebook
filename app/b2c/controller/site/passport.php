<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_site_passport extends b2c_frontpage{
    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
    }

    function index()
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员登录'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
			$url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            $this->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
        }
        if($this->mini_login()) return ;
        if(!isset($_SESSION['next_page']))
        {
            $_SESSION['next_page'] = $_SERVER['HTTP_REFERER'];
        }
        if(strpos($_SESSION['next_page'],'passport')) unset($_SESSION['next_page']);
        $this->gen_login_form();
        $this->set_tmpl('passport');
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');
        foreach(kernel::servicelist('openid_imageurl') as $object)
        {
            if(is_object($object))
            {
                if(method_exists($object,'get_image_url'))
                {
                    $this->pagedata['login_image_url'][] = $object->get_image_url();
                }
            }
        }
        $this->page('site/passport/index.html');
    }

   function gen_vcode()
   {
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key($this->app->app_id);
        $vcode->display();

    }

    function getuname()
    {
        $member = $this->get_current_member();
        $uname = $member['uname'] ? $member['uname'] : '';
        echo $uname;
        exit;
    }
    function login($mini=0)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员登录'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        foreach(kernel::servicelist('openid_imageurl') as $object)
        {
            if(is_object($object))
            {
                if(method_exists($object,'get_image_url'))
                {
                    $this->pagedata['login_image_url'][] = $object->get_image_url();
                }
            }
        }
        if(!isset($_SESSION['next_page']))
        {
            $_SESSION['next_page'] = $_SERVER['HTTP_REFERER'];
        }
        if(strpos($_SESSION['next_page'],'passport')) unset($_SESSION['next_page']);
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            $this->bind_member($_SESSION['account'][pam_account::get_account_type($this->app->app_id)]);
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            if($_GET['mini_passport']==1 || $mini)
            {
                $this->_response->set_http_response_code(404);return;
            }else
            {
                $this->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
            }
        }
        $falg = false;
        if($_GET['mini_passport']==1 || $mini)
        {
            $falg = true;
            $this->pagedata['mini_passport'] = 1;
            $this->pagedata['no_right'] = 1;
        }
        $this->gen_login_form($_GET['mini_passport']);
        $this->set_tmpl('passport');
        if(!$mini)$this->page('site/passport/login.html', $falg);
    }


    private function mini_login()
    {
        if($_GET['mini_passport']==1)
        {
            $this->gen_login_form_mini();
            $this->pagedata["mini_passport"] = 1;
            return true;
        }
        return false;
    }

    function gen_login_form_mini()
    {
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        #设置回调函数地址
        $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login'))));
        foreach(kernel::servicelist('passport') as $k=>$passport)
        {
            if($auth->is_module_valid($k))
            {
                $this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth,$singup_url),
                    );
            }
        }
    }


    function gen_login_form()
    {
        if($_SESSION['next_page'])
        {
            $url = $_SESSION['next_page'];
        }
        else
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        }
        unset($_SESSION['next_page']);
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        $auth->set_appid($this->app->app_id);
        if($_GET['mini_passport'] == 1)
        $pagedata['singup_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup'))."?mini_passport=1";
        else
        $pagedata['singup_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup'));
        $pagedata['lost_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'lost'));
        $pagedata['loginName'] = $_COOKIE['loginName'];
        #设置回调函数地址
        if($_GET['mini_passport']==1)
        {
            $redirect_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login','arg'=>base64_encode($url)))."?mini=1";
            $auth->set_redirect_url(base64_encode($redirect_url));
        }
        else
         $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'post_login','arg'=>base64_encode($url)))));
        foreach(kernel::servicelist('passport') as $k=>$passport)
        {
            if($auth->is_module_valid($k))
            {
                $this->pagedata['passports'][] = array(
                        'name'=>$auth->get_name($k)?$auth->get_name($k):$passport->get_name(),
                        'html'=>$passport->get_login_form($auth, 'b2c', 'site/passport/member-login.html', $pagedata),
                    );
            }
        }
    }


    function post_login_mini()
    {
        $member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        if($member_id)
        {
            $this->bind_member($member_id);
            $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')),app::get('b2c')->_('登录成功'));
        }
        else
        {
            $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),app::get('b2c')->_('登录失败'));
        }
    }



    function post_login($url=null)
    {
        $url = base64_decode($url);
        $mini = $_GET['mini'];
        $member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
        unset($_SESSION['next_page']);
        if($member_id)
        {
            $obj_mem = $this->app->model('members');
            $member_point = $this->app->model('member_point');
            $member_data = $obj_mem->dump($member_id);
            if(!$member_data)
            { //如果pam表存在记录而member表不存在记录
                $this->unset_member();
                if($mini == 1)
                {
                    echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>app::get('b2c')->_("登录失败")));return;
                }
                else
                {
                    $_SESSION['next_page'] = $url;
                    $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),app::get('b2c')->_('登录失败'),'','',true);
                }
            }
            $sdf = $obj_mem->dump($member_id);
            $obj_order = $this->app->model('orders');
            $msg = kernel::single('b2c_message_msg');
            $sdf['order_num'] = count($obj_order->getList('order_id',array('member_id' => $member_id)));
            $sdf['unreadmsg'] = count($msg->getList('*',array('to_id' => $member_id,'has_sent' => 'true','for_comment_id' => 'all','mem_read_status' => 'false')));
            unset($msg);
            if($this->app->getConf('site.level_switch')==1)
            {
                $sdf['member_lv']['member_group_id'] = $obj_mem->member_lv_chk($sdf['member_lv']['member_group_id'],$sdf['experience']);
            }
            if($this->app->getConf('site.level_switch')==0)
            {
                $sdf['member_lv']['member_group_id'] = $member_point->member_lv_chk($member_id,$sdf['member_lv']['member_group_id'],$sdf['score']['total']);
            }
            $obj_mem->save($sdf);
            $this->bind_member($member_id);
            if($mini == 1)
            {
                echo json_encode(array('status'=>'plugin_passport', 'url'=>$url));return;
            }
            else
            {
                $this->app->model('cart_objects')->setCartNum($arr);
				$this->splash('success',$url,app::get('b2c')->_('登录成功'),'','',true);
                exit;
            }
        }
        else
        {
            $msg = $_SESSION['error']?$_SESSION['error']:app::get('b2c')->_('页面已过期,操作失败!');
            unset($_SESSION['error']);
            if($mini == 1)
            {
                echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>$msg));return;
            }
            else
            {
                
                $_SESSION['next_page'] = $url;

                $this->splash('failed',"javascript:changeimg('membervocde');",app::get('b2c')->_($msg),'','',true);
                

            }
        }
    }

    function namecheck()
    {
        $obj_member=&$this->app->model('members');
        $name = trim($_POST['name']);
        if(strlen($name) < 3 )
        {
            echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('长度不能小于3').'</span>';
            exit;
        }
        elseif(strlen($name)>20)
        {
            echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('用户名过长').'</span>';
            exit;
        }
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $name))
        {
            echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('用户名输入有误!').'</span>';
            exit;
        }
        else
        {
            if(!$obj_member->is_exists($name))
            {
                echo '<span class="font-green">&nbsp;'.app::get('b2c')->_('可以使用').'</span>';
                exit;
            }
            else
            {
                echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('已经被占用').'</span>';
                exit;
            }
        }
    }

    function emailcheck()
    {
        $obj_member = $this->app->model('members');
        if($obj_member->is_exists_email($_POST['email']))
        {
            echo '<span class="font-red">&nbsp;'.app::get('b2c')->_('已经被占用').'</span>';exit;
        }
    }

    function verifyCode(){
       $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('LOGINVCODE');
        $vcode->display();
    }
    function verify(){
        $this->begin($this->gen_url('passport','login'));
        $member_model = &$this->app->model('members');
        $verifyCode = app::get('b2c')->getConf('site.register_valide');
        if($verifyCode == "true"){
        if(!base_vcode::verify('LOGINVCODE',intval($_POST['loginverifycode']))){
               $this->splash('failed',$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),app::get('b2c')->_('验证码错误'));

            }
        }
        $rows=app::get('pam')->model('account')->getList('account_id',array('account_type'=>'member','disabled' => 'false','login_name'=>$_POST['login'],'login_password'=>pam_encrypt::get_encrypted_password($_POST['passwd'],pam_account::get_account_type($this->app->app_id),array('login_name'=>$_POST['login']))));
        if($rows){
            $_SESSION['account'][pam_account::get_account_type($this->app->app_id)] = $rows[0]['account_id'];
            $this->bind_member($rows[0]['account_id']);
            $this->end(true,app::get('b2c')->_('登录成功，进入会员中心'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')));
        }else{
            $_SESSION['login_msg']=app::get('b2c')->_('用户名或密码错误');
            $this->end(false,$_SESSION['login_msg'],$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login')));
        }
    }

    function __restore(){
        if($_SESSION['login_info']['post']){
            call_user_func_array(array(&$this,'redirect'),$_SESSION['login_info']['action']);
        }
    }

    function signup($url=null)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员注册'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $login_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login'));
        foreach(kernel::servicelist('api_signup') as $signup)
        {
            if(is_object($signup))
            {
                if($signup->get_status())
                {
                    $signup_url = $signup->get_url();
                    echo "<script>location.href='{$signup_url}';</script>";
                }
            }
        }
        if(!strpos($_SERVER['HTTP_REFERER'],'passport'))
        {
            $_SESSION['signup_next'] = $_SERVER['HTTP_REFERER'];
        }
        $falg = false;
        if($_GET['mini_passport']==1)
        {
            $falg = true;
            $this->pagedata['mini_passport'] = 1;
        }
        $member_model = $this->app->model('members');
        $mem_schema = $member_model->_columns();
        $attr =array();
        foreach($this->app->model('member_attr')->getList() as $item)
        {
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item)
        {
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath)
            {
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1)
                {
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }
            else
            {
                $name = $item['attr_column'];
            }
            if($attr[$key]['attr_type'] == 'select' ||$attr[$key]['attr_type'] == 'checkbox'){
                $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
            }
            $attr[$key]['attr_column'] = $name;
            if($attr[$key]['attr_column']=="birthday"){
                $attr[$key]['attr_column'] = "profile[birthday]";
            }
        }
        $this->pagedata['attr'] = $attr;
        $this->pagedata['next_url'] = $url;
        $this->set_tmpl('passport');
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');
        $this->page("site/passport/signup.html", $falg);

    }

    /**
     * save_attr
     * 保存会员注册信息
     *
     * @access private
     * @return bool
     */
    private function save_attr($member_id=null,$aData,&$msg)
    {
        if(!$member_id)
        {
            $msg = app::get('b2c')->_('注册失败');
            return false;
        }
        $member_model = &$this->app->model('members');
        $aData['pam_account']['account_id'] = $member_id;
        if(!$_POST['profile']['birthday']) unset($aData['profile']['birthday']);
        if($aData['profile']['gender'] == 1){
            $aData['profile']['gender'] = 'male';
        }
        elseif($aData['profile']['gender'] ===0){
            $aData['profile']['gender'] = 'female';
        }
        else{
            $aData['profile']['gender'] = 'no';
        }
        foreach($aData as $key=>$val)
        {
            if(strpos($key,"box:") !== false)
            {
                $aTmp = explode("box:",$key);
                $aData[$aTmp[1]] = serialize($val);
            }
        }

        if($aData['contact']['name']&&!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $aData['contact']['name']))
        {
            $msg = app::get('b2c')->_('姓名包含非法字符');
            return false;
        }
        $obj_filter = kernel::single('b2c_site_filter');
        $aData = $obj_filter->check_input($aData);
        if($member_model->save($aData))
        {
            $msg = app::get('b2c')->_('注册成功');
            return true;
        }
        $msg  = app::get('b2c')->_('注册失败');
        return false;

    }

   /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    function create($next_url=null)
    {
        $mini = $_GET['mini'];
        //$back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'signup'));
        $back_url = null;
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $_POST['pam_account']['login_name']))
        {
            if($mini!=1){
                $this->splash('failed',$back_url,app::get('b2c')->_('用户名包含非法字符'),'','',true);
            }
            else{
                echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>app::get('b2c')->_('用户名包含非法字符')));return;
            }
        }

        $next_url = base64_decode($next_url);
        $member_model = &$this->app->model('members');
        $valideCode = app::get('b2c')->getConf('site.register_valide');
        if($valideCode =='true'){
            if(!base_vcode::verify('LOGINVCODE',intval($_POST['signupverifycode']))){
                if($mini!= 1){
                    $this->splash('failed',$back_url,app::get('b2c')->_('验证码填写错误'),'','',true);
                }
                else{
                    echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>app::get('b2c')->_('验证码填写错误')));return;
                }
            }
        }
        if($_POST['license'] != 'agree'){
            if($mini!= 1){
                $this->splash('failed',$back_url,app::get('b2c')->_('同意注册条款后才能注册'),'','',true);
            }
            else{
                echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>app::get('b2c')->_('同意注册条款后才能注册')));return;
            }
        }
        if(!$member_model->validate($_POST,$msg)){
            if($mini!= 1){
                $this->splash('failed',$back_url,$msg,'','',true);
            }
            else{
                echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>$msg));return;
            }
        }
        $lv_model = &$this->app->model('member_lv');
        $_POST['member_lv']['member_group_id'] = $lv_model->get_default_lv();
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $_POST['currency'] = $arrDefCurrency['cur_code'];
        $_POST['pam_account']['login_name'] = strtolower($_POST['pam_account']['login_name']);
        $_POST['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id);
        $_POST['pam_account']['createtime'] = time();
		$use_pass_data['login_name'] = $_POST['pam_account']['login_name'];
		$use_pass_data['createtime'] = $_POST['pam_account']['createtime'];
		$_POST['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($_POST['pam_account']['login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
        $_POST['reg_ip'] = base_request::get_remote_addr();
        $_POST['regtime'] = time();
        $_POST['contact']['email'] = htmlspecialchars($_POST['contact']['email']);
        $db = kernel::database();
        $db->beginTransaction();

        //--防止恶意修改
        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }
        $arr_colunm = array('regtime','member_id','license','reg_ip','currency','contact','profile','pam_account','forward','member_lv');
        $attr = $this->app->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($_POST as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($_POST[$post_key]);
            }
        }
        //---end

        if($member_model->save($_POST)){
            $member_id = $_POST['member_id'];
             if(!($this->save_attr($member_id,$_POST,$msg))){
                $db->rollBack();
                if($mini!= 1){
                    $this->splash('failed',$back_url,$msg,'','',true);
                }
                else{
                    echo json_encode(array('status'=>'failed', 'url'=>'back', 'msg'=>$msg));return;
                }

            }
            $db->commit();
            $_SESSION['account'][pam_account::get_account_type($this->app->app_id)] = $member_id;
            $this->bind_member($member_id);
            foreach(kernel::servicelist('b2c_save_post_om') as $object) {
                $object->set_arr($member_id, 'member');

                $refer_url = $object->get_arr($member_id, 'member');
            }
            /*注册完成后做某些操作! begin*/
            foreach(kernel::servicelist('b2c_register_after') as $object) {
                $object->registerActive($member_id);
            }
            //增加会员同步 2012-5-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            	$member_rpc_object->createActive($member_id);
            }
            /*end*/
            $data['member_id'] = $member_id;
            $data['uname'] = $_POST['pam_account']['login_name'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['refer_url'] = $refer_url ? $refer_url : '';
            $data['is_frontend'] = true;
            $obj_account=&$this->app->model('member_account');
            $obj_account->fireEvent('register',$data,$member_id);

            if($next_url){
                header("Location: ".$next_url);
            }
            else{
                if($mini!= 1){
                    $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')),app::get('b2c')->_('注册成功'),'','',true);
                }
            else{
                if(isset($_SESSION['signup_next']) && $_SESSION['signup_next']) {
                        $signup_next = $_SESSION['signup_next'] ;
                        unset($_SESSION['signup_next'] );
						echo json_encode(array('status'=>'succ', 'url'=>$signup_next, 'msg'=>app::get('b2c')->_('注册成功')));
                        exit;
                    }
                    else{
						echo json_encode(array('status'=>'succ', 'url'=>$this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index')), 'msg'=>app::get('b2c')->_('注册成功')));
                        exit;
                    }
                }
            }

        }

        $this->splash('failed',$back_url,app::get('b2c')->_('注册失败'),'','',true);
    }

    /*----------- 次要流程 ---------------*/

    function recover()
    {
        $this->path[] = array('title'=>app::get('b2c')->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $obj_member = &$this->app->model('members');
        $rows = app::get('pam')->model('account')->getList('*',array('account_type'=>'member','login_name'=>$_POST['login']));
        $member_id = $rows[0]['account_id'];
        $this->pagedata['data']=$obj_member->dump($member_id);
        $this->pagedata['data']['login_name'] = $rows[0]['login_name'];
        if(empty($member_id)){
            $this->splash('failed','back',app::get('b2c')->_('该用户不存在！'),'','',true);
        }
        if($this->pagedata['data']['disabled'] == "true"){
            $this->splash('failed','back',app::get('b2c')->_('该用户已经放入回收站！'),'','',true);
        }
        $this->set_tmpl('passport');
        $this->display("site/passport/recover.html");
    }

    function sendPSW()
    {
        $this->begin($this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')));
        $rows = app::get('pam')->model('account')->getList('*',array('account_type'=>'member','login_name'=>$_POST['uname']));
        $member_id = $rows[0]['account_id'];
        $obj_member = &$this->app->model('members');
        $data = $obj_member->dump($member_id);
        if(($data['account']['pw_answer']!=$_POST['pw_answer']) || ($data['contact']['email']!=$_POST['email']))
        {
            $this->end(false,app::get('b2c')->_('问题回答错误或当前账户的邮箱填写错误'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),'',true);
        }
        if( $data['pam_account']['account_id'] < 1 )
        {
            $this->end(false,app::get('b2c')->_('会员信息错误'),$this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index')),'',true);
        }
        $objRepass = $this->app->model('member_pwdlog');
        $secret = $objRepass->generate($data['pam_account']['account_id']);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        $sdf = app::get('pam')->model('account')->dump($member_id);
        $new_password = $this->randomkeys(6);
		$use_pass_data['login_name'] = $rows[0]['login_name'];
		$use_pass_data['createtime'] = $rows[0]['createtime'];
        $sdf['login_password'] = pam_encrypt::get_encrypted_password(trim($new_password),pam_account::get_account_type($this->app->app_id),$use_pass_data);
        if(app::get('pam')->model('account')->save($sdf))
        {
            if($this->send_email($_POST['uname'],$data['contact']['email'],$new_password,$member_id))
            {
                $this->end(true,app::get('b2c')->_('密码变更邮件已经发送到').$data['contact']['email'].app::get('b2c')->_('，请注意查收'),$url,'',true);
            }
            else
            {
                $this->end(false,app::get('b2c')->_('发送失败，请与商家联系'),$url,'',true);
            }
        }
        else
        {
            $this->end(false,app::get('b2c')->_('发送失败，请与商家联系'),$url,'',true);
        }
    }

    ####随机取6位字符数
    function randomkeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
     }

    function send_email($login_name,$user_email,$new_password,$member_id)
    {
        $ret = $this->app->getConf('messenger.actions.account-lostPw');
        $ret = explode(',',$ret);
        if(!in_array('b2c_messenger_email',$ret)) return false;
        $data['uname'] = $login_name;
        $data['passwd'] = $new_password;
        $data['email'] = $_POST['contact']['email'];
        $obj_account=&$this->app->model('member_account');
        return $obj_account->fireEvent('lostPw',$data,$member_id);
    }

    function lost()
    {
        $this->path[] = array('title'=>app::get('b2c')->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)])
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            $this->splash('failed',$url,app::get('b2c')->_('请先退出'));
        }
        $this->set_tmpl('passport');
        $this->page("site/passport/lost.html");
    }

    function repass($secret)
    {
        $this->begin($this->gen_url('passport','repass'));
        $objRepass = $this->app->model('member_pwdlog');
        if($objRepass->isValiad($secret))
        {
            $this->pagedata['secret'] = $secret;
            $this->set_tmpl('passport');
        $this->page("site/passport/repass.html");
        }
        else
        {
            $this->end(true,app::get('b2c')->_('参数不正确，请重新申请密码取回'),$this->gen_url('passport','lost'));
        }
    }

    function error()
    {
        $this->unset_member();
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        $this->splash('failed',$back_url,app::get('b2c')->_('本页需要会员才能进入，您未登录或者已经超时'));
    }


    function logout()
    {
        $this->unset_member();
        $this->app->model('cart_objects')->setCartNum($arr);
        $this->redirect(array('app'=>'site','ctl'=>'default','act'=>'index','full'=>1));
    }

    function unset_member()
    {
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }
        $this->app->member_id = 0;
        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',null,time()-3600);
        $this->set_cookie('UNAME','',time()-3600);
        $this->set_cookie('MLV','',time()-3600);
        $this->set_cookie('CUR','',time()-3600);
        $this->set_cookie('LANG','',time()-3600);
        $this->set_cookie('S[MEMBER]','',time()-3600);
        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }
    }

}
