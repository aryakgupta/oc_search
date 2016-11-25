<?php
error_reporting(0);
include(dirname(__FILE__) . '/classes/BharatSearch.class.php');
$obj = new BharatSearch();
$params = array();
$error=0;
$response_array						 =  array();
$response_array['status_code']				 =  '200';
$response_array['status_text']			 =  'OK';

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $params['category_id'] = intval($_GET['category_id']);
}

if (isset($_GET['root_cat_id']) && !empty($_GET['root_cat_id'])) {
    $params['root_cat_id'] = intval($_GET['root_cat_id']);
}
if (isset($_GET['language_id']) && !empty($_GET['language_id'])) {
    $params['language_id'] = intval($_GET['language_id']);
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $params['type'] = $_GET['type'];
}

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $params['q'] = urlencode(strip_tags($_GET['q']));
}

if (isset($_GET['sku']) && !empty($_GET['sku'])) {
    $params['sku'] = $_GET['sku'];
}
if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
    $params['price_range'] = urlencode($_GET['price_range']);
}

if (isset($_GET['name']) && !empty($_GET['name'])) {
    $params['product_name'] = urlencode(strip_tags($_GET['name']));
}

if (isset($_GET['color']) && !empty($_GET['color'])) {
    $params['color'] = urlencode(strip_tags($_GET['color']));
}


if (isset($_GET['offer']) && !empty($_GET['offer'])) {
    $params['offer'] = urlencode(strip_tags($_GET['offer']));
}


if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $params['brand'] = urlencode(strip_tags($_GET['brand']));
}


if (isset($_GET['model']) && !empty($_GET['model'])) {
    $params['model'] = urlencode(strip_tags($_GET['model']));
}


if (isset($_GET['category']) && !empty($_GET['category'])) {
    $params['category'] = urlencode(strip_tags($_GET['category']));
}

if (isset($_GET['subcat']) && !empty($_GET['subcat'])) {
    $params['subcat'] = urlencode(strip_tags($_GET['subcat']));
}

if (isset($_GET['leafcat']) && !empty($_GET['leafcat'])) {
    $params['leafcat'] = urlencode(strip_tags($_GET['leafcat']));
}

if (isset($_GET['price']) && !empty($_GET['price'])) {
    $params['price'] = urlencode(strip_tags($_GET['price']));
}

if (isset($_GET['only_facet']) && !empty($_GET['only_facet'])) {
    $params['is_only_facet'] = 'yes';
}

if (isset($_GET['is_with_facet']) && !empty($_GET['is_with_facet'])) {
    $params['is_with_facet'] = 'yes';
}
$params['is_with_facet'] = 'yes';

if (isset($_GET['start']) && !empty($_GET['start'])) {
    $params['start'] = $_GET['start'];
}
if (isset($_GET['limit']) && !empty($_GET['limit'])) {
    $params['limit'] = $_GET['limit'];
}
if (isset($_GET['price_stats']) && !empty($_GET['price_stats'])) {
    $params['price_stats'] = 'yes';
}

if (isset($_GET['sort']) && !empty($_GET['sort'])) {
    $params['sort'] = urlencode($_GET['sort']);
}
if (isset($_GET['orderby']) && !empty($_GET['orderby'])) {
    $params['orderby'] = urlencode($_GET['orderby']);
}

