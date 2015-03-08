<?php exit(); ?>a:3:{s:5:"value";s:1147:"<style>

.basic-comment .fl{width:80px;}
.basic-comment .fr{width:140px;color:#888888}
.basic-comment h5{height:26px;line-height:26px;border-bottom:2px solid #373737;}
.basic-comment-con{padding:10px;border:1px solid #ececec;}
.basic-comment-con b{font-weight:bolder;height:16px;line-height:16px;overflow:hidden;display:block;margin-bottom:5px;}
.basic-comment-con p{line-height:18px;height:54px;overflow:hidden;text-indent:24px;}
</style>
<div class="basic-comment">
		<h5 class="btm10"><?php echo $this->_vars['setting']['title']; ?></h5>
		<div class="basic-comment-con">
			<ul>
				<?php if($this->_vars['data'])foreach ((array)$this->_vars['data'] as $this->_vars['key'] => $this->_vars['item']){ ?>
					<li class="clearfix btm10">
						<div class="fl"><img src="<?php echo $this->_vars['item']['goodsPic']; ?>" width="80" height="80" alt="<?php echo $this->_vars['item']['goodsName']; ?>" /></div>
						<div class="fr">
								<b><?php echo $this->_vars['item']['commentAuthor']; ?></b>
								<p><?php echo $this->_vars['item']['comment']; ?></p>
						</div>
					</li>
				<?php } ?>
			</ul>
		</div>
</div>";s:3:"ttl";i:0;s:8:"dateline";i:1425797236;}