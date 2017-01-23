<?php
set_time_limit(0);
error_reporting(0);
include('solrConfig.php');
$check_auth=md5('BharttrendingProductIndexDelete');



function GetProductDeleteBySolr($parg) {
	
if(isset($parg['token']) && $check_auth==$parg['token']){
	$obj=new BharatProductIndexDelete();
	if(isset($parg['productId']) && !empty($parg['productId'])){	  
		
		$params=array();
		$params['product_id']=strip_tags($parg['productId']); 
		$params['type']=intval($parg['type']); 
		$params['token']=md5('BharttrendingSearchIndexDelete');		
		$obj->deleteProductById($params);		
	}else if(isset($parg['data_type']) && $parg['data_type']=='searchlogs' && !empty($parg['keyword'])){
	
		$params=array();
		$params['keyword']=strip_tags($parg['keyword']);
		$params['data_type']="searchlogs";
		$params['token']=md5('BharttrendingSearchIndexDelete');			
		$obj->deleteKeywordbasedAutosearchlogs($params);
	}
	else{
		echo "eeOPPS!!!";
	}
}	
}
		
	
	


?>