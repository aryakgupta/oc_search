<?php
set_time_limit(0);
error_reporting(0);
include('solrConfig.php');
$check_auth=md5('BharttrendingProductIndexDelete');
if(isset($_GET['token']) && $check_auth==$_GET['token']){
	
	if(isset($_GET['productId']) && $_GET['productId']>0){	  
		$obj=new BharatProductIndexDelete();
		$params=array();
		$params['product_id']=intval($_GET['productId']); 
		$params['type']=intval($_GET['type']); 
		$params['token']=md5('BharttrendingSearchIndexDelete');		
		$obj->deleteProductById($params);		
	}else{
		echo "eeOPPS!!!";
	}
}	
		
	
	


?>