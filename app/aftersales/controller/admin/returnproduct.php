<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class aftersales_ctl_admin_returnproduct extends desktop_controller{
    public $workground = 'ectools_ctl_admin_order';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->arr_status = array(
            '1' => app::get('aftersales')->_('申请中'),
            '2' => app::get('aftersales')->_('审核中'),
            '3' => app::get('aftersales')->_('接受申请'),
            '4' => app::get('aftersales')->_('完成'),
            '5' => app::get('aftersales')->_('拒绝'),
        );
    }
    
    public function index()
    {
        if($_GET['action'] == 'export') $this->_end_message = '导出售后服务申请';
        $this->finder('aftersales_mdl_return_product',array(
            'title'=>app::get('aftersales')->_('售后服务管理'),
            'actions'=>array(
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>true,'use_buildin_filter'=>true,'use_buildin_export'=>true,
            ));
    }
    
    public function save()
    {
        $rp = &$this->app->model('return_product');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $return_id = $_POST['return_id'];
        $status = $_POST['status'];
        $sdf = array(
            'return_id' => $return_id,
            'status' => $status,
        );
        $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);        
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];
        
        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }
        
        $this->display('admin/return_product/return_status.html');
    }
    
    public function file_download($return_id)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id);
    }
    
    public function send_comment()
    {
        $rp = &$this->app->model('return_product');

        $return_id = $_POST['return_id'];
        $comment = $_POST['comment'];
        $arr_data = array(
            'return_id' => $return_id,
            'comment' => $comment,
        );
        
        $this->begin();
        if($rp->send_comment($arr_data))
        {        
            $this->end(true, app::get('aftersales')->_('发送成功！'));
        }
        else
        {
            //trigger_error(__('发送失败'),E_USER_ERROR);            
            $this->end(false, app::get('aftersales')->_('发送失败！'));
        }
    }
    
    public function settings()
    {
        if (!$_POST)
        {
            $this->pagedata['return_product']['is_open'] = $this->app->getConf('site.is_open_return_product');
            $this->pagedata['return_product']['comment'] = $this->app->getConf('site.return_product_comment');
            $this->page('admin/setting/return_product.html');
        }
        else
        {
            $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=settings');
            
            if ($_POST['return_is_open'] == 'true')
                $this->app->setConf('site.is_open_return_product', 1);
            else
                $this->app->setConf('site.is_open_return_product', 0);
            
            $this->app->setConf('site.return_product_comment', $_POST['conmment']);
            
            $this->end(true, app::get('aftersales')->_("设置成功！"));
        }
    }
}