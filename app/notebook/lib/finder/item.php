<?php
class notebook_finder_item{
    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=notebook&ctl=admin_notebook&act=edit&id='.$row['item_id'].'">编辑</a>';
    }

    var $detail_edit = '详细列表';
    function detail_edit($id){
        $render = app::get('notebook')->render();
        $oItem = kernel::single("notebook_mdl_item");
        $items = $oItem->getList('item_subject, item_posttime, item_email，item_imageid',
                     array('item_id' => $id), 0, 1);
        $render->pagedata['item'] = $items[0];
        $render->display('admin/itemdetail.html');
        //return 'detail';
    }
    
    
    var $column_edit2 = '缩略图';
    function column_edit2($row){
        $o =  app::get('notebook')->model('item');
		$g =$o->db_dump(array('item_id'=>$row['item_id']),'item_imageid');
		$img_src = base_storager::image_path($g['item_imageid'],'s' );
		if(!$img_src)return '';
		return "<a href='$img_src' class='img-tip pointer' target='_blank'
		        onmouseover='bindFinderColTip(event);'>
		<span>&nbsp;pic</span></a>";
    }

}
