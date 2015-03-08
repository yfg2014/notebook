<?php
class notebook_mdl_member extends base_db_model{
    public function getUsername($id){
         $sql = "select login_name from sdb_pam_account where account_id='".$id."'";
         $return = $this->db->selectrow($sql);
         $usrename = $return['login_name'];
         return $usrename;
    }
}