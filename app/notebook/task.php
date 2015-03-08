<?php
class notebook_task{
    function post_install($options){pam_account::register_account_type('notebook',
    'member',app::get('notebook')->_('前台会员系统'));
     }
}