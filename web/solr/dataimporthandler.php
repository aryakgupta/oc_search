<?php
set_time_limit(0);
include(dirname(__FILE__)."/classes/BharatSolrDataImport.class.php");
$obj=new BharatSolrDataImport();
$check_auth=md5('BharatDataImport');
	$whhereclasue='';
	if(isset($_GET['productId']) && $_GET['productId']>0){	  
		$whhereclasue=intval($_GET['productId']);    
	}	
	$startpage=0;
	$limit=1000;
	if(isset($_GET['page']) && $_GET['page']>0){	  
		$startpage=intval($_GET['page']);    
	}
	if(isset($_GET['record']) && $_GET['record']>0){	  
		$limit=intval($_GET['record']);    
	}
	$store_id=1;
	if(isset($_GET['store_id']) && $_GET['store_id']>0){	  
		$store_id=intval($_GET['store_id']);    
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

	$itemdata=array();
	$itemdata=getCulrProcess('http://52.66.27.14/cat1.php?page='.$startpage.'&record='.$limit);
	$itemdata=json_decode($itemdata,true);
	
	echo "<pre>";
	
	if (is_array($itemdata) || is_object($itemdata))
	{
		$data=$obj->indexIntoSolr($itemdata);
		print_r($data);
	}
	echo "</pre>";
	
	
	


?>