<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_promotion_conditions_goods_selectgoods{
    var $tpl_name = "指定商品";
    var $tpl_type = 'config';

    function getConfig($aData = array()) {
        return array(
                  'type'=> 'b2c_sales_goods_aggregator_combine',
                  'aggregator'=> 'all',
                  'conditions'=> array(
                                   0 => array(
                                           'type' =>'b2c_sales_goods_item_goods',
                                           'attribute' => 'goods_goods_id'
                                         )
                                 )
              );
    }
	
	/**
	 * 校验参数是否正确
	 * @param mixed 需要校验的参数
	 * @param string error message
	 * @return boolean 是否成功
	 */
	public function verify_form($data=array(), &$msg='')
	{
		if (!$data) return true;
		
		if (!isset($data['conditions']['conditions'][0]['value']) || !$data['conditions']['conditions'][0]['value'] || !is_array($data['conditions']['conditions'][0]['value']))
		{
			$msg = app::get('b2c')->_('请指定商品！');
			return false;
		}
		
		return true;
	}
}
?>