if($error==0){
	$produt_data = $obj->getResults($params);
	$result= (array)json_decode($produt_data);
	$master_data				= $result['data'];
	$master_data_limit_count	= count($master_data);
	$master_data_count		    = $result['count'];
	
	if($master_data_count>0){			
		$item_send_array = array();
		$main_content['count'] 	= $master_data_count;
		$main_content['items_count'] 	= $master_data_limit_count;			
		for($i=0;$i<$master_data_limit_count;$i++){
			$looping_array								= (array)$master_data[$i];			
			$item_send_array[$i]['product_id'] 			= $looping_array['product_id'];
			$item_send_array[$i]['name']			= $looping_array['product_name'];
			$item_send_array[$i]['sku']	= $looping_array['sku'];
            $item_send_array[$i]['model']	= $looping_array['model'];  
			$item_send_array[$i]['brand_id']	= $looping_array['brand_id'];  
			$item_send_array[$i]['upc']	= $looping_array['upc'];  
			$item_send_array[$i]['ean']	= $looping_array['ean']; 
			$item_send_array[$i]['business_model']	= $looping_array['business_model']; 
			$item_send_array[$i]['return_ploicy']	= $looping_array['return_ploicy']; 
			$item_send_array[$i]['location']	= $looping_array['location']; 
			$item_send_array[$i]['quantity']	= $looping_array['quantity']; 
			$item_send_array[$i]['vendor_id']	= $looping_array['vendor_id']; 
			$item_send_array[$i]['brandname']	= $looping_array['brandname']; 
			$item_send_array[$i]['category']	= json_decode($looping_array['category'],true); 			
                        if(isset($looping_array['option_list']) && !empty($looping_array['option_list'])){
                        $item_send_array[$i]['option']  = json_decode($looping_array['option_list'],true);
                        }else{
                                $item_send_array[$i]['option']  =array();
                        }
                        if(isset($looping_array['filter_list']) && !empty($looping_array['filter_list'])){
                        $item_send_array[$i]['filter']  = json_decode($looping_array['filter_list'],true);
                        }else{
                                $item_send_array[$i]['filter']=array();
                        }
                        if(isset($looping_array['attribute_list']) && !empty($looping_array['attribute_list'])){
                        $item_send_array[$i]['attribute']       = json_decode($looping_array['attribute_list'],true);
                }else{
                        $item_send_array[$i]['attribute']       = array();
                }
			
			if(isset($looping_array['image']) && !empty($looping_array['image'])){
				$item_send_array[$i]['image'] 	= $looping_array['image'];
			}else{
				$item_send_array[$i]['image'] 	= '';
			}
			if(isset($looping_array['images']) && !empty($looping_array['images'])){
				$item_send_array[$i]['images'] 	=json_decode($looping_array['images'],true);
			}else{
				$item_send_array[$i]['images'] 	= '';
			}
			if(isset($looping_array['color']) && !empty($looping_array['color'])){
				$item_send_array[$i]['color'] 	=$looping_array['color'];
			}else{
				$item_send_array[$i]['color'] 	= '';
			}
			if(isset($looping_array['size']) && !empty($looping_array['size'])){
				$item_send_array[$i]['size'] 	=$looping_array['size'];
			}else{
				$item_send_array[$i]['size'] 	= '';
			}
			
			$item_send_array[$i]['shipping']	= $looping_array['shipping']; 
			$item_send_array[$i]['transfer_price']	= $looping_array['transfer_price']; 
			$item_send_array[$i]['tax_class_id']	= $looping_array['tax_class_id']; 
			$item_send_array[$i]['points']	= $looping_array['points']; 
			if(isset($looping_array['date_available']) && !empty($looping_array['date_available'])){
				$item_send_array[$i]['date_available'] 	=$looping_array['date_available'];
			}else{
				$item_send_array[$i]['date_available'] 	= '';
			}
			$item_send_array[$i]['weight']	= $looping_array['weight']; 
			$item_send_array[$i]['weight_class_id']	= $looping_array['weight_class_id']; 			
			$item_send_array[$i]['length']	= $looping_array['length']; 
			$item_send_array[$i]['width']	= $looping_array['width']; 
			$item_send_array[$i]['height']	= $looping_array['height']; 
			$item_send_array[$i]['length_class_id']	= $looping_array['length_class_id']; 
			$item_send_array[$i]['shipment_mode']	= $looping_array['shipment_mode']; 
			$item_send_array[$i]['subtract']	= $looping_array['subtract']; 
			$item_send_array[$i]['minimum']	= $looping_array['minimum']; 
			$item_send_array[$i]['status']	= $looping_array['status']; 
			$item_send_array[$i]['hs_code']	= $looping_array['hs_code']; 
			$item_send_array[$i]['type']	= $looping_array['type']; 
			$item_send_array[$i]['viewed']	= $looping_array['viewed']; 
			$item_send_array[$i]['offers']	= $looping_array['offers']; 
			$item_send_array[$i]['delivery_charge']	= $looping_array['delivery_charge']; 
			$item_send_array[$i]['product_for_id']	= $looping_array['product_for_id']; 
			$item_send_array[$i]['product_usability_id']	= $looping_array['product_usability_id']; 
			$item_send_array[$i]['language_id']	= $looping_array['language_id']; 
			$item_send_array[$i]['description']	= $looping_array['description']; 
			$item_send_array[$i]['tag']	= $looping_array['tag']; 
			$item_send_array[$i]['meta_title']	= $looping_array['meta_title']; 
			$item_send_array[$i]['meta_description']	= $looping_array['meta_description']; 
			$item_send_array[$i]['stock_status']	= $looping_array['stock_status']; 
			$item_send_array[$i]['selling_price']	= $looping_array['selling_price']; 
			$item_send_array[$i]['discount_price']	= $looping_array['discount_price']; 
			$item_send_array[$i]['mrp']	= $looping_array['mrp']; 
			
			if(isset($looping_array['weight_class']) && !empty($looping_array['weight_class'])){
				$item_send_array[$i]['weight_class'] 	=$looping_array['weight_class'];
			}else{
				$item_send_array[$i]['weight_class'] 	= '';
			}
			
			if(isset($looping_array['length_class']) && !empty($looping_array['length_class'])){
				$item_send_array[$i]['length_class'] 	=$looping_array['length_class'];
			}else{
				$item_send_array[$i]['length_class'] 	= '';
			}
			
			
			
	}
	$main_content['data'] = $item_send_array;
	$facet_filter=array();
	foreach($result['facet_data']->root_cat_name_ft as $facetname => $valcount){	
		if($valcount>0){
			$facetlist=array();
			$facetlist['val']=$facetname;
			$facetlist['count']=$valcount;
			$facet_filter['root_cat_name'][]=$facetlist;
			unset($facetlist);
		}
	}
	foreach($result['facet_data']->sub_cat_name_ft as $facetname => $valcount){	
		if($valcount>0){
			$facetlist=array();
			$facetlist['val']=$facetname;
			$facetlist['count']=$valcount;
			$facet_filter['sub_cat_name'][]=$facetlist;
			unset($facetlist);
			
		}
	}
	foreach($result['facet_data']->leaf_cat_name_ft as $facetname => $valcount){	
		if($valcount>0){
			
			$facetlist=array();
			$facetlist['val']=$facetname;
			$facetlist['count']=$valcount;
			$facet_filter['leaf_cat_name'][]=$facetlist;
			unset($facetlist);
		}
	}
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
	$main_content['facet_data'] = $facet_filter;
	
	}else{
		$main_content['count'] 	= 0;
		$main_content['items_count'] 	= 0;
		$main_content['status_text'] 		= 'No records found';
	} 
}else{
	$main_content['status_code'] 		= 400;
	$main_content['status_text'] 	= 'Error';
}
	$header						= array();
	$header['version']			= '0.1';
	$header['website']			= 'https://trendybharat.com/';
	$header['company_name']		= 'Trendy Bharat';
	$header['created_time']		= date('Y-m-d H:i:s');			
	$data_array['response']		= $response_array;
	$data_array['header']		= $header;
	$data_array['main_content']	= $main_content;
	echo json_encode($data_array);
?>
