<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class b2c_mdl_order_items extends dbeav_model{
    /** 
     * ��дgetList����
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        $obj_extends_order_service = kernel::servicelist('b2c.api_order_extends_actions');
        if ($obj_extends_order_service)
        {
            foreach ($obj_extends_order_service as $obj)
                $obj->extend_item_list($arr_list);
        }
        
        return $arr_list;
    }
}
