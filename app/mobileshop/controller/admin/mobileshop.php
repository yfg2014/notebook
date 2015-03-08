<?php
class mobileshop_ctl_admin_mobileshop extends desktop_controller{

    function index(){

        $mobileshop_url = $this->app->getConf('mobileshop.url');
        $mobileshop_token = $this->app->getConf('mobileshop.token');

        if(!empty($mobileshop_url)){
            $wlshop = app::get('b2c')->model('shop');
            $node_ids = $wlshop->getList('node_id', array('node_type'=>'shopex_wmall','status'=>'bind'));
            foreach($node_ids as $value){
                if(!empty($value['node_id'])){
                    $node_id = $value['node_id'];
                }
            }

            $callinfo['node_id'] = $node_id;
            $callinfo['shop_url'] = kernel::base_url(1).kernel::url_prefix()."/";
            $callinfo['shop_license'] = base_certificate::get('certificate_id');
            $callinfo['shop_node'] = base_shopnode::node_id('b2c');
            $callinfo['shop_name'] = app::get('site')->getConf('site.name');
            $callinfo['type'] = '1';

            $callinfo['sign'] = $this->get_sign($callinfo,$mobileshop_token);

            $this->pagedata['ifseturl'] = 1;
            $this->pagedata['node_id'] = $callinfo['node_id'];
            $this->pagedata['type'] = $callinfo['type'];
            $this->pagedata['shop_license'] = $callinfo['shop_license'];
            $this->pagedata['shop_node'] = $callinfo['shop_node'];
            $this->pagedata['shop_url'] = $callinfo['shop_url'];
            $this->pagedata['shop_name'] = $callinfo['shop_name'];
            $this->pagedata['sign'] = $callinfo['sign'];
            $this->pagedata['mobileshop_url'] = $mobileshop_url;

        }else{
            $this->pagedata['ifseturl'] = 0;
        }
        $this->page('admin/index.html');
    }

    function seturl(){
        $this->pagedata['mobileshop_url'] = $this->app->getConf('mobileshop.url');
        $this->page('admin/seturl.html');
    }

    function saveurl(){
            $this->begin('');
            $this->app->setConf('mobileshop.url',$_POST['mobileshop_url']);
            $this->end(true, app::get('mobileshop')->_('保存成功'));
    }

    function get_sign($params,$token){
        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }
        return $sign;
    }
}
