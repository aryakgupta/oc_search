<?php
	require_once('../config.php'); 
   @mysql_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD);
   @mysql_select_db(DB_DATABASE);
   $start=0;
   $end=10;
	if(isset($argv['1']) && !empty( $argv['1'])){	  
		$start=intval($argv['1']);     
	}
	if(isset($argv['2']) && !empty( $argv['2'])){	  
		$end=intval($argv['2']);  
	}	
		
		$query="Select * from oc_trendy_search where keyword!='' ORDER BY `sorts` DESC limit $start,$end";
$result=mysql_query($query);
		$newdata=array();
		$final_response=array();
		
		while($rs=mysql_fetch_array($result)){
		
          $itemdata[] = array(
               'keyword' => $rs['keyword'],
              'popularity' => $rs['sorts'],
              'type' => $rs['type']
          );

		}
		
include(dirname(__FILE__)."/classes/TBharatSolrAutoImport.class.php");
$objauto=new TBharatSolrAutoImport();
	if (is_array($itemdata) || is_object($itemdata))
	{
		$data=$objauto->indexSearchKeyWord($itemdata);
		print_r($data);
	}


?>		
