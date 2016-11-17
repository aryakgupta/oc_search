<?php
error_reporting(0);
require_once(dirname(__FILE__) . '/solrConfig.php');
require_once(dirname(__FILE__) . '/classes/BharatAutoSearch.class.php');
$obj = new BharatAutoSearch();
$searchParam=array();
$autoserachData='';
$response_array						 =  array();
$response_array['status_code']				 =  '200';
$response_array['status_text']			 =  'OK';
$error=1;
		if(isset($_GET['q']) && !empty($_GET['q'])){
			$letters = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($_GET['q'])); // remove all white space
			$letters=urlencode(trim($letters));
			$searchParam['q']=$letters;
			$error=0;
		}else{
			$error=1;
			$response_array['status_code']				 =  '400';
			$response_array['status_text']			 =  'Auto Search Query is Missing';
		}
		if(isset($_GET['start'])){
			$searchParam['start']=strip_tags(trim($_GET['start']));
		}
		if(isset($_GET['limit'])){
			$searchParam['limit']=strip_tags(trim($_GET['limit']));
		}
		if(isset($_GET['data_type'])){
			$searchParam['data_type']=strip_tags(trim($_GET['data_type']));
			$error=0;
		}else{
			$error=1;
			$response_array['status_code']				 =  '400';
			$response_array['status_text']			 =  'Data Type is missing';
		}
		
if($error==0)
{
		
		$autoserachData=$obj->getSuggestionResult($searchParam);
		$result= (array)json_decode($autoserachData,true);
		$master_data				= $result['data'];
		$master_data_limit_count	= count($master_data);
		$master_data_count		    = $result['count'];
	
	if($master_data_count>0){			
		$item_send_array = array();
		$main_content['count'] 	= $master_data_count;
		$main_content['suggest_count'] 	= $master_data_limit_count;			
		for($i=0;$i<$master_data_limit_count;$i++){
			$looping_array								= (array)$master_data[$i];			
			$item_send_array[$i]['id'] 			= $looping_array['id'];
			$item_send_array[$i]['name']			= $looping_array['name1'];
			$item_send_array[$i]['data_type']			= $looping_array['data_type'];
		}
		$main_content['data'] = $item_send_array;
	}else{
		$main_content['count'] 	= 0;
		$main_content['suggest_count'] 	= 0;
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
