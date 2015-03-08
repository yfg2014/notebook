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
class featurecsone_member_init
{	
	function get()
	{
		//获取参数
		$start_time = $_REQUEST['start_time'];
		$end_time = $_REQUEST['end_time'];
		$page_no = $_REQUEST['page_no'];
		$page_size = $_REQUEST['page_size'];
		
		//校验参数
		if( $start_time != '' )
		{
			if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
				trigger_error('start_time error', E_USER_ERROR);
		}
		
		//if( $end_time == '' || ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
		//	trigger_error('end_time error', E_USER_ERROR);
		if( $end_time != '' )
		{
			if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
				trigger_error('end_time error', E_USER_ERROR);
		}
		
		if( $page_no == '' || !is_numeric($page_no) || $page_no < 1 )
			$page_no = 1;
		else
			$page_no = intval($page_no);
		
		if( $page_size == '' || !is_numeric($page_size) )
			$page_size = 40;
		elseif( $page_size < 1 || $page_size > 40 )
			trigger_error('page_size in the range of 1 to 40', E_USER_ERROR);
		else
			$page_size = intval($page_size);
		
		//查询数据
		$mdl_member = &app::get('b2c')->model('members');
		/*$filter = array(':account@pam'=>array('*'));
		if( $start_time != '' )
			$filter = array_merge($filter, array('regtime|than' => $start_time));
		if( $end_time != '' )
			$filter = array_merge($filter, array('regtime|sthan' => $end_time));*/
		$where = '';
		if( $start_time != '' )
			$where .= "AND T1.regtime > '" . $start_time . "' ";
		if( $end_time != '' )
			$where .= "AND T1.regtime <= '" . $end_time . "' ";
		if( $where != '' )
			$where = 'WHERE ' . substr($where, 4);
		
		$sql	=	"SELECT ### FROM " .
					kernel::database()->prefix."b2c_members T1 " .
					"LEFT JOIN " . kernel::database()->prefix."pam_account T2 ON T2.account_id=T1.member_id " .
					$where .
					"ORDER BY T1.regtime ASC";
		
		//获取总数
		$total_results = $mdl_member->db->select( str_replace('###', 'count(*) cc', $sql) );
		if( $total_results )
			$total_results = $total_results[0]['cc'];
		else
			$total_results = 0;
		if($total_results == 0) {
			echo 'no data';
			return false;
		}
		
		//计算分页
		$offset = ($page_no-1) * $page_size;
		$limit = $page_size;
		
		$next_page = $total_results > ($offset+$limit) ? 'true' : 'false';
		
		//$member_sdf = $mdl_member->getList("*",$filter,$offset,$limit,"regtime asc");
		
		$member_sdf = $mdl_member->db->selectLimit( str_replace('###', 'T1.*, T2.login_name', $sql), $limit, $offset );
		
		if(!$member_sdf){		
			trigger_error('no data', E_USER_ERROR);
		}
		
		//解析数据
		$users = array();
		foreach ($member_sdf as $row)
		{			
			$status = '';
			if( $row['state'] == '1' )
				$status = 'inactive';
			elseif( $row['disabled'] == 'true' )
				$status = 'reeze';
			else
				$status = 'normal';
			
			$area = $row['area'];
			$area_array = array();
			if( $area != '' ){
				$area = explode(':', $area);
				if( isset($area[2]) && is_numeric($area[2]) )
					$area_array = explode('/', $area[1]);
				else
					$area_array = array($row['area']);
			}
			
			$sex = $row['sex'];
			if( $sex == '1' )
				$sex = '1'; //男
			elseif( $sex == '0' )
				$sex = '2'; //女
			else
				$sex = ''; //无
			
			
			$users[] = array(
	                'userid' => $row['member_id'],
	        		'uid' => $row['login_name'],
	        		'nick_name' => $row['name'],
	        		'sex' => $sex,
	        		'buyer_credit' => '',
	        		'seller_credit' => '',
	        		'location' => json_encode(array(
	                                    'zip' => $row['zip'],
	                                    'address' => $row['addr'],
	                                    'city' => $area_array[1],
	                                    'state' => $area_array[0],
	                                    'county' => '',
	                                    'district' => $area_array[2],
	                                    )),
	        		'created' => date('Y-m-d H:i:s', $row['regtime']),
	        		'last_visit' => '',
	        		'birthday' => $row['b_year'].'-'.$row['b_month'].'-'.$row['b_day'],
	        		'promoted_type' => '',
	        		'status' => $status,
	        		'alipay_bind' => '',
	        		'consumer_protection' => '',
	        		'alipay_account' => '',
	        		'alipay_no' => '',
	        		'vip_info' => '',
	        		'email' => $row['email'],
	        		//'marital' => $member_sdf['wedlock'],
	        		'marital' => '',
	        		'mobile' => $row['mobile'],
	        		'age' => '',
	        );
		}
		
		//输出数据
		$result = array(
			'user' => $users,
			'total_results' => $total_results,
			'next_page' => $next_page,
		);
		echo json_encode($result);
		return true;
	}
}