<?php exit(); ?>a:3:{s:5:"value";s:1436:"<?php $this->__view_helper_model['base_view_helper'] = kernel::single('base_view_helper'); ?><form action="<?php echo kernel::router()->gen_url(array('app' => b2c,'ctl' => site_search,'act' => result)); ?>" method="post" class="SearchBar  searchBar_<?php echo $this->_vars['widget_id']; ?>" >
<div class="search_label">
     <input type="text" name="name[]" value="<?php echo $this->_vars['data']['search_key']; ?>" autocompleter="associate_autocomplete_goods:name,goods_id" ac_options="{}" class="inputstyle keywords"  />
     <input type="submit" value="<?php $this->_tag_stack[] = array('t', array('app' => 'b2c')); $this->__view_helper_model['base_view_helper']->block_t(array('app' => 'b2c'), null, $this); ob_start(); ?>搜索<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_content = $this->__view_helper_model['base_view_helper']->block_t($this->_tag_stack[count($this->_tag_stack) - 1][1], $_block_content, $this); echo $_block_content; array_pop($this->_tag_stack); $_block_content=''; ?>" class="btn_search" onfocus='this.blur();'/>
</div>

</form>

<span><?php echo $this->_vars['setting']['hotkey']; ?>:</span>
<?php if($this->_vars['data']['search'])foreach ((array)$this->_vars['data']['search'] as $this->_vars['top_key'] => $this->_vars['toplink']){ ?>
	<a href="<?php echo $this->_vars['toplink']['top_link_url']; ?>"><?php echo $this->_vars['toplink']['top_link_title']; ?></a>
<?php } ?>";s:3:"ttl";i:0;s:8:"dateline";i:1425797236;}