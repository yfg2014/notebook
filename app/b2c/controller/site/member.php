<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_member extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('会员中心').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('会员中心_').'_'.$shopname;
            $this->description = app::get('b2c')->_('会员中心_').'_'.$shopname;
        }
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verify_member();
        $this->pagesize = 10;
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->load_info();
        /** end **/
    }

    private function get_headmenu()
    {
        /**
         * 会员中心的头部连接
         */
        $arr_main_top = array(
            'member_center'=>array(
                'label'=>app::get('b2c')->_('会员首页'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'index',
                'args'=>array(),
            ),
            'logout'=>array(
                'label'=>app::get('b2c')->_('退出'),
                'app'=>'b2c',
                'ctl'=>'site_passport',
                'link'=>'logout',
                'args'=>array(),
            ),
            'orders_nopayed'=>array(
                'label'=>app::get('b2c')->_('待付款订单'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'orders',
                'args'=>array('nopayed'),
            ),
            'member_notify'=>array(
                'label'=>app::get('b2c')->_('到货通知'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'notify',
                'args'=>array(),
            ),
            'member_comment'=>array(
                'label'=>app::get('b2c')->_('到货通知'),
                'app'=>'b2c',
                'ctl'=>'site_member',
                'link'=>'comment',
                'args'=>array(),
            ),
        );

        $obj_menu_extends = kernel::servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_top_menu'))
                    $obj->get_extends_top_menu($arr_main_top, array('0'=>'b2c', '1'=>'site_member', '2'=>'index'));
            }
        }
        return $arr_main_top;
    }
    private function get_cpmenu(){
        // 判断是否开启预存款
        $mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $payment_info = $mdl_payment_cfgs->getPaymentInfo('deposit');
        $arr_blance = array();
        $arr_recharge_blance = array();
        $arr_point_history = array();
        $arr_point_coupon_exchange = array();
        $this->pagedata['point_usaged'] = "false";

        if ($payment_info['app_staus'] == app::get('ectools')->_('开启'))
        {
            $arr_blance = array('label'=>app::get('b2c')->_('我的预存款'),'app'=>'b2c','ctl'=>'site_member','link'=>'balance');
            $arr_recharge_blance = array('label'=>app::get('b2c')->_('预存款充值'),'app'=>'b2c','ctl'=>'site_member','link'=>'deposit');
        }

        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method != '1')
        {
            $arr_point_history = array('label'=>app::get('b2c')->_('积分历史'),'app'=>'b2c','ctl'=>'site_member','link'=>'point_history');
            $arr_point_coupon_exchange = array('label'=>app::get('b2c')->_('积分兑换优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'couponExchange');
            $this->pagedata['point_usaged'] = "true";
        }

        $arr_bases = array(
            array('label'=>app::get('b2c')->_('交易管理'),
            'mid'=>0,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('我的订单'),'app'=>'b2c','ctl'=>'site_member','link'=>'orders'),
                        $arr_point_history,
                        $arr_point_coupon_exchange,
                        array('label'=>app::get('b2c')->_('我的优惠券'),'app'=>'b2c','ctl'=>'site_member','link'=>'coupon'),
                        $arr_blance,
                        $arr_recharge_blance,
            )
        ),
        array('label'=>app::get('b2c')->_('我的收藏夹'),
            'mid'=>1,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('商品收藏'),'app'=>'b2c','ctl'=>'site_member','link'=>'favorite'),
                        array('label'=>app::get('b2c')->_('到货通知'),'app'=>'b2c','ctl'=>'site_member','link'=>'notify')
            ),
        ),
         array('label'=>app::get('b2c')->_('我的咨询'),
            'mid'=>2,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('评论与咨询'),'app'=>'b2c','ctl'=>'site_member','link'=>'comment'),
            ),
        ),
         array('label'=>app::get('b2c')->_('我的购买'),
            'mid'=>2,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('最近购买的商品'),'app'=>'b2c','ctl'=>'site_member','link'=>'buy'),
            ),
        ),
        array('label'=>app::get('b2c')->_('个人信息管理'),
            'mid'=>4,
            'items'=>array(
                        array('label'=>app::get('b2c')->_('个人信息'),'app'=>'b2c','ctl'=>'site_member','link'=>'setting'),
                        array('label'=>app::get('b2c')->_('修改密码'),'app'=>'b2c','ctl'=>'site_member','link'=>'security'),
                        array('label'=>app::get('b2c')->_('收货地址'),'app'=>'b2c','ctl'=>'site_member','link'=>'receiver'),
            ),
        ),
        );

        $obj_menu_extends = kernel::servicelist('b2c.member_menu_extends');
        if ($obj_menu_extends)
        {
            foreach ($obj_menu_extends as $obj)
            {
                if (method_exists($obj, 'get_extends_menu'))
                    $obj->get_extends_menu($arr_bases, array('0'=>'b2c', '1'=>'site_member', '2'=>'index'));
            }
        }

        return $arr_bases;
    }

    protected function output($app_id='b2c'){
        $this->pagedata['member'] = $this->member;
        $this->pagedata['cpmenu'] = $this->get_cpmenu();
        $this->pagedata['top_menu'] = $this->get_headmenu();
        $this->pagedata['current'] = $this->action;
        if( $this->pagedata['_PAGE_'] ){
            $this->pagedata['_PAGE_'] = 'site/member/'.$this->pagedata['_PAGE_'];
        }else{
           $this->pagedata['_PAGE_'] = 'site/member/'.$this->action_view;
        }
        foreach(kernel::servicelist('member_index') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_member_html')){
                    $aData[] = $service->get_member_html();
                }
            }
        }
        $this->pagedata['app_id'] = $app_id;
        $this->pagedata['_MAIN_'] = 'site/member/main.html';
        $this->pagedata['get_member_html'] = $aData;
        $member_goods = $this->app->model('member_goods');
        $this->pagedata['sto_goods_num'] = $member_goods->get_goods($this->app->member_id);
        $this->set_tmpl('member');
        $this->page('site/member/main.html');
    }

	private function load_info(){
       #获取会员基本信息
        $obj_member = &$this->app->model('members');
		$obj_pam_account = app::get('pam')->model('account');
		$member_info = $obj_member->getList('*',array('member_id'=>$this->app->member_id));
		$pam_account = $obj_pam_account->getList('*',array('account_id'=>$this->app->member_id));
        //$member_sdf = $obj_member->dump($this->app->member_id,"*",array(':account@pam'=>array('*')));
		if (!$member_info||!$pam_account) return;

		/** 重新组合sdf **/
		$member_info[0]['birthday'] = $member_info[0]['b_year'].'-'.$member_info[0]['b_month'].'-'.$member_info[0]['b_day'];
		$member_sdf = array(
			'pam_account'=>array(
				'account_id'=>$pam_account[0]['account_id'],
				'account_type'=>$pam_account[0]['account_type'],
				'login_name'=>$pam_account[0]['login_name'],
				'login_password'=>$pam_account[0]['login_password'],
				'disabled'=>$pam_account[0]['disabled'],
				'createtime'=>$pam_account[0]['createtime'],
			),
			'member_lv'=>array(
				'member_group_id'=>$member_info[0]['member_lv_id'],
			),
			'contact'=>array(
				'name' => $member_info[0]['name'],
				'lastname' => $member_info[0]['lastname'],
				'firstname' => $member_info[0]['firstname'],
				'area' => $member_info[0]['area'],
				'addr' => $member_info[0]['addr'],
				'phone' =>
				array (
				  'mobile' => $member_info[0]['mobile'],
				  'telephone' => $member_info[0]['tel'],
				),
				'email' => $member_info[0]['email'],
				'zipcode' => $member_info[0]['zip'],
			),
			'score'=>array(
				'total'=>$member_info[0]['point'],
				'freeze'=>$member_info[0]['point_freeze'],
			),
			'order_num'=>$member_info[0]['order_num'],
			'refer_id'=>$member_info[0]['refer_id'],
			'refer_url'=>$member_info[0]['refer_url'],
			'b_year'=>$member_info[0]['b_year'],
			'b_month'=>$member_info[0]['b_month'],
			'b_day'=>$member_info[0]['b_day'],
			'profile'=>array(
				'gender'=>$member_info[0]['sex'],
				'birthday'=>$member_info[0]['birthday'],
			),
			'addon'=>$member_info[0]['addon'],
			'wedlock'=>$member_info[0]['wedlock'],
			'education'=>$member_info[0]['education'],
			'vocation'=>$member_info[0]['vocation'],
			'interest'=>$member_info[0]['interest'],
			'advance'=>array(
				'total'=>$member_info[0]['advance'],
				'freeze'=>$member_info[0]['advance_freeze'],
			),
			'point_history'=>$member_info[0]['point_history'],
			'score_rate'=>$member_info[0]['score_rate'],
			'reg_ip'=>$member_info[0]['reg_ip'],
			'vocation'=>$member_info[0]['vocation'],
			'regtime'=>$member_info[0]['regtime'],
			'state'=>$member_info[0]['state'],
			'vocation'=>$member_info[0]['vocation'],
			'pay_time'=>$member_info[0]['pay_time'],
			'biz_money'=>$member_info[0]['biz_money'],
			'fav_tags'=>$member_info[0]['fav_tags'],
			'custom'=>$member_info[0]['custom'],
			'currency'=>$member_info[0]['cur'],
			'vocation'=>$member_info[0]['vocation'],
			'lang'=>$member_info[0]['lang'],
			'unreadmsg'=>$member_info[0]['unreadmsg'],
			'disabled'=>$member_info[0]['disabled'],
			'remark'=>$member_info[0]['remark'],
			'vocation'=>$member_info[0]['vocation'],
			'remark_type'=>$member_info[0]['remark_type'],
			'login_count'=>$member_info[0]['login_count'],
			'experience'=>$member_info[0]['experience'],
			'foreign_id'=>$member_info[0]['foreign_id'],
			'member_refer'=>$member_info[0]['member_refer'],
			'source'=>$member_info[0]['source'],
		);

		/** 访问member相关的meta **/
		$member_meta = dbeav_meta::get_meta_column($obj_member->table_name(1));
		foreach ((array)$member_meta['metaColumn'] as $meta_column){
			$obj_meta_value = new dbeav_meta($obj_member->table_name(1),$meta_column);
			$arr_meta_value = $obj_meta_value->value->db->select('SELECT * FROM '.$obj_meta_value->value->table.' WHERE `mr_id`='.$obj_meta_value->mr_id.' AND `pk`='.$this->app->member_id);
			if ($arr_meta_value)
				$member_sdf['contact'][$meta_column] = $arr_meta_value[0]['value'];
			else
				$member_sdf['contact'][$meta_column] = '';
		}

        $service = kernel::service('pam_account_login_name');
        if(is_object($service)){
            if(method_exists($service,'get_login_name')){
                $member_sdf['pam_account']['login_name'] = $service->get_login_name($member_sdf['pam_account']);
            }
        }
        $this->member['member_id'] = $member_sdf['pam_account']['account_id'];
        $this->member['uname'] =  $member_sdf['pam_account']['login_name'];
        $this->member['name'] = $member_sdf['contact']['name'];
        $this->member['sex'] =  $member_sdf['profile']['gender'];
        $this->member['point'] = $member_sdf['score']['total'];
        $this->member['usage_point'] = $this->member['point'];
        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            // 当前会员拥有的积分
            $obj_extend_point->get_real_point($this->member['member_id'], $this->member['point']);
            // 当前会员实际可以使用的积分
            $obj_extend_point->get_usage_point($this->member['member_id'], $this->member['usage_point']);
        }
        $this->member['experience'] = $member_sdf['experience'];
        $this->member['email'] = $member_sdf['contact']['email'];
        $this->member['member_lv'] = $member_sdf['member_lv']['member_group_id'];
        $this->member['advance'] = $member_sdf['advance'];

        #获取会员等级
        $obj_mem_lv = &$this->app->model('member_lv');
		$levels = $obj_mem_lv->getList('name,disabled',array('member_lv_id'=>$member_sdf['member_lv']['member_group_id']));
        //$levels = $obj_mem_lv->dump($member_sdf['member_lv']['member_group_id']);
        if($levels[0]['disabled']=='false'){
            $this->member['levelname'] = $levels[0]['name'];
        }
        #获取待付款订单数
        $orders = $this->app->model('orders');
        $un_pay_orders = $orders->getList('order_id',array('member_id' => $this->member['member_id'],'pay_status' => 0,'status'=>'active'));
        $this->member['un_pay_orders'] = count($un_pay_orders);
        #获取回复信息
        $mem_msg = $this->app->model('member_comments');
        $object_type = array('msg','discuss','ask');
        $aData = $mem_msg->getList('*',array('to_id' => $this->member['member_id'],'for_comment_id' => 'all','object_type'=> $object_type,'has_sent' => 'true','inbox' => 'true','mem_read_status' => 'false','display' => 'true'));
        unset($mem_msg);
        $this->member['un_readmsg'] = count($aData);

    }

    function pagination($current,$totalPage,$act,$arg='',$app_id='b2c',$ctl='site_member'){ //本控制器公共分页函数
        if (!$arg)
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>array(($tmp = time())))),
                'token'=>$tmp,
                );
        else
        {
            $arg = array_merge($arg, array(($tmp = time())));
            $this->pagedata['pager'] = array(
                'current'=>$current,
                'total'=>$totalPage,
                'link' =>$this->gen_url(array('app'=>$app_id, 'ctl'=>$ctl,'act'=>$act,'args'=>$arg)),
                'token'=>$tmp,
                );
        }
    }

    function get_start($nPage,$count){
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start = ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $aPage['start'] = $start;
        $aPage['maxPage'] = $maxPage;
        return $aPage;
    }

    function setting(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('个人信息'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $member_model = &$this->app->model('members');
        $mem = $member_model->dump($this->app->member_id);
        $cur_model = app::get('ectools')->model('currency');
        $cur = $cur_model->curAll();
        foreach((array)$cur as $item){
           $options[$item['cur_code']] = $item['cur_name'];
        }
        $cur['options'] = $options;
        $cur['value'] = $mem['currency'];
        $this->pagedata['currency'] = $cur;
        $mem_schema = $member_model->_columns();
        $attr =array();
            foreach($this->app->model('member_attr')->getList() as $item){
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item){
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath){
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1){
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                    foreach($a_temp  as $value){
                        $name .= '['.$value.']';
                    }
                }
            }else{
                $name = $item['attr_column'];
            }
            if($item['attr_group'] == 'defalut'){
             switch($attr[$key]['attr_column']){
                    case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                     case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                    case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                    case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                    case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                    case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                    case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                    case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                    case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                    case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                   }
           }
          if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
              $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
              if($item['attr_sdfpath'] == ""){
              $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
              if($attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
              }
          }
          }

          $attr[$key]['attr_column'] = $name;
          if($attr[$key]['attr_column']=="birthday"){
              $attr[$key]['attr_column'] = "profile[birthday]";
          }

          if($attr[$key]['attr_type'] =="select" ||$attr[$key]['attr_type'] =="checkbox"){
              $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
          }

        }
        $this->pagedata['attr'] = $attr;
        $this->pagedata['email'] = $mem['contact']['email'];
        $this->output();
    }

    function save_setting(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>"site_member",'act'=>"setting"));
        $member_model = &$this->app->model('members');
        foreach($_POST as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $_POST[$aTmp[1]] = serialize($val);
            }
        }

        $_POST = $this->check_input($_POST);

        if($member_model->is_exists_email($_POST['contact']['email'],$this->app->member_id)){
            $this->splash('failed',$url , app::get('b2c')->_('邮件已经存在'),'','',true);
        }

        if($_POST['contact']['phone']['mobile'] && !preg_match('/^1[3458][0-9]{9}$/',$_POST['contact']['phone']['mobile'])){
            $this->splash('failed',$url , app::get('b2c')->_('手机输入格式不正确'));
        }

        //--防止恶意修改
        $arr_colunm = array('contact','profile','pam_account','currency');
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

        $_POST['member_id'] = $this->app->member_id;
        if($member_model->save($_POST)){

            //增加会员同步 2012-05-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->modifyActive($_POST['member_id']);
            }

            $this->splash('success', $url , app::get('b2c')->_('提交成功'),'','',true);
        }else{
            $this->splash('failed',$url , app::get('b2c')->_('提交失败'),'','',true);
        }
    }

    /**
     * Member order list datasource
     * @params int equal to 1
     * @return null
     */
    public function orders($pay_status='all', $nPage=1)
    {
         $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('我的订单'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $order = &$this->app->model('orders');
        if ($pay_status == 'all')
        {
            $aData = $order->fetchByMember($this->app->member_id,$nPage-1);
        }
        else
        {
            $order_status = array();
            if ($pay_status == 'nopayed')
            {
                $order_status['pay_status'] = 0;
                $order_status['status'] = 'active';
            }
            $aData = $order->fetchByMember($this->app->member_id,$nPage-1,$order_status);
        }
        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');//print_r($aData['data'][0]['goods_items']);exit;
        foreach($aData['data'] as $k=>$v) {
            foreach($v['goods_items'] as $k2=>$v2) {
                if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aData['data'][$k]['goods_items'][$k2]['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
            }
        }
        $this->pagedata['orders'] = $aData['data'];

        $arr_args = array($pay_status);
        $this->pagination($nPage,$aData['pager']['total'],'orders',$arr_args);
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->output();
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string tpl
     * @return null
     */
    protected function get_order_details(&$aData,$tml='member_orders')
    {
        if (isset($aData['data']) && $aData['data'])
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }

            foreach ($aData['data'] as &$arr_data_item)
            {
                $this->get_order_detail_item($arr_data_item,$tml);
            }
        }
    }

    /**
     * 得到订单列表详细
     * @param array 订单详细信息
     * @param string 模版名称
     * @return null
     */
    protected function get_order_detail_item(&$arr_data_item,$tpl='member_order_detail')
    {
        if (isset($arr_data_item) && $arr_data_item)
        {
            $objMath = kernel::single('ectools_math');
            // 所有的goods type 处理的服务的初始化.
            $arr_service_goods_type_obj = array();
            $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
            foreach ($arr_service_goods_type as $obj_service_goods_type)
            {
                $goods_types = $obj_service_goods_type->get_goods_type();
                $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
            }


            $arr_data_item['goods_items'] = array();
            $obj_specification = $this->app->model('specification');
            $obj_spec_values = $this->app->model('spec_values');
            $obj_goods = $this->app->model('goods');
            if (isset($arr_data_item['order_objects']) && $arr_data_item['order_objects'])
            {
                foreach ($arr_data_item['order_objects'] as $k=>$arr_objects)
                {
                    $index = 0;
                    $index_adj = 0;
                    $index_gift = 0;
                    $image_set = app::get('image')->getConf('image.set');
                    if ($arr_objects['obj_type'] == 'goods')
                    {
                        foreach ($arr_objects['order_items'] as $arr_items)
                        {
                            if (!$arr_items['products'])
                            {
                                $o = $this->app->model('order_items');
                                $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                            }

                            if ($arr_items['item_type'] == 'product')
                            {
                                if ($arr_data_item['goods_items'][$k]['product'])
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k]['product']['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k]['product']['quantity'] = $arr_items['quantity'];

                                $arr_data_item['goods_items'][$k]['product']['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k]['product']['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k]['product']['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k]['product']['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k]['product']['quantity']);
                                $arr_data_item['goods_items'][$k]['product']['amount'] = $arr_items['amount'];
                                $arr_goods_list = $obj_goods->getList('image_default_id', array('goods_id' => $arr_items['goods_id']));
                                $arr_goods = $arr_goods_list[0];
                                if (!$arr_goods['image_default_id'])
                                {
                                    $arr_goods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k]['product']['thumbnail_pic'] = $arr_goods['image_default_id'];
                                $arr_data_item['goods_items'][$k]['product']['link_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'index','arg0'=>$arr_items['goods_id']));;
                                if ($arr_items['addon'])
                                {
                                    $arrAddon = $arr_addon = unserialize($arr_items['addon']);
                                    if ($arr_addon['product_attr'])
                                        unset($arr_addon['product_attr']);
                                    $arr_data_item['goods_items'][$k]['product']['minfo'] = $arr_addon;
                                }else{
                                    unset($arrAddon,$arr_addon);
                                }
                                if ($arrAddon['product_attr'])
                                {
                                    foreach ($arrAddon['product_attr'] as $arr_product_attr)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k]['product']['attr']) && $arr_data_item['goods_items'][$k]['product']['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k]['product']['attr'] = substr($arr_data_item['goods_items'][$k]['product']['attr'], 0, strrpos($arr_data_item['goods_items'][$k]['product']['attr'], $this->app->_(" ")));
                                    }
                                }
                            }
                            elseif ($arr_items['item_type'] == 'adjunct')
                            {
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);


                                if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj])
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']));
                                else
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity'] = $arr_items['quantity'];

                                if (!$arrGoods['image_default_id'])
                                {
                                    $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                }
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['name'] = $arr_items['name'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['quantity']);
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['goods_id'] = $arr_items['goods_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['price'] = $arr_items['price'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['link_url'] = $arrGoods['link_url'];
                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['amount'] = $arr_items['amount'];

                                if ($arr_items['addon'])
                                {
                                    $arr_addon = unserialize($arr_items['addon']);

                                    if ($arr_addon['product_attr'])
                                    {
                                        foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                        }
                                    }
                                }

                                if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'])
                                {
                                    if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")) !== false)
                                    {
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_adj]['attr'], $this->app->_(" ")));
                                    }
                                }

                                $index_adj++;
                            }
                            else
                            {
                                // product gift.
                                if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                                {
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_items['item_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if ($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift])
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['name'] = $arr_items['name'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['price'] = $arr_items['price'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['score'] = intval($arr_items['score']*$arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['quantity']);
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr']) && $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'])
                                    {
                                        if (strpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'] = substr($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], 0, strrpos($arr_data_item['goods_items'][$k][$arr_items['item_type'].'_items'][$index_gift]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                                $index_gift++;
                            }
                        }
                    }
                    else
                    {
                        if ($arr_objects['obj_type'] == 'gift')
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {
                                foreach ($arr_objects['order_items'] as $arr_items)
                                {
                                    if (!$arr_items['products'])
                                    {
                                        $o = $this->app->model('order_items');
                                        $tmp = $o->getList('*', array('item_id'=>$arr_items['item_id']));
                                        $arr_items['products']['product_id'] = $tmp[0]['product_id'];
                                    }

                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                    $str_service_goods_type_obj->get_order_object(array('goods_id' => $arr_items['goods_id'], 'product_id'=>$arr_items['products']['product_id']), $arrGoods,$tpl);

                                    if (!isset($arr_items['products']['product_id']) || !$arr_items['products']['product_id'])
                                        $arr_items['products']['product_id'] = $arr_items['goods_id'];

                                    if ($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']])
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $objMath->number_plus(array($arr_items['quantity'], $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']));
                                    else
                                        $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity'] = $arr_items['quantity'];

                                    if (!$arrGoods['image_default_id'])
                                    {
                                        $arrGoods['image_default_id'] = $image_set['S']['default_image'];
                                    }

                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['name'] = $arr_items['name'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['goods_id'] = $arr_items['goods_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['price'] = $arr_items['price'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['thumbnail_pic'] = $arrGoods['image_default_id'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['point'] = intval($arr_items['score']*$arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['quantity']);
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['nums'] = $arr_items['quantity'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['link_url'] = $arrGoods['link_url'];
                                    $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['amount'] = $arr_items['amount'];

                                    if ($arr_items['addon'])
                                    {
                                        $arr_addon = unserialize($arr_items['addon']);

                                        if ($arr_addon['product_attr'])
                                        {
                                            foreach ($arr_addon['product_attr'] as $arr_product_attr)
                                            {
                                                $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] .= $arr_product_attr['label'] . $this->app->_(":") . $arr_product_attr['value'] . $this->app->_(" ");
                                            }
                                        }
                                    }

                                    if (isset($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr']) && $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'])
                                    {
                                        if (strpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")) !== false)
                                        {
                                            $arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'] = substr($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], 0, strrpos($arr_data_item[$arr_items['item_type'].'_items'][$arr_items['products']['product_id']]['attr'], $this->app->_(" ")));
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            if ($arr_service_goods_type_obj[$arr_objects['obj_type']])
                            {

                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$arr_objects['obj_type']];
                                $arr_data_item['extends_items'][] = $str_service_goods_type_obj->get_order_object($arr_objects, $arr_Goods,$tpl);
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Generate the order detail
     * @params string order_id
     * @return null
     */
    public function orderdetail($order_id=0)
    {
        if (!isset($order_id) || !$order_id)
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'index'));
            $this->end(false, app::get('b2c')->_('订单编号不能为空！'));
        }

        $objOrder = &$this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        $sdf_order = $objOrder->dump($order_id, '*', $subsdf);
        $objMath = kernel::single("ectools_math");

        if(!$sdf_order||$this->app->member_id!=$sdf_order['member_id']){
            $this->_response->set_http_response_code(404);
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            return;
        }
        if($sdf_order['member_id']){
            $member = &$this->app->model('members');
            $aMember = $member->dump($sdf_order['member_id'], 'email');
            $sdf_order['receiver']['email'] = $aMember['contact']['email'];
        }

        // 处理收货人地区
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }

        // 订单的相关信息的修改
        $obj_other_info = kernel::servicelist('b2c.order_other_infomation');
        if ($obj_other_info)
        {
            foreach ($obj_other_info as $obj)
            {
                $this->pagedata['discount_html'] = $obj->gen_point_discount($sdf_order);
            }
        }
        $this->pagedata['order'] = $sdf_order;

        $order_items = array();
        $gift_items = array();
        $this->get_order_detail_item($sdf_order,'member_order_detail');
        $this->pagedata['order'] = $sdf_order;

        /** 去掉商品优惠 **/
        if ($this->pagedata['order']['order_pmt'])
        {
            foreach ($this->pagedata['order']['order_pmt'] as $key=>$arr_pmt)
            {
                if ($arr_pmt['pmt_type'] == 'goods')
                {
                    unset($this->pagedata['order']['order_pmt'][$key]);
                }
            }
        }
        /** end **/

        // 得到订单留言.
        $oMsg = &kernel::single("b2c_message_order");
        $arrOrderMsg = $oMsg->getList('*', array('order_id' => $order_id, 'object_type' => 'order'), $offset=0, $limit=-1, 'time DESC');

        $this->pagedata['ordermsg'] = $arrOrderMsg;
        $this->pagedata['res_url'] = $this->app->res_url;

        // 生成订单日志明细
        //$oLogs =&$this->app->model('order_log');
        //$arr_order_logs = $oLogs->getList('*', array('rel_id' => $order_id));
        $arr_order_logs = $objOrder->getOrderLogList($order_id);

        // 取到支付单信息
        $obj_payments = app::get('ectools')->model('payments');
        $this->pagedata['paymentlists'] = $obj_payments->get_payments_by_order_id($order_id);

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($this->pagedata['order']['payinfo']['pay_app_id']);
        
        //物流跟踪安装并且开启
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        $logisticst_service = kernel::service('b2c_change_orderloglist');
        if(isset($logisticst) && $logisticst == 'true' && $logisticst_service){
            $this->pagedata['services']['logisticstack'] = $logisticst_service;
        }
        
        $this->pagedata['orderlogs'] = $arr_order_logs['data'];
        // 添加html埋点
        foreach( kernel::servicelist('b2c.order_add_html') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'fetchHtml') ) {
                    $services->fetchHtml($this,$order_id,'site/invoice_detail.html');
                }
            }
        }
        $this->output();
    }

    /**
     * 会员中心订单提交页面
     * @params string order id
     * @params boolean 支付方式的选择
     */
    public function orderPayments($order_id, $selecttype=false)
    {
        $objOrder = &$this->app->model('orders');
        $sdf = $objOrder->dump($order_id);
        $objMath = kernel::single("ectools_math");
        if(!$sdf){
            exit;
        }
        $sdf['total'] = $sdf['cur_amount'];
        $sdf['cur_amount'] = $objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));
        $sdf['total_amount'] = $objMath->number_div(array($sdf['cur_amount'], $sdf['cur_rate']));

        $this->pagedata['order'] = $sdf;
        // 货到付款不能进入此页面
        if ($sdf['payinfo']['pay_app_id'] == '-1')
        {
            $this->begin(array('app' => 'b2c','ctl' => 'site_member', 'act'=>'orderdetail', 'arg0'=>$order_id));
            $this->end(false, app::get('b2c')->_('配送方式只支持货到付款'));
        }

        if($selecttype){
            $selecttype = 1;
        }else{
            $selecttype = 0;
        }
        $this->pagedata['order']['selecttype'] = $selecttype;
        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency']);

        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
        {
            if (!$sdf['member_id'])
            {
                if (trim($arrPayments['app_id']) == 'deposit')
                {
                    unset($this->pagedata['payments'][$key]);
                    continue;
                }
            }

            if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
            {
                $arrPayments['cur_money'] = $objMath->formatNumber($this->pagedata['order']['cur_amount'], $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['total_amount'] = $objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
            }
            else
            {
                $arrPayments['cur_money'] = $this->pagedata['order']['cur_amount'];
                $cur_discount = $objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));
                if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
                {
                    if ($this->pagedata['order']['cur_amount'] > 0)
                        $cost_payments_rate = $objMath->number_div(array($arrPayments['cur_money'], $objMath->number_plus(array($this->pagedata['order']['cur_amount'], $this->pagedata['order']['payed']))));
                    else
                        $cost_payments_rate = 0;
                    $cost_payment = $objMath->number_multiple(array($objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }
                else
                {
                    $arrPayments['cur_money'] = $objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $cost_payment = $objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }

                $arrPayments['total_amount'] = $objMath->formatNumber($objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['cur_money'] = $objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
            }
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];

        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'dopayment','arg0'=>'order'));
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html)
        {
            foreach ($obj_order_payment_html as $obj)
            {
                $obj->gen_data($this, $app_id);
            }
        }

        $this->output($app_id);
    }

    function deposit(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('预存款充值'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $oCur = app::get('ectools')->model('currency');
        $currency = $oCur->getDefault();
        $this->pagedata['currencys'] = $currency;
        $this->pagedata['currency'] = $currency['cur_code'];
        $opay = app::get('ectools')->model('payment_cfgs');
        $aOld = $opay->getList('*', array('status' => 'true', 'platform'=>'ispc', 'is_frontend' => true));

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];

        $aData = array();
        foreach($aOld as $val){
        if(($val['app_id']!='deposit') && ($val['app_id']!='offline'))$aData[] = $val;
        }
        $this->pagedata['total'] = $this->member['advance']['total'];
        $this->pagedata['payments'] = $aData;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'balance'));

        $this->output();
    }

    public function balance($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的预存款'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = &$this->app->model('members');
        $mem_adv = &$this->app->model('member_advance');
        $items_adv = $mem_adv->get_list_bymemId($this->app->member_id);
        $count = count($items_adv);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $mem_adv->getList('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'balance');
        $this->pagedata['advlogs'] = $params['data'];
        $data = $member->dump($this->app->member_id,'advance');
        $this->pagedata['total'] = $data['advance']['total'];
        // errorMsg parse.
        $this->pagedata['errorMsg'] = json_decode($_GET['errorMsg']);
        $this->output();
    }


    function pointHistory($nPage=1) {
        $userId = $this->app->member_id;
        $oPointHistory = &$this->app->model('point_history');
        $obj_memberberPoint = &$this->app->model('trading/memberPoint');
        $this->pagedata['historys'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'pointHistory');
        $this->output();
    }

    function favorite($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('商品收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];
        $oImage = app::get('image')->model('image');
        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                $aProduct[$k]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['favorite'] = $aProduct;
        $this->pagination($nPage,$aData['page'],'favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'ajax_del_fav','args'=>array('goods')));
        $this->output();
    }

    function index() {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;
        $oMem = &$this->app->model('members');
        $oRder = &$this->app->model('orders');
        $oMem_lv = $this->app->model('member_lv');
        $this->pagedata['switch_lv'] = $oMem_lv->get_member_lv_switch($this->member['member_lv']);
        $order = $oRder->getList('*',array('member_id' => $this->app->member_id));
        $order_total = count($order);
        $aInfo = $oMem->dump($this->app->member_id);
        $order = &$this->app->model('orders');
        $aData = $order->fetchByMember($this->app->member_id,$nPage-1);
        $this->get_order_details($aData, 'member_latest_orders');

        #获取默认的货币
        $obj_currency = app::get('ectools')->model('currency');
        $arr_def_cur = $obj_currency->getDefault();
        $this->pagedata['def_cur_sign'] = $arr_def_cur['cur_sign'];
        #获取咨询评论回复
        $obj_mem_msg = kernel::single('b2c_message_disask');
        $this->member['unreadmsg'] = $obj_mem_msg->calc_unread_disask($this->member['member_id']);
        $this->pagedata['orders'] = $aData['data'];
        $this->pagedata['pager'] = $aData['pager'];
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_point($this->member['member_id']);
            }
        }
        // 判断是否开启预存款
        $_mdl_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $_payment_info = $_mdl_payment_cfgs->getPaymentInfo('deposit');
        if($_payment_info['app_staus'] == app::get('ectools')->_('开启'))
            $this->pagedata['deposit_status'] = 'true';
        $this->pagedata['member'] = $this->member;
        $this->pagedata['total_order'] = $order_total;
        $this->pagedata['aNum']=$aInfo['advance']['total'];$this->set_tmpl('member');
        $obj_member = &$this->app->model('member_goods');
        $aData_fav = $obj_member->get_favorite($this->app->member_id,$this->member['member_lv']);
        $this->pagedata['favorite'] = $aData_fav['data'];
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $rule = kernel::single('b2c_member_solution');
        $this->pagedata['wel'] = $rule->get_all_to_array($this->member['member_lv']);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->output();
    }

    function del_fav($nGid=null,$delAll=false){
        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,'goods',$nGid,$page)){
            $this->splash('failed', 'back', app::get('b2c')->_('删除错误！'));
            }

        $this->redirect(array('app'=>'b2c','ctl'=>'site_member','act'=>'favorite','args'=>array($page)));
    }

    function ajax_del_fav($object_type='goods'){
        if(!$this->app->member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        }

        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$_POST['gid'],$maxPage)){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            header('Content-Type:text/jcmd; charset=utf-8');

            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'favorite','args'=>array($current_page)));
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"",reload:"'.$reload_url.'"}';exit;
        }

            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
    }
        }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $str_html = $this->fetch('site/member/favorite_items.html');
            echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"'.addslashes(str_replace("\r\n","",$str_html)).'",reload:null}';
        }
    }

    function ajax_fav()
    {
        $object_type = $_POST['type'];
        $nGid = $_POST['gid'];
        $act_type = $_POST['act_type'];
        if($act_type == 'del'){
            if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$nGid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null}';
            }
        }
        else{
            if (!kernel::single('b2c_member_fav')->add_fav($this->app->member_id,$object_type,$nGid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('添加失败！').'",_:null}';
            }else{
                $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('添加成功！').'",_:null}';
            }
        }


    }

    //收件箱
    function inbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $aData['data'] = $row;
        #print_r($row);
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$this->pagesize);

        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'inbox');
        $this->output();
    }

    //草稿箱
    function outbox($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'outbox');
        $this->output();
    }

    //已发送
    function track($nPage=1) {
        $this->get_msg_num();
        $oMsg = kernel::single('b2c_message_msg');
        $row = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'));
        $aData['data'] = $row;
        $aData['total'] = count($row);
        $count = count($row);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $oMsg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagedata['message'] = $params['data'];
        $this->pagedata['total_msg'] = $aData['total'];
        $this->pagination($nPage,$params['page'],'track');
        $this->output();
    }

    function view_msg($nMsgId){
        $objMsg = kernel::single('b2c_message_msg');
        $aMsg = $objMsg->getList('comment',array('comment_id' => $nMsgId,'for_comment_id' => 'all','to_id'=>$this->app->member_id));
        if($aMsg[0]&&($aMsg[0]['author_id']!=$this->member['member_id']&&$aMsg[0]['to_id']!=$this->member['member_id'])){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('对不起，您没有权限查看这条信息！');exit;
        }
        $objMsg->setReaded($nMsgId);
        $objAjax = kernel::single('b2c_view_ajax');
        echo $objAjax->get_html(htmlspecialchars_decode($aMsg[0]['comment']),'b2c_ctl_site_member','view_msg');
        exit;

    }

    function viewMsg($nMsgId){
        $objMsg = kernel::single('b2c_message_msg');
        $objMsg->type = 'msg';

        $nMsgId = kernel::database()->quote($nMsgId);
        $filter = array(
            'filter_sql'=>'(`comment_id`='.$nMsgId.' AND `for_comment_id`="all" AND `to_id`="'.$this->app->member_id.'") OR (`comment_id`='.$nMsgId.' AND `for_comment_id`="all" AND `author_id`="'.$this->app->member_id.'")'
        );

        $aMsg = $objMsg->getList('comment',$filter);
        if($aMsg[0]&&($aMsg[0]['author_id']!=$this->member['member_id']&&$aMsg[0]['to_id']!=$this->member['member_id'])){
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('b2c')->_('对不起，您没有权限查看这条信息！');exit;
        }
        echo htmlspecialchars_decode($aMsg[0]['comment']);
        exit;

    }

    function del_in_box_msg(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'inbox'));
        if(!empty($_POST['delete']))
        {
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['delete'],$this->member['member_id']))
            {
                if($objMsg->delete_msg($_POST['delete'],'inbox'))
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'),'','',true);
                else $this->splash('failed',$url,app::get('b2c')->_('删除失败！'),'','',true);
            }
            else
            {
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }

        }
        else
        {
              $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function del_track_msg() {
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'track'));
        if(!empty($_POST['deltrack'])){
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['deltrack'],$this->member['member_id']))
            {
                if($objMsg->delete_msg($_POST['deltrack'],'track'))
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'),'','',true);
                else $this->splash('failed',$url,app::get('b2c')->_('删除失败！'),'','',true);
            }
            else
            {
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }

        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function del_out_box_msg() {
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'outbox'));
        if(!empty($_POST['deloutbox']))
        {
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['deloutbox'],$this->member['member_id']))
            {
                $objMsg->delete(array('object_type' => 'msg','comment_id' =>$_POST['deloutbox']));
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'));
            }
            else
            {
                 $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }
        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }

    function send($nMsgId=false,$type='') {
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($type == 'reply'){
                $objMsg->setReaded($nMsgId);
                $init['to_uname'] = $init['author'];
                $init['subject'] = "Re:".$init['title'];
                $init['comment'] = '';
                $this->pagedata['is_reply'] = true;
            }
            else{
                $init['subject'] = $init['title'];
            }
            $this->pagedata['init'] = $init;
            $this->pagedata['comment_id'] = $nMsgId;
        }

        $this->output();
    }

    function ajax_send($nMsgId=false,$type='') {
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($type == 'reply'){
                $objMsg->setReaded($nMsgId);
                $init['to_uname'] = $init['author'];
                $init['subject'] = "Re:".$init['title'];
                $init['comment'] = '';
                $this->pagedata['is_reply'] = true;
            }
            else{
                $init['subject'] = $init['title'];
            }
            $this->pagedata['init'] = $init;
            $this->pagedata['comment_id'] = $nMsgId;
        }

        echo $this->fetch('site/member/ajax_send.html');
        exit;
    }

     function ajax_message($nMsgId=false, $status='send') { //给管理员发信件
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            $this->pagedata['init'] = $init;
            $this->pagedata['msg_id'] = $nMsgId;
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
         echo $this->fetch('site/member/ajax_message.html');
        exit;
    }

    function message($nMsgId=false, $status='send') { //给管理员发信件
        $this->get_msg_num();
        if($nMsgId){
            $objMsg = kernel::single('b2c_message_msg');
            $init =  $objMsg->dump($nMsgId);
            if($init['author_id'] == $this->app->member_id)
            {
                $this->pagedata['init'] = $init;
                $this->pagedata['msg_id'] = $nMsgId;
            }
        }
        if($status === 'reply'){
            $this->pagedata['reply'] = 1;
        }
        $this->output();
    }

    function msgtoadmin(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'message'));
        $_POST['msg_to'] = 0;
        if($_POST['subject'] && $_POST['comment']) {
            $objMessage = kernel::single('b2c_message_msg');
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['member_id'] = $this->app->member_id;
            $_POST['uname'] = $this->member[uname];
            $_POST['to_type'] = 'admin';
            $_POST['contact'] = $this->member['email'];
            $_POST['ip'] = $_SERVER["REMOTE_ADDR"];
            $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
            $_POST['subject'] = strip_tags($_POST['subject']);
            $_POST['comment'] = strip_tags($_POST['comment']);
            if( $objMessage->send($_POST) ) {
            if($_POST['has_sent'] == 'false'){
                $this->splash('success',$url,app::get('b2c')->_('保存到草稿箱成功！'),'','',true);
            }else{
                $this->splash('success',$url,app::get('b2c')->_('发送成功！'),'','',true);
            }
            } else {
                $this->splash('failed',$url,app::get('b2c')->_('发送失败！！'),'','',true);
            }
        }
        else {
            $this->splash('failed',$url,app::get('b2c')->_('必填项不能为空！！'),'','',true);
        }
    }

    function send_msg(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'inbox'));
        if($_POST['msg_to'] && $_POST['subject'] && $_POST['comment']) {
            $obj_member = &$this->app->model('members');
            if($to_id = $obj_member->get_id_by_uname($_POST['msg_to'])){
                $objMessage = kernel::single('b2c_message_msg');
                $_POST['member_id'] = $this->app->member_id;
                $_POST['uname'] = $this->member[uname];
                $_POST['has_sent'] = $_POST['has_sent'] == 'false' ? 'false' : 'true';
                $_POST['to_id'] = $to_id;
                if($_POST['comment_id']){
                    //$data['comment_id'] = $_POST['comment_id'];
                    $_POST['comment_id'] = '';//防止用户修改comment_id
                }
                $_POST['subject'] = strip_tags($_POST['subject']);
                $_POST['comment'] = strip_tags($_POST['comment']);
                if( $objMessage->send($_POST) ) {
                    if($_POST['has_sent'] == 'false'){
                         $this->splash('success','back',app::get('b2c')->_('保存到草稿箱成功！！'));
                    }else{
                         $this->splash('success','back',app::get('b2c')->_('发送成功！！'));
                    }
                 } else {
                     $this->splash('failed','back',app::get('b2c')->_('发送失败！！'));
                 }
            } else {
                $this->splash('failed','back',app::get('b2c')->_('找不到你填写的用户！！'));
            }
        } else {
               $this->splash('failed','back',app::get('b2c')->_('必填项不能为空！！'));

        }
    }

    function security($type = ''){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('修改密码'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $obj_member = &$this->app->model('members');
        $this->pagedata['mem'] = $obj_member->dump($this->app->member_id);
        $this->pagedata['type'] = $type;
        $this->output();
    }

    function save_security(){
       $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'security'));
        $obj_member = &$this->app->model('members');
        $result = $obj_member->save_security($this->app->member_id,$_POST,$msg);
        if($result){
            $this->splash('success',$url,$msg,'','',true);
        }
        else{
            $this->splash('failed',$url,$msg,'','',true);
        }
    }

    function save_security_issue(){
       $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'security'));
        $obj_member = &$this->app->model('members');
       if($obj_member->save_security($this->app->member_id,$_POST,$msg)){
           $this->splash('success',$url,$msg,'','',true);
       }
       else{
           $this->splash('failed',$url,$msg,'','',true);
       }
    }

    function receiver(){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('收货地址'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $objMem = &$this->app->model('members');
        $this->pagedata['receiver'] = $objMem->getMemberAddr($this->app->member_id);//print_r($this->pagedata['receiver']);
        $this->pagedata['is_allow'] = (count($this->pagedata['receiver'])<5 ? 1 : 0);
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->output();
    }


    //添加收货地址
    function add_receiver(){
        $obj_member = &$this->app->model('members');
        if($obj_member->isAllowAddr($this->app->member_id)){
            $this->output();
        }else{
            echo app::get('b2c')->_('不能新增收货地址');
        }
    }

    function insert_rec(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if(!$obj_member->isAllowAddr($this->app->member_id)){
             $this->splash('failed',$url,app::get('b2c')->_('不能新增收货地址'),'','',true);
        }
        $aData = $this->check_input($_POST);
        if($obj_member->insertRec($aData,$this->app->member_id,$message)){
             $this->splash('success',$url,$message,'','',true);
            }
        else{
            $this->splash('failed',$url,$message,'','',true);
        }

    }

    //设置和取消默认地址，$disabled 2为设置默认1为取消默认
    function set_default($addrId=null,$disabled=1){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        $member_id = $this->app->member_id;
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($obj_member->set_to_def($addrId,$member_id,$message,$disabled))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        }
    }

    //修改收货地址
    function modify_receiver($addrId=null){
        if(!$addrId)
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($aRet = $obj_member->getAddrById($addrId))
            {
                $aRet['defOpt'] = array('0'=>app::get('b2c')->_('否'), '1'=>app::get('b2c')->_('是'));
                 $this->pagedata = $aRet;
            }else
            {
                $this->_response->set_http_response_code(404);
                $this->_response->set_body(app::get('b2c')->_('修改的收货地址不存在！'));
                exit;
            }
            $this->output();
        }
        else
        {
            echo  app::get('b2c')->_("参数错误");exit;
        }
    }

    function save_rec(){
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($_POST['addr_id'],$this->member['member_id']))
        {
            $aData = $this->check_input($_POST);
            if($obj_member->save_rec($aData,$this->app->member_id,$message)){
                //$this->splash('success',$back_url,app::get('b2c')->_('操作成功'),'','',true);
                echo json_encode(array('status'=>'success', 'msg'=>app::get('b2c')->_('操作成功')));exit;
            }
            else{
                //$this->splash('failed',$back_url,$message,'','',true);
                echo json_encode(array('status'=>'failed', 'msg'=>$message));exit;
            }
        }
        else
        {
            $this->splash('failed',$back_url,app::get('b2c')->_('操作失败'),'','',true);
            //echo json_encode(array('status'=>'failed','msg'=>app::get('b2c')->_('操作失败')));exit;
        }
    }

    //删除收货地址
    function del_rec($addrId=null){
        if(!$addrId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'));
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'receiver'));
        $obj_member = &$this->app->model('members');
        if($obj_member->check_addr($addrId,$this->member['member_id']))
        {
            if($obj_member->del_rec($addrId,$message,$this->member['member_id']))
            {
                $this->splash('success',$url,$message,'','',true);
            }
            else
            {
                $this->splash('failed',$url,$message,'','',true);
            }
        }
        else
        {
            $this->splash('failed', 'back', app::get('b2c')->_('操作失败'),'','',true);
        }


    }

    function score(){
        $this->output();
    }

    function exchange($cpnsId=null) {
        //积分设置的用途
        $site_point_usage = app::get('b2c')->getConf('site.point_usage');
        if($site_point_usage != '1'){
            $this->splash('failed',$url,app::get('b2c')->_('兑换失败,原因:积分只用于抵扣，不能兑换...'),'','',true);
        }
        if(!$cpnsId) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'couponExchange'));
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $memberId = intval($this->app->member_id);//会员id号
        if ($memberId){
            if ($oExchangeCoupon->exchange($cpnsId,$memberId,$this->member['point'],$params)){
                $cpns_point = $params['cpns_point'];
                $member_point = $this->app->model('member_point');
                if($member_point->change_point($this->member['member_id'],-$cpns_point,$msg,'exchange_coupon',2,$memberId,$memberId,'exchange')){
                    $this->splash('success',$url,app::get('b2c')->_('兑换成功'),'','',true);
                }
                else{
                    $oExchangeCoupon->exchange_delete($params);
                    $this->splash('failed',$url,$msg,'','',true);
                }

            }
            }
        else{
            $this->splash('failed',$url,app::get('b2c')->_('没有登录'),'','',true);
        }
        $this->splash('failed',$url,app::get('b2c')->_('兑换失败,原因:积分不足/兑换购物券无效...'),'','',true);
     }

    function download_ddvanceLog(){
        $charset = kernel::single('base_charset');
        $obj_member = &$this->app->model('member_advance');
        $aData = $obj_member->get_list_bymemId($this->app->member_id);
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=advance_".date("Ymd").".csv");
        $out = app::get('b2c')->_("事件,存入金额,支出金额,当前余额,时间\n");
        foreach($aData as $v){
            $out .= $v['message'].",".$v['import_money'].",".$v['explode_money'].",".$v['member_advance'].",".date("Y-m-d H:i",$v['mtime'])."\n";
        }
        echo $charset->utf2local($out,'zh');
        exit;
    }

    /**
     * 添加留言
     * @params string order_id
     * @params string message type
     */
    public function add_order_msg( $order_id , $msgType = 0 ){
        $objOrder = $this->app->model('orders');
        $aOrder = $objOrder->dump($order_id );

        $timeHours = array();
        for($i=0;$i<24;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeHours[$v] = $v;
        }
        $timeMins = array();
        for($i=0;$i<60;$i++){
            $v = ($i<10)?'0'.$i:$i;
            $timeMins[$v] = $v;
        }
        $this->pagedata['orderId'] = $order_id;
        $this->pagedata['msgType'] = $msgType;
        $this->pagedata['timeHours'] = $timeHours;
        $this->pagedata['timeMins'] = $timeMins;

        $this->output();
    }

    /**
     * 订单留言提交
     * @params null
     * @return null
     */
    public function toadd_order_msg()
    {
        $this->begin();

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $_POST['to_type'] = 'admin';
        $_POST['author_id'] = $this->member['member_id'];
        $_POST['author'] = $this->member['uname'];
        $is_save = true;
        $obj_order_message = kernel::single("b2c_order_message");
        if ($obj_order_message->create($_POST))
            $this->end(true,app::get('b2c')->_('留言成功!'));
        else
            $this->end(false,app::get('b2c')->_('留言失败!'));
    }

    function point_history($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('我的积分'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $member = $this->app->model('members');
        $member_point = $this->app->model('member_point');
        $obj_gift_link = kernel::service('b2c.exchange_gift');
        if ($obj_gift_link)
        {
            $this->pagedata['exchange_gift_link'] = $obj_gift_link->gen_exchange_link();
        }
        // 额外的会员的信息 - 冻结积分、将要获得的积分
        $obj_extend_point = kernel::servicelist('b2c.member_extend_point_info');
        if ($obj_extend_point)
        {
            foreach ($obj_extend_point as $obj)
            {
                $this->pagedata['extend_point_html'] = $obj->gen_extend_point($this->member['member_id']);
            }
        }
        $data = $member->dump($this->app->member_id,'*',array('score/event'=>array('*')));
        $count = count($data['score']['event']);
        $aPage = $this->get_start($nPage,$count);
        $params['data'] = $member_point->getList('*',array('member_id' => $this->member['member_id']),$aPage['start'],$this->pagesize);
        $params['page'] = $aPage['maxPage'];
        $this->pagination($nPage,$params['page'],'point_history');
        $this->pagedata['total'] = $data['score']['total'];
        $this->pagedata['historys'] = $params['data'];
        $this->output();
    }

    /*
        过滤POST来的数据,基于安全考虑,会把POST数组中带HTML标签的字符过滤掉
    */
    function check_input($data){
        $aData = $this->arrContentReplace($data);
        return $aData;
    }

    function arrContentReplace($array){
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] =     $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
        }
        return $array;
    }

    function set_read($comment_id=null,$object_type='ask'){
        if(!$comment_id) return ;
        $comment = kernel::single('b2c_message_disask');
        $comment->type = $object_type;
        $reply_data = $comment->getList('comment_id',array('for_comment_id' => $comment_id));
        foreach($reply_data as $v){
            $comment->setReaded($v['comment_id']);
        }

    }

    function get_msg_num(){
        $this->pagedata['controller'] = "comment";
        $msg = kernel::single('b2c_message_msg');
        if($this->member['member_id']){
            $row = $msg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
            $this->pagedata['inbox_num'] = count($row)?count($row):0;
            $row = $msg->getList('*',array('author_id' => $this->app->member_id,'has_sent' => 'true','track' => 'true'));
            $this->pagedata['track_num'] = count($row)?count($row):0;
             $row = $msg->getList('*',array('has_sent' => 'false','author_id' => $this->app->member_id));
            $this->pagedata['outbox_num'] = count($row)?count($row):0;
            unset($msg);
        }
        else{
            return null;
        }
    }

    function comment($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('评论与咨询'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $comment = kernel::single('b2c_message_disask');
        $goods = $this->app->model('goods');
        $objPoint = $this->app->model('comment_goods_point');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,'discuss');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach((array)$aData['data'] as $k => $v){
                $goods_data = $goods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
                if(!$goods_data) continue;
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                    $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
                }
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                    $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
                $v['name'] = $goods_data[0]['name'];
                $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
                $v['udfimg'] = $goods_data[0]['udfimg'];
                $v['image_default_id'] = $goods_data[0]['image_default_id'];
                $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'comment');
        $this->output();
    }

    function ask($nPage=1){
        $this->pagedata['controller'] = "comment";
        $comment = kernel::single('b2c_message_disask');
        $goods = $this->app->model('goods');
        $objPoint = $this->app->model('comment_goods_point');
        $aData = $comment->get_member_disask($this->app->member_id,$nPage,'ask');
        $comment=array();
        $imageDefault = app::get('image')->getConf('image.set');
        $oImage = app::get('image')->model('image');
        foreach($aData['data'] as $k => $v){
                $goods_data = $goods->getList('name,thumbnail_pic,udfimg,image_default_id',array('goods_id'=>$v['type_id']));
                if(!$goods_data) continue;
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['image_default_id']))){
                    $goods_data[0]['image_default_id'] = $imageDefault['S']['default_image'];
                }
                if(!$oImage->getList('image_id', array('image_id'=>$goods_data[0]['thumbnail_pic']))) {
                    $goods_data[0]['thumbnail_pic'] = $imageDefault['S']['default_image'];
                }
                $v['goods_point'] = $objPoint->get_single_point($v['type_id']);
                $v['name'] = $goods_data[0]['name'];
                $v['thumbnail_pic'] = $goods_data[0]['thumbnail_pic'];
                $v['udfimg'] = $goods_data[0]['udfimg'];
                $v['image_default_id'] = $goods_data[0]['image_default_id'];
                $comment[] = $v;
        }
        $this->pagedata['commentList'] = $comment;
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagination($nPage,$aData['page'],'ask');
        $this->output();
    }

     ##缺货登记
    function notify($nPage=1){
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('到货通知'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $oMem = &$this->app->model('member_goods');
        $aData = $oMem->get_gnotify($this->app->member_id,$this->member['member_lv'],$nPage);
        $this->pagedata['notify'] = $aData['data'];
        $this->pagination($nPage,$aData['page'],'notify');
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['setting'] = $setting;
        $this->pagedata['member_id'] = $this->app->member_id;
        $this->output();
    }

    ##删除缺货登记
    function del_notify($pid=null,$member_id=null){
        if(!$pid || !$member_id) $this->splash('failed', 'back', app::get('b2c')->_('参数错误'),'','',true);
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'notify'));
        $member_goods= $this->app->model('member_goods');
        if($member_goods->getList('gnotify_id',array('product_id' => $pid,'member_id' => $this->member['member_id'])))
        {
            if($member_goods->delete(array('product_id'=>$pid,'member_id'=>$this->member['member_id']))){
                $this->splash('success',$url,app::get('b2c')->_('删除成功'),'','',true);
            }
            else{
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
            }
        }
        else
        {
            $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }

    }

    function coupon($nPage=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('我的优惠券'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($this->member['member_id']);
        if ($aData) {
            foreach ($aData as $k => $item) {
                if ($item['coupons_info']['cpns_status']==1) {
                    $member_lvs = explode(',',$item['time']['member_lv_ids']);
                    if (in_array($this->member['member_lv'],(array)$member_lvs)) {
                        $curTime = time();
                        if ($curTime>=$item['time']['from_time'] && $curTime<$item['time']['to_time']) {
                            if ($item['memc_used_times']<$this->app->getConf('coupon.mc.use_times')){
                                if ($item['coupons_info']['cpns_status']){
                                    $aData[$k]['memc_status'] = app::get('b2c')->_('可使用');
                                }else{
                                    $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券已作废');
                                }
                            }else{
                                $aData[$k]['memc_status'] = app::get('b2c')->_('本优惠券次数已用完');
                            }
                        }else{
                            $aData[$k]['memc_status'] = app::get('b2c')->_('还未开始或已过期');
                        }
                    }else{
                        $aData[$k]['memc_status'] = app::get('b2c')->_('本级别不准使用');
                    }
                }else{
                    $aData[$k]['memc_status'] = app::get('b2c')->_('此种优惠券已取消');
                }
            }
        }
        $this->pagedata['mc_use_times'] = $this->app->getConf('coupon.mc.use_times');
        $this->pagedata['coupons'] = $aData;
        $this->output();
    }

    function couponExchange($page=1) {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
         $this->path[] = array('title'=>app::get('b2c')->_('积分兑换优惠卷'),'link'=>'#');
          $GLOBALS['runtime']['path'] = $this->path;
        $pageLimit = 10;
        $oExchangeCoupon = kernel::single('b2c_coupon_mem');
        $filter = array('ifvalid'=>1);
        $site_point_usage = $this->app->getConf('site.point_usage');
        $this->pagedata['site_point_usage'] = ($site_get_policy_method != '1' && $site_point_usage == '1') ? 'true' : 'false';
        if ($aExchange = $oExchangeCoupon->get_list()) {
            $this->pagedata['couponList'] = $aExchange;
        }
        $this->output();
    }

    function buy($page=1){
        $list_listnum = $this->pagesize;
        $order = $this->app->model('orders');
        $order_items = $this->app->model('order_items');
        $goods = $this->app->model('goods');
        $member_comment = $this->app->model('member_comments');
        $oImage = app::get('image')->model('image');
        if($page == 1 || !$_SESSION['order_goods_data']){
            $row = $order->getList('order_id,createtime',array('member_id' => $this->app->member_id,'createtime|than' => time()-30*24*3600,'pay_status' => 1));
            $falg = array();
            foreach($row as $val){
            $data = $order_items->getList('goods_id',array('order_id' => $val['order_id']));
                foreach($data as $v){
                    if(!in_array($v['goods_id'],$falg)){
                        $result = current($goods->getList('name,goods_id,thumbnail_pic,udfimg,marketable,view_count,view_w_count,buy_count,buy_w_count,image_default_id,comments_count',array('goods_id'=>$v['goods_id'])));
                        $result['buytime'] = $val['createtime'];
                        if($row = $member_comment->getList('comment_id',array('for_comment_id' => 0,'object_type' => 'discuss','type_id' => $v['goods_id'],'author_id' => $this->app->member_id)))
                        $result['is_discuss'] = 'true';
                        else
                        $result['is_discuss'] = 'false';
                        if(!$oImage->getList("image_id",array('image_id'=>$result['image_default_id']))){
                            $result['image_default_id'] = '';
                        }
                        if(!$oImage->getList("image_id",array('image_id'=>$result['thumbnail_pic']))) {
                            $result['thumbnail_pic'] = '';
                        }
                        $aData[] = $result;
                    }
                    $falg[] = $v['goods_id'];
                }
            }
            $_SESSION[$this->app->member_id]['order_goods_data'] = $aData;
        }
        $total = count($_SESSION[$this->app->member_id]['order_goods_data'])/$list_listnum;
        $count = count($_SESSION[$this->app->member_id]['order_goods_data']);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $this->pagination($page,$total,'buy');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['aData'] = array_slice((array)$_SESSION[$this->app->member_id]['order_goods_data'],$start,$list_listnum);
        $this->pagedata['switch_discuss'] = $this->app->getConf('comment.switch.discuss');
        $this->output();
    }

}
