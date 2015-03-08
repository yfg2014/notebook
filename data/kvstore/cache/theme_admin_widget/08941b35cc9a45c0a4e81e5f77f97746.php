<?php exit(); ?>a:3:{s:5:"value";s:4594:"<ul class="MenuList MenuList_<?php echo $this->_vars['widgets_id']; ?> clearfix">
<?php $this->_env_vars['foreach'][wgtmenu]=array('total'=>count($this->_vars['data']),'iteration'=>0);foreach ((array)$this->_vars['data'] as $this->_vars['key'] => $this->_vars['item']){
                        $this->_env_vars['foreach'][wgtmenu]['first'] = ($this->_env_vars['foreach'][wgtmenu]['iteration']==0);
                        $this->_env_vars['foreach'][wgtmenu]['iteration']++;
                        $this->_env_vars['foreach'][wgtmenu]['last'] = ($this->_env_vars['foreach'][wgtmenu]['iteration']==$this->_env_vars['foreach'][wgtmenu]['total']);
 if( $this->_vars['setting']['max_leng'] && $this->_vars['key']>$this->_vars['setting']['max_leng'] ){  if( $this->_vars['item']['custom_url'] != '' ){ ?>
    <div><a href="<?php echo $this->_vars['item']['custom_url']; ?>" <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div>
    <?php }else{ ?>
    <div><a href="<?php echo kernel::router()->gen_url(array('app' => $this->_vars['item']['app'],'ctl' => $this->_vars['item']['ctl'],'act' => $this->_vars['item']['act'],'args' => $this->_vars['item']['params'],'full' => 1)); ?>"  <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div>
    <?php }  }elseif( $this->_vars['key']==$this->_vars['setting']['max_leng'] && $this->_vars['setting']['max_leng'] ){  $this->_vars["page"]="true"; ?>
    <li class="wgt-menu-more" id="<?php echo $this->_vars['widgets_id']; ?>_menu_base"><a class="wgt-menu-view-more" href="javascript:void(0);"><?php echo $this->_vars['setting']['showinfo']; ?></a>
    <div class="v-m-page" style="display:none;position:absolute; top:25px; left:0;" id="<?php echo $this->_vars['widgets_id']; ?>_showMore">
    <?php if( $this->_vars['item']['custom_url'] != '' ){ ?>
    <div><a href="<?php echo $this->_vars['item']['custom_url']; ?>" <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div>
    <?php }else{ ?>
    <div><a href="<?php echo kernel::router()->gen_url(array('app' => $this->_vars['item']['app'],'ctl' => $this->_vars['item']['ctl'],'act' => $this->_vars['item']['act'],'args' => $this->_vars['item']['params'],'full' => 1)); ?>" <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></div>
    <?php }  }else{  if( $this->_vars['item']['custom_url'] != '' ){ ?>
    <li><a <?php if( $this->_env_vars['foreach']['menu']['last'] ){ ?>class="last"<?php } ?> href="<?php echo $this->_vars['item']['custom_url']; ?>" <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></li>
    <?php }else{ ?>
    <li><a href="<?php echo kernel::router()->gen_url(array('app' => $this->_vars['item']['app'],'ctl' => $this->_vars['item']['ctl'],'act' => $this->_vars['item']['act'],'args' => $this->_vars['item']['params'],'full' => 1)); ?>" <?php if( $this->_vars['item']['target_blank'] == 'true' ){ ?>target="_blank"<?php } ?>><?php echo $this->_vars['item']['title']; ?></a></li>
    <?php }  }  } unset($this->_env_vars['foreach'][wgtmenu]);  if( $this->_vars['page']=="true" ){ ?>
</div>
</li>
<?php } ?>
</ul>

<script>
window.addEvent('domready',function(){
	var hrf=location.href.split('/').getLast(), n=hrf.lastIndexOf('-'),
		menulist=$$('.MenuList_<?php echo $this->_vars['widgets_id']; ?> li');

	if(n>-1)hrf=hrf.substring(0,n);
	if(!hrf.trim().length) hrf='index.html';

	var reg=new RegExp('\/'+hrf,'i');
	menulist.each(function(el){
		var link=el.getElement('a');
		if(link&&link.href.test(reg))
		el.addClass('<?php echo $this->_vars['setting']['className']; ?>');

	});

    var showMore = $('<?php echo $this->_vars['widgets_id']; ?>_showMore');
    if(showMore){
        var menuBase = $('<?php echo $this->_vars['widgets_id']; ?>_menu_base');
        document.addEvent('click',function(e){
            if(e.target != menuBase && e.target != menuBase.getElement('a')){
                showMore.style.display='none';
            }else{
                if(showMore.style.display=='none'){
                    showMore.style.display='';
                }else{
                    showMore.style.display='none';
                }
            };
        });
    }
});

</script>
";s:3:"ttl";i:0;s:8:"dateline";i:1425797237;}