<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_view_helper{

    function __construct($app){
        $this->app = $app;
    }

    function function_goodsmenu($params,&$smarty){
        return $smarty->_fetch_compile_include('b2c','site/product/menu.html',$params);
    }

    function function_selector($params, &$smarty){
        $filter = $params['filter'];
        if(is_numeric($params['key'])){           
            if($params['del']&&isset($filter['p_'.$params['key']])){
                 unset($filter['p_'.$params['key']]);
            }else{
                $data = &$filter['p_'.$params['key']];
            }
        }elseif ($params['key']=="spec"){
            $tmp=explode(",",$params['value']);
            $data = &$filter['s_'.$tmp[0]];
        }else{
            $data = &$filter[$params['key']];
        }

        if($params['mod']=='append'){
            $data[] = $params['value'];
        }elseif($params['mod']=='remove'){
            $data = array_flip($data);
            unset($data[$params['value']]);
            $data = array_flip($data);
        }else{
            if ($params['key']=="spec"){
                $tmpData = explode(",",$params['value']);
                $data = array($tmpData[1]);
            }
            elseif(!$params['del']){
                $data = array($params['value']);
            }

        }

    $searchtools = $smarty->app->model('search');
        $args = $params['args'];

        $args[1] = $searchtools->encode($filter);
        $args[4]=1;

        return app::get('site')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'site_gallery','full'=>1,'args'=>$args));
    }

    function function_pagers($params, &$smarty){
    if(!$params['data']['current'])$params['data']['current'] = 1;
    if(!$params['data']['total'])$params['data']['total'] = 1;
    if($params['data']['total']<2){
        return '';
    }

    $prev = $params['data']['current']>1?
        '<a href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).__('" class="prev" title='.app::get('b2c')->_("上一页").'>&laquo;'.app::get('b2c')->_('上一页').'</a>'):
        '<span title='.app::get('b2c')->_("已经是第一页").' class="unprev">'.app::get('b2c')->_("已经是第一页").'</span>';

    $next = $params['data']['current']<$params['data']['total']?
      '<a href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).__('" class="next last" title='.app::get('b2c')->_("下一页").'>'.app::get('b2c')->_('下一页').'&raquo;</a>'):
        '<span title='.app::get('b2c')->_("已经是最后一页").' class="unnext">'.app::get('b2c')->_("已经是最后一页").'</span>';

    if($params['type']=='mini'){
        return <<<EOF
    <div class="pager"><strong class="pagecurrent">{$params['data']['current']}</strong><span class="line">/</span><span class="pageall">{$params['data']['total']}</span>{$prev}{$next}</div>
EOF;
    }else{

        $c = $params['data']['current']; $t=$params['data']['total']; $v = array();  $l=$params['data']['link']; $p=$params['data']['token'];

        if($t<11){
            $v[] = $this->_pager_link(1,$t,$l,$p,$c);
            //123456789
        }else{
            if($t-$c<8){
                $v[] = $this->_pager_link(1,3,$l,$p);
                $v[] = $this->_pager_link($t-8,$t,$l,$p,$c);
                //12..50 51 52 53 54 55 56 57
            }elseif($c<10){
                $v[] = $this->_pager_link(1,max($c+3,10),$l,$p,$c);
                $v[] = $this->_pager_link($t-1,$t,$l,$p);
                //1234567..55
            }else{
                $v[] = $this->_pager_link(1,3,$l,$p);
                $v[] = $this->_pager_link($c-2,$c+3,$l,$p,$c);
                $v[] = $this->_pager_link($t-1,$t,$l,$p);
                //123 456 789
            }
        }
        $links = implode('...',$v);

//    str_replace($params['data']['token'],4,$params['data']['link']);
//    if($params['data']['total']
        return <<<EOF
    <div class="pager">{$prev}{$links}{$next}</div>    
EOF;
    }
}

function function_goods_pager($params, &$smarty){
	if(!$params['data']['current'])$params['data']['current'] = 1;
    if(!$params['data']['total'])$params['data']['total'] = 1;
    if($params['data']['total']<2){
        return '';
    }
	
	$v = array();
	$params['data']['current']>1?($v[] = ('<span class="first" rel="request" href="'.str_replace($params['data']['token'],1,$params['data']['link']).'">&laquo;&laquo;</span><span class="prev" rel="request" href="'.str_replace($params['data']['token'],$params['data']['current']-1,$params['data']['link']).'">&laquo;</span>'.(($params['data']['current']>2)?'<span class="andson">...</span>':''))):($v[] = ('<span class="first disabled">&laquo;&laquo;</span><span class="prev disabled">&laquo;</span>'.(($params['data']['current']>3)?'<span class="andson">...</span>':'')));
	
	$links = '';
	$c = $params['data']['current'];
	$t = $params['data']['total'];  
	$l=$params['data']['link']; 
	$p=$params['data']['token'];
	
	if($t<4){
		$v[] = $this->_pager_links(1,$t,$l,$p,$c);
		//123456789
	}else{
		if($c==1){
			$v[] = $this->_pager_links(1,3,$l,$p,$c);
		}else{
			if ($c+1>$t)
				$v[] = $this->_pager_links($c-2,$t,$l,$p,$c);
			else
				$v[] = $this->_pager_links($c-1,$c+1,$l,$p,$c);
		}
	}
	
	$params['data']['current']<$params['data']['total']?($v[] = (((intval($params['data']['total']-$params['data']['current'])>1)?'<span class="andson">...</span>':'').'<span class="next" rel="request" href="'.str_replace($params['data']['token'],$params['data']['current']+1,$params['data']['link']).'">&raquo;</span><span class="last" rel="request" href="'.str_replace($params['data']['token'],$params['data']['total'],$params['data']['link']).'">&raquo;&raquo;</span>')):($v[] = ('<span class="next disabled">&raquo;</span><span class="last disabled">&raquo;&raquo;</span>'));
	
	$links = implode('',$v);
	
	return <<<EOF
    <div class="pager"><div class="pagernum">{$prev}{$links}{$next}</div></div>  
EOF;
}

function _pager_links($from,$to,$l,$p,$c=null){
	for($i=$from;$i<$to+1;$i++){
        if($c==$i){
            $r[]=' <span class="current">'.$i.'</span> ';
        }else{
        $r[]=' <span rel="request" href="'.str_replace($p,$i,$l).'">'.$i.'</span> ';
        }
    }
    return implode(' ',$r);
}

function _pager_link($from,$to,$l,$p,$c=null){
    for($i=$from;$i<$to+1;$i++){
        if($c==$i){
            $r[]=' <strong class="pagecurrent">'.$i.'</strong> ';
        }else{
        $r[]=' <a class="pagernum" href="'.str_replace($p,$i,$l).'">'.$i.'</a> ';
        }
    }
    return implode(' ',$r);
}

    function modifier_paddingleft($vol,$empty,$fill)
    {
        return str_repeat($fill,$empty).$vol;

    }

    
    function modifier_lazyimg($v){  
     //   $result = preg_replace("/(\<img[\s\S]+)src=/Us","\\1lazy-load-img=",$v); 
        return $v;
    }

    function function_goodsspec($params) {
        return kernel::single("b2c_goods_detail_spec")->show($params['goods_id'], $aGoods, array('spec_node_new'=>$params['spec_node_new']));
    }

}
