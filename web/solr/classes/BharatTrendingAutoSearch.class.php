<?php 
class BharatTrendingAutoSearch
{
	private $solr;
	private $query;
	private $start;
	private $limit;
	private $options;
	
	public function __construct()
	{
		require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');		
        $this->solr= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, TRENDINGSEARCH);
	
			
	}

	public function __desctruct(){
		unsetsolr();
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
        $string = urldecode($string);
        return strip_tags($string);
    }


	
	public function getTrendingAutoSearch($params=array()){
		
	
	try{			
		if($this->solr->ping()){
			
				$this->query = "A";	
				if (isset($params["q"])){
					$this->query = strip_tags($params["q"]);	
				}
				
				$this->query=$this->stripallslashes($this->query);				
		
		$wheretype=' AND type:0';
		if (isset($params['type']) && !empty($params['type'])) {
			$wheretype=' AND type:'.$params['type'];
		}
		if(isset($params["q"]) && !empty($params["q"])){
				$arr = explode (" ", $this->query);
				$tmpQuery = "";
				foreach ($arr as $key=>$val){
					$tmpQuery .= "name2:(\"". $val . "\") AND ";	

				}
				$tmpQuery = preg_replace ('/( AND )$/', "", $tmpQuery);
				$tmpQuery = "(" . $tmpQuery . ")";		

				$query1 = "(name1:(\"" . $this->query . "\") OR ".$tmpQuery ;
				$query1 .= " )";	
				$this->query=$query1.$wheretype;	
		
		}else{				
				$this->query="(*:*)".$wheretype;;	
			}

			$this->options =array('fl' => 'name1,popularity','sort'=>'popularity desc'); 
			if(isset($params["start"]) && $params["start"]!=""){
				$this->start=$params["start"];}
			else{$this->start=0;}
			if(isset($params["limit"]) && $params["limit"]!=""){
				$this->limit=$params["limit"];}
			else{$this->limit=10;}
			
		
			$result = $this->solr->search($this->query, $this->start, $this->limit,$this->options);
			$result_str = "";
			$start = 0;
			$found = $result->response->numFound;
			$countheadline=array();
			$countheadline['status_code']=$result->getHttpStatus();
			if($result->getHttpStatusMessage()=="OK"){
				$countheadline['status_text']="Success";
			}else{
				$countheadline['status_text']=$result->getHttpStatusMessage();
			}
			$countheadline['count']=$found;
			if($found > 0){
				$results_arr = array();
				$flag = 0;
				$index = 0;

				foreach ($result->response->docs as $doc){
					$doc->_fields = $doc->getFieldNames();
					$fcount =  count($doc->_fields);
					$jsonarray=array();
					
					for($ctr=0; $ctr < $fcount; $ctr++){
						if(is_array($doc->__get($doc->_fields[$ctr]))){						
							$jsonarray[$doc->_fields[$ctr]]=strip_tags(implode(', ', $doc->__get($doc->_fields[$ctr])));
						}					
						else{
							
								$jsonarray[$doc->_fields[$ctr]]=$doc->__get($doc->_fields[$ctr]);
							
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
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
		}	
		
	} 

	public function  unsetsolr(){
			unset($this->solr);
	}
	
}

?>	
