<?php
class solrCRUD{

    function __construct(){
        //constructure
    }

    function create($itemdata=array()){
		
        set_time_limit(0);
		
        include_once(dirname(__FILE__)."/classes/BharatSolrDataImport.class.php");
        $obj=new BharatSolrDataImport();
        $check_auth=md5('BharatDataImport');
		echo "total".count($itemdata);
		echo "<pre>dsdasdsa";
		
        $itemdata=json_decode($itemdata,true);
        
        echo "<pre>";
        
        if(is_array($itemdata) || is_object($itemdata)){
			echo "coming";
            $data=$obj->indexIntoSolr($itemdata);
            print_r($data);
        }
        echo "</pre>";

    }

    function getCulrProcess($cUrl){
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $cUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
          $content = curl_exec($ch);
          $status = curl_getinfo($ch);
         // print_r($status);
          curl_close($ch);
         
          return $content;
    }


    function search($searchparms=array()){
        include_once(dirname(__FILE__) . '/classes/BharatSearch.class.php');
        $obj = new BharatSearch();
        $params = array();
        $error=0;
        $response_array                      =  array();
        $response_array['status_code']               =  '200';
        $response_array['status_text']           =  'OK';

        if (isset($searchparms['category_id']) && !empty($searchparms['category_id'])) {
            $params['category_id'] = intval($searchparms['category_id']);
        }

        if (isset($searchparms['root_cat_id']) && !empty($searchparms['root_cat_id'])) {
            $params['root_cat_id'] = intval($searchparms['root_cat_id']);
        }
        if (isset($searchparms['language_id']) && !empty($searchparms['language_id'])) {
            $params['language_id'] = intval($searchparms['language_id']);
        }

        if (isset($searchparms['type']) && !empty($searchparms['type'])) {
            $params['type'] = $searchparms['type'];
        }

        if (isset($searchparms['q']) && !empty($searchparms['q'])) {
			
           // $params['q'] = urlencode($this->parseQuery(strip_tags($searchparms['q'])));
		    $params['q'] = urlencode(strip_tags($searchparms['q']));
        }

        if (isset($searchparms['sku']) && !empty($searchparms['sku'])) {
            $params['sku'] = $searchparms['sku'];
        }
        if (isset($searchparms['price_range']) && !empty($searchparms['price_range'])) {
            $params['price_range'] = urlencode($searchparms['price_range']);
        }

        if (isset($searchparms['name']) && !empty($searchparms['name'])) {
            $params['product_name'] = urlencode(strip_tags($searchparms['name']));
        }

        if (isset($searchparms['ids']) && !empty($searchparms['ids'])) {
            $params['product_ids'] = urlencode(strip_tags($searchparms['ids']));
        }


if (isset($searchparms['color']) && !empty($searchparms['color'])) {
    $params['color'] = urlencode(strip_tags($searchparms['color']));
}


if (isset($searchparms['offer']) && !empty($searchparms['offer'])) {
    $params['offer'] = urlencode(strip_tags($searchparms['offer']));
}


if (isset($searchparms['brand']) && !empty($searchparms['brand'])) {
    $params['brand'] = urlencode(strip_tags($searchparms['brand']));
}


if (isset($searchparms['model']) && !empty($searchparms['model'])) {
    $params['model'] = urlencode(strip_tags($searchparms['model']));
}


if (isset($_GET['category']) && !empty($searchparms['category'])) {
    $params['category'] = urlencode(strip_tags($searchparms['category']));
    if( $this->qCatname ){ $params['category'] = $this->qCatname; }
}

if (isset($searchparms['subcat']) && !empty($searchparms['subcat'])) {
    $params['subcat'] = urlencode(strip_tags($searchparms['subcat']));
}

if (isset($_GET['leafcat']) && !empty($searchparms['leafcat'])) {
    $params['leafcat'] = urlencode(strip_tags($searchparms['leafcat']));
}

if (isset($searchparms['price']) && !empty($searchparms['price'])) {
    $params['price'] = urlencode(strip_tags($searchparms['price']));
}
        if (isset($searchparms['only_facet']) && !empty($searchparms['only_facet'])) {
            $params['is_only_facet'] = 'yes';
        }

        if (isset($searchparms['is_with_facet']) && !empty($searchparms['is_with_facet'])) {
            $params['is_with_facet'] = 'yes';
        }
        
        if (isset($searchparms['page']) && !empty($searchparms['page'])) {
            $params['start'] = $searchparms['page'];
        }
        if (isset($searchparms['limit']) && !empty($searchparms['limit'])) {
            $params['limit'] = $searchparms['limit'];
        }
        if (isset($searchparms['price_stats']) && !empty($searchparms['price_stats'])) {
            $params['price_stats'] = 'yes';
        }

        if (isset($searchparms['sort']) && !empty($searchparms['sort'])) {
            $params['sort'] =$searchparms['sort'];
        }
        if (isset($searchparms['order']) && !empty($searchparms['order'])) {
            $params['orderby'] =$searchparms['order'];
        }
		 if (isset($searchparms['stock_status']) && !empty($searchparms['stock_status'])) {
                    $params['stock_status'] =$searchparms['stock_status'];
                }
		if (isset($searchparms['attribute']) && !empty($searchparms['attribute'])) {
			$params['attribute'] =$searchparms['attribute'];
		}
		if (isset($searchparms['size']) && !empty($searchparms['size'])) {
			$params['size'] =$searchparms['size'];
		}
		if (isset($searchparms['type']) && !empty($searchparms['type'])) {
				$params['type'] = $searchparms['type'];
			}
		if (isset($searchparms['filter']) && !empty($searchparms['filter'])) {
			$params['filter'] =$searchparms['filter'];
		}
        if($error==0){
            //$time_start = microtime(true);
			
            $produt_data = $obj->getResults($params);
            //$time_end = microtime(true);
            //$time = $time_end - $time_start;
            //echo "Execution time:: $time seconds\n";

            $result= (array)json_decode($produt_data);
            $master_data                            = $result['data'];
            $master_data_limit_count        = count($master_data);
            $master_data_count                  = $result['count'];
            if($master_data_count>0){
                $item_send_array = array();
				if(isset($result['didyoumean'])){
					$main_content['didyoumean'] =$result['didyoumean'];
				}
                $main_content['count']  = $master_data_count;
                $main_content['items_count']    = $master_data_limit_count;
                for($i=0;$i<$master_data_limit_count;$i++){
                        $looping_array = (array)$master_data[$i];
                        $item_send_array[$i]['product_id'] = $looping_array['product_id'];
                        $item_send_array[$i]['name']                    = $looping_array['product_name'];
                        $item_send_array[$i]['sku']     = $looping_array['sku'];
                        $item_send_array[$i]['model']       = $looping_array['model'];
                        $item_send_array[$i]['brand_id']        = $looping_array['brand_id'];
                        $item_send_array[$i]['upc']     = $looping_array['upc'];
                        $item_send_array[$i]['ean']     = $looping_array['ean'];
                        $item_send_array[$i]['business_model']  = $looping_array['business_model'];
                        $item_send_array[$i]['return_ploicy']   = $looping_array['return_ploicy'];
                        $item_send_array[$i]['location']        = $looping_array['location'];
                        $item_send_array[$i]['quantity']        = $looping_array['quantity'];
                        $item_send_array[$i]['vendor_id']       = $looping_array['vendor_id'];
                        $item_send_array[$i]['brandname']       = $looping_array['brandname'];
                        $item_send_array[$i]['category']        = json_decode($looping_array['category'],true);

                        if(isset($looping_array['image']) && !empty($looping_array['image'])){
                                $item_send_array[$i]['image']   = $looping_array['image'];
                        }else{
                                $item_send_array[$i]['image']   = '';
                        }
                        if(isset($looping_array['images']) && !empty($looping_array['images'])){
                                $item_send_array[$i]['images']  =json_decode($looping_array['images'],true);
                        }else{
                                $item_send_array[$i]['images']  = '';
                        }
                        if(isset($looping_array['color']) && !empty($looping_array['color'])){
                            $item_send_array[$i]['color']   =$looping_array['color'];
                        }else{
                                $item_send_array[$i]['color']   = '';
                        }
                        if(isset($looping_array['size']) && !empty($looping_array['size'])){
                                $item_send_array[$i]['size']    =$looping_array['size'];
                        }else{
                                $item_send_array[$i]['size']    = '';
                        }

                        $item_send_array[$i]['shipping']        = $looping_array['shipping'];
                        $item_send_array[$i]['transfer_price']  = $looping_array['transfer_price'];
                        $item_send_array[$i]['tax_class_id']    = $looping_array['tax_class_id'];
                        $item_send_array[$i]['points']  = $looping_array['points'];
                        if(isset($looping_array['date_available']) && !empty($looping_array['date_available'])){
                                $item_send_array[$i]['date_available']  =$looping_array['date_available'];
                        }else{
                                $item_send_array[$i]['date_available']  = '';
                        }
                        $item_send_array[$i]['weight']  = $looping_array['weight'];
                        $item_send_array[$i]['weight_class_id'] = $looping_array['weight_class_id'];
                        $item_send_array[$i]['length']  = $looping_array['length'];
                        $item_send_array[$i]['width']   = $looping_array['width'];
                        $item_send_array[$i]['height']  = $looping_array['height'];
                        $item_send_array[$i]['length_class_id'] = $looping_array['length_class_id'];
                        $item_send_array[$i]['shipment_mode']   = $looping_array['shipment_mode'];
                        $item_send_array[$i]['subtract']        = $looping_array['subtract'];
                        $item_send_array[$i]['minimum'] = $looping_array['minimum'];
                        $item_send_array[$i]['status']  = $looping_array['status'];
                        $item_send_array[$i]['hs_code'] = $looping_array['hs_code'];
                        $item_send_array[$i]['type']    = $looping_array['type'];
                        $item_send_array[$i]['viewed']  = $looping_array['viewed'];
                        $item_send_array[$i]['offers']  = $looping_array['offers'];
                        $item_send_array[$i]['delivery_charge'] = $looping_array['delivery_charge'];
                        $item_send_array[$i]['product_for_id']  = $looping_array['product_for_id'];
                        $item_send_array[$i]['product_usability_id']    = $looping_array['product_usability_id'];
                        $item_send_array[$i]['language_id']     = $looping_array['language_id'];
                        $item_send_array[$i]['description']     = $looping_array['description'];
                        @$item_send_array[$i]['tag']     = $looping_array['tag'];
                        $item_send_array[$i]['meta_title']      = $looping_array['meta_title'];
                        $item_send_array[$i]['meta_description']        = $looping_array['meta_description'];
                         $item_send_array[$i]['stock_status']    = $looping_array['stock_status'];
                        $item_send_array[$i]['selling_price']   = $looping_array['selling_price'];
                        $item_send_array[$i]['discount_price']  = $looping_array['discount_price'];
                        $item_send_array[$i]['mrp']     = $looping_array['mrp'];

                        if(isset($looping_array['weight_class']) && !empty($looping_array['weight_class'])){
                                $item_send_array[$i]['weight_class']    =$looping_array['weight_class'];
                        }else{
                                $item_send_array[$i]['weight_class']    = '';
                        }

                        if(isset($looping_array['length_class']) && !empty($looping_array['length_class'])){
                                $item_send_array[$i]['length_class']    =$looping_array['length_class'];
                        }else{
                                $item_send_array[$i]['length_class']    = '';
                        }
        }
        $main_content['data'] = $item_send_array;
        $facet_filter=array();
		$treecat=array();
        if($result['facet_data']){
          
			
            foreach($result['facet_data']->selling_price as $facetname => $valcount){
                if($valcount>0){
                        $facetlist=array();
                        $facetlist['val']=$facetname;
                        $facetlist['count']=$valcount;
                        $facet_filter['selling_price'][]=$facetlist;
                        unset($facetlist);

                }
            }
            foreach($result['facet_data']->model_ft as $facetname => $valcount){
                if($valcount>0){
                        $facetlist=array();
                        $facetlist['val']=$facetname;
                        $facetlist['count']=$valcount;
                        $facet_filter['model'][]=$facetlist;
                        unset($facetlist);
                }
            }
            foreach($result['facet_data']->brandname_ft as $facetname => $valcount){
                if($valcount>0){
                        $facetlist=array();
                        $facetlist['val']=$facetname;
                        $facetlist['count']=$valcount;
                        $facet_filter['brandname'][]=$facetlist;
                        unset($facetlist);
                }
            }
            foreach($result['facet_data']->offers_ft as $facetname => $valcount){
                if($valcount>0){
                        $facetlist=array();
                        $facetlist['val']=$facetname;
                        $facetlist['count']=$valcount;
                        $facet_filter['offers'][]=$facetlist;
                        unset($facetlist);
                }
            }
            foreach($result['facet_data']->color_ft as $facetname => $valcount){
                if($valcount>0){
                        $facetlist=array();
                        $facetlist['val']=$facetname;
                        $facetlist['count']=$valcount;
                        $facet_filter['color'][]=$facetlist;
                        unset($facetlist);

                }
            }
			
			foreach($result['facet_data']->price_range as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['price_range'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			
			foreach($result['facet_data']->size_ft as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['size'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			
			foreach($result['facet_data']->stock_status as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['stock_status'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			
			foreach($result['facet_data']->attribute_list_ft as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['attribute'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			foreach($result['facet_data']->filter_list_ft as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['filter'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			
			foreach($result['facet_data']->category_path_desc as $facetname => $valcount){
				if($valcount>0){		
				
				
					$facetlist=array();
					$facetlist['val']=$facetname;
					$facetlist['count']=$valcount;
					$facet_filter['category_path'][]=$facetlist;
					unset($facetlist);
					
				}
			}
			
        }
        $main_content['facet_data'] = $facet_filter;
		
			if(isset($result['facet_data']->facet_new_tree_category)){
				$main_content['facet_new_tree_category'] =$result['facet_data']->facet_new_tree_category;
			}
			
        }else{
                $main_content['count']  = 0;
                $main_content['items_count']    = 0;
                $main_content['status_text']            = 'No records found';
        }
    }else{
        $main_content['status_code']            = 400;
        $main_content['status_text']    = 'Error';
    }
        $header                                         = array();
        $header['version']                      = '0.1';
        $header['website']                      = 'https://trendybharat.com/';
        $header['company_name']         = 'Trendy Bharat';
        $header['created_time']         = date('Y-m-d H:i:s');
        $data_array['response']         = $response_array;
        $data_array['header']           = $header;
        $data_array['main_content']     = $main_content;
        return $data_array;

    }
	
	function trending_search($searchparms=array()){
		//error_reporting(0);
		require_once(dirname(__FILE__) . '/solrConfig.php');
                require_once(dirname(__FILE__) . '/classes/BharatTrendingAutoSearch.class.php');
		$obj = new BharatTrendingAutoSearch();
        
		$params = array();
		if (isset($searchparms['q']) && !empty($searchparms['q'])) {
			$params['q'] = urlencode(strip_tags($searchparms['q']));
		}
		if (isset($searchparms['start']) && !empty($searchparms['start'])) {
			$params['start'] = $searchparms['start'];
		}
		if (isset($searchparms['limit']) && !empty($searchparms['limit'])) {
			$params['limit'] = $searchparms['limit'];
		}
		
		if (isset($searchparms['type']) && !empty($searchparms['type'])) {
			$params['type'] = $searchparms['type'];
		}
		$sdearchdata = $obj->getTrendingAutoSearch($params);
		$result = json_decode($sdearchdata,true);
		return $result;
	}

       function autosuggest($searchparms=array()){
		  
			require_once(dirname(__FILE__) . '/solrConfig.php');
            require_once(dirname(__FILE__) . '/classes/BharatAutoSearch.class.php');
            $obj = new BharatAutoSearch();
            $searchParam=array();
            $autoserachData='';
            $response_array                                          =  array();
            $response_array['status_code']                           =  '200';
            $response_array['status_text']                   =  'OK';
            $error=1;
            if(isset($searchparms['q']) && !empty($searchparms['q'])){
                    $letters = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($searchparms['q'])); // remove all white space
                    $letters=urlencode(trim($letters));
                    $searchParam['q']=$letters;
                    $error=0;
            }else{
                    $error=1;
                    $response_array['status_code']                           =  '400';
                    $response_array['status_text']                   =  'Auto Search Query is Missing';
            }
            if(isset($searchparms['start'])){
                    $searchParam['start']=strip_tags(trim($searchparms['start']));
            }
            if(isset($searchparms['limit'])){
                    $searchParam['limit']=strip_tags(trim($searchparms['limit']));
            }
            if(isset($searchparms['data_type']) && $searchparms['data_type']=='Product'){
                    $searchParam['data_type']=strip_tags(trim($searchparms['data_type']));
                    $error=0;
            }else{
                    $searchParam['data_type']='Product';
					$error=0;
            }
			if (isset($searchparms['type']) && !empty($searchparms['type'])) {
				$searchParam['type'] = $searchparms['type'];
			}
            if($error==0)
            {
				
                $autoserachData=$obj->getSuggestionResult($searchParam);
                $result= json_decode($autoserachData,true);
                $master_data                            = $result['data'];
                $master_data_limit_count        = count($master_data);
                $master_data_count                  = $result['count'];
                
				require_once(dirname(__FILE__) . '/classes/BharatSearchCat.class.php');
				$objcat = new BharatSearchCat();
				
				$serachwords=urldecode($searchParam['q']);
				if($master_data_count>0){
					foreach($master_data as $resultcatVal){
						if($resultcatVal['data_type']=='searchlogs'){
							$searchParam['q']=$resultcatVal['name1'];
							break;
						}
					}
				}
				$produt_data = $objcat->getCatautosuggest($searchParam);
				$resultcat= (array)json_decode($produt_data);
				$master_data_countCat                  = $resultcat['count'];
				$getflag=0;
		if($master_data_count>0 ||$master_data_countCat>0){
				foreach($master_data as $resultcatVal){
					if($resultcatVal['data_type']=='searchlogs'){
						$serachwords=$resultcatVal['name1'];
						$getflag=1;				
						break;
					}
				}
				if($getflag==0 && !empty($master_data[0]['name1'])){
					$serachwords=$master_data[0]['name1'];
				}
				$item_send_catarray = array();
				if(count($resultcat)>0){
					$r=0;
					$k=3;
                    $healthySearch = array("women", "womens", "mens", "men", "kid", "kids");
					foreach($resultcat as $datatype=>$resultcatVal){
					
						
						if($resultcatVal=="Mens" || $resultcatVal=="Men" || $resultcatVal=="Women" || $resultcatVal=="Kids" || $resultcatVal=="Kid"){

						$newphrasesearch = str_replace($healthySearch, "", trim(strtolower($serachwords)));
						$string = preg_replace("/\bfor\b/", "", $string);
						$item_send_catarray[$r]['id']= md5($resultcatVal);
							$item_send_catarray[$r]['name']= ucwords(strtolower(preg_replace("/\bfor\b/", "",preg_replace("/\bFor\b/","",$newphrasesearch))))." For ".$resultcatVal;
							$item_send_catarray[$r]['data_type']			='category';
						}else{
							
							if($datatype==='brandname_ft'){
								/*foreach($resultcatVal as $resultcatValues){
								$item_send_catarray[$k]['id']= md5($resultcatValues);
								$item_send_catarray[$k]['name']= ucwords(strtolower(preg_replace("/\bfrom\b/", "",preg_replace("/\bFrom\b/", "",$serachwords))))." From ".$resultcatValues;
								$item_send_catarray[$k]['data_type']='brandname';
								$k++;
								}*/
							}else{
							$item_send_catarray[$r]['id']= md5($resultcatVal);
							$item_send_catarray[$r]['name']= ucwords(strtolower(preg_replace("/\bin\b/", "",preg_replace("/\bIn\b/", "",$serachwords))))." In ".$resultcatVal;
							$item_send_catarray[$r]['data_type']			='category';
							}
						}
						
						$r++;
					}
					
					foreach($resultcat['brandname_ft'] as $resultcatVal){
						
								$item_send_catarray[$k]['id']= md5($resultcatVal);
								$item_send_catarray[$k]['name']= ucwords(strtolower(preg_replace("/\bfrom\b/", "",preg_replace("/\bFrom\b/", "",$serachwords))))." From ".$resultcatVal;
								$item_send_catarray[$k]['data_type']='brandname';
						$k++;
					}
				}
////tp end////                  
					
                    $item_send_array = array();
                    $main_content['count']  = $master_data_count;
                    $main_content['suggest_count']  = $master_data_limit_count;
                    for($i=0;$i<$master_data_limit_count;$i++){
                            $looping_array      = (array)$master_data[$i];
                            $item_send_array[$i]['id']                      = $looping_array['id'];
                            $item_send_array[$i]['name']                    = ucwords(strtolower($looping_array['name1']));
                            $item_send_array[$i]['data_type']                       = $looping_array['data_type'];
                    }
					$merge_response=array_merge($item_send_catarray,$item_send_array);
					$main_content['data'] = $merge_response;
                  //  $main_content['data'] = $item_send_array;
                }else{
                        $main_content['count']  = 0;
                        $main_content['suggest_count']  = 0;
                        $main_content['status_text']            = 'No records found';
                }
            }else{
                    $main_content['status_code']            = 400;
                    $main_content['status_text']    = 'Error';
            }
            $header                                         = array();
            $header['version']                      = '0.1';
            $header['website']                      = 'https://trendybharat.com/';
            $header['company_name']         = 'Trendy Bharat';
            $header['created_time']         = date('Y-m-d H:i:s');
            $data_array['response']         = $response_array;
            $data_array['header']           = $header;
            $data_array['main_content']     = $main_content;
            return $data_array;
      }
	  
	  function product_details($searchparms=array()){
			error_reporting(0);
			require_once(dirname(__FILE__) . '/solrConfig.php');
			include(dirname(__FILE__) . '/classes/BharatSearch.class.php');
			$obj = new BharatSearch();
			$params = array();
			$id 			= isset($searchparms['id'])  ? intval($searchparms['id']) : 0 ;
			$error=0;
			
			if(empty($id) || $id==0){ 
				$error								 =	1;
				$response_array						 =  array();
				$response_array['status_code']				 =  '400';
				$response_array['status_text']			 =  'Product  Id Missing';
			}
			else{
				$response_array						 =  array();
				$response_array['status_code']				 =  '200';
				$response_array['status_text']			 =  'OK';
				 $params['product_ids'] =$id;
			}

			if (isset($searchparms['type']) && !empty($searchparms['type'])) {
				$params['type'] = $searchparms['type'];
			}


			if($error==0){
            $produt_data = $obj->getResults($params);
            $result= (array)json_decode($produt_data);
            $master_data                            = $result['data'];
            $master_data_limit_count        = count($master_data);
            $master_data_count                  = $result['count'];
            if($master_data_count>0){
                $item_send_array = array();
				
                $main_content['count']  = $master_data_count;
                $main_content['items_count']    = $master_data_limit_count;
				$i=0;
                
                        $looping_array = (array)$master_data[$i];
                        $item_send_array[$i]['product_id'] = $looping_array['product_id'];
                        $item_send_array[$i]['name']                    = $looping_array['product_name'];
                        $item_send_array[$i]['sku']     = $looping_array['sku'];
                        $item_send_array[$i]['model']       = $looping_array['model'];
                        $item_send_array[$i]['brand_id']        = $looping_array['brand_id'];
                        $item_send_array[$i]['upc']     = $looping_array['upc'];
                        $item_send_array[$i]['ean']     = $looping_array['ean'];
                        $item_send_array[$i]['business_model']  = $looping_array['business_model'];
                        $item_send_array[$i]['return_ploicy']   = $looping_array['return_ploicy'];
                        $item_send_array[$i]['location']        = $looping_array['location'];
                        $item_send_array[$i]['quantity']        = $looping_array['quantity'];
                        $item_send_array[$i]['vendor_id']       = $looping_array['vendor_id'];
                        $item_send_array[$i]['brandname']       = $looping_array['brandname'];
                        $item_send_array[$i]['category']        = json_decode($looping_array['category'],true);

                        if(isset($looping_array['image']) && !empty($looping_array['image'])){
                                $item_send_array[$i]['image']   = $looping_array['image'];
                        }else{
                                $item_send_array[$i]['image']   = '';
                        }
                        if(isset($looping_array['images']) && !empty($looping_array['images'])){
                                $item_send_array[$i]['images']  =json_decode($looping_array['images'],true);
                        }else{
                                $item_send_array[$i]['images']  = '';
                        }
                        if(isset($looping_array['color']) && !empty($looping_array['color'])){
                            $item_send_array[$i]['color']   =$looping_array['color'];
                        }else{
                                $item_send_array[$i]['color']   = '';
                        }
                        if(isset($looping_array['size']) && !empty($looping_array['size'])){
                                $item_send_array[$i]['size']    =$looping_array['size'];
                        }else{
                                $item_send_array[$i]['size']    = '';
                        }

                        $item_send_array[$i]['shipping']        = $looping_array['shipping'];
                        $item_send_array[$i]['transfer_price']  = $looping_array['transfer_price'];
                        $item_send_array[$i]['tax_class_id']    = $looping_array['tax_class_id'];
                        $item_send_array[$i]['points']  = $looping_array['points'];
                        if(isset($looping_array['date_available']) && !empty($looping_array['date_available'])){
                                $item_send_array[$i]['date_available']  =$looping_array['date_available'];
                        }else{
                                $item_send_array[$i]['date_available']  = '';
                        }
                        $item_send_array[$i]['weight']  = $looping_array['weight'];
                        $item_send_array[$i]['weight_class_id'] = $looping_array['weight_class_id'];
                        $item_send_array[$i]['length']  = $looping_array['length'];
                        $item_send_array[$i]['width']   = $looping_array['width'];
                        $item_send_array[$i]['height']  = $looping_array['height'];
                        $item_send_array[$i]['length_class_id'] = $looping_array['length_class_id'];
                        $item_send_array[$i]['shipment_mode']   = $looping_array['shipment_mode'];
                        $item_send_array[$i]['subtract']        = $looping_array['subtract'];
                        $item_send_array[$i]['minimum'] = $looping_array['minimum'];
                        $item_send_array[$i]['status']  = $looping_array['status'];
                        $item_send_array[$i]['hs_code'] = $looping_array['hs_code'];
                        $item_send_array[$i]['type']    = $looping_array['type'];
                        $item_send_array[$i]['viewed']  = $looping_array['viewed'];
                        $item_send_array[$i]['offers']  = $looping_array['offers'];
                        $item_send_array[$i]['delivery_charge'] = $looping_array['delivery_charge'];
                        $item_send_array[$i]['product_for_id']  = $looping_array['product_for_id'];
                        $item_send_array[$i]['product_usability_id']    = $looping_array['product_usability_id'];
                        $item_send_array[$i]['language_id']     = $looping_array['language_id'];
                        $item_send_array[$i]['description']     = $looping_array['description'];
                        @$item_send_array[$i]['tag']     = $looping_array['tag'];
                        $item_send_array[$i]['meta_title']      = $looping_array['meta_title'];
                        $item_send_array[$i]['meta_description']        = $looping_array['meta_description'];
                         $item_send_array[$i]['stock_status']    = $looping_array['stock_status'];
                        $item_send_array[$i]['selling_price']   = $looping_array['selling_price'];
                        $item_send_array[$i]['discount_price']  = $looping_array['discount_price'];
                        $item_send_array[$i]['mrp']     = $looping_array['mrp'];

                        if(isset($looping_array['weight_class']) && !empty($looping_array['weight_class'])){
                                $item_send_array[$i]['weight_class']    =$looping_array['weight_class'];
                        }else{
                                $item_send_array[$i]['weight_class']    = '';
                        }

                        if(isset($looping_array['length_class']) && !empty($looping_array['length_class'])){
                                $item_send_array[$i]['length_class']    =$looping_array['length_class'];
                        }else{
                                $item_send_array[$i]['length_class']    = '';
                        }
        
        $main_content['data'] = $item_send_array;
        
			
        
			
        }else{
                $main_content['count']  = 0;
                $main_content['items_count']    = 0;
                $main_content['status_text']            = 'No records found';
        }
    }else{
        $main_content['status_code']            = 400;
        $main_content['status_text']    = 'Error';
    }
        $header                                         = array();
        $header['version']                      = '0.1';
        $header['website']                      = 'https://trendybharat.com/';
        $header['company_name']         = 'Trendy Bharat';
        $header['created_time']         = date('Y-m-d H:i:s');
        $data_array['response']         = $response_array;
        $data_array['header']           = $header;
        $data_array['main_content']     = $main_content;
        return $data_array;
	  }

    function parseQuery($q=''){
            // parse query and return only relevent key from the search key
            // split the query on the basis of 'from' key and send only query before the 'from' key
            //return $q;
            $this->query = $q;
            $exceptFrom = preg_split('/ for /i', $q,  2);
            if(count($exceptFrom)>1){        // blue shirts for men
                $this->query = $exceptFrom[0];
                $this->qCatname = $exceptFrom[1];
                $exceptFrom = preg_split('/ from /i', $exceptFrom[1], 2);
                if(count($exceptFrom)>1){    // neakers for men from lime
                   $this->qCatname = $exceptFrom[0];
                   $this->qBrandname = $exceptFrom[1];
                }else{                       // briefs for men in offer zone
                    $exceptFrom = preg_split('/ in /i', $exceptFrom[0], 2);
                    if(count($exceptFrom)>1){
                       $this->qCatname = $exceptFrom[0];
                       //$this->qBrandname = $exceptFrom[1];
                    }
                }
            }else{
                $exceptFrom = preg_split('/ from /i', $exceptFrom[0], 2);
                if(count($exceptFrom)>1){    // mens shoes from Gcollection
                    $this->query = $exceptFrom[0];
                    $this->qBrandname = $exceptFrom[1];
                    //$exceptFrom = preg_split('/ in /i', $exceptFrom[0], 2);
                }else{                       // leggings in kids
                    $exceptFrom = preg_split('/ in /i', $exceptFrom[0], 2);
                    $this->query = $exceptFrom[0];
                    if(count($exceptFrom)>1){
                       $this->qCatname = $exceptFrom[1];
                       //$this->qBrandname = $exceptFrom[1];
                    }
                }
            }
            echo '---Q---'.$this->query. '---c--'.$this->qCatname.'----b--'.$this->qBrandname;
            return $this->query;

        }


}
?>
