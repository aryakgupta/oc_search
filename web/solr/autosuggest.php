<?php
error_reporting(0);
 require_once(dirname(__FILE__) . '/solrConfig.php');
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
		echo "======";
		$autoserachData=$obj->getSuggestionResult($searchParam);
	
		$result= (array)json_decode($autoserachData,true);
		$master_data				= $result['data'];
		$master_data_limit_count	= count($master_data);
		$master_data_count		    = $result['count'];
	
	if($master_data_count>0){
		
		foreach($master_data as $resultcatVal){
			if($resultcatVal['data_type']=='searchlogs'){
				$searchParam['q']=$resultcatVal['name1'];
				break;
			}
		}
	$objcat = new BharatSearchCat();
	echo "======";
	print_r($searchParam);
	$produt_data = $objcat->getCatautosuggest($searchParam);
	$resultcat= (array)json_decode($produt_data);
	$item_send_catarray = array();
	if(count($resultcat)>0){
		$r=0;
		$serachwords=urldecode($searchParam['q']);
		$getflag=0;
		foreach($master_data as $resultcatVal){
			if($resultcatVal['data_type']=='searchlogs'){
				$serachwords=$resultcatVal['name1'];
				$getflag=1;				
				break;
			}
		}
		if($getflag==0){
			$serachwords=$master_data[0]['name1'];
		}
		foreach($resultcat as $resultcatVal){
			$item_send_catarray[$r]['id'] 			= md5($resultcatVal);
			
			$item_send_catarray[$r]['name']			= $serachwords." For ".$resultcatVal;
			$item_send_catarray[$r]['data_type']			='category';
			$r++;
		}
	}
		
		
		$item_send_array = array();
		$main_content['count'] 	= $master_data_count;
		$main_content['suggest_count'] 	= $master_data_limit_count;			
		for($i=0;$i<$master_data_limit_count;$i++){
			$looping_array								= (array)$master_data[$i];			
			$item_send_array[$i]['id'] 			= $looping_array['id'];
			$item_send_array[$i]['name']			= $looping_array['name1'];
			$item_send_array[$i]['data_type']			= $looping_array['data_type'];
		}
		$merge_response=array_merge($item_send_catarray,$item_send_array);
		$main_content['data'] = $merge_response;
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
	echo "<pre>";
	print_r($data_array);
	//echo json_encode($data_array);
?>
