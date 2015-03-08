<?php exit(); ?>a:3:{s:5:"value";s:3375:"<style>
.class1{color:#ff9900;}
.class2{color:#0099ff;}



.goods950 h5{height:26px;line-height:26px;border-bottom:2px solid #373737;}

.goods950 .switchable-content{border-bottom:1px solid #ececec}
.goods950 .switchable-content li{float:left;width:160px;margin:15px 16px 10px;_display:inline;}
.goods950 .demo-pic{width:160px;height:160px; overflow:hidden;}
.goods950 .demo-pic a{width:160px;height:160px; display:table-cell;text-align:center;vertical-align:middle;}
.goods950 .demo-pic a img{width:160px;}
.goods950 h6 a{height:34px;line-height:18px;font-weight:100;color:#888888;margin-bottom:3px;display:block}
.goods950 li span{font-family:Tahoma;color:#888888}
.goods950 li i{color:#ed1c24;font-family:Verdana;font-weight:bolder;}
</style>



<div class="goods950  border-<?php echo $this->_vars['setting']['goodsColor']; ?>">
				<h5><?php echo $this->_vars['setting']['title']; ?></h5>
				<div class="goods950-ad clearfix" style="margin-top:10px;">
					<div class="w700 fl"><a target="_blank" href="<?php echo $this->_vars['setting']['pic1url']; ?>"><img src="<?php echo $this->_vars['setting']['pic1']; ?>" alt="<?php echo $this->_vars['setting']['pic1txt']; ?>" /></a></div>
					<div class="w250 fr"><a target="_blank" href="<?php echo $this->_vars['setting']['pic2url']; ?>"><img src="<?php echo $this->_vars['setting']['pic2']; ?>" alt="<?php echo $this->_vars['setting']['pic2txt']; ?>" /></a></div>
				</div>
				<ul class="switchable-content clearfix">
						<?php $this->_vars["do"]='1';  if($this->_vars['data']['goodsdata']['goodsRows'])foreach ((array)$this->_vars['data']['goodsdata']['goodsRows'] as $this->_vars['key'] => $this->_vars['goodsdemo']){ ?>
								<li class="class-<?php echo $this->_vars['do']++; ?>">
										<div class="demo-pic">
												<a target="_blank" href="<?php echo $this->_vars['goodsdemo']['goodsLink']; ?>">
													<?php if( $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['pic'] ){ ?>
														<img src="<?php echo $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['pic']; ?>" alt="<?php if( $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'] ){  echo $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'];  }else{  echo $this->_vars['goodsdemo']['goodsName'];  } ?>" />
													<?php }else{ ?>
														<img src="<?php echo $this->_vars['goodsdemo']['goodsPicM']; ?>" alt="<?php if( $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'] ){  echo $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'];  }else{  echo $this->_vars['goodsdemo']['goodsName'];  } ?>"  />
													<?php } ?>
												</a>
										</div>
										<h6>
												<a target="_blank" href="<?php echo $this->_vars['goodsdemo']['goodsLink']; ?>">
													<?php if( $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'] ){  echo $this->_vars['data']['info'][$this->_vars['goodsdemo']['goodsId']]['nice'];  }else{  echo $this->_vars['goodsdemo']['goodsName'];  } ?>
												</a>
										</h6>
										<span><?php echo $this->_vars['setting']['price_show']; ?>：<i><?php echo trigger_error("'cur_odr' modifier does not exist", E_USER_NOTICE);; ?></i></span>
								</li>
						<?php } ?>
				</ul>
</div>";s:3:"ttl";i:0;s:8:"dateline";i:1425797236;}