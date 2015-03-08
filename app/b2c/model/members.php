<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_members extends dbeav_model{
    var $has_tag = true;
    var $defaultOrder = array('regtime','DESC');
    var $has_many = array(
        'contact/other'=>'member_addrs:append',
        'advance/event'=>'member_advance:append:member_id^member_id',
        'score/event'=>'member_point:append',
    );

    var $has_parent = array(
        'pam_account' => 'account@pam'
    );

    var $subSdf = array(
        'default' => array(
            'pam_account:account@pam' => array('*'),
         )
    );

    static private $member_info;
    function __construct($app){
        parent::__construct($app);
        $this->use_meta();  //member中的扩展属性将通过meta系统进行存储
    }

    function save(&$sdf,$mustUpdate=null){
        if(isset($sdf['member_id']) && !isset($sdf['pam_account']['account_id'] )){
            $sdf['pam_account']['account_id'] = $sdf['member_id'];
        }
        if(isset($sdf['profile']['gender'])){
            if($sdf['profile']['gender']=='male'){
            $sdf['profile']['gender']=1;
            }elseif($sdf['profile']['gender']=='female'){
               $sdf['profile']['gender']=0;
            }else{
                unset($sdf['profile']['gender']);
            }
        }
        if(isset($sdf['profile']['birthday']) && $sdf['profile']['birthday']){
              $data = explode('-',$sdf['profile']['birthday']);
              $sdf['b_year']=intval($data[0]);$sdf['b_month']=intval($data[1]);$sdf['b_day']=intval($data[2]);
            unset($sdf['profile']['birthday']);
        }
        $sdf['contact']['addr'] = htmlspecialchars($sdf['contact']['addr']);
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($sdf,'b2c_mdl_members',__FUNCTION__);
        parent::save($sdf);
        #$this->save_member_info_kv($sdf['member_id']);
        return true;
    }


    function dump($filter,$field = '*',$subSdf = null){
        if($ret = parent::dump($filter,$field,$subSdf)){
            $ret['profile']['birthday'] = $ret['b_year'].'-'.$ret['b_month'].'-'.$ret['b_day'];
            if($ret['profile']['gender']== 1){
                $ret['profile']['gender'] = 'male';
            }
            elseif($ret['profile']['gender']== 0){
                $ret['profile']['gender'] = 'female';
            }
            else{
                $ret['profile']['gender'] = 'no';
            }
        }

        //ajx 修改当订单导出时用户名显示问题 和邮件发送时获取会员信息的问题
        if(isset($ret) && !$ret['pam_account']){
            $list = app::get('pam')->model('account')->getList('login_name',array('account_id'=>$filter['member_id']));
            $ret['contact']['name'] = ($list[0]['login_name'])?$list[0]['login_name']:'匿名购买';
        }
        if(intval($filter) == 0 || (is_array($filter) && intval($filter['member_id']) == 0)){
            $ret['contact']['name'] = "匿名购买";
        }
        return $ret;
    } 

    function edit($nMemberId,$aMemInfo){
        $sdf=$this->dump($nMemberId,'*');
        $sdf['profile']['gender'] = $aMemInfo['gender'];
        $sdf['contact']['name'] = $aMemInfo['name'];
        $sdf['contact']['area'] = $aMemInfo['area'];
        $sdf['contact']['addr'] = $aMemInfo['addr'];
        $sdf['contact']['zipcode'] = $aMemInfo['zipcode'];
        $sdf['contact']['email'] = $aMemInfo['email'];
        $sdf['contact']['phone']['telephone'] = $aMemInfo['telephone'];
        $sdf['contact']['phone']['mobile'] = $aMemInfo['mobile'];
        $sdf['member_lv']['member_group_id'] = $aMemInfo['member_group_id'];
        $sdf['account']['pw_question'] = $aMemInfo['pw_question'];
        $sdf['account']['pw_answer'] = $aMemInfo['pw_answer'];
        if(is_numeric($aMemInfo['birthday'])){
            $aMemInfo['birthday'] = date('Y-m-d',$aMemInfo['birthday']);
        }
        $sdf['profile']['birthday'] = $aMemInfo['birthday'];

        return $this->save($sdf);

    }

     //密码修改
    function save_security($nMemberId,$aData,&$msg){

        $aMem = $this->dump($nMemberId,'*',array(':account@pam'=>array('*')));
        if(!$aMem){
            $msg=app::get('b2c')->_('无效的用户Id');
            return false;
        }
        $member_sdf['member_id'] = $nMemberId;
        //如果密码是空的则进入安全问题修改过程
        if(empty($aData['passwd'])){
            if( !$aData['pw_answer'] || !$aData['pw_question'] ){
                $msg=app::get('b2c')->_('安全问题修改失败！');
                return false;
            }
            $member_sdf = $this->dump($nMemberId,'*');
            $member_sdf['account']['pw_question'] = $aData['pw_question'];
            $member_sdf['account']['pw_answer'] = $aData['pw_answer'];
             $msg=app::get('b2c')->_('安全问题修改成功');
            return $this->save($member_sdf);
        } else{
            $use_pass_data['login_name'] = $aMem['pam_account']['login_name'];
            $use_pass_data['createtime'] = $aMem['pam_account']['createtime'];
            if(pam_encrypt::get_encrypted_password(trim($aData['old_passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data)!= $aMem['pam_account']['login_password']){
                $msg=app::get('b2c')->_('输入的旧密码与原密码不符！');
                return false;
            }

            if($aData['passwd'] != $aData['passwd_re']){
                $msg=app::get('b2c')->_('两次输入的密码不一致！');
                return false;
            }

            if( strlen($aData['passwd']) <  4 ){
                 $msg=app::get('b2c')->_('密码长度不能小于4');
                 return false;
             }

             if( strlen($aData['passwd']) > 20 ){
                 $msg=app::get('b2c')->_('密码长度不能大于20');
                 return false;
             }
             $aMem['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($aData['passwd']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
             $aMem['pam_account']['account_id'] = $nMemberId;
             if($this->save($aMem)){
                $aData = array_merge($aMem,$aData);
                $data['email'] = $aMem['contact']['email'];
                $data['uname'] = $aMem['pam_account']['login_name'];
                $data['passwd'] = $aData['passwd_re'];
                $obj_account=&$this->app->model('member_account');
                 $obj_account->fireEvent('chgpass',$data,$nMemberId);
                $msg = app::get('b2c')->_("密码修改成功");
                 return true;
             }else{
                $msg=app::get('b2c')->_('密码修改失败！');
                return false;
             }
         }
     }

    function getMemberByUser($uname)    {
        if($ret = $this->getList('*',array('pam_account'=>array('login_name'=>$uname)) )){
            return $ret[0];
        }
        return false;
     }

     /*根据查询字符串返回UNMAE 数组
       litie@shopex.cn
     */
     function getUserNameLikeStr($str,$dataType='json'){

         if(!$str||$str !=''){
			 $str = $this->db->quote($str);
            $sql  = 'select uname from '.$this->tableName.' where uname like "'.$str.'%" and disabled=false';
         }else if($str == '_ALL_'){
            $sql  = 'select uname from '.$this->tableName.' where disabled=false';
         }
         $data = $this->db->select($sql);

         if($dataType!='json')return $data;

         return json_decode($data,true);

     }


     function getMemberAddr($nMemberId){
            $objMemberAddr = $this->app->model('member_addrs');
            return $objMemberAddr->getList('*',array('member_id'=>$nMemberId));
     }

     function getAddrById($nAddrId){
            $objMemberAddr = $this->app->model('member_addrs');
            return $objMemberAddr->dump($nAddrId);

     }

      function isAllowAddr($nMemberId){
         $objMemberAddr = $this->app->model('member_addrs');
         $aAddr = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId));
         if(count($aAddr) < $objMemberAddr->addrLimit){
            return true;
        }else{
            return false;
        }
    }

     //插入收货人地址
    function insertRec($aData,$nMemberId,&$message){
        foreach ($aData as $key=>$val){
            if(is_string($val))
            $aData[$key] = trim($val);
            if(empty($aData[$key])){
                switch ($key){
                case 'name':
                    $message = app::get('b2c')->_('姓名不能为空！');
                    return false;
                    break;
                case 'zipcode':
                    $message = app::get('b2c')->_('邮编不能为空！');
                    return false;
                    break;
                case 'area':
                    $message = app::get('b2c')->_('地区不能为空！');
                    return false;
                    break;
                default:
                    break;
                }
            }
        }
        if(!is_numeric($aData['zipcode'])||strpos($aData['zipcode'],".")!==false){
            $message = app::get('b2c')->_("邮政编码必须是数字");
            return false;
        }
        if($aData['phone']['telephone']){
            if(!is_numeric($aData['phone']['telephone'])||strpos($aData['phone']['telephone'],".")!==false){
                $message = app::get('b2c')->_("手机必须是数字");
                return false;
            }
        }
        if($aData['phone']['telephone'] == '' && $aData['phone']['mobile'] == ''){
            $message = app::get('b2c')->_('联系电话和手机不能都为空！');
            return false;
        }

        $aData['member_id'] = $nMemberId;
        $at = explode(':',$aData['area']);
        $area['area_type'] = $at[0];
        $area['sar'] = explode('/',$at[1]);
        $area['id'] = $at[2];
        $aData['area'] = $area;

        $objMemberAddr = $this->app->model('member_addrs');
        if($objMemberAddr->is_exists_addr($aData,$nMemberId)){
            $message = app::get('b2c')->_('收货地址重复');
            return false;
        }
        if($objMemberAddr->save($aData)){
            $message = app::get('b2c')->_('保存成功！');
            return true;
        }else{
            $message = app::get('b2c')->_('保存失败！');
            return false;
        }
    }

      //设为默认收获地址
    function set_to_def($addrId,$nMemberId,&$message,$disabled){
        $disabled = intval($disabled);
        if($addrId){
           $objMemberAddr = $this->app->model('member_addrs');
           $row = $objMemberAddr->getList('addr_id',array('addr_id' => $addrId));
           if(!$row){
                   $message = app::get('b2c')->_('参数错误！');
                   return false;
               }
           if( ($row = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId,'def_addr'=>1))) and $disabled === 2){
                $data['def_addr'] =  0;
                $filter = array('addr_id'=> $row[0]['addr_id']);
                if(!$objMemberAddr->update($data,$filter)){
                    $message = app::get('b2c')->_('设置失败！');
                    return false;
                }
            }
            $data['def_addr'] = $disabled === 2 ? 1 : 0;
            $filter = array('addr_id'=> $addrId);
            if($objMemberAddr->update($data,$filter)){
                return true;
            }else{
               $message = app::get('b2c')->_('设置失败！');
                return false;
            }
        }else{
            $message = app::get('b2c')->_('参数错误！');
            return false;
        }
    }

      //保存修改
    function save_rec($aData,$nMemberId,&$message){

        if($aData['phone']['telephone'] == '' && $aData['phone']['mobile'] == ''){
            $message = app::get('b2c')->_('联系电话和手机不能都为空！');
            return false;
        }
        if(!is_numeric($aData['zipcode'])||strpos($aData['zipcode'],".")!==false){
            $message = app::get('b2c')->_("邮政编码必须是数字");
            return false;
        }
        if($aData['phone']['telephone']){
            if(!is_numeric($aData['phone']['telephone'])||strpos($aData['phone']['telephone'],".")!==false){
                $message = app::get('b2c')->_("手机必须是数字");
                return false;
            }
        }
        #print_r($aData);exit;
        $objMemberAddr = $this->app->model('member_addrs');
        if($aData['default'] ){
             $row = $objMemberAddr->getList('addr_id',array('member_id'=>$nMemberId,'def_addr'=>1));
             $defaultAddrId = $row['0']['addr_id'];
             //关闭当前默认地址
             if($defaultAddrId != $aData['addr_id']){
                $addr_sdf['addr_id'] = $defaultAddrId;
                $addr_sdf['default'] = 0;
                $objMemberAddr->save($addr_sdf);
             }
        }
        $at = explode(':',$aData['area']);
        $area['area_type'] = $at[0];
        $area['sar'] = explode('/',$at[1]);
        $area['id'] = $at[2];
        $aData['area'] = $area;
        if($objMemberAddr->is_exists_addr($aData,$nMemberId)){
            $message = app::get('b2c')->_('收货地址重复');
            return false;
        }
         if($objMemberAddr->save($aData)){
            return true;
        }else{
            $message = app::get('b2c')->_('保存地址失败');
            return false;
        }
    }
    //删除
    function del_rec($addrId=null,&$message,$member_id=null){
        if($addrId && $member_id){
            $member_addr = &$this->app->model('member_addrs');
             $filter = array('addr_id'=>$addrId,'member_id' => $member_id);
             if($member_addr->delete($filter)){
                  $meesage = app::get('b2c')->_("删除成功");
                   return true;
             }
             else{
                 $meesage = app::get('b2c')->_("删除失败");
                   return true;
             }

        }else{
            $message = app::get('b2c')->_("参数有误");
             return false;
        }

    }

     public function check_addr($addrId=null,$member_id=null){
         $member_addr = &$this->app->model('member_addrs');
        if(!$addrId && !$member_id) return false;
        $row = $member_addr->getList('addr_id',array('addr_id' => $addrId, 'member_id' => $member_id));
        if($row) return true;
        else return false;
    }

    function checkUname($uname,&$message){
        $uname = trim($uname);
        $len = strlen($uname);
        if($len<3){
            $message = app::get('b2c')->_('用户名过短!');
            return false;
        }elseif($len>20){
            $message = app::get('b2c')->_('用户名过长!');
            return false;
        }elseif(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $uname)){
            $message = app::get('b2c')->_('用户名包含非法字符!');
            return false;
        }else{
			$uname = $this->db->quote($uname);
            $row = $this->db->selectrow("select uname from sdb_b2c_members where uname='{$uname}'");
            if($row['uname']){
                $message = app::get('b2c')->_('重复的用户名!');
                return false;
            }else{
                return true;
            }
        }
    }

    function get_id_by_uname($uname){
        $pam_account = app::get('pam')->model('account');
        if($ret = $pam_account->getList('account_id',array('login_name'=>$uname)) ){
            return $ret[0]['account_id'];
        }

        return false;
    }


    function getOrderByMemId($nMemberId=null,$start=0,$limit=10){
        if(!$nMemberId) return array();
        $objOrder = $this->app->model('orders');
        $aOrderList = $objOrder->getList('order_id,status,pay_status,ship_status,total_amount,createtime ',array('member_id'=>$nMemberId ),$start,$limit);
        return $aOrderList;
    }

    function  getRemarkByMemId($nMemberId){
        $row = $this->getList('remark,remark_type',array('member_id'=>$nMemberId ));
        return $row[0];
    }

   ##注册会员检查
    function validate(&$data,&$msg){
        $flg = 1;
        $unamelen = strlen($data['pam_account']['login_name']);
        if($unamelen < 3){
            $msg = app::get('b2c')->_('长度不能小于3');
            $flg = 0;
        }
        if($this->is_exists($data['pam_account']['login_name'])){
            $msg = app::get('b2c')->_('该用户名已经存在');
            $flg = 0;
        }
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $data['pam_account']['login_name'])){
            $msg = app::get('b2c')->_('用户名含非法字符');
            $flg = 0;
        }
        if($unamelen>20){
            $msg = app::get('b2c')->_('用户名过长!');
            $flg = 0;
        }
       if(!preg_match('/\S+@\S+/',$data['contact']['email'])){
           $msg = app::get('b2c')->_('邮件格式不正确');
           $flg = 0;
        }
        if(($this->is_exists_email($data['contact']['email']))){
           $msg = app::get('b2c')->_('该邮箱已经存在');
           $flg = 0;
        }
        $passwdlen = strlen($data['pam_account']['login_password']);
        if($passwdlen<4){

            $msg = app::get('b2c')->_('密码长度不能小于4');
            $flg = 0;
        }
        if($passwdlen>20){
            $msg = app::get('b2c')->_('密码长度不能大于20');
            $flg = 0;
        }
        if($data['pam_account']['login_password'] != $data['pam_account']['psw_confirm']){
            $msg = app::get('b2c')->_('输入的密码不一致');
            $flg = 0;
        }
        if($data['contact']['name']&&!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $data['contact']['name'])){
            $msg = app::get('b2c')->_('用户姓名含非法字符');
            $flg = 0;
        }
        return $flg;
    }

    function gen_secret_str($member_id){
        $row=app::get('pam')->model('account')->dump($member_id);
        $row['login_name'] = md5($row['login_name']);
        $row['login_password'] = md5($row['login_password'].STORE_KEY);
        return $member_id.'-'.utf8_encode($row['login_name']).'-'.$row['login_password'].'-'.time();
    }

    function create($data){
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['pam_account']['account_type'] = pam_account::get_account_type($this->app->app_id);
        $data['pam_account']['createtime'] = time();
        $data['reg_ip'] = base_request::get_remote_addr();
        $data['regtime'] = time();
        $data['pam_account']['login_name'] = strtolower($data['pam_account']['login_name']);
        $use_pass_data['login_name'] = $data['pam_account']['login_name'];
        $use_pass_data['createtime'] = $data['pam_account']['createtime'];
        $data['pam_account']['login_password'] = pam_encrypt::get_encrypted_password(trim($data['pam_account']['login_password']),pam_account::get_account_type($this->app->app_id),$use_pass_data);
        $this->save($data);
        return $data['member_id'];
    }

    function is_exists($uname){
        $account_type = pam_account::get_account_type($this->app->app_id);
        $obj_pam_account = new pam_account($account_type);
        return $obj_pam_account->is_exists($uname);
    }

    function is_exists_email($email=null,$member_id=null){
        if(!$email) return true;
        $aEmail = $this->getList('member_id',array('email' => $email));
        if($aEmail && !$member_id) return true;
        if($aEmail && ($member_id != $aEmail[0]['member_id'])) return true;
        return false;
    }
    ####修改经验值
    function change_exp($member_id,$experience,&$msg=''){
        $aMem = $this->dump($member_id,'*',array('contact'=>array('*')));
        if(!$aMem) return null;
        if(!is_numeric($experience)||strpos($experience,".")!==false){
            $msg = app::get('b2c')->_("请输入整数值");
            return false;
        }
        if($experience<0){
            if($aMem['experience']<-$experience){
                $msg = app::get('b2c')->_('经验值不足!');
                return false;
            }
        }
        $experience += $aMem['experience'];
        $aMem['experience'] = $experience;
        if($this->app->getConf('site.level_switch')==1){
            $aMem['member_lv']['member_group_id'] = $this->member_lv_chk($aMem['member_lv']['member_group_id'],$experience);
        }
        $aMem['member_id'] = $member_id;
        if($aMem['member_id'] && $this->save($aMem)){
                return true;
        }else{
                $msg = app::get('b2c')->_('保存失败!');
                return false;
         }
        }

     ###根据经验值修改会员等级

    function member_lv_chk($member_lv_id,$experience){
        $current_member_lv_id = $member_lv_id;
        $objmember_lv = $this->app->model('member_lv');
        $objmember_lv->defaultOrder = array('experience', ' ASC');
        $sdf_lv = $objmember_lv->getList('*');
        foreach($sdf_lv as $sdf){
            if($experience>=$sdf['experience']) $member_lv_id = $sdf['member_lv_id'];
        }
        $current_row = $objmember_lv->getList('experience',array('member_lv_id' => $current_member_lv_id));
        $after_row = $objmember_lv->getList('experience',array('member_lv_id' => $member_lv_id));
        if($current_row[0]['experience']>=$after_row[0]['experience'])
        return $current_member_lv_id;
        return $member_lv_id;
    }
    ##进回收站前操作
     function pre_recycle($data){
        $falg = true;
        $obj_pam = app::get('pam')->model('account');
        foreach($data as $val){
            if($val['advance']>0)
            {
                $this->recycle_msg = app::get('b2c')->_('会员存在预存款,不能删除');
                $falg = false;
            break;
            }
        }
        return $falg;
   }

    function pre_restore(&$data,$restore_type='add'){
         if(!$this->is_exists($data['pam_account']['login_name'])){
            $data[$this->schema['idColumn']] = $data['pam_account']['account_id'];
            $data['need_delete'] = true;
            return true;
         }
         else{

             if($restore_type == 'add'){
                    $new_name = $data['pam_account']['login_name'].'_1';
                    while($this->is_exists($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['pam_account']['login_name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                    $data['need_delete'] = false;
                    return true;
             }
         }
    }

    function title_modifier($id){
        if ($id === 0 || $id == '0'){
            return app::get('b2c')->_('非会员顾客');
        }
        else{
            $obj_member = app::get('pam')->model('account');
            $sdf = $obj_member->dump($id);
            return $sdf['login_name'];
        }

    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){

        foreach(kernel::servicelist('b2c_mdl_members.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }

        if($filter['member_key']){
            $aData = app::get('pam')->model('account')->getList('account_id',array('login_name|has' => $filter['member_key']));
            if($aData){
                foreach($aData as $key=>$val){
                    $member[$key] = $val['account_id'];
                }
                $filter['member_id'] = $member;
            }
            else{
                return 0;
            }
            unset($filter['member_key']);
        }
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($filter,'b2c_mdl_members',__FUNCTION__);
        $filter = parent::_filter($filter);

        return $filter;
    }

    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                if ($k == 'member_id')
                {
                    $columns['member_key'] = $v['label'];
                }
                else
                    $columns[$k] = $v['label'];
            }
        }

        return $columns;
    }

    /**
     * @根据会员ID获取会员等级名称
     * @access public
     * @param $cols 查询字段
     * @param $sLv 会员等级id
     * @return void
     */
    public function get_lv_name($member_id)
    {
        if(empty($member_id) || $member_id < 0){
            return null;
        }
        $row = $this->db->selectrow('SELECT mlv.name FROM sdb_b2c_members AS m
                                                        LEFT JOIN sdb_b2c_member_lv  AS mlv ON m.member_lv_id=mlv.member_lv_id
                                                        WHERE mlv.disabled = \'false\' AND m.member_id = '.intval($member_id));
        return $row['name'];
    }

    /**
     * 重写getList方法
     * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_member = parent::getList($cols, $filter, $offset, $limit, $orderType);
        $mem_point = $this->app->model('member_point');

		foreach ($arr_member as $key=>$arr)
		{
			if ($arr['member_id'])
				$arr_member[$key]['point'] = $mem_point->get_total_count($arr['member_id']);
		}
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($arr_member,'b2c_mdl_members',__FUNCTION__);
		return $arr_member;
	}

	public function title_recycle($sdf)
	{
		if(!$sdf) return ;
		return $sdf['pam_account']['login_name'] ? $sdf['pam_account']['login_name']:'';
	}

	/**
	 * 得到当前登陆用户的信息
	 * @param null
	 * @return array 用户信息
	 */
	public function get_current_member()
	{
		if (!$this->app->member_id){
			kernel::single('base_session')->start();
			$this->app->member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];
		}
        if($this->member_info) return $this->member_info;
        #获取会员基本信息 jiaolei
        return $this->get_member_info( $this->app->member_id );
    }

    //入参 会员id return会员信息
    public function get_member_info( $member_id ) {
        #$member_sdf = $this->dump($member_id,"*",array(':account@pam'=>array('*')));
        $member_sdf = $this->db->selectrow("select p.login_name,m.member_id,m.name,m.sex,m.point,m.experience,m.email,m.member_lv_id,cur,advance
        from sdb_b2c_members as m left join sdb_pam_account as p on m.member_id = p.account_id where m.member_id=".intval($member_id));
        $service = kernel::service('pam_account_login_name');
        if(is_object($service)){
            if(method_exists($service,'get_login_name')){
                $member_sdf['pam_account']['login_name'] = $service->get_login_name($member_sdf['pam_account']);
            }
        }
        if( !empty($member_sdf) ) {
            $this->member_info['member_id'] = $member_sdf['member_id'];
            $this->member_info['uname'] =  $member_sdf['login_name'];
            $this->member_info['name'] = $member_sdf['name'];
            $this->member_info['sex'] =  $member_sdf['sex'] == 1 ?'男':'女';
            $this->member_info['point'] = $member_sdf['point'];
            $this->member_info['usage_point'] = $this->member_info['point'];
            $obj_extend_point = kernel::service('b2c.member_extend_point_info');
            if ($obj_extend_point)
            {
                // 当前会员拥有的积分
                $obj_extend_point->get_real_point($this->member_info['member_id'], $this->member_info['point']);
                // 当前会员实际可以使用的积分
                $obj_extend_point->get_usage_point($this->member_info['member_id'], $this->member_info['usage_point']);
            }
            $this->member_info['experience'] = $member_sdf['experience'];
            $this->member_info['email'] = $member_sdf['email'];
            $this->member_info['member_lv'] = $member_sdf['member_lv_id'];
            $this->member_info['member_cur'] = $member_sdf['cur'];
            $this->member_info['advance'] = $member_sdf['advance'];
            #获取会员等级
            $obj_mem_lv = &$this->app->model('member_lv');
            $levels = $obj_mem_lv->getList("name",array("member_lv_id"=>$member_sdf['member_lv_id']));
            if($levels['disabled']=='false'){
                $this->member_info['levelname'] = $levels[0]['name'];
            }
        }
        return $this->member_info;
    }

    /**
    *直接调用父类UPDATE方法 完全是为了把会员信息存入KV by hanbingshu
    */
    public function update($data,$filter,$mustUpdate = null)
    {
        return parent::update($data,$filter,$mustUpdate);
        #return $this->save_member_info_kv($filter['member_id']);
    }
    /*会员数据存入KV by hanbingshu*/
    //function save_member_info_kv($member_id)
    //{
    //    $member_sdf = $this->dump($member_id,"*",array(':account@pam'=>array('login_name')));
    //    if( !empty($member_sdf) ) {
    //        $member_info['member_id'] = $member_id;
    //        $member_info['uname'] =  $member_sdf['pam_account']['login_name'];
    //        $member_info['name'] = $member_sdf['contact']['name'];
    //        $member_info['sex'] =  $member_sdf['profile']['gender'];
    //        $member_info['point'] = $member_sdf['score']['total'];
    //        $member_info['usage_point'] = $this->member_info['point'];
    //        $obj_extend_point = kernel::service('b2c.member_extend_point_info');
    //        if ($obj_extend_point)
    //        {
    //            // 当前会员拥有的积分
    //            $obj_extend_point->get_real_point($member_info['member_id'], $member_info['point']);
    //            // 当前会员实际可以使用的积分
    //            $obj_extend_point->get_usage_point($member_info['member_id'], $member_info['usage_point']);
    //        }
    //        $member_info['experience'] = $member_sdf['experience'];
    //        $member_info['email'] = $member_sdf['contact']['email'];
    //        $member_info['member_lv'] = $member_sdf['member_lv']['member_group_id'];
    //        $member_info['member_cur'] = $member_sdf['currency'];
    //        $member_info['advance'] = $member_sdf['advance'];
    //        #获取会员等级
    //        $obj_mem_lv = &$this->app->model('member_lv');
    //        $levels = $obj_mem_lv->getList('name',array('member_group_id'=>$member_sdf['member_lv']['member_group_id']));
    //        if($levels[0]['disabled']=='false'){
    //            $member_info['levelname'] = $levels[0]['name'];
    //        }
    //    }
    //    return base_kvstore::instance('b2c_member_info')->store('member_info_'.$member_id,$member_info);
    //}
    /*从kvstore里获取会员信息 by hanbingshu*/
    //function get_member_info_kv($member_id)
    //{
    //    if(base_kvstore::instance('b2c_member_info')->fetch('member_info_'.$member_id, $contents)=== false)
    //        $contents = $this->get_member_info($member_id);
    //    return $contents;
    //}
}
