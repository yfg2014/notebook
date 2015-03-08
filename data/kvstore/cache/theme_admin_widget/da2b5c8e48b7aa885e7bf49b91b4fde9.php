<?php exit(); ?>a:3:{s:5:"value";s:1623:"<style>
.norlcate{border:1px solid #ececec;padding:15px;}
.norlcate li.goodscat1{margin-bottom:10px;}
.norlcate .class-14{border:none;}
.norlcate .cat1 .cat-a{display:block;float:left;margin-right:10px;width:65px;height:24px;line-height:24px;background:#eeeef0;color:#363638;text-align:center;}
.norlcate .cat1 a{color:#6a6a6a;}
.norlcate .cat1 a.cat-a:hover{text-decoration:none;}
.norlcate .cat1 .cat-ul{float:left;height:24px;line-height:24spx;overflow:hidden;}
.norlcate .cat-ul li{float:left;display:inline;margin:0 5px;line-height:24px;}
.norlcate .cat-ul li a{line-height:18px;padding:3px 5px;}
.norlcate .cat-ul li a:hover{color:#ffffff;background:#ff0000;}
</style>
<div class="norlcate">
	<ul class="cat1">
		<?php $this->_vars["do"]='1';  if($this->_vars['data'])foreach ((array)$this->_vars['data'] as $this->_vars['key'] => $this->_vars['goodscat']){ ?>
			<li class="clearfix class-<?php echo $this->_vars['do']++; ?> goodscat1">

				<span class="cat-a" title="<?php echo $this->_vars['goodscat']['catName']; ?>"><?php echo $this->_vars['goodscat']['catName']; ?></span>
				<?php if( $this->_vars['goodscat']['items'] ){ ?>
					<ul class="clearfix cat-ul">
						<?php if($this->_vars['goodscat']['items'])foreach ((array)$this->_vars['goodscat']['items'] as $this->_vars['goodscat1']){ ?>
							<li><a target="_blank" title="<?php echo $this->_vars['goodscat1']['catName']; ?>" href="<?php echo $this->_vars['goodscat1']['catLink']; ?>"><?php echo $this->_vars['goodscat1']['catName']; ?></a></li>
						<?php } ?>
					</ul>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
</div>";s:3:"ttl";i:0;s:8:"dateline";i:1425797236;}