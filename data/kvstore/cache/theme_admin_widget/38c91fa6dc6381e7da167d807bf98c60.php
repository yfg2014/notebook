<?php exit(); ?>a:3:{s:5:"value";s:453:"<div>
  <?php if( $this->_vars['data']['iflogin']==true ){ ?>
  欢迎你，<?php echo $this->_vars['data']['username']; ?>！
  <a href="<?php echo kernel::router()->gen_url(array('app' => 'notebook','ctl' => 'site_passport','act' => 'logout')); ?>">退出</a>
  <?php }else{ ?>
  你好！
  <a href="<?php echo kernel::router()->gen_url(array('app' => 'notebook','ctl' => 'site_passport','act' => 'index')); ?>">登录</a>
  <?php } ?>
</div>";s:3:"ttl";i:0;s:8:"dateline";i:1425797258;}