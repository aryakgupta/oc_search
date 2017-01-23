<?php
set_time_limit(0);
include('solrConfig.php');
$obj=new BharatTrendingDataImport();
	$startpage=1;
	$limit=1;
	if(isset($_GET['start']) && $_GET['start']>0){	  
		$startpage=intval($_GET['start']);    
	}
	if(isset($_GET['limit']) && $_GET['limit']>0){	  
		$limit=intval($_GET['limit']);    
	}
	

function getCulrProcess($cUrl){
	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL, $cUrl);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	  $content = curl_exec($ch);
	  $status = curl_getinfo($ch);
	  curl_close($ch);
	  return $content;
}

	$itemdata=array();
	if(isset($_GET['index_type']) && $_GET['index_type']==1){
		$itemdata=getCulrProcess('http://35.154.14.59/top10_trendy_search-export.php');
	}else{
		$itemdata=getCulrProcess('http://35.154.14.59/top10_trendy_search-domestic.php');
		
	}
	
	$itemdata=json_decode($itemdata,true);
	if (is_array($itemdata) || is_object($itemdata))
	{
		
		$data=$obj->indexTrendingKeyWord($itemdata);
		echo "<pre>";
		print_r(json_decode($data));
		echo "</pre>";	
	}

	
	
	
	


?>