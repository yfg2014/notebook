<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * ctl_cart
 *
 * @uses b2c_frontpage
 * @package
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class b2c_ctl_site_cart extends b2c_frontpage{

    var $customer_template_type='cart';
    var $noCache = true;
    var $show_gotocart_button = true;

    public function __construct(&$app) {

        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('购物车').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('购物车_').'_'.$shopname;
            $this->description = app::get('b2c')->_('购物车_').'_'.$shopname;
        }

        $this->set_tmpl('cart');
        $this->mCart = $this->app->model('cart');
        $this->set_no_store();

        kernel::single('base_session')->start();

        $this->member_status = $this->check_login();

        $this->objMath = kernel::single("ectools_math");
        $this->pagedata['res_url'] = $this->app->res_url;

        $this->mCart->unset_data();
    }

    public function index(){
        $GLOBALS['runtime']['path'][] = array('link'=>$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),'title'=>'购物车');
        if( !isset($this->guest_enabled) )
            $this->guest_enabled = $this->app->getConf('security.guest.enabled');
        $this->pagedata['guest_enabled'] = $this->guest_enabled;

        $this->_common(1);
        $this->_response->set_header('Cache-Control','no-store');
        $current_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));
        $this->pagedata['go_back_link'] = ($_SERVER['HTTP_REFERER'] != $current_url) ? $_SERVER['HTTP_REFERER'] : 'javascript:window.history.go(-1);';
        setcookie('cart[go_back_link]', $this->pagedata['go_back_link'], 0, kernel::base_url() . '/');
        $this->pagedata['aCart']['subtotal_prefilter'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['discount_amount_prefilter']));
        $this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
        $this->pagedata['checkout_link'] = $this->gen_url( array('app'=>'b2c','act'=>'checkout','ctl'=>'site_cart') );
        $cart_json = kernel::single('b2c_cart_json');
        $currency = app::get('ectools')->model('currency');
        $Default_currency = $currency->getDefault();
        $this->pagedata['currency'] = $Default_currency['cur_sign'];
        $cur = app::get('ectools')->model('currency');
        //货币格式输出
        $ret = $cur->getFormat();
        $ret =array(
            'decimals'=>$this->app->getConf('system.money.decimals'),
            'dec_point'=>$this->app->getConf('system.money.dec_point'),
            'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
            'sign' => $ret['sign']
        );
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['json'] = $cart_json->get_json($this->pagedata);
        $this->page('site/cart/index.html');
    }

    // 添加
    public function add($type='goods') {
        /**
         * 处理信息和验证过程
         */
        $arr_objects = array();
        if ($objs = kernel::servicelist('b2c_cart_object_apps'))
        {
            foreach ($objs as $obj)
            {
                if ($obj->need_validate_store()){
                    $arr_objects[$obj->get_type()] = $obj;
                }
            }
        }

        $data = $this->_request->get_params(true);

        /**
         * 处理校验各自的数据是否可以加入购物车
         */
        if (!$arr_objects[$type])
        {
            $msg = app::get('b2c')->_('加入购物车类型错误！');
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            } else {
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
            }
        }
        if (method_exists($arr_objects[$type], 'get_data'))
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                if($_POST['mini_cart']){
                    echo json_encode( array('error'=>$msg) );exit;
                } else {
                    $fail_url = $arr_objects[$type]->get_fail_url($data);
                    $this->begin($fail_url);
                    $this->end(false, $msg);
                }
            }
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                if($_POST['mini_cart']){
                    echo json_encode( array('error'=>$msg) );exit;
                } else {
                    $fail_url = $arr_objects[$type]->get_fail_url($data);
                    $this->begin($fail_url);
                    $this->end(false, $msg);
                }
            }
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            } else {
                $fail_url = $arr_objects[$type]->get_fail_url($data);
                $this->begin($fail_url);
                $this->end(false, $msg);
            }
        }
        /** end **/

        //快速购买
        if(isset($aData[1]) && $aData[1] == 'quick' && empty($this->member_status)) $this->redirect(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));

        if(!$obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg)){
                if($_POST['mini_cart']){
                    echo json_encode( array('error'=>$msg) );exit;                    
                } else {
                    $fail_url = $arr_objects[$type]->get_fail_url($data);
                    $this->begin($fail_url);
                    $this->end(false, $msg);

                }
            } else {
                if(isset($aData[1]) && $aData[1] == 'quick') {
                    if(!$this->member_status && !$_COOKIE['ST_ShopEx-Anonymity-Buy']){
                        $this->page('site/cart/loginbuy_fast.html', true);
                        return;
                    }

                    $this->checkout();
                }else{
                    if($_POST['mini_cart']){
                        $arr = $this->app->model("cart")->get_objects();
                        $temp = $arr['_cookie'];

                        $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                        $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                        $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];
                        $this->page('site/cart/mini_cart.html', true);
                        return;
                    }
                // coupon
                if($aData[0]=='coupon'){
                    if (!$data['response_type']){
                        $url = array('app'=>'b2c', ctl=>'site_cart','act'=>'checkout');
                        $this->redirect( $url );
                    }else{
                        $this->_common(1);
                        $arr_json_data = array(
                            'success'=>app::get('b2c')->_('优惠券使用成功！'),
                            'data'=>$this->pagedata['aCart']['object']['coupon'],
                            'md5_cart_info'=>kernel::single('b2c_cart_objects')->md5_cart_objects(),
                        );
                        echo json_encode($arr_json_data);exit;
                    }
                }
                else {
                        $url = array('app'=>'b2c', ctl=>'site_cart');
                    $this->redirect( $url );
                }
            }
        }
    }


    // 修改 - 完全数据
    public function update() {
        $msg = "";
        $this->update_cart($msg);
        /** 完全数据 **/
        $this->_cart_main($msg);
        }


    /** 最小数据的修改对应的控制器 **/
    public function updateMiniCart() {
        $this->update_cart($msg,'mini');
        /** 最小数据 **/
        $this->_cart_main($msg,'mini');
    }

    /** 中等数据购物车修改控制器方法 **/
    public function update_middle() {
        $this->update_cart($msg,'middle');
        /** 中等数据 **/
        $this->_cart_main($msg,'middle');
    }

    /**
     * 修改购物车的方法-私有，只处理数据
     * @param string error message
     * @param string json type
     * @return null
     */
    private function update_cart(&$msg='',$type='all') {
        $aParams = $this->_request->get_params(true);
        $mCartObject = $this->app->model('cart_objects');
        $aCart = $this->mCart->get_basic_objects();
        foreach($aCart as $row)
        {
             if( isset($aParams['modify_quantity'][$row['obj_ident']]) )
             {
                $update_row = $row;
                break;
             }
        }
        if($aParams['modify_quantity'] && is_array($aParams['modify_quantity'])){
            $view = "";
            switch ($type){
                case 'mini':
                    $view = "site/cart/view.html";
                break;
                case 'middle':
                    $view = "site/cart/middle_index.html";
                break;
                case 'all':
                default:
                    $view = "site/cart/index.html";
                break;
            }

            foreach ($aParams['modify_quantity'] as $obj_ident=>$arr_object){
             $temp = $aParams['modify_quantity'][$obj_ident];
             $flag = $this->_v_cart_object($temp, $update_row, false);
                $arr_object['quantity'] = (float)$arr_object['quantity'];
                $_flag = $mCartObject->update_object( $aParams[0],$obj_ident,$arr_object );

                        if( is_array($_flag) && isset($_flag['status']) && isset($_flag['msg']) ) {
                            if( $_flag['status'] ) {
                                $this->ajax_update = true;
                        $this->update_obj_ident['ident'] = $obj_ident; //值不同。修改 失败直接推出循环
                        $msg = $_flag['msg'];
                            } else {
                        $error_json = array(
                            'error'=>$_flag['msg'],
                        );
                        //echo json_encode($error_json);exit;
                        $this->pagedata = $error_json;
                        $this->page($view);
                            }
                        } else {
                            if( !$_flag ) {
                        $error_json = array(
                            'error'=>app::get('b2c')->_('购物车修改失败！'),
                        );
                        //echo json_encode($error_json);exit;
                        $this->pagedata = $error_json;
                        $this->page($view);
                            } else {
                                $this->ajax_update = true;
                        $this->update_obj_ident['ident'] = $obj_ident; //值不同。修改 失败直接推出循环
                        $msg = app::get('b2c')->_('购物车修改成功！');
                        }
                    }
                }
        }
    }


    // 删除&清空
    public function remove() {
        $msg = '';
        $this->remove_cart_object($msg);

        $this->_cart_main($msg);
    }

    // 删除优惠券 优惠券移动到checkout
    public function removeCartCoupon() {
        $msg = '';
        $this->remove_cart_object($msg,'mini');

        $this->_cart_main($msg,'mini');
    }

    // 删除&清空 迷你购物车删除商品接口
    public function removeMiniCart() {
        $msg = '';
        $this->remove_cart_object($msg,'mini');

        $this->_cart_main($msg,'mini');
    }

    // 返回中等数据的删除机制
    public function remove_middle() {
        $msg = '';
        $this->remove_cart_object($msg,'middle');

        $this->_cart_main($msg,'middle');
    }

    /**
     * 删除购物车的方法
     * @param string message
     * @param string json type
     * @return null
     */
    private function remove_cart_object(&$msg='',$type='all'){
        $aParams = $this->_request->get_params(true);
        $mCartObject = $this->app->model('cart_objects');
        $this->ajax_html = true;  //用于返回页面识别。当无商品是跳转至cart_empty

        $view = "";
        switch ($type){
            case 'mini':
                $view = "site/cart/view.html";
            break;
            case 'middle':
                $view = "site/cart/middle_index.html";
            break;
            case 'all':
            default:
                $view = "site/cart/index.html";
            break;
    }

        if ($aParams[0] == 'coupon'){
            $ident = $aParams['cpn_ident'];

            $flag = $mCartObject->remove_object('coupon', $ident, $msg);
            if( !$flag ){
                $error_json = array(
                    'error'=>$msg,
                );
                $this->pagedata = $error_json;
                $this->page($view);
            }
        }else{
        // 清空购物车
        if($aParams[0] == 'all' || empty($aParams['modify_quantity'])) {
            $obj_type = null;
                if (!$mCartObject->remove_object('', null, $msg)){
                    // 不带入参清空所有的
                    $error_json = array(
                        'error'=>$msg,
                    );
                    $this->pagedata = $error_json;
                    $this->page($view);
                }
        } else {
                // 删除单一商品.
                if($aParams['modify_quantity'] && is_array($aParams['modify_quantity'])){
                    foreach ($aParams['modify_quantity'] as $obj_ident=>$arr_object){
                        if ($arr_object['quantity']){
                            // 删除整个商品对象.
                            if (!$mCartObject->remove_object($aParams[0], $obj_ident, $msg)){
                                $error_json = array(
                                    'error'=>$msg,
                                );
                                $this->pagedata = $error_json;
                                if($type=='mini'){
                                    $this->view();
                                }else{
                                    $this->page($view);
                                }
                    }
                        }else{
                            // 删除购物车对象中的附属品，配件和赠品等.
                            foreach ($arr_object as $obj_type=>$arr_quantity){
                                if (!$mCartObject->remove_object_part($obj_type, $obj_ident, $arr_quantity, $msg)){
                                    $error_json = array(
                                        'error'=>$msg,
                                    );
                                    $this->pagedata = $error_json;
                                    $this->page($view);
            }
        }
    }
                        }
                    }
                }
        }
    }

    public function remove_cart_to_disabled() {
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident]['gift'][$_product_id] = 'true';
        $this->_response->set_http_response_code(404);return;
    }



    /**
     * checkout
     * 切记和admin/order:create保持功能上的同步
     *
     * @access public
     * @return void
     */
    public function checkout($isfastbuy=0)
    {
        /**
         * 取到扩展参数
         */
        $arr_args = func_get_args();
        $arr_args = array(
            'get' => $arr_args,
            'post' => $_POST,
        );
        $this->pagedata['json_args'] = json_encode($arr_args);

        // 判断顾客登录方式.
        $login_type = $this->app->getConf('site.login_type');
        $is_member_buy = $this->app->getConf('security.guest.enabled');
        $arrMember = $this->get_current_member();
        //团购不需要判断登陆
        if($arr_args['get'][0] != 'group'){
            //todo暂时修改为不管是跳转登录还是弹出框登录都统一为跳转到登陆页@lujy
            if (!$arrMember['member_id'] && (($_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true') || $is_member_buy != 'true'))
                //if (!$arrMember['member_id'] && (($login_type == 'href' && $_COOKIE['S']['ST_ShopEx-Anonymity-Buy'] != 'true') || $is_member_buy != 'true'))
                $this->redirect(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy','arg0'=>'1'));
        }
        // 初始化购物车数据
        $this->_common();
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));

        // 购物车是否为空
        if ($this->pagedata['is_empty'])
        {
            $this->end(false, app::get('b2c')->_('购物车为空！'));
        }

        // 购物是否满足起订量和起订金额
        if ((isset($this->pagedata['aCart']['cart_status']) && $this->pagedata['aCart']['cart_status'] == 'false') && (isset($this->pagedata['aCart']['cart_error_html']) && $this->pagedata['aCart']['cart_error_html'] != ""))
        {
            $this->end(false, $this->pagedata['aCart']['cart_error_html']);
        }

        $this->checkout_result();
    }

    /**
     * checkout 结果页面
     * @params int
     * @return null
     */
    public function checkout_result($isfastbuy=0)
    {
        $this->pagedata['checkout'] = 1;
        $this->pagedata['md5_cart_info'] = kernel::single("b2c_cart_objects")->md5_cart_objects();

        $arrMember = $this->get_current_member();
        /** 判断请求的参数是否是group **/
        $arr_request_params = $this->_request->get_params();
        if ($arr_request_params[0] == 'group')
        {
            $this->pagedata['is_group_orders'] = 'true';
        }
        else
        {
            $this->pagedata['is_group_orders'] = 'false';
        }

        /**
         * 额外设置的地址checkbox是否显示
         */
        $is_recsave_display = 'true';
        $is_rec_addr_edit = 'true';
        $app_id = 'b2c';
        $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
        $arr_extends_checkout = array();
        if ($obj_recsave_checkbox)
        {
            foreach($obj_recsave_checkbox as $object)
            {
                if(!is_object($object)) continue;

                if( method_exists($object,'get_order') )
                    $index = $object->get_order();
                else $index = 10;

                while(true) {
                    if( !isset($arr_extends_checkout[$index]) )break;
                    $index++;
                }
                $arr_extends_checkout[$index] = $object;
            }
            ksort($arr_extends_checkout);
        }
        if ($arr_extends_checkout)
        {
            foreach ($arr_extends_checkout as $obj)
            {
                if ( method_exists($obj,'check_display') )
                    $obj->check_display($is_recsave_display);
                if ( method_exists($obj,'check_edit') )
                    $obj->check_edit($is_rec_addr_edit);
                if ( method_exists($obj,'check_app_id') )
                    $obj->check_app_id($app_id);
            }
        }
        $this->pagedata['is_recsave_display'] = $is_recsave_display;
        $this->pagedata['is_rec_addr_edit'] = $is_rec_addr_edit;
        $this->pagedata['app_id'] = $app_id;

        // 如果会员已登录，查询会员的信息
        $obj_member_addrs = $this->app->model('member_addrs');
        $obj_dltype = $this->app->model('dlytype');
        $addr = array();
        $member_point = 0;
        $shipping_method = '';
        $shipping_id = 0;
        $arr_shipping_method = array();
        $payment_method = 0;
        $def_addr = 0;
        $arr_def_addr = array();
        $str_def_currency = $arrMember['member_cur'] ? $arrMember['member_cur'] : "";
        if ($arrMember['member_id'])
        {
            // 得到当前会员的积分
            $obj_members = $this->app->model('members');
            $arr_member = $obj_members->dump($arrMember['member_id'], 'point,addon');
            $member_point = $arr_member['point'];
            if (isset($arr_member['addon']) && $arr_member['addon'])
            {
                $arr_addon = unserialize(stripslashes($arr_member['addon']));
                if ($arr_addon)
                {
                    $obj_session = kernel::single('base_session');
                    $obj_session->start();
                    if ($arr_addon['def_addr']['usable'])
                    {
                        if (!isset($_COOKIE['purchase']['addr']['usable']))
                        {
                            $arr_addon['def_addr']['usable'] = '';
                            $str_addon = serialize($arr_addon);
                            $obj_members->update(array('addon'=>$str_addon), array('member_id'=>$arrMember['member_id']));
                        }
                        elseif ($_COOKIE['purchase']['addr']['usable'] != md5($obj_session->sess_id().$arrMember['member_id']))
                        {
                            $arr_addon['def_addr']['usable'] = '';
                            $str_addon = serialize($arr_addon);
                            $obj_members->update(array('addon'=>$str_addon), array('member_id'=>$arrMember['member_id']));
                        }
                    }
                    $tmp_cnt = $obj_member_addrs->count(array('member_id'=>$arrMember['member_id'],'def_addr'=>'1'));
                    if ($arr_addon['def_addr']  && ((isset($arr_addon['def_addr']['usable']) && $arr_addon['def_addr']['usable'] == md5($obj_session->sess_id().$arrMember['member_id'])) || $tmp_cnt == 0))
                    {
                        $def_addr = $arr_addon['def_addr']['addr_id'] ? $arr_addon['def_addr']['addr_id'] : 0;
                        $arr_area = explode(':', $arr_addon['def_addr']['area']);
                        $def_area = $arr_area[2];
                        $arr_def_addr = $arr_addon['def_addr'];
                        $arr_def_addr['addr_id'] = $arr_addon['def_addr']['addr_id'];
                        $arr_def_addr['def_addr'] = $arr_addon['def_addr']['def_addr'];
                        $arr_def_addr['addr_region'] = $arr_addon['def_addr']['area'];
                        $arr_def_addr['addr'] = $arr_addon['def_addr']['addr'];
                        $arr_def_addr['zip'] = $arr_addon['def_addr']['zip'];
                        $arr_def_addr['name'] = $arr_addon['def_addr']['name'];
                        $arr_def_addr['mobile'] = $arr_addon['def_addr']['mobile'];
                        $arr_def_addr['tel'] = $arr_addon['def_addr']['tel'] ? $arr_addon['def_addr']['tel'] : '';
                        $arr_def_addr['day'] = $arr_addon['def_addr']['day'] ? $arr_addon['def_addr']['day'] : '';
                        $arr_def_addr['specal_day'] = $arr_addon['def_addr']['specal_day'] ? $arr_addon['def_addr']['specal_day'] : '';
                        $arr_def_addr['time'] = $arr_addon['def_addr']['time'] ? $arr_addon['def_addr']['time'] : '';
                        if ($arr_def_addr['day'] == app::get('b2c')->_('任意日期') && $arr_def_addr['time'] == app::get('b2c')->_('任意时间段'))
                        {
                            unset($arr_def_addr['day']);
                            unset($arr_def_addr['time']);
                        }
                    }
                }
            }

            $addrMember = array(
                'member_id' => $arrMember['member_id'],
            );
            $addrlist = $obj_member_addrs->getList('*',array('member_id'=>$arrMember['member_id']));
            $is_checked = false;
            $is_def = false;
            foreach($addrlist as $key=>$rows)
            {
                if(empty($rows['tel'])){
                    $str_tel = app::get('b2c')->_('手机：').$rows['mobile'];
                }else{
                    $str_tel = app::get('b2c')->_('电话：').$rows['tel'];
                }
                if ((isset($arr_def_addr['addr_id']) && $rows['addr_id'] == $arr_def_addr['addr_id']) || (!$arr_def_addr && $rows['def_addr']))
                {
                    $is_def = true;
                    $is_checked = true;
                }
                $addr[] = array('addr_id'=> $rows['addr_id'],'def_addr'=>$is_def ? 1 : 0,'addr_region'=> $rows['area'],
                                'addr_label'=> $rows['addr'].app::get('b2c')->_(' (收货人：').$rows['name'].' '.$str_tel.app::get('b2c')->_(' 邮编：').$rows['zip'].')');
                if ($rows['def_addr'])
                {
                    $def_addr = $rows['addr_id'];
                    $arr_area = explode(':', $rows['area']);
                    $def_area = $arr_area[2];
                    $arr_def_addr_member = array(
                        'addr_id'=> $rows['addr_id'],
                        'def_addr'=>$rows['def_addr'],
                        'addr_region'=> $rows['area'],
                        'addr'=> $rows['addr'],
                        'zip' => $rows['zip'],
                        'name' => $rows['name'],
                        'mobile' => $rows['mobile'],
                        'tel' => $rows['tel'],
                    );
                }
                else
                {
                    if ($key == 0 && !$def_area)
                    {
                        $arr_area = explode(':', $rows['area']);
                        $def_area = $arr_area[2];
                    }
                }
            }
            if ($arr_def_addr && !$is_checked)
                $this->pagedata['other_addr_checked'] = 'true';
            if ($addrlist && !$arr_def_addr && !$is_checked)
            {
                $def_addr = $addrlist[0]['addr_id'];
                $arr_area = explode(':', $addrlist[0]['area']);
                $def_area = $arr_area[2];
                $arr_def_addr_member = $addrlist[0];
                $arr_def_addr_member['addr_id'] = $addrlist[0]['addr_id'];
                $arr_def_addr_member['def_addr'] = 1;
                $arr_def_addr_member['addr_region'] = $addrlist[0]['area'];
                $arr_def_addr_member['addr'] = $addrlist[0]['addr'];
                $arr_def_addr_member['zip'] = $addrlist[0]['zip'];
                $arr_def_addr_member['name'] = $addrlist[0]['name'];
                $arr_def_addr_member['mobile'] = $addrlist[0]['mobile'];
                $arr_def_addr_member['tel'] = $addrlist[0]['tel'];
                $addr[0]['def_addr'] = 1;
            }
        }

        // shipping, payment and default address
        if ((!$def_addr || !$str_def_currency) && !$arrMember['member_id'])
        {
            if ($_COOKIE['purchase']['addon'])
            {
                $arr_addon = unserialize(stripslashes($_COOKIE['purchase']['addon']));
                if (!$def_addr)
                {
                    if (isset($arr_addon['member']['ship_area']) && $arr_addon['member']['ship_area'])
                    {
                        $def_addr = 0;
                        $arr_area = explode(':', $arr_addon['member']['ship_area']);
                        $def_area = $arr_area[2];
                        $arr_def_addr = $arr_addon['member'];
                        $arr_def_addr['addr_region'] = $arr_addon['member']['ship_area'];
                        $arr_def_addr['addr'] = $arr_addon['member']['ship_addr'];
                        $arr_def_addr['zip'] = $arr_addon['member']['ship_zip'] ? $arr_addon['member']['ship_zip'] : '';
                        $arr_def_addr['name'] = $arr_addon['member']['ship_name'];
                        $arr_def_addr['email'] = $arr_addon['member']['ship_email'];
                        $arr_def_addr['mobile'] = $arr_addon['member']['ship_mobile'];
                        $arr_def_addr['tel'] = $arr_addon['member']['ship_tel'] ? $arr_addon['member']['ship_tel'] : '';
                        $arr_def_addr['day'] = $arr_addon['member']['day'] ? $arr_addon['member']['day'] : '';
                        $arr_def_addr['specal_day'] = $arr_addon['member']['specal_day'] ? $arr_addon['member']['specal_day'] : '';
                        $arr_def_addr['time'] = $arr_addon['member']['time'] ? $arr_addon['member']['time'] : '';
                        if ($arr_def_addr['day'] == app::get('b2c')->_('任意日期') && $arr_def_addr['time'] == app::get('b2c')->_('任意时间段'))
                        {
                            unset($arr_def_addr['day']);
                            unset($arr_def_addr['time']);
                        }
                        $this->pagedata['addr'] = array(
                            'area'=> $arr_addon['member']['ship_area'],
                            'addr'=> $arr_addon['member']['ship_addr'],
                            'zipcode' => $arr_addon['member']['ship_zip'] ? $arr_addon['member']['ship_zip'] : '',
                            'name' => $arr_addon['member']['ship_name'],
                            'email' => $arr_addon['member']['ship_email'],
                            'phone' => array(
                                'mobile'=>$arr_addon['member']['ship_mobile'],
                                'telephone' => $arr_addon['member']['ship_tel'] ? $arr_addon['member']['ship_tel'] : ''
                            ),
                        );
                    }
                }
            }
        }

        $obj_dlytype = $this->app->model('dlytype');
        $arr_shipping_info = $obj_dlytype->get_shiping_info($shipping_id, $this->pagedata['aCart']["subtotal"]);
        $this->pagedata['def_addr'] = $def_addr ? $def_addr : 0;
        $this->pagedata['def_area'] = $def_area ? $def_area : 0;

        $this->pagedata['addrlist'] = $addr;
        $this->pagedata['address']['member_id'] = $arrMember['member_id'];
        $this->pagedata['def_arr_addr'] = $arr_def_addr ? $arr_def_addr : $arr_def_addr_member;
        $this->pagedata['def_arr_addr_member'] = $arr_def_addr_member;
        $this->pagedata['def_arr_addr_other'] = $arr_def_addr;
        $this->pagedata['site_checkout_zipcode_required_open'] = $this->app->getConf('site.checkout.zipcode.required.open');
        /** 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        // 是否有默认的当前的配送方式和支付方式
        $this->pagedata['shipping_method'] = (isset($_COOKIE['purchase']['shipping']) && $_COOKIE['purchase']['shipping']) ? unserialize($_COOKIE['purchase']['shipping']) : '';
        $this->pagedata['arr_def_payment'] = (isset($_COOKIE['purchase']['payment']) && $_COOKIE['purchase']['payment']) ? unserialize($_COOKIE['purchase']['payment']) : '';

        /**
         取到优惠券的信息
         */
        if ($arrMember['member_id'])
        {
            $oCoupon = kernel::single('b2c_coupon_mem');
            $aData = $oCoupon->get_list_m($arrMember['member_id']);
            if( is_array($aData) ) {
                foreach( $aData as $_key => $_val ) {
                    if( $_val['memc_used_times'] ) unset($aData[$_key]);
                }
            }
            $this->pagedata['coupon_lists'] = $aData;
        }

        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');

        if (!$str_def_currency)
        {
            $arrDefCurrency = $currency->getDefault();
            $str_def_currency = $arrDefCurrency['cur_code'];
        }
        else
        {
            $arrDefCurrency = $currency->getcur($str_def_currency);
        }

        $aCur = $currency->getcur($str_def_currency);
        $this->pagedata['current_currency'] = $str_def_currency;

        $obj_payments = kernel::single('ectools_payment_select');
        /** 判断是否有货到付款的支付方式 **/
        $shipping_has_cod = $obj_dlytype->shipping_has_cod();
        $this->pagedata['shipping_has_cod'] = $shipping_has_cod;
        $this->pagedata['payment_html'] = $obj_payments->select_pay_method($this, $arrDefCurrency, $arrMember['member_id']);

        // 得到税金的信息
        $this->pagedata['trigger_tax'] = $this->app->getConf("site.trigger_tax");
        $this->pagedata['tax_ratio'] = $this->app->getConf("site.tax_ratio");

        $demical = $this->app->getConf('system.money.operation.decimals');

        $total_item = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount_prefilter']));
        //$total_item = $this->pagedata['aCart']["subtotal"];
        // 取到商店积分规则
        $policy_method = $this->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $policy_rate = $this->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($this->objMath->number_plus(array(0, $this->pagedata['aCart']['subtotal_gain_score'])));
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            case '3':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $subtotal_gain_score = round($this->pagedata['aCart']['subtotal_gain_score']);
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }

        $total_amount = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount']));
        if ($total_amount < 0)
            $total_amount = 0;
        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis)
        {
            $obj_point_dis->set_order_total($total_amount);
            $this->pagedata['point_dis_html'] = $obj_point_dis->get_html($arrMember['member_id']);
            $this->pagedata['point_dis_js'] = $obj_point_dis->get_javascript($arrMember['member_id']);
        }
        // 得到cart total支付的信息
        $this->pagedata['order_detail'] = array(
            'cost_item' => $total_item,
            'total_amount' => $total_amount,
            'currency' => $this->app->getConf('site.currency.defalt_currency'),
            'pmt_order' => $this->pagedata['aCart']['discount_amount_order'],
            'pmt_amount' => $this->pagedata['aCart']['discount_amount'],
            'totalConsumeScore' => $subtotal_consume_score,
            'totalGainScore' => $subtotal_gain_score,
            'totalScore' => $totalScore,
            'cur_code' => $strDefCurrency,
            'cur_display' => $strDefCurrency,
            'cur_rate' => $aCur['cur_rate'],
            'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
        );

        if ($arrMember['member_id'])
        {
            $this->pagedata['order_detail']['totalScore'] = $member_point;
        }
        else
        {
            $this->pagedata['order_detail']['totalScore'] = 0;
            $this->pagedata['order_detail']['totalGainScore'] = 0;    //如果是非会员购买获得积分为0，@lujy
        }

        $odr_decimals = $this->app->getConf('system.money.decimals');
        $total_amount = $this->objMath->get($this->pagedata['order_detail']['total_amount'], $odr_decimals);
        $this->pagedata['order_detail']['discount'] = $this->objMath->number_minus(array($this->pagedata['order_detail']['total_amount'], $total_amount));
        $this->pagedata['order_detail']['total_amount'] = $total_amount;
        $this->pagedata['order_detail']['current_currency'] = $strDefCurrency;

        // 获得商品的赠品信息
        $arrM_info = array();
        foreach ($this->pagedata['aCart']['object']['goods'] as $arrGoodsInfo)
        {
            if (isset($arrGoodsInfo['gifts']) && $arrGoodsInfo['gifts'])
            {
                $this->pagedata['order_detail']['gift_p'][] = array(
                    'storage' => $arrGoodsInfo['gifts']['storage'],
                    'name' => $arrGoodsInfo['gifts']['name'],
                    'nums' => $arrGoodsInfo['gifts']['nums'],
                );
            }

            // 得到商品购物信息的必填项目
            $goods_id = $arrGoodsInfo['obj_items']['products'][0]['goods_id'];
            $product_id = $arrGoodsInfo['obj_items']['products'][0]['product_id'];
            // 得到商品goods表的信息
            $objGoods = $this->app->model('goods');
            $arrGoods = $objGoods->dump($goods_id, 'type_id');
            if (isset($arrGoods) && $arrGoods && $arrGoods['type']['type_id'])
            {
                $objGoods_type = $this->app->model('goods_type');
                $arrGoods_type = $objGoods_type->dump($arrGoods['type']['type_id'], '*');

                if ($_COOKIE['checkout_b2c_goods_buy_info'])
                {
                    //$this->pagedata['has_goods_minfo'] = true;
                    $goods_need_info = json_decode($_COOKIE['checkout_b2c_goods_buy_info'], 1);

                }
                if ($arrGoods_type['minfo'])
                {
                    if ($arrGoodsInfo['obj_items']['products'][0]['spec_info'])
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'] . '(' . $arrGoodsInfo['obj_items']['products'][0]['spec_info'] . ')';
                    else
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'];
                    $arrM_info[$product_id]['nums'] = $this->objMath->number_multiple(array($arrGoodsInfo['obj_items']['products'][0]['quantity'],$arrGoodsInfo['quantity']));

                    foreach ($arrGoods_type['minfo'] as $key=>$arr_minfo)
                    {
                        if (isset($goods_need_info[$product_id][$key]) && $arr_minfo['label'] == $goods_need_info[$product_id][$key]['name'])
                        {
                            $arr_minfo['value'] = $goods_need_info[$product_id][$key]['val'][0];
                        }else{
                            $no_value = true;
                        }
                        $arrM_info[$product_id]['minfo'][] = $arr_minfo;
                    }
                }
            }
        }

        if($no_value){
            $this->pagedata['has_goods_minfo'] = false;
        }else{
            $this->pagedata['has_goods_minfo'] = true;
        }
        $this->pagedata['minfo'] = $arrM_info;
        $this->pagedata['is_checkout'] = 1;
        $this->pagedata['base_url'] = kernel::base_url().'/';
        // checkout result 页面添加项目埋点
        foreach( kernel::servicelist('b2c.checkout_add_item') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addItem') ) {
                    $services->addItem($this);
                }
            }
        }
        $this->page('site/cart/checkout.html',false,$app_id);
    }

    public function get_default_info()
    {
        if ($_POST['_type'])
        {
            if (isset($_COOKIE['purchase']['addon']) && $_COOKIE['purchase']['addon'])
                $arr_member = $_COOKIE['purchase']['addon'];

            $arr_addon = unserialize(stripslashes($arr_member));
            switch ($_POST['_type'])
            {
                case 'get_addr':
                    if ($arr_addon)
                    {
                        echo $arr_addon['def_addr']['addr_id'] ? $arr_addon['def_addr']['addr_id'] : '0';exit;
                    }
                    else
                    {
                        echo '';exit;
                    }
                    break;
                case 'get_payment':
                    if ($arr_addon)
                    {
                        echo $arr_addon['payment']['pay_app_id'] ? $arr_addon['payment']['pay_app_id'] : '0';exit;
                    }
                    else
                    {
                        echo '0';exit;
                    }
                    break;
                case 'get_default_shipping':
                    if ($arr_addon)
                    {
                        echo $arr_addon['delivery']['shipping_id'] ? $arr_addon['delivery']['shipping_id'] : '0';exit;
                    }
                    else
                    {
                        echo '0';exit;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public function purchase_save_addr($_POST)
    {
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');

        $obj_member_addrs = kernel::single('b2c_member_addrs');
        if ( $str_html = $obj_member_addrs->purchase_save_addr($this, $_POST,$msg) ){
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');
            header('Content-Type:text/html; charset=utf-8');
            echo '{success:"'.app::get('b2c')->_("添加成功！").'",_:null,data:"'.addslashes(str_replace("\r\n","",$str_html)).'"}';
            exit;
        }else{
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');
            header('Content-Type:text/html; charset=utf-8');
            echo '{error:"'.$msg.'",_:null}';exit;
        }
    }

    public function purchase_def_addr()
    {
        //过滤输入数据
        foreach($_POST['delivery'] as $k=>&$v){
            $this->RemoveXSS($v);
        }
        $arr_delivery = $_POST['delivery'];

        if ($arr_delivery)
        {
            $is_insert = false;
            if ($_POST['member_id'])
            {
                $member_id = intval($_POST['member_id']);
                // member default addr.
                if ($arr_delivery)
                {
                    $obj_member_addr = $this->app->model('member_addrs');

                    //新增地址
                    if (!$arr_delivery['addr_id'])
                    {
                        $arr_member_addr = array(
                            'member_id' => $member_id,
                            'name' => $arr_delivery['ship_name'],
                            'area' => $arr_delivery['ship_area'],
                            'addr' => $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'],
                            'zip' => $arr_delivery['ship_zip'] ? $arr_delivery['ship_zip'] : '',
                            'tel' => $arr_delivery['ship_tel'] ? $arr_delivery['ship_tel'] : '',
                            'mobile' => $arr_delivery['ship_mobile'],
                            'def_addr' => $arr_delivery['is_default'] ? 1 : 0,

                        );
                    }
                    else
                    {
                        $tmp = $obj_member_addr->getList('*', array('addr_id' => $arr_delivery['addr_id']));
                        $arr_member_addr = $tmp[0];

                        //修改地址
                        if ($arr_delivery['ship_name'] && $arr_delivery['ship_area'] && $arr_delivery['ship_addr'] && $arr_delivery['ship_mobile'])
                        {
                            $arr_member_addr['name'] = $arr_delivery['ship_name'];
                            $arr_member_addr['area'] = $arr_delivery['ship_area'];
                            $arr_member_addr['addr'] = $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'];
                            $arr_member_addr['zip'] = $arr_delivery['ship_zip'];
                            $arr_member_addr['tel'] = $arr_delivery['ship_tel'];
                            $arr_member_addr['mobile'] = $arr_delivery['ship_mobile'];
                        }
                        $arr_member_addr['addr_id'] = $addr_id = $arr_delivery['addr_id'];
                    }

                    /**
                     * 是否需要将addr_id设置为0
                     */
                    $is_set_addr_id = true;
                    $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
                    $arr_extends_checkout = array();
                    if ($obj_recsave_checkbox)
                    {
                        foreach($obj_recsave_checkbox as $object)
                        {
                            if(!is_object($object)) continue;

                            if( method_exists($object,'get_order') )
                                $index = $object->get_order();
                            else $index = 10;

                            while(true) {
                                if( !isset($arr_extends_checkout[$index]) )break;
                                $index++;
                            }
                            $arr_extends_checkout[$index] = $object;
                        }
                        ksort($arr_extends_checkout);
                    }
                    if ($arr_extends_checkout)
                    {
                        foreach ($arr_extends_checkout as $obj)
                        {
                            if ( method_exists($obj,'check_set_addr_id') )
                                $obj->check_set_addr_id($is_set_addr_id);
                        }
                    }
                    if ($is_set_addr_id)
                        $arr_member_addr['addr_id'] = '0';
                    $arr_member_addr['day'] = $arr_delivery['day'];
                    $arr_member_addr['specal_day'] = $arr_delivery['specal_day'] ? $arr_delivery['specal_day'] : '';
                    $arr_member_addr['time'] = $arr_delivery['time'];
                    $arr_member_addr['phone']['mobile'] = $arr_delivery['ship_mobile'];
                    $arr_member_addr['phone']['telephone'] = $arr_delivery['ship_tel'];
                    $arr_member_addr['zipcode'] = $arr_delivery['ship_zip'];
                    $arr_member_addr['is_usable'] = 'true';

                    $obj_member = $this->app->model('members');
                    $arr_addon = array();
                    $tmp = $obj_member->getList('addon', array('member_id'=>$member_id));
                    if ($tmp)
                        $arr_addon = $tmp[0]['addon'];

                    $arr_addon = unserialize(stripslashes($arr_addon));
                    if (!$arr_addon)
                        $arr_addon = array();
                    $obj_session = kernel::single('base_session');
                    $obj_session->start();
                    $seKey = md5($obj_session->sess_id().$member_id);
                    setcookie('purchase[addr][usable]', $seKey, 0, kernel::base_url() . '/');
                    $arr_member_addr['usable'] = $seKey;
                    $arr_addon['def_addr'] = $arr_member_addr;
                    $arr_member = array('addon'=>$arr_addon);
                    $obj_member->update($arr_member, array('member_id'=>$member_id));
                }
            }
            else
            {
                //存入cookie，无用户信息...
                if ($arr_delivery)
                {
                    $arr_addon = unserialize(stripslashes($_COOKIE['purchase']['addon']));

                    // shipping
                    if (isset($arr_addon['member']) && $arr_addon['member'] && is_array($arr_addon['member']))
                    {
                        $arr_addon['member'] = $arr_delivery;
                        $arr_addon['member']['ship_addr'] = $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'];
                    }
                    else
                    {
                        $arr_addon['member'] = array();
                        $arr_addon['member'] = $arr_delivery;
                        $arr_addon['member']['ship_addr'] = $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'];
                    }
                }

                $arr_addon['member']['day'] = $arr_delivery['day'];
                $arr_addon['member']['specal_day'] = $arr_delivery['specal_day'] ? $arr_delivery['specal_day'] : '';
                $arr_addon['member']['time'] = $arr_delivery['time'];
                setcookie('purchase[addon]', serialize($arr_addon), 0, kernel::base_url() . '/');
            }

            $arr_delivery['radio_index'] = $_POST['radio_index'];
            $obj_addr = kernel::single('b2c_member_addrs');
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');

            $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
            echo $obj_addr->get_def_addr($this, $arr_delivery, $is_insert);exit;
        }
    }

    // 保存配送方式
    public function purchase_shipping()
    {
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        if (!isset($_POST['shipping_id']) || !$_POST['shipping_id'])
        {
            echo app::get('b2c')->_('配送方式的id不能为空！');exit;
        }

        $arr_shipping = array(
            'shipping_id'=>$_POST['shipping_id'],
            'is_protect'=>$_POST['is_protect'],
        );
        setcookie('purchase[shipping]', serialize($arr_shipping), 0, kernel::base_url() . '/');
        echo app::get('b2c')->_('配送方式保存成功！');exit;
    }

    // 暂时保存支付方式
    public function purchase_payment()
    {
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        if (!isset($_POST['pay_app_id']) || !$_POST['pay_app_id'])
        {
            echo app::get('b2c')->_('支付方式的id不能为空！');exit;
        }

        $arr_payment = array(
            'pay_app_id'=>$_POST['pay_app_id'],
        );
        setcookie('purchase[payment]', serialize($arr_payment), 0, kernel::base_url() . '/');
        echo app::get('b2c')->_('支付方式保存成功！');exit;
    }

    public function getAddr(){
        $obj_addr = new b2c_member_addrs();
        $addr_id = $_GET['addr_id'];
        $arr_member = $this->get_current_member();
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        echo $obj_addr->get_receive_addr($this,$addr_id,$arr_member['member_id']);exit;
    }

    public function shipping(){
        $this->_common();
        $area_id = ($_POST['area']);
        $shipping_method = $_POST['shipping_method'];
        $obj_delivery = new b2c_order_dlytype();
        $sdf = array();
        $sdf = $this->pagedata['aCart'];
        if (isset($_POST['payment']) && $_POST['payment'])
            $sdf['pay_app_id'] = $_POST['payment'];

        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        /**
         * 额外设置的地址checkbox是否显示
         */
        $is_recsave_display = 'true';
        $is_rec_addr_edit = 'true';
        $app_id = 'b2c';
        $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
        $arr_extends_checkout = array();
        if ($obj_recsave_checkbox)
        {
            foreach($obj_recsave_checkbox as $object)
            {
                if(!is_object($object)) continue;

                if( method_exists($object,'get_order') )
                    $index = $object->get_order();
                else $index = 10;

                while(true) {
                    if( !isset($arr_extends_checkout[$index]) )break;
                    $index++;
                }
                $arr_extends_checkout[$index] = $object;
            }
            ksort($arr_extends_checkout);
        }
        if ($arr_extends_checkout)
        {
            foreach ($arr_extends_checkout as $obj)
            {
                if ( method_exists($obj,'check_display') )
                    $obj->check_display($is_recsave_display);
                if ( method_exists($obj,'check_edit') )
                    $obj->check_edit($is_rec_addr_edit);
                if ( method_exists($obj,'check_app_id') )
                    $obj->check_app_id($app_id);
            }
        }
        $this->pagedata['app_id'] = $app_id;
        echo $obj_delivery->select_delivery_method($this,$area_id,$sdf,$shipping_method);exit;
    }

    public function payment(){
        //$this->_get_cart();
        $obj_payment_select = new ectools_payment_select();
        $sdf = $_POST;
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        /**
         * 额外设置的地址checkbox是否显示
         */
        $is_recsave_display = 'true';
        $is_rec_addr_edit = 'true';
        $app_id = 'b2c';
        $obj_recsave_checkbox = kernel::servicelist('b2c.checkout_recsave_checkbox');
        $arr_extends_checkout = array();
        if ($obj_recsave_checkbox)
        {
            foreach($obj_recsave_checkbox as $object)
            {
                if(!is_object($object)) continue;

                if( method_exists($object,'get_order') )
                    $index = $object->get_order();
                else $index = 10;

                while(true) {
                    if( !isset($arr_extends_checkout[$index]) )break;
                    $index++;
                }
                $arr_extends_checkout[$index] = $object;
            }
            ksort($arr_extends_checkout);
        }
        if ($arr_extends_checkout)
        {
            foreach ($arr_extends_checkout as $obj)
            {
                if ( method_exists($obj,'check_display') )
                    $obj->check_display($is_recsave_display);
                if ( method_exists($obj,'check_edit') )
                    $obj->check_edit($is_rec_addr_edit);
                if ( method_exists($obj,'check_app_id') )
                    $obj->check_app_id($app_id);
            }
        }
        $this->pagedata['app_id'] = $app_id;
        echo $obj_payment_select->select_pay_method($this, $sdf, false);exit;
    }

    public function total()
    {
        $this->_common();
        $obj_total = new b2c_order_total();
        $sdf_order = $_POST;
        $arrMember = $this->get_current_member();
        $sdf_order['member_id'] = $arrMember['member_id'];
        $arr_cart_object = $this->pagedata['aCart'];

        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        echo $obj_total->order_total_method($this,$arr_cart_object,$sdf_order);exit;
    }

    //widgets cart
    public function view(){
        $oCart = $this->app->model("cart_objects");
        $arr = array();
        $aData = $oCart->setCartNum( $arr );
        $this->pagedata['trading'] = $aData['trading'];
        $this->pagedata['cartCount'] = $aData['CART_COUNT'];
        $this->pagedata['cartNumber'] = $aData['CART_NUMBER'];
        $this->_common();

        // 购物车数据项的render 迷你购物车
        $this->pagedata['item_section'] = $this->mCart->get_item_render_view();

        // 购物车数据项的render
        $this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render_view();

        $tpl = 'site/cart/view.html';
        $this->page($tpl, true);
    }

    public function loginBuy($isfastbuy=0)
    {
        $this->pagedata['guest_enabled'] = $this->app->getConf('security.guest.enabled');
        #if (!$isfastbuy)
        #    $this->__tmpl = 'site/cart/loginBuy.html';
        #else
            $this->__tmpl = 'site/cart/loginbuy_fast.html';
        if( $this->check_login() ) {
            $this->begin( $this->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'site_cart') ) );
            $this->end( true,'您已经是登录状态！');
        }
        $this->pagedata['no_right'] = 1;
        if(!isset($_SESSION['next_page']))
            $_SESSION['next_page'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));

	$this->pagedata['toUrl'] = $_SESSION['next_page'];
	$oAP = $this->app->controller('site_passport');
        $oAP->gen_login_form(1);
        $this->pagedata['isfastbuy'] = $isfastbuy;
        $this->pagedata['base_path'] = kernel::base_url();
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

        if ($isfastbuy)
            $this->page($this->__tmpl);
        else
            $this->page($this->__tmpl, true);
    }









    ////////////////////////////// 以下私有函数/////////////////////////////

    private function _cart_main($msg='',$json_type='all') {
        if (!$msg) $msg = app::get('b2c')->_('购物车修改成功！');
        $obj_currency = app::get('ectools')->model('currency');
       $this->pagedata['ajax_html'] = $this->ajax_html;

       $this->_common(1);
        if( !$this->pagedata['is_empty'] ) {
       $this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
            $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
            $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
            switch ($json_type)
            {
                case 'mini':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    $view = 'site/cart/view.html';
                    //$this->view();exit;
                break;
                case 'middle':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($aCart['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/middle_index.html';
                break;
                case 'all':
                default:
                    $arr_json_data = array(
                        'success'=> $msg,
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($this->pagedata['aCart']['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($this->pagedata['aCart']['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'unuse_rule'=>$this->pagedata['unuse_rule'],
                        'is_checkout'=>false,
                        'edit_ajax_data'=>$this->pagedata['edit_ajax_data'],
                        'promotion'=>$this->pagedata['aCart']['promotion'],
                        'error_msg'=>$this->pagedata['error_msg'],
                        'number'=>array(
                            'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                            'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/index.html';
                break;
            }
        }else{
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
       }

        $md5_cart_info = kernel::single('b2c_cart_objects')->md5_cart_objects();
        $arr_json_data['md5_cart_info'] = $md5_cart_info;
        $this->pagedata = $arr_json_data;
        if($json_type=='mini'){
          $this->view();
        }else{
            $this->page($view);
        }
    }

    public function _common($flag=0) {
        // 购物车数据信息
        $aCart = $this->mCart->get_objects($this->_request->get_params(true));
        $this->_item_to_disabled( $aCart,$flag ); //处理购物扯删除项
        $this->pagedata['aCart'] = $aCart;

        if( $this->show_gotocart_button ) $this->pagedata['show_gotocart_button'] = 'true';

        if( $this->ajax_update === true ) {
            foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
                if( !is_object($object) ) continue;

                //应该判断是否实现了接口
                if( !method_exists( $object,'get_update_num' ) ) continue;
                if( !method_exists( $object,'get_type' ) ) continue;

                $this->pagedata['edit_ajax_data'] = $object->get_update_num( $aCart['object'][$object->get_type()],$this->update_obj_ident );
                if( $this->pagedata['edit_ajax_data'] ) {
                    $this->pagedata['edit_ajax_data'] = json_encode( $this->pagedata['edit_ajax_data'] );
                    if( $object->get_type()=='goods' ) {
                        $this->pagedata['update_cart_type_godos'] = true;
                        if( !method_exists( $object,'get_error_html' ) ) continue;
                        $this->pagedata['error_msg'] = $object->get_error_html( $aCart['object']['goods'],$this->update_obj_ident );
                    }
                    break;
                }
            }
        }



        // 购物车是否为空
        $this->pagedata['is_empty'] = $this->mCart->is_empty($aCart);
        //ajax_html 删除单个商品是触发
        if($this->ajax_html && $this->mCart->is_empty($aCart)) {
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
            $this->pagedata = $arr_json_data;
            $this->page('site/cart/cart_empty.html', true);
            return ;
        }

        // 购物车数据项的render
        $this->pagedata['item_section'] = $this->mCart->get_item_render();

        // 购物车数据项的render
        $this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render();

        // 优惠信息项render
        $this->pagedata['solution_section'] = $this->mCart->get_solution_render();

        //未享受的订单规则
        $this->pagedata['unuse_rule'] = $this->mCart->get_unuse_solution_cart($aCart);


        if( $this->member_status ) {
            /*
            $arr_member = $this->get_current_member();
            $aData = $this->app->model('member_goods')->get_favorite( $arr_member['member_id'], 0, 100);
            $objProduct = $this->app->model('products');
            $oGoodsLv = &$this->app->model('goods_lv_price');
            $oMlv = &$this->app->model('member_lv');
            $mlv = $oMlv->db_dump( $this->member['member_lv'],'dis_count' );

            $aProduct = $aData['data'];
            if($aProduct){
                foreach ($aProduct as $key => &$val) {
                    $temp = $objProduct->getList('product_id, spec_info, price, freez, store, goods_id',array('goods_id'=>$val['goods_id'],'goods_type'=>array('normal','gift')));
                    if( $arr_member['member_lv'] ){
                        $tmpGoods = array();
                        foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=> $arr_member['member_lv'] ) ) as $k => $v ){
                            $tmpGoods[$v['product_id']] = $v['price'];
                        }
                        foreach( $temp as &$tv ){
                            $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                        }
                        $val['price'] = $tv['price'];
                    }
                    $val['spec_desc_info'] = $temp;
                }
            }

            $this->pagedata['member_goods'] = $aProduct;
            #$this->pagination($nPage,$aData['page'],'favorite');
            $imageDefault = app::get('image')->getConf('image.set');
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            */
        } else {
            $this->pagedata['login'] = 'nologin';
        }

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
    }


    /*
	 * item 禁用的数据类型如下：
      $_SESSION['cart_objects_disabled_item']
      array(
      'goods' => array(
        'goods_12_23' => array(
            'gift' => array(
                0 => true,
                3 => true,
                ),
            ),
        ),
      );
    */
    private function _item_to_disabled( &$aCart,$flag ) {

        foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            $o[$object->get_type()] = $object;
        }

        $arr_cart_disabled_session = $_SESSION['cart_objects_disabled_item'];
        foreach( (array)$aCart['object'] as $_obj_type => $_arr_by_obj_type ) {
            $tmp = $arr_cart_disabled_session[$_obj_type];
            if( isset($arr_cart_disabled_session[$_obj_type]) ) {
                if( !$o[$_obj_type] ) continue;
                if( !method_exists( $o[$_obj_type],'apply_to_disabled' ) ) continue;
                $aCart['object'][$_obj_type] = $o[$_obj_type]->apply_to_disabled( $_arr_by_obj_type, $tmp, $flag );
                $_SESSION['cart_objects_disabled_item'][$_obj_type] = $tmp;
            } else {
                if( $flag )
                    unset($_SESSION['cart_objects_disabled_item'][$obj_type]);
            }
        }
    }



    private function _v_cart_object ($temp, $row,$flag=false) {
        if( !$temp['quantity']) {
            if( isset($row['params']['adjunct']) && is_array($row['params']['adjunct']) ) {
                foreach( $row['params']['adjunct'] as $adjunct ) {
                    if( !isset($adjunct['adjunct']) || !is_array($adjunct['adjunct']) ) continue;
                    foreach( $adjunct['adjunct'] as $p_id => $p_quantity ) {
                        if( !isset($temp['adjunct'][$adjunct['group_id']][$p_id]) ) { $flag = false; continue; }
                        #if($temp[$adjunct['group_id']][$p_id]['quantity']!=$p_quantity) {
                            $this->update_obj_ident['index'] = array('adjunct');
                            $this->update_obj_ident['id'] = $p_id;
                            $flag = false;
                            break 2;
                        #}
                    }
                }
            }
            return $flag;
        }
        return $flag;
    }

}
