<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
define('PLUGIN_DIR_LIB', ROOT_DIR.'/app/b2c/lib');
class b2c_mdl_member_messenger {

    var $plugin_type = 'dir';
    var $plugin_name = 'messenger';
    var $prefix = 'messenger.';
    var $db;
    function __construct(&$app){
        $this->app = $app;
        $this->db = kernel::database();
    }
    
    function getList($filter=array(), $ifMethods=true,$withDesc=false){
        $services = kernel::servicelist('b2c_messenger');
        $service = array();
        foreach($services as $key=>$v){
            $service[$key] = (array)$v;
            $service[$key]['methods'] = get_class_methods($v);
        }
        return $service;
    }

    function &_load($sender){
        if(!$this->_sender[$sender]){
            $obj = $this->load($sender);
            $this->_sender[$sender] = &$obj;
            if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions'))
                $obj->config = $this->getOptions($sender,true);
            if(method_exists($obj,'outgoingConfig')||method_exists($obj,'outgoingconfig'))
                $obj->outgoingOptions = $this->outgoingConfig($sender,true);
        }else{
            $obj = &$this->_sender[$sender];
        }
        return $obj;
    }

    function _ready(&$obj){
        if(!$obj->_isReady){
            if(method_exists($obj,'ready')) $obj->ready($obj->config);
            if(method_exists($obj,'finish')){
                if(!$this->_finishCall){
                    register_shutdown_function(array(&$this,'_finish'));
                    $this->_finishCall=array();
                }
                $this->_finishCall[] = &$obj;
            }
            $obj->_isReady = true;
        }
    }

    function _send($sendMethod,$tmpl_name,$target,$data,$type,$title=null){
        $sender = &$this->_load($sendMethod);
        $this->_ready($sender);
        if(!$this->_systmpl){
            $this->_systmpl = &$this->app->model('member_systmpl');
        }
        $content = $this->_systmpl->fetch($tmpl_name,$data);
        $tile = $this->loadTitle($type,$sendMethod,'',$data);
        $service = kernel::service("b2c.messenger.fireEvent_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $content = $service->get_content($content);
                $tile = $service->get_content($tile);
        }
        if($tile=='') $tile = app::get('site')->getConf('site.name');
        $sender->config['shopname'] = app::get('site')->getConf('site.name');
        $sender->send($target,$tile,$content,$sender->config);
        return ($ret || !is_bool($ret));


    }
    
    ##获取发送对象的联系方式 /email,ID,phone
    
    function get_send_type($sdfpath='pam_account/account_id',$data,$member_id,$tmpl_name=null){
        $obj_member = $this->app->model('members');
        $sdf = $obj_member->dump($member_id);
        $sdf['addon'] = unserialize($sdf['addon']);
        eval(' $target= $sdf["'.implode('"]["',explode('/',$sdfpath)).'"]; ');
        if('addon/def_addr/mobile' == $sdfpath){
            if(empty($sdf['addon']['def_addr']['mobile'])){
                // 会员手机号
                $target=$sdf['contact']['phone']['mobile'];
            }
            //获取发货单上的手机号码
            if($data['delivery']['ship_mobile']){
                $target = $data['delivery']['ship_mobile'];
            }
        }
                /*
        if($type_msg == "b2c_messenger_msgbox") {
        $target = $member_id; 
        }
        if($type_msg == "b2c_messenger_email"){
        $target = $sdf['contact']['email'];
        if(!$target) $target = $data['email'];
        }
        if($type_msg == "b2c_messenger_sms") {
       $target = $sdf['contact']['phone']['mobile'];
        }*/
		foreach(kernel::servicelist("message_contact") as $k=>$service)
		{
			if(is_object($service))
			{
				if(method_exists($service,"get_contact"))
				{
					$service->get_contact($member_id,$target,$tmpl_name,$sdfpath);
				}
			}
		}
        return $target;
    }

    function _finish(){
        foreach($this->_finishCall as $obj){
            $obj->finish($obj->config);
        }
    }

