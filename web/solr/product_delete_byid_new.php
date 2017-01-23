<?php
set_time_limit(0);
error_reporting(0);
include('solrConfig.php');
$check_auth=md5('BharttrendingProductIndexDelete');
//if(isset($_GET['token']) && $check_auth==$_GET['token']){
if($check_auth=='5a49d2390186559a904f563879998db3'){
	
	if(isset($_GET['productId']) && !empty($_GET['productId'])){	  
		$obj=new BharatProductIndexDelete();
		$params=array();
		$params['product_id']=strip_tags($_GET['productId']); 
		$params['type']=intval($_GET['type']); 
		$params['token']=md5('BharttrendingSearchIndexDelete');		
		$obj->deleteProductById($params);		
	}else{
		echo "eeOPPS!!!";
	}
}	
		
	
	


?>