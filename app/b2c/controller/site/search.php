<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_search extends b2c_frontpage{

     function __construct($app){
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('搜索').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('搜索_').'_'.$shopname;
            $this->description = app::get('b2c')->_('搜索_').'_'.$shopname;
        }
    }

    function index(){
        $aBrands = array();
        $objBrand = &$this->app->model('brand');
        $this->pagedata['brand'] = $objBrand->getAll();
        $objCat = &$this->app->model('goods_cat');
        $this->pagedata['categorys'] = $objCat->get_cat_list();
        $this->pagedata['args'] = array($cat_id,$filter,$orderBy,$tab,$page);
        //print_R($this->pagedata['args']);exit;
        $this->page('site/search/index.html');
    }

    function result(){
		$this->set_no_store();
        $oSearch = &$this->app->model('search');
        $emu_static = $this->app->getConf('system.seo.emuStatic');
		foreach(kernel::servicelist("search.prepare") as $obj )
		{
			$obj->parse($_POST);
		}
        $cat_id = $_POST['cat_id'];
        unset($_POST['cat_id']);
        foreach($_POST as $k=>$v){
            if($k=="name" && $_POST[$k][0]){
                $_POST[$k][0]=str_replace('_','%xia%',$_POST[$k][0]);
                $_POST[$k][0] = strip_tags($_POST[$k][0]);
            }
            if($k=="price" && $_POST[$k][1]){
                $_POST[$k][0]=floatval($_POST[$k][0]);
                $_POST[$k][1]=floatval($_POST[$k][1]);

            }
        }
            if(isset($_POST['filter'])&&$filter = $oSearch->decode($_POST['filter'],$path)){
                $filter = array_merge($filter,$_POST);

            }else{
                $filter = $_POST;
            }

        unset($_POST['filter']);

        $filter = $oSearch->encode($filter);
        if(empty($cat_id)&&empty($filter))
            $args=null;
        else
            $args = array($cat_id,$filter);

        $this->sredirect(array('app'=>'b2c', 'ctl'=>'site_gallery', 'act'=>'index', 'args'=>$args));

    }

    function showCat(){

        $objCat = &$this->app->model('goods_cat');
        $objBrand = &$this->app->model('brand');
        if(!empty($_POST['cat_id'])){
            $cat = $objCat->getlist('*',array('cat_id'=>$_POST['cat_id']));
            $type = $objBrand->getBidByType($cat[0]['type_id']);
            foreach($type as $key=>$val){
                 $brand_id['brand_id'][] = $val['brand_id'];
            }
            if(empty($brand_id['brand_id'])) $brand_id['brand_id'] = '-1';
            $cat['brand']  = $objBrand->getlist('*',$brand_id);

        }else{
            $cat['brand']  = $objBrand->getlist('*','');
        }
        $this->pagedata['cat'] = $cat;
        $this->display("site/search/showCat.html");
        exit;

    }

    function sredirect($url, $js_jump=false){
        if(is_array($url)){
            $arg = $url['args'][1];
            unset($url['args'][1]);
            $url = $this->gen_url($url).'?scontent='.$arg;
        }
        $this->_response->set_redirect($url)->send_headers();
    }
}
