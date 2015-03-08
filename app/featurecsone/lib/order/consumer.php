<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package featurecsone
 * @author mabaineng@shopex.cn
 */
class featurecsone_order_consumer
{
	static function publish($cursor_id, $params, $errmsg)
	{
		if( $cursor_id != 'store.trades.get.notify' ) {
			$errmsg = 'cursor_id not match';
			return false;
		}
		
		// 与中心交互
		/*$is_need_rpc = false;
		$obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
		foreach ($obj_rpc_obj_rpc_request_service as $obj)
		{
			if ($obj && method_exists($obj, 'rpc_judge_send'))
			{
				if ($obj instanceof b2c_api_rpc_notify_interface)
					$is_need_rpc = $obj->rpc_judge_send($order_data);
			}
		
			if ($is_need_rpc) break;
		}*/
		//error_log(json_encode($params), 3, '/Users/benn/test.log');
		
		$obj_order_create = kernel::single("b2c_order_create");		
		$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
		$use_service = $obj_rpc_request_service 
						&& method_exists($obj_rpc_request_service, 'rpc_caller_request')
						&& ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface);
		
		$mdl_order = &app::get('b2c')->model('orders');
		
		$filter = array();
		if( $params['start_time'] != '' )
			$filter = array_merge($filter, array('createtime|than' => $params['start_time']));
		if( $params['end_time'] != '' )
			$filter = array_merge($filter, array('createtime|sthan' => $params['end_time']));
		
		$order_list = $mdl_order->getList("*", $filter, 0, -1, 'createtime ASC');
		if( $order_list )
		{
			$subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
			foreach( $order_list as &$row )
			{
				$row = $mdl_order->dump($row['order_id'], '*', $subsdf);				
				if ($use_service)
				{
					$obj_rpc_request_service->rpc_caller_request($row,'create');
				}
				else
				{
					$obj_order_create->rpc_caller_request($row);
				}
			}
		}
	}
}