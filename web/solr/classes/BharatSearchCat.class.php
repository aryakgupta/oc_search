<?php
class BharatSearchCat {

    private $solr;
    private $fq;
    private $sorting_field;
    private $is_facet;
    private $sorting_order;
    private $params = array();
    private $limit;
    private $start;
    private $result;
    private $results_arr = array();
    private $countheadline = array();
    private $facetdatafield = array();
    private $statsdata = array();
    private $merge_search_data = array();
    private $found;
    private $groupresult;
    private $fqitems;
	private $spellparams=array();
	private $categorylist=array('root_cat_name_ft','sub_cat_name_ft','leaf_cat_name_ft');
	private $stripcategorylist=array('mens','women','men','womens');
	
    public function __construct() {

        require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');
        require_once(dirname(__FILE__) . '/../solrConfig.php');

        $this->solr = new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTSEARCH);
		
    }

    public function __desctruct() {
        $this->unsetsolr();
    }
private function spell_checkr($spellq){
				$this->spellparams['spellcheck']='true';
				$this->spellparams['spellcheck.collate']='true';
				$this->spellparams['spellcheck.build']='true';
				
				$splelresult=array();
				$splelresult= $this->solr->spellsearch(htmlspecialchars_decode(urldecode($spellq)), 0, 3, $this->spellparams);
				
				$spellcount=0;
				if(isset($splelresult->spellcheck->suggestions[1])){
					$spellcount=$splelresult->spellcheck->suggestions[1]->numFound;
				}
				$spellsearchname='';
				if($spellcount>0){
					
					$spellsearchname=$splelresult->spellcheck->suggestions[1]->suggestion[0]->word;
					if($splelresult->spellcheck->suggestions[3]->suggestion[0]->word!=''){
						$spellsearchname.=" ".$splelresult->spellcheck->suggestions[3]->suggestion[0]->word;
					}
					if($splelresult->spellcheck->suggestions[5]->suggestion[0]->word!=''){
						$spellsearchname.=" ".$splelresult->spellcheck->suggestions[5]->suggestion[0]->word;
					}
									
				}
				return $spellsearchname;
	
}
private function spell_correction($string){
	/*if($string=="men"){
		$string = str_replace("men", "Mens", $string);
	}
	if($string=="boy"){
		$string = str_replace("boy", "Mens", $string);
	}
	if($string=="womens"){
		  $string = str_replace("womens", "Women", $string);
	}
	if($string=="girls"){
		 $string = str_replace("girls", "Women", $string);
	}
	if($string=="girl"){
		$string = str_replace("girl", "Women", $string);
	}
		
		if($string=="watch"){
			$string = str_replace("watch", "Watches", $string);     
		}*/
		$string=strtolower($string);
		
		$string = preg_replace("/\bmen\b/", "Mens", $string);
		$string = preg_replace("/\bboy\b/", "Mens", $string);
		$string = preg_replace("/\bboys\b/", "Mens", $string);
        $string = preg_replace("/\bwomens\b/", "Women", $string);
        $string = preg_replace("/\bgirls\b/", "Women", $string);
		$string = preg_replace("/\bgirl\b/", "Women", $string);
		$string = preg_replace("/\bwatch\b/", "Watches", $string);
		$string = preg_replace("/\bWatchess\b/", "Watches", $string);
		$string = preg_replace("/\bwatchess\b/", "Watches", $string);
		$string = preg_replace("/\bWatcheses\b/", "Watches", $string);
		$string = preg_replace("/\bwatcheses\b/", "Watches", $string);
		$string = preg_replace("/\bMenss\b/", "Mens", $string);
		$string = preg_replace("/\bmenss\b/", "Mens", $string);
		
        return $string;
	
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
		$string = preg_replace("/\bfor\b/", "", $string);
		$string = preg_replace("/\bFor\b/", "", $string);
		$string = preg_replace("/\bin\b/", "", $string);
        $string=$this->solr->escape($string);
        $string = urldecode($string);
        return strip_tags($string);
    }
