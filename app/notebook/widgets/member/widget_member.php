<?php
    function widget_member($setting,&$smarty){
       $sess = kernel::single('base_session');
       $sess->start();
       if ($_SESSION['username']!=array()){
           $data['username'] = $_SESSION['username'];
           $data['iflogin'] = true;
       }else{
           $data['iflogin'] = false;
       }
       return $data;
    }

?>