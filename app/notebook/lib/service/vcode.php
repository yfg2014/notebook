<?php
class notebook_service_vcode{
     function __construct($app){
        $this->app = $app;
     }
     function status(){
         if(app::get('notebook')->getConf('site.login_valide') == 'false'){
            if($_SESSION['error_count'][$this->app->app_id] >= 3)
            return true;
         }
        return app::get('notebook')->getConf('site.login_valide') == 'true' ? true : false;
    }
}