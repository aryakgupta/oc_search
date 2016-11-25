<?php
set_time_limit(0);

include(dirname(__FILE__)."/classes/TBharatSolrAutoImport.class.php");
include(dirname(__FILE__) . '/classes/BharatSearch.class.php');
$obj = new BharatSearch();
$params = array();
$error=0;

$params['start'] =0;
$params['limit']=1000;

if($error==0){
	$produt_data = $obj->getResults($params);

	$result= (array)json_decode($produt_data);
	
	$master_data				= $result['data'];
	$master_data_limit_count	= count($master_data);
	$master_data_count		    = $result['count'];
	
	if($master_data_count>0){			
		$item_send_array = array();
			
		for($i=0;$i<$master_data_limit_count;$i++){
			$looping_array								= (array)$master_data[$i];			
			$item_send_array[$i]['product_id'] 			= $looping_array['product_id'];
			$item_send_array[$i]['name']			= $looping_array['product_name'];
			$item_send_array[$i]['sku']	= $looping_array['sku'];
            $item_send_array[$i]['model']	= $looping_array['model']; 
			$item_send_array[$i]['modile_id']	= md5($looping_array['model']); 
			
			$item_send_array[$i]['brand_id']	= $looping_array['brand_id']; 			
			$item_send_array[$i]['root_cat_name']	= $looping_array['root_cat_name'];  
			$item_send_array[$i]['sub_cat_name']	= $looping_array['sub_cat_name'];  
			$item_send_array[$i]['leaf_cat_name']	= $looping_array['leaf_cat_name']; 
			$item_send_array[$i]['root_cat_id']	= $looping_array['root_cat_id']; 
			$item_send_array[$i]['sub_cat_id']	= $looping_array['sub_cat_id']; 
			$item_send_array[$i]['leaf_cat_id']	= $looping_array['leaf_cat_id']; 			
			
			$item_send_array[$i]['brandname']	= $looping_array['brandname']; 
			
			
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
			

			$item_send_array[$i]['type']	= $looping_array['type']; 

			$item_send_array[$i]['mrp']	= $looping_array['mrp']; 
			
	
			
			
			
	}
	
$objauto=new TBharatSolrAutoImport();

	
	if (is_array($item_send_array) || is_object($item_send_array))
	{
	
		$data=$objauto->index_auto_produt($item_send_array);
		$data2=$objauto->index_auto_brand($item_send_array);
		$data3=$objauto->index_auto_module($item_send_array);
		
		
		
		print_r($data);
		print_r($data2);
		print_r($data3);
	}

	

	
	}
	echo "</pre>";
	
	
}


?>