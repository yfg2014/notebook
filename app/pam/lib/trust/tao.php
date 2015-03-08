<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class pam_trust_tao {

    public function login($params=null){
        if(!$params) return false;
        foreach(kernel::servicelist('api_login') as $k=>$passport){
            return $passport->login($params);
        }
    }

}
