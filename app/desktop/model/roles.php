<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class desktop_mdl_roles extends dbeav_model{

##进回收站前操作
    function pre_recycle($data)
    {
        $falg = true;
        $obj_hasrole = app::get('desktop')->model('hasrole');
        $arr_role = array();
        foreach($data as $val){
            $arr_role[] = $val['role_id'];
        }
        $row = $obj_hasrole->getList('role_id',array('role_id' => $arr_role));
        if($row){
            $this->recycle_msg = app::get('desktop')->_('角色下存在管理员,不能删除');
            $falg = false;
        }
        return $falg;
    }
    
    /*从回收站恢复*/
    function pre_restore(&$data,$restore_type='add'){
         if(!$this->is_exists($data['role_name']) && $restore_type == 'add'){
            
             $data['need_delete'] = true;
             return true;
         }
         elseif($this->is_exists($data['role_name'])){
             if($restore_type == 'add'){
                    $new_name = $data['role_name'].'_1';
                    while($this->is_exists($new_name)){
                        $new_name = $new_name.'_1';
                    }
                    $data['role_name'] = $new_name;
                    $data['need_delete'] = true;
                 return true;
             }
             if($restore_type == 'none'){
                    return true;
             }
         }
         else
         {
            $data['need_delete'] = true;
            return true;    
         }
    }
    
    function is_exists($role_name)
    {
        $row_data = $this->getList('role_id',array('role_name'=>$role_name));
        if($row_data)
            return true;
        else
            return false;
    }
    function getAllActions(){
        $actions = array(
            '1'=>app::get('desktop')->_('商品'),
            '2'=>app::get('desktop')->_('订单'),
            '3'=>app::get('desktop')->_('会员'),
            '4'=>app::get('desktop')->_('营销推广'),
            '5'=>app::get('desktop')->_('页面管理'),
            '6'=>app::get('desktop')->_('统计报表'),
            '7'=>app::get('desktop')->_('商店配置'),
            '8'=>app::get('desktop')->_('工具箱'),
        );
        if($this->app->getConf('certificate.distribute')){
            $actions['29'] = app::get('desktop')->_('采购中心');
        }
        
        return $actions;
    }

    function rolemap(){
        $map = array(
            'goods'=>1,
            'order'=>2,
            'member'=>3,
            'sale'=>4,
            'site'=>5,
            'analytics'=>6,
            'setting'=>7,
            'tools'=>8,
        );
        if($this->app->getConf('certificate.distribute')){
            $map['distribution'] = 29;
        }
        
        return $map;
    }


    function getColumns(){
        $ret = array('_cmd'=>array('label'=>app::get('desktop')->_('操作'),'width'=>75,'html'=>'admin/roles_cmd.html'));
        return array_merge($ret,parent::getColumns());
    }

    function instance($role_id){
        $role = parent::instance($role_id);
        if($role){
            $rows = $this->db->select('select * from sdb_lnk_acts where role_id='.intval($role_id));
            foreach($rows as $r){
                $role['actions'][] = $r['action_id'];
            }
        }
        return $role;
    }

    function updatebak($data,$filter){
        $c = parent::update($data,$filter);

        if($filter['role_id']){
            $role_id = array();
            foreach($this->getList('role_id',$filter) as $r){
                $role_id[] = $r['role_id'];
            }
        }else{
            $role_id = $filter['role_id'];
        }

        if(count($role_id)==1){
            $rows = $this->db->select('select action_id from sdb_lnk_acts where role_id in ('.implode(',',$role_id).')');
            $in_db = array();
            foreach($rows as $r){
                $in_db[] = $r['action_id'];
            }
            $data['actions'] = $data['actions']?$data['actions']:array();
            $to_add = array_diff($data['actions'],$in_db);
            $to_del = array_diff($in_db,$data['actions']);

            if(count($to_add)>0){
                $sql = 'INSERT INTO `sdb_lnk_acts` (`role_id`,`action_id`) VALUES ';
                foreach($to_add as $action_id){
                    $actions[] = "({$role_id[0]},$action_id)";
                }
                $sql .= implode($actions,',').';';
                $a = $this->db->exec($sql);
            }

            if(count($to_del)>0){
                $this->db->exec('delete from sdb_lnk_acts where action_id in ('.implode(',',$to_del).') and role_id='.intval($role_id[0]));
            }
        }else{

        }

        return $c;
    }

    function insert($data){
        $role_id = parent::insert($data);
        if($role_id && is_array($data['actions'])){
            $sql = 'INSERT INTO `sdb_lnk_acts` (`role_id`,`action_id`) VALUES ';
            foreach($data['actions'] as $action_id){
                $actions[] = "($role_id,$action_id)";
            }
            $sql .= implode($actions,',').';';
            $a = $this->db->exec($sql);
        }
        return $role_id;
    }
   ####检查工作组名称
   function check_gname($name){
      # $result = $this->db->select("select * from sdb_desktop_roles where role_name='$name'");
    $result = $this->getList('role_id',array('role_name'=>$name));
       if($result){
           
           return $result[0]['role_id'];
       }
       else{
           return false;
       }
   }
   
   function validate($aData,&$msg){
        if($aData['role_name']==''){
        $msg = app::get('desktop')->_("工作组名称不能为空");
        return false;
        }
        if(!$aData['workground']){
        $msg = app::get('desktop')->_("请至少选择一个权限");
        return false;
        }
        $opctl = &$this->app->model('roles');
        $result = $opctl->check_gname($aData['role_name']);
        if($result){
        $msg = app::get('desktop')->_("该名称已经存在");
        return false;    
         }
         return true;
     }
}
?>
