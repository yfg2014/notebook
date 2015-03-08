<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
interface b2c_deposit_interface
{
    /**
     * 得到支付对象类别 - 当前为充值消费卡
     * @param null
     * @param string - 消费卡类别
     */
    public function get_type();
    
    /**
     * 消费卡充值处理
     * @param array sdf 消费卡的标准数据结构
     * @param array 支付单和支付对象关联表
     * @param string type 在线或者线下
     * @param string 操作员id
     * @param string 操作员name
     * @param array errorMsg error message
     * @return boolean
     */
    public function generate_bills($sdf, $arr_bills, $type='online', &$op_id, &$op_name, &$errorMsg);
}