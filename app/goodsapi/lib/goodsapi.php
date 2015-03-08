﻿<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class goodsapi_goodsapi extends goodsapi_shopex_interface_goodsapi{

    public function __construct(){
        parent::__construct();
    }

    function api(){
        $params = $_POST;
        $service_name = 'openapi.goodsapi.'.$params['act'];
        $method = $params['act'];
        $obj = kernel::service($service_name);
        if( $obj && method_exists($obj,$method) ){
            $obj->$method();
        }else{
            $error['code'] = null;
            $error['msg']  =  $method."方法不可用";
            $this->send_error($error);
        }
    }
}

