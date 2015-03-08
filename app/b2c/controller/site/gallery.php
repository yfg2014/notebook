<?php
/**
  ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_gallery extends b2c_frontpage{

    var $_call = 'index';
    var $type='goodsCat';
    var $seoTag=array('shopname','goods_amount','goods_cat','goods_cat_p','goods_type','brand','sort_path');

    public function __construct(&$app) {
        parent::__construct($app);
        $shopname = $app->getConf('system.shopname');
        $this->shopname = $shopname;
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('商品分类').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('商品分类_').'_'.$shopname;
            $this->description = app::get('b2c')->_('商品分类_').'_'.$shopname;
        }
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
    }

    public function index($cat_id='',$urlFilter=null,$orderBy=0,$tab=null,$page=1,$cat_type=null,$view=null) {
        $tab = intval($tab);
        $urlFilter = $this->RemoveXSS($urlFilter);
        $urlFilter=htmlspecialchars(urldecode($urlFilter));
        $_GET['scontent'] = htmlspecialchars($_GET['scontent']);
        if(!empty($urlFilter) && $urlFilter != $_GET['scontent']){
            $urlFilter .= '_'.$_GET['scontent'];
        }else{
            $urlFilter = $_GET['scontent'];
        }

        $virCatObj = &$this->app->model('goods_virtual_cat');
        if( $cat_type ){
            $vcatid = $cat_type;
            $oSearch = &$this->app->model('search');

            /** 颗粒缓存商品虚拟分类 **/
            if(!cachemgr::get('goods_virtual_cat_'.intval($vcatid), $vcat)){
                cachemgr::co_start();
                $vcat = $virCatObj->getList('cat_id,cat_path,virtual_cat_id,filter,virtual_cat_name as cat_name',array('virtual_cat_id'=>intval($vcatid)));
                cachemgr::set('goods_virtual_cat_'.intval($vcatid), $vcat, cachemgr::co_end());
            }

            $vcat = current( $vcat );
            $vcatFilters = $virCatObj->_mkFilter($vcat['filter']);
            $vcatFilters = $virCatObj->getFilter($vcatFilters);
            $old_cat_id = $cat_id;
            $old_urlFilter = $urlFilter;
            $cat_id = $cat_id?$cat_id.implode(",",$vcatFilters['cat_id']):implode(",",$vcatFilters['cat_id']);
            $urlFilter = $urlFilter?$urlFilter:$oSearch->encode($vcatFilters);
        }

        $page = ($page > 1) ? intval($page) : 1;
        if($cat_id == '_ANY_'){
            unset($cat_id);
        }
        if($cat_id){
            $cat_id=explode(",",$cat_id);
            foreach($cat_id as $k=>$v){
                if($v) $cat_id[$k]=intval($v);
            }
            $this->id = implode(",",$cat_id);
        }else{
            $cat_id = array('');
            $this->id = '';
        }
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 20);
        $this->pagedata['pdtPic']=array('width'=>100,'heigth'=>100);
        $productCat = &$this->app->model('goods_cat');
        $objGoods = $this->app->model('goods');

        $global_runtime_path = "";

        // ajx 这里添加对分类的判断，当分类不存在时不做缓存处理
        if(!cachemgr::get('global_runtime_path' . $this->id, $global_runtime_path)){
            cachemgr::co_start();
            if($cat_type){
                $global_runtime_path = $virCatObj->getPath($cat_type,'');
            }else{
                $global_runtime_path = $productCat->getPath($cat_id[0],'');
            }
            cachemgr::set('global_runtime_path', $global_runtime_path, cachemgr::co_end());
        }
        
        /****ajx 以下是为了当搜索条件存在时 面包屑中显示 搜索条件 ***/
        if( $_GET['scontent'] && strlen($urlFilter) > 0 ){
            $global_runtime_path = array(array('type'=>'goodsCat','title'=>app::get('site')->_('首页'),'link'=>kernel::base_url(1)));
            $title_ = explode('_',$urlFilter);
            $title_arr = "" ;
            foreach($title_ as $val_xin){
                $title_xin = explode(',',$val_xin);
                if(count($title_xin) > 2){
                    unset($title_xin[0]);
                    foreach($title_xin as $xin_val){
                        $title_arr[] = $xin_val;
                    }
                }else{
                    $title_arr[] = $title_xin[1];
                }                        
            }
            $title = implode(',',$title_arr);
            array_push($global_runtime_path,array('type'=>'goodsCat','title'=>app::get('site')->_($title),'link'=>kernel::base_url(1)));
        }

        // ajx 以下是为了当无分类和搜索条件时 显示所有商品
        if( count($global_runtime_path) < 2 ){
            $global_runtime_path = array(
                array('type'=>'goodsCat','title'=>app::get('site')->_('首页'),'link'=>kernel::base_url(1)),
                array('type'=>'goodsCat','title'=>app::get('site')->_('所有商品'),'link'=>kernel::base_url(1)),
            );
        }
        
        $GLOBALS['runtime']['path'] = $global_runtime_path;

        if ($cat_id[0]){
            if(!cachemgr::get('goods_cat_'.$cat_id[0], $this->cat_result)){
                cachemgr::co_start();
                $this->cat_result = $productCat->getList('cat_name,gallery_setting,type_id',array('cat_id|in'=>$cat_id),0,1);
                cachemgr::set('goods_cat_'.$cat_id[0], $this->cat_result, cachemgr::co_end());
            }
            $type_filter['type_id'] = $this->cat_result[0]['type_id'];
        }

        if( isset($this->cat_result[0]['gallery_setting']['gallery_template'])&&$this->cat_result[0]['gallery_setting']['gallery_template'] ){
            $this->set_tmpl_file($this->cat_result[0]['gallery_setting']['gallery_template']);                 //添加模板
        }

        if(empty($view))
            $view = $this->app->getConf('gallery.default_view')?$this->app->getConf('gallery.default_view'):'index';

        if(!cachemgr::get('goods_cat_childnode_'.$cat_id[0], $this->pagedata['childnode'])){
            cachemgr::co_start();
            $this->pagedata['childnode'] = $productCat->getCatParentById($cat_id,$view);
            cachemgr::set('goods_cat_childnode_'.$cat_id[0], $this->pagedata['childnode'], cachemgr::co_end());
        }

        $cat = kernel::service('b2c_site_goods_list_viewer_apps')->get_view($cat_id,$view,$type_filter['type_id'],$cat_type);

        $args = array( ( $cat_type?$old_cat_id:$this->id),($cat_type?$old_urlFilter:urlencode($urlFilter)),$orderBy,$tab,$page,$cat_type,$view);
        $this->pagedata['args'] = $args;
        $this->pagedata['args1'] = $args[1];
        $args[1] = null;
        $this->pagedata['args2'] = $args;


        if($this->app->getConf('system.seo.noindex_catalog'))
            $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $searchtools = &$this->app->model('search');
        $path =array();

        $propargs = $searchtools->decode($urlFilter,$path,$cat);

        if(is_array($propargs)){
            foreach($propargs as $rk=>$rv){
                $pos = strpos($rk,'p_');
                if($pos === 0){
                    $propz[$rk] = $rv[0];
                    $rk =substr($rk,2);
                    $proparg[$rk] = $rv;
                    $prot[] = $rk;
                }
            }
        }
        if(isset($propargs['name'])){
            $GLOBALS['runtime']['search_key'] = $propargs['name'][0];
        }
       $this->pagedata['prot'] = $prot;
       $filter = $propargs;
       if(is_array($filter)){
            $filter=array_merge(array('cat_id'=>$cat_id,'marketable'=>'true'),$filter);
            if( ($filter['cat_id'][0] === '' || $filter['cat_id'][0] === null ) && !isset( $filter['cat_id'][1] ) )
                unset($filter['cat_id']);
            if( ($filter['brand_id'][0] ==='' || $filter['brand_id'][0] === null) && !isset( $filter['brand_id'][1] ))
                unset($filter['brand_id']);
        }else{
            $filter = array('cat_id'=>$cat_id,'marketable'=>'true');
        }
        //--------获取类型关联的规格
        $type_id = $type_filter['type_id'];

        $gType = &$this->app->model('goods_type');
        if(!cachemgr::get('goods_type_'.$type_id, $SpecList)){
            cachemgr::co_start();
            $SpecList = $gType->getSpec($type_id,1);//获取关联的规格
            cachemgr::set('goods_type_'.$type_id, $SpecList, cachemgr::co_end());
        }
        if ($SpecList)
            $this->spec_goods = $SpecList[1];

        $oGoodsTypeSpec = $this->app->model('goods_type_spec');
        if(!cachemgr::get('goods_type_spec'.$type_id, $type_spec)){
            cachemgr::co_start();
            $type_spec = $oGoodsTypeSpec->get_type_spec($type_id);
            cachemgr::set('goods_type_spec'.$type_id, $type_spec, cachemgr::co_end());
        }

        $filter['cat_id'] = $cat_id;
        $filter['goods_type'] = 'normal';
        $filter['marketable'] = 'true';
        //-----查找当前类别子类别的关联类型ID
        if ($urlFilter){
            if($vcat['type_id']){
                //$filter['type_id']=$vcat['type_id'];
                $filter['type_id']=null;

            }
        }
        //--------

       foreach($path as $p){
            $arg = unserialize(serialize($this->pagedata['args']));
            $arg[1] = $p['str'];
            $title = array();
            if($p['type']=='brand_id'){
                $brand = array();

                foreach($cat['brand'] as $b){
                    $brand[$b['brand_id']] = $b['brand_name'];
                }
                foreach($p['data'] as $i){
                    $title[] = $brand[$i];
                    $tip = __("品牌");
                }
                unset($brand);
            }elseif(substr($p['type'],0,2)=='s_'){
                $spec = array();
                foreach($p['data'] as $spk => $spv){
                    $tmp=explode(",",$spv);
                    $tip = $SpecList[$tmp[0]]['name'];
                    $title[]=$SpecList[$tmp[0]]['spec_value'][$tmp[1]]['spec_value'];
                    $g['pdt_desc']=$SpecList[$tmp[0]]['spec_value'][$tmp[1]]['spec_value'];/*前台搜索商品规格筛选，所要获取的pdt_desc*/

                }
            }
            $curSpec[$tmp[0]]=$tmp[1];
        }
        $this->pagedata['tabs'] = $cat['tabs'];
        $this->pagedata['cat_id'] = implode(",",$cat_id);
        $views = $cat['setting']['list_tpl'];

        foreach($views as $key=>$val){
            $this->pagedata['views'][$key] = array( ( $cat_type?$old_cat_id:$this->id ),'',$orderBy,$tab,$page,$cat_type,$val);

        }


        if($cat['tabs'][$tab]){
            parse_str($cat['tabs'][$tab]['filter'],$_filter);
            $filter = array_merge($filter,$_filter);
        }
        if(isset($this->pagedata['orderBy'][$orderBy])){
            $orderby = $this->pagedata['orderBy'][$orderBy]['sql'];
        }

        $selector = array();
        $search = array();

       /***********************/

        if ($SpecList){
            if ($curSpec)
                $curSpecKey=array_keys($curSpec);
            foreach($SpecList as $spk => $spv){
                $selected=0;
                if ($curSpecKey&&in_array($spk,$curSpecKey)){
                    $spv['spec_value'][$curSpec[$spk]]['selected']=true;
                    $selected=1;
                }
                if ($spv['spec_style']=="select"){ //下拉
                    $SpecSelList[$spk] = $spv;
                    if ($selected)
                        $SpecSelList[$spk]['selected'] = true;
                }
                elseif ($spv['spec_style']=="flat"){
                    $SpecFlatList[$spk] = $spv;
                    if ($selected)
                        $SpecFlatList[$spk]['selected'] = true;
                }
            }
        }

        $this->pagedata['SpecFlatList'] = $SpecFlatList;
        $this->pagedata['specimagewidth'] = $this->app->getConf('spec.image.width');
        $this->pagedata['specimageheight'] = $this->app->getConf('spec.image.height');
        $this->pagedata['orderBy'] = $objGoods->orderBy();//排序方式
        if(empty($orderBy)) $orderBy = 1;
        if(!isset($this->pagedata['orderBy'][$orderBy])){
            $this->_response->set_http_response_code(404);
        }else{
            $orderby = $this->pagedata['orderBy'][$orderBy]['sql'];
        }

        $selector['ordernum'] = $cat['ordernum'];

        if(app::get('base')->getConf('server.search_server.search_goods')){
           $searchApp = search_core::instance('search_goods');
           $sfilter['filter'] = $filter;
           $sfilter['from'] = $pageLimit*($page-1);     //分页
           $sfilter['to'] = $pageLimit;
           $sfilter['order'] = $orderby;
           $sfilter['scount'] = count($cat['props']);
        }
        $res = false;   //初始化
        if(is_object($searchApp)){
           $sphinxstart = true;
           $queryRes = $searchApp->query($sfilter);
           if($queryRes){
               $res = $searchApp->commit();
               $nprop = $res['prop'];           //属性搜索
               $cbrand = $res['brand'];
               $rfilter['goods_id'] = $res['result'];
               if(is_array($res['result'])){
                   $count = $res['total'];
                   $search_data = $objGoods->getList('*', $rfilter);
                   foreach($search_data AS $tmp_data){
                       $tmp_search_data[$tmp_data['goods_id']] = $tmp_data;
                   }
                   foreach($res['result'] as $v){
                       if(!isset($tmp_search_data[$v])) continue;
                       $aProduct[] = $tmp_search_data[$v];  //产品
                   }
                   unset($search_data);
                   unset($tmp_search_data);
               }else{
                   $count = 0;
                   $aProduct = array();
               }
           }
        }

        if($res === false){
            if (isset($filter['tag'][0])&&!$filter['tag'][0])
                unset($filter['tag']);
            $tmp_filter['str_where'] = $objGoods->_filter($filter);
            $aProduct = $objGoods->getList('*',$tmp_filter,$pageLimit*($page-1),$pageLimit,$orderby);
            $count = $objGoods->count($tmp_filter);
        }

        /************************/
        if (is_array($cat['brand'])){
            if($sphinxstart){
                $bCount = $cbrand;
            }else{
                $bCount = $objGoods->countBrandGoods($tmp_filter,$cat['brand']);
            }
            foreach($cat['brand'] as $bk => $bv){
                if(is_array($filter['brand_id'])){
                    $bid = array_flip($filter['brand_id']);
                }
                $brand = array('name'=>app::get('b2c')->_('品牌'),'value'=>$bid);
                $brandArray[$bv['brand_id']] = $bv['brand_name'];
            }
            foreach((array)$bCount as $sk => $sv){
                if(isset($brandArray[$bCount[$sk]['brand_id']])){
                    $tmpOp[$bCount[$sk]['brand_id']]=$brandArray[$bCount[$sk]['brand_id']]."<span class='num'>(".$bCount[$sk]['_count'].")</span>";
                }
            }
            $brand['options'] = $tmpOp;
            $selector['brand_id'] = $brand;
        }

        $goods_relate = array();
        if((!is_array($cat_id) && $cat_id) || $cat_id[0] || $cat_type){
            //$goods_relate = $aProduct;
            $goods_relate=$objGoods->getList("p_1,p_2,p_3,p_4,p_5,p_6,p_7,p_8,p_9,p_10,p_11,p_12,p_13,p_14,p_15,p_16,p_17,p_18,p_19,p_20",$filter,0,100);
        }

        foreach((array)$cat['props'] as $prop_id=>$prop){
            if($prop['search']=='select'){
                if(count($prop['options']) > 0){
                    $prop['value'] = $filter['p_'.$prop_id][0];
                    $searchSelect[$prop_id] = $prop;
                }
            }elseif($prop['search']=='input'){
                $prop['value'] = ($filter['p_'.$prop_id][0]);
                $searchInput[$prop_id] = $prop;
            }elseif($prop['search']=='nav'){

                if(is_array($filter['brand_id'])&&isset($filter['p_'.$prop_id]))
                    $prop['value'] = array_flip($filter['p_'.$prop_id]);
                $plugadd=array();
                if(is_array($goods_relate)){
                    foreach($goods_relate as $k=>$v){

                        if($v["p_".$prop_id]!=null){

                            if($plugadd[$v["p_".$prop_id]]){
                                $plugadd[$v["p_".$prop_id]]=$plugadd[$v["p_".$prop_id]]+1;
                            }else{
                                $plugadd[$v["p_".$prop_id]]=1;
                            }
                        }
                        $aFilter['goods_id'][] = $v['goods_id'];    //当前的商品结果集
                    }
                }
                $navselector=0;
                if(is_array($prop['options'])){
                    foreach($prop['options'] as $q=>$e){
                        if($plugadd[$q]){
                            $prop['options'][$q]=$prop['options'][$q]."<span class='num'>(".$plugadd[$q].")</span>";
                            if (!$navselector)
                                $navselector=1;
                        }else{
                            unset($prop['options'][$q]);
                        }
                    }
                }

                $selector[$prop_id] = $prop;
            }
        }

        if ($navselector){
            $nsvcount=0;
            $noshow=0;
            foreach($selector as $sk => $sv){
                if ($sv['value']){
                    $nsvcount++;
                }
                if (is_numeric($sk)&&!$sv['show']){
                    $noshow++;
                }
            }
            if ($nsvcount==intval(count($selector)-$noshow))
                $navselector=0;
        }
        foreach((array)$cat['spec'] as $spec_id=>$spec_name){
            $sId['spec_id'][] = $spec_id;
        }

        $cat['ordernum'] = $cat['ordernum']?$cat['ordernum']:array(''=>2);
        if ($cat['ordernum']){
            if ($selector){
                foreach($selector as $key => $val){
                    if(!in_array($key,$cat['ordernum'])&&$val){
                        $selectorExd[$key]=$val;
                    }
                }
            }
        }
        if(is_array($aProduct)){
            foreach($aProduct as $apk=>$apv){
                $rfilter[] = $apv['goods_id'];
            }
        }
        if(is_object(kernel::service('propselect.prop_search')))
            $dprop = kernel::service('propselect.prop_search')->getProps($filter,$propz,$prot,$dprop,$searchSelect);


        $this->pagedata['dprop'] = $dprop;
        $this->pagedata['nprop'] = $nprop;

        //对商品进行预处理
        $this->pagedata['mask_webslice'] = $this->app->getConf('system.ui.webslice')?' hslice':null;
        $this->pagedata['searchInput'] = &$searchInput;
        $this->pagedata['selectorExd'] = $selectorExd;
        $this->cat_id = $cat_id;
        $this->_plugins['function']['selector'] = array(&$this,'_selector');
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=>  $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','full'=>1,'act'=>'index','args'=>array( ($cat_type?$old_cat_id:implode(',',$cat_id) ),'',$orderBy,$tab,($tmp=time()),$cat_type,$view))).'?scontent='.urlencode($p['str']),
            'token'=>$tmp);
        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->_response->set_http_response_code(404);
        }
        if(!$count){
            $this->pagedata['emtpy_info'] = kernel::single('site_errorpage_get')->getConf('errorpage.search');

        }
        $this->pagedata['searchtotal']=$count;

        $aData = $this->get_current_member();
        if(is_array($aProduct) && count($aProduct) > 0){
            $objProduct = $this->app->model('products');
            if($this->app->getConf('site.show_mark_price')=='true'){
                $setting['mktprice'] = $this->app->getConf('site.show_mark_price');
                if(isset($aProduct)){
                    foreach($aProduct as $pk=>$pv){
                        if(empty($aProduct[$pk]['mktprice']))
                            $aProduct[$pk]['mktprice'] = $objProduct->getRealMkt($pv['price']);
                    }
                }
            }else{
                $setting['mktprice'] = 0;
            }
            $setting['saveprice'] = $this->app->getConf('site.save_price');
            $setting['buytarget'] = $this->app->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            //spec_desc

            $this->site_member_lv_id = $aData['member_lv'];
            $oGoodsLv = &$this->app->model('goods_lv_price');
            $oMlv = &$this->app->model('member_lv');
            if ($this->site_member_lv_id)
                $mlv = $oMlv->getList( 'dis_count', array('member_lv_id'=>$this->site_member_lv_id) );


            $tmpGoods = array();
            $tmp_goods_ids = array_map('current',$aProduct);
            /** 获取所有商品对应的货品 **/
            $tmp_products = $objProduct->getList('product_id, spec_info, price,mktprice, freez, store,   marketable, goods_id',array('goods_id|in'=>$tmp_goods_ids,'marketable'=>'true'));
            $tmp_products = utils::array_change_key($tmp_products,'goods_id', 1);

            /** 促销处理 **/
            $aProduct['goods_ids'] = $tmp_goods_ids;
            $aPromotion_price = kernel::single('b2c_goods_promotion_price')->process($aProduct);
            unset($aProduct['goods_ids']);

            foreach ($aProduct as $key=>&$val) {
                $tvPrice = array();
                //$temp = $objProduct->getList('mktprice,product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
                $temp = $tmp_products[$val['goods_id']];
                $priceArea = array();
                $mktpriceArea = array();
                foreach( $tmp_products[$val['goods_id']] as $tpv ){
                    $tvPrice[] = $tpv['price'];
                    $priceArea[]=$tpv['price'];//销售价区域
                    if( $tpv['mktprice'] == '' || $tpv['mktprice'] == null )
                       $mktpriceArea[]= $objProduct->getRealMkt($tpv['mktprice']);
                    else
                       $mktpriceArea[]= $tpv['mktprice'];
                }

                if ($this->app->getConf('site.show_mark_price')=="true" && count($priceArea)>1){//列表页价格区间@lujy
                    $minprice = min($priceArea);
                    $maxprice = max($priceArea);
                    if ($minprice<>$maxprice){
                        $val['minprice'] = $minprice;
                        $val['maxprice'] = $maxprice;
                    }
                }
                if ($this->app->getConf('site.show_mark_price')=="true" && count($mktpriceArea)>1){//列表也市场价区间
                    $mktminprice = min($mktpriceArea);
                    $mktmaxprice = max($mktpriceArea);
                    if ($mktminprice<>$mktmaxprice){
                        $val['minmktprice'] = $mktminprice;
                        $val['maxmktprice'] = $mktmaxprice;

                    }
                }

                $val['price'] = min($tvPrice);
                $val['mktprice'] = max($mktpriceArea);
                if( $mlv ){
                    $tmpGoods = array();
                    foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$this->site_member_lv_id ) ) as $k => $v ){
                        $tmpGoods[$v['product_id']] = $v['price'];
                    }
                    foreach( $temp as &$tv ){
                        $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv[0]['dis_count']*$tv['price'] ));
                    }
                    $val['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv[0]['dis_count']*$val['price'] ));
                }

                if($aPromotion_price[$val['goods_id']]){
                    $promotion_price = $aPromotion_price[$val['goods_id']];

                    if($promotion_price['price']) {
                        $val['timebuyprice'] = $promotion_price['price'];
                    }
                    else {
                        $val['timebuyprice'] = $val['price'];
                    }
                    $val['show_button'] = $promotion_price['show_button'];
                    $val['timebuy_over'] = $promotion_price['timebuy_over'];
                }

                $val['spec_desc_info'] = $temp;
                $aProduct[$key]['product_id'] = $temp[0]['product_id'];
            }

            // add here by liuyong
            // show tag on goods image
            foreach( kernel::servicelist('tags_special.add') as $services ) {
                if ( is_object($services)) {
                    if ( method_exists( $services, 'add') ) {
                        $services->add( $rfilter, $aProduct );
                    }
                }
            }

            // end of add
            $this->pagedata['products'] = &$aProduct;
        }

        if(!$aData['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
        if($SpecSelList){
            $this->pagedata['SpecSelList'] = $SpecSelList;
        }
        if($searchSelect){
            $this->pagedata['searchSelect'] = &$searchSelect;
        }
        $this->pagedata['curView'] = $view;
        $this->pagedata['selector'] = &$selector;

        $this->pagedata['cat_type'] = $cat_type;
        if($GLOBALS['search_array']&&is_array($GLOBALS['search_array'])){
            $this->pagedata['search_array'] = implode("+",$GLOBALS['search_array']);
        }
        $this->pagedata['_PDT_LST_TPL'] = $cat['tpl'];
        $this->pagedata['filter'] = $filter;
        unset($filter['name']);
        $this->pagedata['bfilter'] = $filter;
        $this->set_tmpl('gallery');
        $this->pagedata['gallery_display'] = $this->app->getConf('gallery.display.grid.colnum');
        $this->pagedata['show_cat'] = $this->app->getConf('site.cat.select');
        if($count < $this->pagedata['gallery_display']){
            $this->pagedata['gwidth'] = $count * (100/$this->pagedata['gallery_display']);
        }else{
            $this->pagedata['gwidth'] = 100;
        }
        $this->pagedata['property_select'] = $this->app->getConf('site.property.select');
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['proparg'] = $proparg;

        /** 获取商品表相关meta **/
        if ($cat_id){
            $obj_meta_register = app::get('dbeav')->model('meta_register');
            $arr_meta_register = $obj_meta_register->getList('*',array('tbl_name'=>$productCat->table_name(1),'col_name'=>'seo_info'));
            if (!$arr_meta_register){
                $productCat->cat_meta_register();
            }

            $meta_desc = $arr_meta_register[0]['col_desc'];
            $col_type = $arr_meta_register[0]['col_type'];
            $obj_meta_value = app::get('dbeav')->model('meta_value_'.$col_type);
            $seo_info = $obj_meta_value->select($arr_meta_register[0]['mr_id'],$cat_id);

            if(is_array($seo_info) && count($seo_info) == 1){
                $seo_info = $seo_info[$cat_id[0]];
            }elseif(is_string($seo_info)){
                $seo_info = unserialize($seo_info[$cat_id[0]]);
            }elseif(!$seo_info){
                $seo_info="";
            }
        }

        if(!empty($seo_info['seo_info']['seo_title']) || !empty($seo_info['seo_info']['seo_keywords']) || !empty($seo_info['seo_info']['seo_description'])){
            $this->title = $seo_info['seo_info']['seo_title'];
            $this->keywords = $seo_info['seo_info']['seo_keywords'];
            $this->description = $seo_info['seo_info']['seo_description'];
        }else{
            $this->setSeo('site_gallery','index',$this->prepareSeoData($this->pagedata));
        }

        $this->page('site/gallery/index.html');
    }

    function prepareSeoData($data){
        //$objtype = $this->app->model('goods_type');
        $catpath = $GLOBALS['runtime']['path'];

        if(is_array($catpath)){
            $cat_path = $catpath[0]['title'];
            unset($catpath[0]);
            foreach($catpath as $ck=>$cv){
                    $cat_path .= ','.$cv['title'];
            }
        }
        return array(
            'shop_name'=>$this->shopname,
            'search_num'=>$data['searchtotal'],
            'goods_cat'=>$this->cat_result[0]['cat_name']?$this->cat_result[0]['cat_name']:'',
            'goods_cat_p'=>$cat_path,
            'goods_type'=>$this->spec_goods?$this->spec_goods['name']:'',
        );
    }

}
