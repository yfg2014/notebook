<?php
$db['member']=
    array (
       'columns' =>
       array (
          'member_id' =>
          array (
             'type' => 'number',
             'required' => true,
             'extra' => 'auto_increment',
             'pkey' => true,
             ),
          'member_user' =>
          array (
             'type' => 'varchar(100)',
             'in_list'=>true,
             'is_title'=>true,
             'default_in_list'=>true,
             'label'=>'用户名',
             'filtertype'=>true,
             'searchtype'=>true,
             'searchtype' => 'has',
             ),
          'member_password' =>
          array (
             'lable' => '密码',
             'type' => 'varchar(32)',
             ),
          'member_time' =>
          array (
             'in_list'=>true,
             'default_in_list' => true,
             'label' => '注册时间',
             'type' => 'time',
             ),
          'member_email' =>
          array (
             'in_list'=>true,
             'default_in_list' => true,
             'label' => 'email',
             'type' => 'email',
             ),
          ),
    );