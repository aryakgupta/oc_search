<?php 
class BharatAutoSearch{
	private $solr;
	private $fq;
	private $sortfield='';
	 public function __construct() {

        require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');
        //require_once(dirname(__FILE__) . '/../solrConfig.php');
        $this->solr= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTAUTOSEARCH);
    }

    public function __desctruct() {
        $this->unsetsolr();
    }

    private function stripallslashes($string = "") {

        while (strchr($string, '\\')) {
            $string = stripslashes(trim($string));
        }
        $string = str_replace(":", "\:", $string);
        $string = str_replace("^", "\^", $string);
        $string = str_replace("*", "\*", $string);
        $string = str_replace("*:*", "\*\:\*", $string);
        $string = str_replace("*:", "\:", $string);
        $string = str_replace('()', '\(\)', $string);
        $string = str_replace(')', '\)', $string);
        $string = str_replace('(', '\(', $string);
        $string = str_replace("!", "\!", $string);
        $string=$this->solr->escape($string);
        $string = urldecode($string);
        return strip_tags($string);
    }


public function getSuggestionResult($searchParam=array())
	{
		
	try {
			
		if($this->solr->ping())
			{
				
				$data_type='';
				if(isset($searchParam["data_type"])){
					$data_type = strip_tags($searchParam["data_type"]);	
				}
				else{
					$data_type = "Product";	
				}					

				if (isset($searchParam["q"])){
					$query = strip_tags($searchParam["q"]);	
				}
				else{
					$query = "A";	
				}		
			$query=$this->stripallslashes($query);
				
switch ($data_type){

case "Product":	
		$arr = explode (" ", $query);
		$tmpQuery = "";
		foreach ($arr as $key=>$val){
			$tmpQuery .= "name2:(\"". $val . "\") AND ";	

		}
		$tmpQuery = preg_replace ('/( AND )$/', "", $tmpQuery);
		$tmpQuery = "(" . $tmpQuery . ")";		
		
		$query1 = "(name1:(\"" . $query . "\") OR ".$tmpQuery ;
		$query1 .= " )";
	
		
		
		$query=' (' . $query1.'  AND  (data_type:"Product" OR data_type:"Brand"^999.9 OR data_type:"Model"^77.1 OR data_type:"leaf_category"^3.9 OR data_type:"sub_category"^5.9 OR data_type:"root_category"^46.9))';
     	break;
		
		default:
		$arr = explode (" ", $query);
		$tmpQuery = "";
		foreach ($arr as $key=>$val){
			$tmpQuery .= "name2:(\"". $val . "\") AND ";	

		}
		$tmpQuery = preg_replace ('/( AND )$/', "", $tmpQuery);
		$tmpQuery = "(" . $tmpQuery . ")";		
		$query1 = "(name1:(\"" . $query . "\") OR ".$tmpQuery ;
		$query1 .= " )";
		
		
		$query=' (' . $query1.'  AND  (data_type:"Product" OR data_type:"Brand" OR data_type:"Model"))';
		
}

	$fl='*';
	$options =array('facet' => 'true', 'facet.field' =>"data_type", 'facet.limit' => '20', 'facet.mincount' => '1', 'facet.sort' => 'true', 'fl' => '*,score'); 


	if(isset($searchParam["start"]) && $searchParam["start"]!=""){
			$start=$searchParam["start"];}
		else{$start=0;}
		if(isset($searchParam["limit"]) && $searchParam["limit"]!=""){
			$limit=$searchParam["limit"];}
		else{$limit=10;}
		if($limit>10){
			$limit=10;
		}
		
		$result = $this->solr->search($query, $start, $limit,$options);	
		$found = $result->response->numFound;
		$countheadline=array();
		$countheadline['status_code']=$result->getHttpStatus();
			
			if($result->getHttpStatusMessage()=="OK"){
				$countheadline['status_text']="Success";
			}else{
				$countheadline['status_text']=$result->getHttpStatusMessage();
			}
				
		$countheadline['count']=$found;
		$results_arr=array();
		if($found > 0){
			$results_arr = array();
				
			foreach ($result->response->docs as $doc){
				$doc->_fields = $doc->getFieldNames();
				$fcount =  count($doc->_fields);
				$jsonarray=array();
				for($ctr=0; $ctr < $fcount; $ctr++){
					if(is_array($doc->__get($doc->_fields[$ctr]))){
						$jsonarray[$doc->_fields[$ctr]]=strip_tags(implode(', ', $doc->__get($doc->_fields[$ctr])));
					}if ($doc->_fields[$ctr] == "id")
						{
							$ests = explode ("#", $doc->__get($doc->_fields[$ctr]));
							
							$jsonarray[$doc->_fields[$ctr]]=strip_tags($ests[1]);
						}
						else
						{
							
							$jsonarray[$doc->_fields[$ctr]]=strip_tags($doc->__get($doc->_fields[$ctr]));
						}
				}
				$results_arr['data'][] = $jsonarray;
			}
			$mergedata=array_merge($countheadline,$results_arr);
			return json_encode($mergedata);
	
		}else{

			return json_encode($countheadline);
			}
		}
	}
		catch (Exception $e) {
    		$this->countheadline['status_code']="Caught exception";
			$this->countheadline['status_text']=$e->getMessage();
			return json_encode($this->countheadline);
		}	
			
	} 

	public function  unsetsolr(){
			unset($this->solr);
	}
	
}

?>
