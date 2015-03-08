<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class trafficservice_ctl_admin_trafficservice extends desktop_controller{
    public function __construct($app)
    {
        parent::__construct($app);
        $this->entid = base_enterprise::ent_id();
    }
    function index(){
        $entID = $this->entid;
        $sourceID = 'ecos.b2c';
        $this->pagedata['traffic_url'] = "http://service.saas.csones.com/index.php/flowfront-{$entID}-{$sourceID}.html";    
        $this->page('admin/index.html');
    }

}