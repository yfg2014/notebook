<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
#收款单
class operatorlog_payments
{
    /**
     * 
     * 删除前获取提交过来的收款单信息
     * @param unknown_type $params
     */
    public function logDelInfoStart($params) 
    {
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        $this->info=$params;
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }//End Function
    /**
     * 
     * Enter description here ...
     * @param unknown_type $delflag 是否被删除标识
     */
    public function logDelInfoEnd($delflag=false) 
    {
        if($delflag==true){
            $payment_ids = implode($this->info['payment_id'], ',');
            $memo='收款单号('.$payment_ids.')';
            kernel::single('operatorlog_logs')->inlogs($memo, '删除收款单', 'orders');
        }
    }//End Function

}//End Class