    function _target($sender,$contectInfo,$member_id){
        $obj = &$this->_load($sender);
        if(($dataname = $obj->dataname) && $contectInfo[$dataname]){
            return $contectInfo[$dataname];
        }else{
            $row = $this->db->selectrow('select email,member_id,name,custom,mobile from sdb_b2c_members where member_id='.intval($member_id));
            if($dataname){
                return $row[$dataname];
            }elseif($custom = unserialize($row['custom'])){
                return $custom[$sender];
            }else{
                return false;
            }
        }
    }

    /**
     * actionSend
     *
     * @param mixed $type 类型
     * @param mixed $contectInfo  联系数组
     * @param mixed $member_id 会员id
     * @param mixed $data 信息
     * @access public
     * @return void
     */
    function actionSend($type,$data,$member_id=null){
        $actions = $this->actions();
        $senders = $this->getSenders($type); //email/msbox/sms
        $level = $actions[$type]['level'];
        $desc = $actions[$type]['label'];
        foreach($senders as $sender){
            $tmpl_name = 'messenger:'.$sender.'/'.$type;
            $contractInfo = $data;
             if($sender){
                $target = $this->get_send_type(kernel::single($sender)->sdfpath,$data,$member_id,$tmpl_name); 
                if($target){
                    if($level < 9){ //队列
                      //  $this->addQueue($sender,$target,$desc,$data,$tmpl_name,$level,$type);
                    }else{ //直接发送 print
                    
                       $this->_send($sender,$tmpl_name,$target,$data,$type);
                    }
                }
            }
        }
        
    }

    function getSenders($act){
        $ret = $this->app->getConf('messenger.actions.'.$act);
        return explode(',',$ret);
    }

    function saveActions($actions){
        foreach($this->actions() as $act=>$info){
            if(!$actions[$act]){
                $actions[$act] = array();
            }
        }
        foreach($actions as $act=>$call){
            $this->app->setConf('messenger.actions.'.$act,implode(',',array_keys($call)));
        }
        return true;
    }

