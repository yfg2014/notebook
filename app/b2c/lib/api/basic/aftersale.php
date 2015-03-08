<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * b2c aftersales interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_api_basic_aftersale
{
    /**
     * app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = app::get('aftersales');
        $this->app_b2c = $app;
    }

    /**
     * 售后服务单创建
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj)
    {
        if (!$sdf['order_bn'] || !$sdf['return_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('售后服务单数据异常！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
        }
        else
        {
            $is_save = true;
            $obj_return_product = $this->app->model('return_product');

            $return_id = $obj_return_product->gen_id();
            $arr_product_data = json_decode($sdf['return_product_items'], true);
            $str_product_data = serialize($arr_product_data);
            $tmp = $obj_return_product->getList('*',array('return_bn'=>$sdf['return_bn'],'order_id'=>$sdf['order_bn']));
            if ($tmp)
            {
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单已经存在！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }

            // 开始事务
            $db = kernel::database();
            $transaction_status = $db->beginTransaction();

            $arr_data = array(
                'order_id' => $sdf['order_bn'],
                'return_bn' => $sdf['return_bn'],
                'return_id' => $return_id,
                'title' => $sdf['title'],
                'content' => $sdf['content'],
                'comment' => $sdf['comment'],
                'status' => $sdf['status'],
                'product_data' => $str_product_data,
                'member_id' => $sdf['member_id'],
                'add_time' => $sdf['add_time'],
            );
            if ($sdf['url'] && strpos($sdf['url'], '/') !== false)
            {
                $mdl_img = app::get('image')->model('image');
                $image_name = substr($sdf['url'], strrpos($sdf['url'],'/')+1);
                $image_id = $mdl_img->store($sdf['url'],null,null,$image_name);
                $arr_data['image_file'] = $image_id;
            }

            $is_save = $obj_return_product->save($arr_data);

            if ($is_save)
            {
                $db->commit($transaction_status);
                return array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']);
            }
            else
            {
                $db->rollback();
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单添加失败！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }
        }
    }

    /**
     * 售后服务单修改
     * @param array sdf
     * @param string message
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj)
    {
        if (!$sdf['order_bn'] || !$sdf['return_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('售后服务单数据异常！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
        }
        else
        {
            $obj_return_product = $this->app->model('return_product');

            $arr_data = $obj_return_product->dump(array('order_bn'=>$sdf['order_bn'],'return_bn'=>$sdf['return_bn']));
            if ($arr_data)
            {
                if ($sdf['return_product_items'])
                {
                    $arr_product_data = json_decode($sdf['return_product_items']);
                    $str_product_data = serialize($arr_product_data);
                }
                else
                {
                    $str_product_data = "";
                }

                $arr_data['order_id'] = $sdf['order_bn'];
                $arr_data['return_bn'] = $sdf['return_bn'];
                if ($sdf['title'])
                    $arr_data['title'] = $sdf['title'];
                if ($sdf['content'])
                    $arr_data['content'] = $sdf['content'];
                if ($sdf['comment'])
                    $arr_data['comment'] = $sdf['comment'];
                if ($sdf['status'])
                    $arr_data['status'] = $sdf['status'];
                if ($str_product_data)
                    $arr_data['product_data'] = $str_product_data;
                if ($sdf['member_id'])
                    $arr_data['member_id'] = $sdf['member_id'];
                if ($sdf['add_time'])
                    $arr_data['add_time'] = $sdf['add_time'];

                $obj_return_product->save($arr_data);

                return array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('售后服务单不存在！'), array('tid' => $sdf['order_bn'], 'return_id' => $sdf['return_bn']));
            }
        }
    }
}