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
class featurecsone_order_init
{	
	function notify()
	{
		//获取参数
		$start_time = $_REQUEST['start_time'];
		$end_time = $_REQUEST['end_time'];
		
		//校验参数
		if( $start_time != '' )
		{
			if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
				trigger_error('start_time error', E_USER_ERROR);
		}
		
		if( $end_time != '' )
		{
			if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
				trigger_error('end_time error', E_USER_ERROR);
		}
		
		//查询数据		
		$queue = kernel::single("base_queue");
		$result = $queue->publish(array(
					'queue_title' => '订单初始化',
					'start_time' => time(),
					'worker' => 'featurecsone_order_consumer.publish',
					'cursor_id' => 'store.trades.get.notify',
					'params' => array(
								'start_time' => $start_time,
								'end_time' => $end_time,
							),
				));
		
		if( !$result )
			trigger_error('inti fail', E_USER_ERROR);
		
		echo 'succ';
		return true;
	}
}