private function querySegmentation($queryString =  null){ 
 
	//$queryString = " tops for girls";
	$params=array();
	$params['is_only_facet'] = 'yes';
	$params['call_querySegmentation'] = 'yes';
	$produt_data = $this->getCatautosuggest($params);
	$result= (array)json_decode($produt_data);
	
	
	$fcat_rootcat=array();
	$fcat_subcat=array();
	$fcat_leafcat=array();
	$fcat_brand=array();
	$fcat_model=array();	   
foreach($result['facet_data']->root_cat_name_ft as $facetname => $valcount){	
		
		array_push($fcat_rootcat, trim(strtolower($facetname)));		
}
foreach($result['facet_data']->sub_cat_name_ft as $facetname => $valcount){	
			
		array_push($fcat_subcat, trim(strtolower($facetname)));		
}
foreach($result['facet_data']->leaf_cat_name_ft as $facetname => $valcount){	
		if(!in_array(strtolower($facetname),$this->stripcategorylist)){							
			array_push($fcat_leafcat, trim(strtolower($facetname)));	
		}
		//array_push($fcat_leafcat, trim(strtolower($facetname)));		
}

		$queryString=$this->spell_correction($queryString);
    	$strArr = explode(" ", trim(urldecode($queryString)));
		
        $strArr	= array_map('strtolower', $strArr);
		
	$j = 0;
	foreach($strArr as $key=>$value){
		
		$spelwords=$this->spell_checkr($value);
		$count =  count($strArr) - ($j );
	    for($i =$count;  $i > 0; $i-- ){
	        $search_text = implode(" ", array_slice($strArr, $j, $i)); //print $search_text;  print '<br/>';
			$search_text = rtrim($search_text, ','); // remove malviyaa nager, like error
				
				
	       if( in_array($search_text, $fcat_rootcat) ){
			   
		      $keywords['root_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
	
	       }else if(in_array($spelwords, $fcat_rootcat))
		   {			   
		      $keywords['root_cat_name_ft'][]= ucwords($spelwords);  //print $search_text;  print '<br/>';	
	       }
		   if( in_array($search_text, $fcat_subcat) ){
			
		      $keywords['sub_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
	       }else if(in_array($spelwords, $fcat_subcat))
		   {			   
		      $keywords['sub_cat_name_ft'][]= ucwords($spelwords);  //print $search_text;  print '<br/>';	
	       }
		   
		   if( in_array($search_text, $fcat_leafcat) ){
		      $keywords['leaf_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
	       }else if(in_array($spelwords, $fcat_leafcat))
		   {			   
		      $keywords['leaf_cat_name_ft'][]= ucwords($spelwords);  //print $search_text;  print '<br/>';	
	       }
	      
		   if( in_array($search_text, $fcat_brand) ){
		      $keywords['brandname_ft'][]= ucwords($search_text);
	       }else if(in_array($spelwords, $fcat_brand))
		   {			   
		      $keywords['brandname_ft'][]= ucwords($spelwords);  //print $search_text;  print '<br/>';	
	       }
	   }
	$j++;
	} 
	  return (!empty($keywords)) ? $keywords : false;           
	
}
    public function getCatautosuggest($searchParams = array()){
		
				$searchParams['q']=$this->spell_correction($searchParams['q']);

	           if ($this->solr->ping()) {

                $this->fq = ' status:1';

                if (isset($searchParams['type']) && !empty($searchParams['type;'])) {
                    $this->fq.=' AND type:' . intval($searchParams['type;']);
                } else {
                    $this->fq.=' AND type:0';
                }
				
                if (isset($searchParams['q']) && $searchParams['q'] != '') {
					
					$query = $this->stripallslashes(urldecode($searchParams['q']));
                    $searchParams['q'] = implode(' ',array_filter(explode(" ", $query)));
				 $keywords =  $this->querySegmentation($searchParams['q']);				 
				 if(!empty($keywords)){  
							 
						if(!empty($keywords['root_cat_name_ft'])){
								
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['root_cat_name_ft'] as $key => $category){ 
								if ($i == 1) {
									$keyword_query .=' (root_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								} else {
									$keyword_query .=' OR (root_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								}
								$i++;
								$string=strtolower($category);		
								$searchParams['q']=str_replace($string,'', strtolower($searchParams['q']));
								
								
							}
							$keyword_query .=')';
								
						}
						if(!empty($keywords['sub_cat_name_ft'])){
													
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['sub_cat_name_ft'] as $key => $category){ 
								if ($i == 1) {
									$keyword_query .=' (sub_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								} else {
									$keyword_query .=' OR (sub_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								}
								$i++;
								$string=strtolower($category);		
								$searchParams['q']=str_replace($string,'', strtolower($searchParams['q']));
							}
							$keyword_query .=')';
							
						}  
						if(!empty($keywords['leaf_cat_name_ft'])){
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['leaf_cat_name_ft'] as $key => $category){ 
								if ($i == 1) {
									$keyword_query .=' (leaf_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								} else {
									$keyword_query .=' OR (leaf_cat_name_ft:"' .$category. '" OR    category_all_tight:('.$category.'))';
								}
								$i++;
								$string=strtolower($category);		
								$searchParams['q']=str_replace($string,'', strtolower($searchParams['q']));
							}
							$keyword_query .=')';			
							
						} 
						
						if(!empty($keywords['brandname_ft']) ){
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['brandname_ft'] as $key => $brandname){ 
								if ($i == 1) {
									$keyword_query .='brandname_ft:"' .$brandname. '"';
								} else {
									$keyword_query .=' OR brandname_ft:"' .$brandname. '"';
								}
								$i++;
								$string=strtolower($category);		
								$searchParams['q']=str_replace($string,'', strtolower($searchParams['q']));
							}
							$keyword_query .=')';
							
						} 
						if(!empty($keywords['model_ft'])){
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['model_ft'] as $key => $model){ 
								if ($i == 1){
									$keyword_query .='model_ft:"' .$model. '"';
								} else {
									$keyword_query .=' OR model_ft:"' .$model. '"';
								}
								$i++;
							}
							$keyword_query .=')';
							
						}      						
                
					}	
					if($this->stripallslashes(trim($searchParams['q']))!="" && !empty($this->stripallslashes(trim($searchParams['q'])))){
						$query = trim($this->stripallslashes(urldecode($searchParams['q'])));
						
						
						$arr = array_filter(explode(" ", $query));	


						$tmpQuery = "";
						foreach ($arr as $key => $val) {
							
							$tmpQuery .= "name_gramcat2:(\"" . $val . "\")^9999.1 AND ";
							
						}
						$tmpQuery = preg_replace('/( AND )$/', "", $tmpQuery);
						$tmpQuery = "(" . $tmpQuery . ")";

						$query1 = "(name_gramcat:(\"" . $query . "\")^9999.1 OR " . $tmpQuery;
						$query1 .= " )";
						//$this->fq.=' AND (' . $query1;
												
						//$this->fq.=' )';
						
						$tmpQuery = "";
						foreach ($arr as $key => $val) {
							
							$tmpQuery .= "name_gram2:(\"" . $val . "\")^6999.1 AND ";
							
						}
						$tmpQuery = preg_replace('/( AND )$/', "", $tmpQuery);
						$tmpQuery = "(" . $tmpQuery . ")";

						$query2 = "(name_gram:(\"" . $query . "\")^6999.1 OR " . $tmpQuery;
						$query2 .= " )";
						
						
						$this->fq.=' AND ('.$query1;
						$this->fq.=' OR ('.$query2.')';
						
						//$this->fq.=' OR nametight:("' . $this->stripallslashes($searchParams['q']) . '")';
						//$this->fq.=' OR product_name:("' . $this->stripallslashes($searchParams['q']) . '")';
						//$this->fq.=' OR tag:("' . $this->stripallslashes($searchParams['q']) . '")';
						//$this->fq.=' OR filter_list_all:(' . $this->stripallslashes($searchParams['q']) . ')';
						//$this->fq.=' OR attribute_list_all:(' . $this->stripallslashes($searchParams['q']) . ')';
						//$this->fq.=$keyword_query_cat;
						
						$this->fq.=' )';
					}
					 $this->fq.= $keyword_query ? $keyword_query : ''; 
					
                }
				$facetlimit=2;
				if(isset($searchParams['call_querySegmentation']) && $searchParams['call_querySegmentation']=="yes"){
					$facetlimit=5000;
					 $this->limit = 0;
					
				}else{
					$this->limit=1;
				}
			           
                    $this->start = 0;
                  
                    $this->is_facet = 'true';               
					$this->params['fl'] = 'root_cat_name_ft,sub_cat_name_ft,leaf_cat_name_ft';
                    $this->params['facet'] = $this->is_facet;
                    $this->params['facet.field'] = array('root_cat_name_ft','sub_cat_name_ft','leaf_cat_name_ft','brandname_ft');
					$this->params['facet.limit'] =$facetlimit;
                    $this->params['facet.mincount'] = '1';
                    $this->params['facet.sort'] = 'true';   
				
				    $this->result = $this->solr->search(htmlspecialchars_decode(urldecode($this->fq)), $this->start, $this->limit, $this->params);
					
				  $this->found = $this->result->response->numFound;								
                if ($this->found > 0) {
					
					 foreach ($this->result->response->docs as $doc) {
                        $doc->_fields = $doc->getFieldNames();
                        $fcount = count($doc->_fields);
                        $jsonarray = array();
                        for ($ctr = 0; $ctr < $fcount; $ctr++) {
                            if (is_array($doc->__get($doc->_fields[$ctr]))) {
								 //$jsonarray[$doc->_fields[$ctr]] = strip_tags(implode(', ', $doc->__get($doc->_fields[$ctr])));
								$jsonarray[$doc->_fields[$ctr]] =$doc->__get($doc->_fields[$ctr])[0];
                            } else {
                                $jsonarray[$doc->_fields[$ctr]] = strip_tags($doc->__get($doc->_fields[$ctr]));
                            }
                        }
						
                        $this->results_arr['data']= $jsonarray;
                    }
					if (isset($this->result->facet_counts->facet_fields) && count($this->result->facet_counts->facet_fields) > 0) {
						
						if(isset($searchParams['call_querySegmentation']) && $searchParams['call_querySegmentation']=="yes"){
							foreach ($this->result->facet_counts->facet_fields as $key => $fields) {
                            $facetfield = array();

                            foreach ($fields as $key1 => $value1) {
                                $facetfield[$key1] = $value1;
                            }							
							
                            $this->facetdatafield['facet_data'][$key]=$facetfield;
                        }
						}else{							
							$facetfield = array();
							 foreach ($this->result->facet_counts->facet_fields as $key => $fields) {
									 foreach ($fields as $key1 => $value1){	
										if($key!='brandname_ft'){
											//$facetfield[$key]=$key1;
											break;
										}else{
											$facetfield[$key][]=$key1;
										}									
									}	
								
								$this->facetdatafield= $facetfield;
							}						
						}
                    }			
					if(isset($searchParams['call_querySegmentation']) && $searchParams['call_querySegmentation']=="yes"){
					
					return json_encode($this->facetdatafield);
					}else{
					
				   return json_encode(array_merge($this->results_arr['data'],$this->facetdatafield));
					}
				
                } 
            }
        
    }

    private function unsetsolr() {
        unset($this->solr);
    }

}
