<?php
class b2c_goods_model{

    private $data = null;

    function __construct(){
        $this->appB2c = &app::get('b2c');
        $this->oGoods = &$this->appB2c->model('goods');
    }

    function getGoods( $gid ){
        //function getGoods( $gid ,$resp=null){
        if( $this->data[$gid] )
            return $this->data[$gid];
        $objProducts = &$this->appB2c->model('products');

        $subsdf = array(
            'product'=>array(
                '*',array(
                        'price/member_lv_price'=>array('*')
                    )
            ),
            ':brand'=>array('*'),
            ':goods_type'=>array('*'),
            'keywords'=> array('*'),
            'images' => array('*',array('image'=>array('*')))
        );
//        $aGoods = $this->oGoods->fetchkv( array( 'goods_id' => $gid ) );

        if( $aGoods ){
            $objBrand = &$this->appB2c->model('brand');
            $brand_name = $objBrand->dump(array('brand_id'=>$aGoods['brand']['brand_id']),'brand_name');
            $tmpGoods = $this->oGoods->dump($gid,'goods_id,store,price',array(
                'product'=>array(
                    'goods_id,product_id,price,store,freez',
                    array('price/member_lv_price'=>array('*'))
                ),
            ));
            if(is_array($tmpGoods['product'])){
                foreach( $tmpGoods['product'] as $k =>&$v ){
//                  $aGoods['product'][$k]['store'] = $v['store'];
//                  $aGoods['product'][$k]['freez'] = $v['freez'];
                    $aGoods['product'][$k] = array_merge( $aGoods['product'][$k] ,$v );
                }
            }
//            $aGoods = array_merge( $aGoods,$tmpGoods );
            $aGoods['current_price'] = $tmpGoods['current_price'];
            $aGoods['brand']['brand_name'] = $brand_name['brand_name'];
            $aGoods['store'] = $tmpGoods['store'];
            $aGoods['freez'] = $tmpGoods['freez'];
        }else{
            $aGoods = $this->oGoods->dump($gid,'*',$subsdf);
        };
        if( $aGoods['product'] && is_array($aGoods['product'])){
            foreach($aGoods['product'] as $ak=>$av){
                if($mlv = $this->site_member_lv_id){
                    $aGoods['price'] = $av['price']['member_lv_price'][$mlv]['price'];
                    if(!$this->appB2c->getConf('site.member_price_display')&&isset($av['price']['member_lv_price'][$mlv])){
                        unset($aGoods['product'][$ak]['price']['member_lv_price']);
                        $aGoods['product'][$ak]['price']['member_lv_price'][$mlv] = $av['price']['member_lv_price'][$mlv];
                    }
      //              $aGoods['product'][$ak]['price']['price']['current_price'] = $av['price']['member_lv_price'][$mlv]['price'];
                }else{
     //               $aGoods['product'][$ak]['price']['price']['current_price'] = $aGoods['product'][$ak]['price']['price']['price'];
                }
                if($av['status'] == 'true'){
                       $aGoods['upstore'] += $av['store'];
                       $aGoods['current_price'] = $av['price']['price']['current_price'];
                }

            }
        }else{
            return false;
        }
        if(!is_array($aGoods['adjunct']))
            $aGoods['adjunct'] = unserialize($aGoods['adjunct']);
        else
            $aGoods['adjunct'] = $aGoods['adjunct'];
		
		/** 调整goods的配件的属性，参数和显示 - 库存为0或者已经下架的配件不能显示出来 **/
		$obj_tag_rel = app::get('desktop')->model('tag_rel');
        if(is_array($aGoods['adjunct'])){
            foreach($aGoods['adjunct'] as $key => $rows){    //loop group
				$gfilter = array();
                if($rows['set_price'] == 'minus'){
                    $cols = 'product_id,goods_id,name, spec_info, store, freez, price, price-'.$rows['price'].' AS adjprice,marketable';
                }else{
                    $cols = 'product_id,goods_id,name, spec_info, store, freez, price, price*'.($rows['price']?$rows['price']:1).' AS adjprice,marketable';
                }
                if($rows['type'] == 'goods'){
                    if(!$rows['items']['product_id']) $rows['items']['product_id'] = array(-1);
                    $arr = $rows['items'];
                }else{
                    parse_str($rows['items'].'&dis_goods[]='.$gid, $arr);
                }
				
                if(isset($arr['type_id'])){
					/** 指定搜索条件来确定配件 **/
					if ($arr['tag']){
						$arr['goods_id'] = array();
						$str_tags = implode(',',$arr['tag']);
						if ($arr_rel_ids = $obj_tag_rel->db->select('SELECT `rel_id` FROM '.$obj_tag_rel->table_name(1)." WHERE `tag_id` IN (".$str_tags.")")){
							foreach ($arr_rel_ids as $rel_ids){
								array_push($arr['goods_id'], $rel_ids['rel_id']);
							}
						}
						unset($arr['tag']);
					}
                    if(is_array($arr['props'])){
                        $c = 1;
                        foreach($arr['props'] as $pk=>$pv){
                            $p_id= 'p_'.$c;
                             foreach($pv as $sv){
                                 if($sv == '_ANY_'){
                                     unset($pv);
                                 }
                             }
                             if(isset($pv))
                                 $arr[$p_id] = $pv;
                             $c++;
                        }
                        unset($arr['props']);
                    }
					if ($arr['pricefrom']){
						$arr['price|than'] = $arr['pricefrom'];
						unset($arr['pricefrom']);
					}
					if ($arr['priceto']){
						$arr['price|lthan'] = $arr['priceto'];
						unset($arr['priceto']);
					}
					$arr = array_merge($arr,array('marketable'=>'true'));
                    $gId = $this->oGoods->getList('goods_id',$arr,0,-1);
                    if(is_array($gId)){
                        foreach($gId as $gv){
                            $gfilter['goods_id'][] = $gv['goods_id'];
                        }
                        if(empty($gfilter))
                        $gfilter['goods_id'] = '-1';
                    }
                }else{
					if ($arr['product_id']){
						/** 商品下架了，对应的货品就不能显示在前台的配件里面了 **/
						foreach ($arr['product_id'] as $pkey=>$product_id){
							$arr_product = $objProducts->db->select("SELECT `goods_id` FROM ".$objProducts->table_name(1)." WHERE `product_id`=".intval($product_id));
							$arr_goods = $this->oGoods->db->select("SELECT `marketable` FROM ".$this->oGoods->table_name(1)." WHERE `goods_id`=".intval($arr_product[0]['goods_id']));
							if ($arr_goods[0]['marketable'] == 'false') unset($arr['product_id'][$pkey]);
						}
						if (!$arr['product_id']) $arr['product_id'] = '-1';
						
					}
                    $gfilter = $arr;
                }
				/** 加入筛选条件 **/
				$gfilter = array_merge($gfilter,array('marketable'=>'true'));
                if($aAdj = $objProducts->getList($cols,$gfilter,0,-1)){
                    if(is_array($aAdj)){
                        foreach($aAdj as $ak=>$av){
							if (isset($av['store'])&&$av['store']==0) unset($aAdj[$ak]);
                            $aGoods['adjunct']['totaladjprice'] += $av['adjprice'];
                        }
                    }
                    $aGoods['adjunct'][$key]['items'] = $aAdj;
                }else{
                    unset($aGoods['adjunct'][$key]);
                }


            }
        }
        if( $aGoods['product'] ){
            foreach($aGoods['product'] as $pkey => $p){
                if( $p['status'] == 'false' ){
                    if($aGoods['status'] == 'true') {
                        unset( $aGoods['product'][$pkey] );
                    }
                    continue;
                }
                if( $p['spec_desc']['spec_private_value_id'] )
                if(is_array($p['spec_desc']['spec_private_value_id'])){
                    foreach($p['spec_desc']['spec_private_value_id'] as $key=>$spec_private_value_id){
                        $used_spec[$spec_private_value_id] = 1;
                    }
                }
            }
        }

        $aGoods['used_spec'] = $used_spec;
        if(!$aGoods || empty($aGoods['product'])){
            return false;
            //$resp->set_http_response_code(404, '无效商品'); //todo: response千万别写到lib里来，也不需要有echo在基类里，不符合http协议
            //exit;
        }
        $objCat = &$this->appB2c->model('goods_cat');
        $aCat = $objCat->dump($aGoods['category']['cat_id'],'cat_name,addon');
        $aCat['addon'] = unserialize($aCat['addon']);
        if($aGoods['seo']['meta_keywords']){
            if(empty($this->keyWords))
            $this->keyWords = $aGoods['seo']['meta_keywords'];
        }else{
            if(trim($aCat['addon']['meta']['keywords'])){
                $this->keyWords = trim($aCat['addon']['meta']['keywords']);
            }
        }
        if($aGoods['seo']['meta_description']){
            $this->metaDesc = $aGoods['seo']['meta_description'];
        }else{
            if(trim($aCat['addon']['meta']['description'])){
                $this->metaDesc = trim($aCat['addon']['meta']['description']);
            }
        }
        //初始化货品

        if(!empty($aGoods['product'])){
            foreach($aGoods['product'] as $key => $products){
                $a = array();
                if( $products['props']['spec'] )
                foreach($products['props']['spec'] as $k=>$v){
                    $a[] = trim($k).':'.trim($v);
                }
                $aGoods['product'][$key]['params_tr'] = implode('-',$a);
                $aPdtIds[] = $products['product_id'];
                if($aGoods['price'] > $products['price']){
                    $aGoods['price'] = $products['price'];//前台默认进来显示商品的最小价格
                }
            }
        }else{
            $aPdtIds[] = $aGoods['product_id'];
        }
        if($this->appB2c->getConf('site.show_mark_price')=='true'){
            $aGoods['setting']['mktprice'] = $this->appB2c->getConf('site.show_mark_price');

             if( !isset( $aGoods['mktprice'] ) )
                $aGoods['mktprice'] = $objProducts->getRealMkt($aGoods['price']);
        }else{
            $this->pagedata['showMktp'] = true;
            $aGoods['setting']['mktprice'] = 0;
        }
        $aGoods['setting']['saveprice'] = $this->appB2c->getConf('site.save_price');
        $aGoods['setting']['buytarget'] = $this->appB2c->getConf('site.buy.target');
        $aGoods['setting']['score'] = $this->appB2c->getConf('point.get_policy');
        $aGoods['setting']['scorerate'] = $this->appB2c->getConf('point.get_rate');
        if($aGoods['setting']['score'] == 1){
            $aGoods['score'] = intval($aGoods['price'] * $aGoods['setting']['scorerate']);
        }

        /*--------------规格关联商品图片--------------*/
        if (!empty($specImg)){
            $tmpImgAry=explode("_",$specImg);
            if (is_array($tmpImgAry)){
                foreach($tmpImgAry as $key => $val){
                    $tImgAry = explode("@",$val);
                    if (is_array($tImgAry)){
                          $spec[$tImgAry[0]]=$val;
                          $imageGroup[]=substr($tImgAry[1],0,strpos($tImgAry[1],"|"));
                          $imageGstr .= substr($tImgAry[1],0,strpos($tImgAry[1],"|")).",";
                          $spec_value_id = substr($tImgAry[1],strpos($tImgAry[1],"|")+1);
                          if ($aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'])
                            $specValue[]=$aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'];
                          if ($aGoods['FlatSpec']&&array_key_exists($tImgAry[0],$aGoods['FlatSpec']))
                              $aGoods['FlatSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
                          if ($aGoods['SelSpec']&&array_key_exists($tImgAry[0],$aGoods['SelSpec']))
                              $aGoods['SelSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
                    }
                }
                if ($imageGstr){
                    $imageGstr=substr($imageGstr,0,-1);
                }
            }

            /****************设置规格链接地址**********************/
            if (is_array($aGoods['specVdesc'])){
                foreach($aGoods['specVdesc'] as $gk => $gv){
                    if (is_array($gv['value'])){
                        foreach($gv['value'] as $gkk => $gvv){
                            if(is_array($spec)){
                                $specId = substr($gvv['spec_goods_images'],0,strpos($gvv['spec_goods_images'],"@"));
                                foreach($spec as $sk => $sv){
                                    if ($specId != $sk){
                                        $aGoods['specVdesc'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        if ($aGoods['FlatSpec']&&array_key_exists($gk,$aGoods['FlatSpec'])){
                                            $aGoods['FlatSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        }
                                        if ($aGoods['SelSpec']&&array_key_exists($gk,$aGoods['SelSpec'])){
                                            $aGoods['SelSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //页面提示所选规格名称
//            $this->pagedata['SelectSpecValue'] = array('value' => implode("、", (array)$specValue), 'selected' => 1);
        }
        else{

             if (is_array($aGoods['spec'])&&count($aGoods['spec'])>0){
                 foreach($aGoods['spec'] as $agk => $agv){
                    $specValue[]=$agv['spec_name'];
                }
             }
//            $this->pagedata['SelectSpecValue'] = array('value'=>implode("、",(array)$specValue),'selected'=>0);
        }



        /********-------------------*********/
        $objProprice = kernel::single('b2c_goods_promotion_price');
        foreach($aGoods['product'] as $product_id=>$product){
            $promotionPrice = $objProprice->process($product);

            $this->pagedata['product0id'] = $product_id;
            if( $product['price']['member_lv_price'] ){
               foreach($product['price']['member_lv_price'] as $k=>$v){
                    $mprice[$v['level_id']] = empty($promotionPrice['price'])?$v['price']:$promotionPrice['price'];
               }
            }
            $product2spec[$product_id] = array(
                    'bn'=>$product['bn'],
                    'price'=>empty($promotionPrice['price'])?$product['price']['price']['current_price']:$promotionPrice['price'],
                    'mktprice'=>$product['price']['mktprice']['price'],
                    'mprice'=>$mprice,
                    'discountprice'=>($product['price']['mktprice']['price'])-(empty($promotionPrice['price'])?$product['price']['price']['current_price']:$promotionPrice['price']),
                    'store'=>(isset($product['store'])?($product['store']-intval( $product['freez'] )):999999 ),
                    'weight'=>$product['weight'],
                    'spec_private_value_id'=>$product['spec_desc']['spec_private_value_id'],
                );

            if( $product['spec_desc']['spec_private_value_id'] )
            foreach($product['spec_desc']['spec_private_value_id'] as $k=>$v){
                $spec2product[$v]['product_id'][] = $product_id;
                $spec2product[$v]['images'] = array_flip( array_merge( array_flip( (array)$spec2product[$v]['images'] ), array_flip( explode(',',$aGoods['spec'][$k]['option'][$v]['spec_goods_images'])) ));
            }
            unset($promotionPrice);
        }

        $aGoods['product2spec'] = json_encode( $product2spec );

        $aGoods['spec2product'] = json_encode( $spec2product );



        $this->data[$gid] = $aGoods;
        return $aGoods;

    }

}

