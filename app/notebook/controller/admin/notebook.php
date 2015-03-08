<?php
class notebook_ctl_admin_notebook extends desktop_controller{
    
    //默认方法。显示留言列表和添加留言
    function index(){
        $this->finder('notebook_mdl_item',
              array('title'=>'留言列表',
                'actions' =>
                array(
                  array(
                    'label' => app::get('notebook')->_('添加留言'),
                    'icon' => 'add.gif',
                    'href' => 'index.php?app=notebook&ctl=admin_notebook&act=add',
                    //        'target' => '_blank'
                    ),
                  ),
                'use_buildin_set_tag'=>true,
                'use_buildin_filter'=>true,
                'use_buildin_tagedit'=>true,
                'use_buildin_set_tag'=>true,
                'use_buildin_export'=>true,
                'use_buildin_import'=>true,
                'allow_detail_popup'=>true,
                //'use_view_tab'=>true,
                ));


    }
    
    //后台添加留言函数 。
    function add(){
        $this->page('admin/edit.html');
    }

    
    //留言修改函数
    function edit(){
        header("cache-control:no-store,no-cache,must-revalidate");
        $id = $_GET["id"];
        $oItem = kernel::single('notebook_mdl_item');
        $row = $oItem->getList('*',array('item_id'=>$id),0,1);
        $this->pagedata['item'] = $row[0];
        $this->page('admin/edit.html');
    }

    //留言添加函数。留言修改函数 。
    function toEdit(){
    $oItem = kernel::single("notebook_mdl_item");
    $arr = $_POST['item'];
        $this->begin('index.php?app=notebook&ctl=admin_notebook&act=index');
    $oItem->save($arr);
        $this->end(true, "留言添加成功！");

    }
}