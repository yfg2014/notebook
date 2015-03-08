<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 定时执行的任务方法列表
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.misc
 */
class ectools_misc_task implements base_interface_task{

    function rule() {
	return '0 */1 * * *';
    }

    function exec() {
        kernel::single('ectools_analysis_task')->analysis_hour();
        kernel::single('ectools_analysis_task')->analysis_day();
    }

    function description() {
	return 'ectools分析统计';
    }
}