    /**
     * actions
     * 所有自动消息发送列表，只要触发匹配格式的事件就会发送
     *
     * 格式：
     *            对象-事件 => array(label=>名称 , level=>紧急程度)
     *
     * 如果不存在匹配的事件，则需要手动通过send()方法发送
     *
     * @access public
     * @return void
     */
    function actions(){
        $actions = array(
            'account-register'=>array('label'=>app::get('b2c')->_('会员注册时'),'level'=>9,'varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('密码').'&nbsp;<{$passwd}>'),
            'account-lostPw'=>array('label'=>app::get('b2c')->_('会员找回密码'),'level'=>9,'varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('密码').'&nbsp;<{$passwd}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('姓名').'&nbsp;<{$name}>'),
            'account-chgpass'=>array('label'=>app::get('b2c')->_('会员更改密码时'),'level'=>9,'varmap'=>app::get('b2c')->_('密码').'&nbsp;<{$passwd}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('登录名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>'),
            'orders-create'=>array('label'=>app::get('b2c')->_('订单创建时'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('总价').'&nbsp;<{$total_amount}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('配送方式').'&nbsp;<{$shipping_id}><br>'.app::get('b2c')->_('收货人手机').'&nbsp;<{$ship_mobile}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人电话').'&nbsp;<{$ship_tel}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人地址').'&nbsp;<{$ship_addr}><Br>'.app::get('b2c')->_('收货人').'Email&nbsp;<{$ship_email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人邮编').'&nbsp;<{$ship_zip}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人姓名').'&nbsp;<{$ship_name}>'),
            'orders-payed'=>array('label'=>app::get('b2c')->_('订单付款时'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('付款人').'&nbsp;<{$pay_account}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('付款时间').'&nbsp;<{$pay_time}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('付款金额').'&nbsp;<{$money}>'),
            'orders-shipping'=>array('label'=>app::get('b2c')->_('订单发货时'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('实际费用').'&nbsp;<{$delivery.money}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('配送方式').'&nbsp;<{$delivery.delivery}><br>'.app::get('b2c')->_('物流公司').'&nbsp;<{$ship_corp}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('物流单号').'&nbsp;<{$ship_billno}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人姓名').'&nbsp;<{$delivery.ship_name}><br>'.app::get('b2c')->_('收货人地址').'&nbsp;<{$delivery.ship_addr}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人邮编').'&nbsp;<{$delivery.ship_zip}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人电话').'&nbsp;<{$delivery.ship_tel}><br>'.app::get('b2c')->_('收货人手机').'&nbsp;<{$delivery.ship_mobile}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('收货人').'Email&nbsp;<{$delivery.ship_email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('操作者').'&nbsp;<{$delivery.op_name}><br>'.app::get('b2c')->_('备注').'&nbsp;<{$delivery.memo}>'),
            'orders-returned'=>array('label'=>app::get('b2c')->_('订单退货时'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>'),
            'orders-refund'=>array('label'=>app::get('b2c')->_('订单退款时'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>'),
            'orders-cancel'=>array('label'=>app::get('b2c')->_('订单作废'),'level'=>9,'varmap'=>app::get('b2c')->_('订单号').'&nbsp;<{$order_id}>'),
            'goods-notify'=>array('label'=>app::get('b2c')->_('商品到货通知'),'level'=>6,'varmap'=>app::get('b2c')->_('商品名称').'&nbsp;<{$goods_name}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('会员名称').'&nbsp;<{$username}>'),
               /*             'goods-replay'=>array('label'=>'商品评论回复','level'=>9), todo */
            /*             'comment-replay'=>array('label'=>'留言回复时','level'=>9,'varmap'=>''), todo */
            /*             'indexorder-pay'=>array('label'=>'前台订单支付','level'=>9), */
            /*             'comment-new'=>array('label'=>'订单生成通知商家','level'=>9), */
        );
        foreach(kernel::servicelist('firevent_type') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_type')){
                    $data = $service->get_type();
                }
            }
			$actions = array_merge($actions,(array)$data);
        }
        
        return $actions;
    }


    function loadTmpl($action,$msg,$lang=''){
        $systmpl = &$this->app->model('member_systmpl');
        return $systmpl->get('messenger:'.$msg.'/'.$action);
    }

    function loadTitle($action,$msg,$lang='',$data=""){

        $tmpArr=$data;
        $title = $this->app->getConf('messenger.title.'.$action.'.'.$msg);

        if($data!=""){
            preg_match_all('/<\{\$(\S+)\}>/iU', $title, $result);

            foreach($result[1] as $k => $v){
               $v=explode('.',$v);
               $data=$tmpArr;

               foreach($v as $key => $val){

                     $data=$data[$val];

                     if(is_array($data))
                     continue ;
                     else{

                         $title = str_replace($result[0][$k],$data,$title);

                     }

                 }
             }

         }

        return $title;
    }

    function saveContent($action,$msg,$data){
        $systmpl = &$this->app->model('member_systmpl');    
         $info = $this->getParams($msg);  
        if($info['hasTitle']) $this->app->setConf('messenger.title.'.$action.'.'.$msg,$data['title']);
        return $systmpl->set('messenger:'.$msg.'/'.$action,$data['content']);
    }
 
    function &load($item){
        if (!$this->_plugin_obj[$item]) {
           if($obj = kernel::single($item))   return $obj;
           else return null;
        }
        return $this->_plugin_obj[$item];
    }

    function getOptions($item,$valueOnly = false){
        $obj = $this->load($item);
        if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions')){
            $options = $obj->getOptions();      #print_r($options);exit;
            foreach($options as $key=>$value){
                $app = app::get('desktop');
                $v = $app->getConf('email.config.'.$key);
                if($valueOnly){
                    $options[$key] = (is_null($v))?$options[$key]:$v;
                }
                else{
                    $options[$key]['value'] = (is_null($v))?$options[$key]['value']:$v;
                }
            }
            return $options;
        }
    }

     function getParams($msg){
        $aData = $this->getList();
        return $aData[$msg];
    }
}

?>
