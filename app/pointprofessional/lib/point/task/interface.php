<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface pointprofessional_point_task_interface
{
	/**
	 * �õ���ӻ��ֵ�����
	 * @param null
	 * @return string ��������
	 */
	public function get_point_task_type();
	
	/**
	 * ������ʱ��ṹ
	 * @param array - ��������Ĳ���
	 * @param array - ��������ֵ����Ҫ�Ľ��
	 * @return null
	 */
	public function generate_data($arr_data=array(), &$arr_point_task);
}