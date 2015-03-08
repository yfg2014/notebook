<?php

class lang  
{
    static private $_langPack = array();

    /*
     * 初始化语言文件包
     * @var object $app
     * @access public
     * @return mixed
     */
    static public function init_pack($app) 
    {
        $current_lang = kernel::get_lang();
        $lang_resource = $app->lang_resource;
        if(is_array($lang_resource[$current_lang]) && in_array('config.php', $lang_resource[$current_lang])){
            self::$_langPack[$app->app_id] = (array)@include($app->lang_dir . '/' . $current_lang . '/config.php');
        }elseif(is_array($lang_resource['zh-cn']) && in_array('config.php', $lang_resource['zh-cn'])){
            self::$_langPack[$app->app_id] = (array)@include($app->lang_dir . '/zh-cn/config.php');
        }else{
            //trigger_error('language pack is lost in '.$this->app_id, E_USER_ERROR);
            self::$_langPack[$app->app_id] = array();
        }
    }//End Function

    /*
     * 取得语言文件信息
     * @var object $app
     * @var string $res
     * @var string $key
     * @access public
     * @return mixed
     */
    static public function get_info($app_id, $res=null, $key=null) 
    {
        if(!isset(self::$_langPack[$app_id])){
            self::init_pack(app::get($app_id));
        }//验证存在
        return is_null($res) ? self::$_langPack[$app_id] : (is_null($key) ? self::$_langPack[$app_id][$res] : self::$_langPack[$app_id][$res][$key]);
    }//End Function

    static public function set_res($app_id, $res) 
    {
        $app_res = (array)self::get_res($app_id);
        $app_res = array_merge($app_res, (array)$res);
        return base_kvstore::instance('lang/'.$app_id)->store('res', $app_res);
    }//End Function

    static public function get_res($app_id) 
    {
        if(base_kvstore::instance('lang/'.$app_id)->fetch('res', $app_res)){
            return $app_res;
        }else{
            return array();
        }
    }//End Function

    static public function del_res($app_id) 
    {
        return base_kvstore::instance('lang/'.$app_id)->store('res', array());
    }//End Function

}//End Class