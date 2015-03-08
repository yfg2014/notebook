<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
#发货单
class operatorlog_delivery
{
    /**
     * 
     * 删除前获取提交过来的发货单信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params) 
    {
        $this->info=$params;
    }//End Function
    /**
     * 
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false) 
    {
        if($delflag==true){
            $delivery_ids = implode($this->info['delivery_id'], ',');
            $memo='发货单号('.$delivery_ids.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除发货单', 'orders');
        }
    }//End Function

}//End Class
