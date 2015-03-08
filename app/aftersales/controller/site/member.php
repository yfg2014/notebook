<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class aftersales_ctl_site_member extends b2c_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
		$this->app_current = $app;
		$this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }

	public function return_policy($app='',$ctl='',$act='')
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('申请售后服务'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->begin($this->gen_url(array('app' => $app, 'ctl' => $ctl, 'act' => $act)));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['is_open_return_product'] = $arr_settings['is_open_return_product'];
        $this->pagedata['comment'] = $arr_settings['return_product_comment'];
		$this->pagedata['args'] = array($app, $ctl, $act);
        $this->output('aftersales');
    }

	public function return_list($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('售后服务列表'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $clos = "return_id,order_id,title,add_time,status";
        $filter = array();
        $filter["member_id"] = $this->member['member_id'];
        if( $_POST["title"] != "" ){
            $filter["title"] = $_POST["title"];
        }

        if( $_POST["status"] != "" ){
            $filter["status"] = $_POST["status"];
        }

        if( $_POST["order_id"] != "" ){
            $filter["order_id"] = $_POST["order_id"];
        }

		$this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $aData = $obj_return_policy->get_return_product_list($clos, $filter, $nPage);
        if (isset($aData['data']) && $aData['data'])
            $this->pagedata['return_list'] = $aData['data'];

        $arrPager = $this->get_start($nPage, $aData['total']);
        $this->pagination($nPage, $arrPager['maxPage'], 'return_list', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }

	public function return_order_list($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('新增退货申请'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $obj_orders = $this->app->model('orders');
        $clos = "order_id,createtime,final_amount,currency";
        $filter = array();
        if( $_POST['order_id'] )
        {
            $filter['order_id|has'] = $_POST['order_id'];
        }
        $filter['member_id'] = $this->member['member_id'];
        $filter['pay_status'] = 1;
        $filter['ship_status'] = 1;

        $aData = $obj_orders->getList($clos, $filter, ($nPage-1)*10, 10);
        if (isset($aData) && $aData)
            $this->pagedata['orders'] = $aData;
        $total = $obj_orders->count($filter);

        $arrPager = $this->get_start($nPage, $total);
        $this->pagination($nPage, $arrPager['maxPage'], 'return_order_list', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }

	public function return_add($order_id,$page=1)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product']){
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $limit = 10;
        $objOrder = &$this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id, $page, $limit);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'));
        }

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

		$objMath = kernel::single("ectools_math");
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
			$tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
						$item['item_type'] = 'goods';

					if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
						$tmp_array = (array)$tmp_array;
						if (!$tmp_array) continue;
						
						$product_id = $tmp_array['products']['product_id'];
						if (!$order_items[$product_id]){
							$order_items[$product_id] = $tmp_array;
						}else{
							$order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
							$order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
						}
						//$order_items[$item['products']['product_id']] = $tmp_array;
					}
                }
            }
			else
			{
				if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
				{
					$tmp_array = (array)$tmp_array;
					if (!$tmp_array) continue;
					foreach ($tmp_array as $tmp){
						if (!$order_items[$tmp['product_id']]){
							$order_items[$tmp['product_id']] = $tmp;
						}else{
							$order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
							$order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
							$order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
						}
					}
				}
				//$order_items = array_merge($order_items, $tmp_array);
			}
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = array_slice($order_items,($page-1)*$limit,$limit);
        $count = count($order_items);
        $arrMaxPage = $this->get_start($page, $count);
        $this->pagination($page, $arrMaxPage['maxPage'], 'return_add', array($order_id), 'aftersales', 'site_member');
        $this->pagedata['url'] = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_order_items', 'arg' => array($order_id)));
        $this->output('aftersales');
    }

	/**
	 * 截取文件名不包含扩展名
	 * @param 文件全名，包括扩展名
	 * @return string 文件不包含扩展名的名字
	 */
	private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

	public function return_save()
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

		if (!$_POST['product_bn'])
		{
			$com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
			$this->end(false, app::get('aftersales')->_("您没有选择商品，请先选择商品！"), $com_url);
		}

        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
            $this->end(false, app::get('aftersales')->_("上传文件不能超过300M"), $com_url);
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url);
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $product_data = array();
        foreach ((array)$_POST['product_bn'] as $key => $val)
        {
			if ($_POST['product_item_nums'][$key] < intval($_POST['product_nums'][$key]))
			{
				$com_url = $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_add', 'arg0' => $_POST['order_id']));
				$this->end(false, app::get('aftersales')->_("申请售后商品的数量不能大于订购数量: "), $com_url);
			}

            $item = array();
            $item['bn'] = $val;
            $item['name'] = $_POST['product_name'][$key];
            $item['num'] = intval($_POST['product_nums'][$key]);
            $product_data[] = $item;
        }

        $aData['order_id'] = $_POST['order_id'];
        $aData['title'] = $_POST['title'];
        $aData['add_time'] = time();
        $aData['image_file'] = $image_id;
        $aData['member_id'] = $this->member['member_id'];
        $aData['product_data'] = serialize($product_data);
        $aData['content'] = $_POST['content'];
        $aData['status'] = 1;

        //ajx  新机制中售后的api接口
        if($obj_return_policy->save_return_product($aData)){
            $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
            $obj_apiv->rpc_caller_request($aData,'aftersales');
            $this->end(true, app::get('b2c')->_('提交成功！'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));

        }else{
            $this->end(false, app::get('b2c')->_('提交失败'), $this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
        }
    }

	public function return_details($return_id)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if(!$arr_settings['is_open_return_product'])
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有开启！"));
        }

        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id);
        $this->pagedata['return_id'] = $return_id;
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }

        $this->output('aftersales');
    }

	/**
	 * 下载售后附件
	 * @param string return id
	 * @return null
	 */
	public function file_download($return_id)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id);
    }
}
