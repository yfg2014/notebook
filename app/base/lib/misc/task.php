<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_misc_task implements base_interface_task
{
    function rule(){
	return '0 0 */1 * *';
    }
    function exec(){
        base_kvstore::delete_expire_data();
    }

    function description()
    {
        return '删除过期数据';
    }
}
