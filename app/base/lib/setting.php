<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_setting{

    var $_cfg;
    
    function __construct($app){
        $this->app = $app;
    }

    function &source(){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/setting.php')){
           include(CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/setting.php');
        }else{
           include($this->app->app_dir.'/setting.php');
        }
        return $setting;
    }
}
