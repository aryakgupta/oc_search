<?php
class BharatSearch {

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
	private $stripcategorylist=array('mens','women','men','womens','kids','kid');
	
    public function __construct() {

        require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');
        require_once(dirname(__FILE__) . '/../solrConfig.php');

        $this->solr = new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTSEARCH);
		
    }

    public function __desctruct() {
        $this->unsetsolr();
    }
private function spell_correction($string){
	
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
		$string = preg_replace("/\bin\b/", "", $string);
		$string = preg_replace("/\bfrom\b/", "", $string);
		
        $string=$this->solr->escape($string);
        $string = urldecode(trim($string));
        return strip_tags($string);
    }
private function querySegmentation($queryString =  null){ 
 
 
//$queryString = " tops for girls";
$params=array();
$params['is_only_facet']= 'yes';
$params['querySegmentation']='yes';
$produt_data = $this->getResults($params);
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
}
/*
foreach($result['facet_data']->model_ft as $facetname => $valcount){	
		
		array_push($fcat_model, trim(strtolower($facetname)));		
}*/
foreach($result['facet_data']->brandname_ft as $facetname => $valcount){	
		
		array_push($fcat_brand, trim(strtolower($facetname)));		
}
		$queryString=$this->spell_correction($queryString);
	   	$strArr = explode(" ", trim(urldecode($queryString)));
		
        $strArr	= array_map('strtolower', $strArr);
		
		
	$j = 0;
	
	foreach($strArr as $key=>$value){
		
	   $count =  count($strArr) - ($j );
	   for($i =$count;  $i > 0; $i-- ){
	        $search_text = implode(" ", array_slice($strArr, $j, $i)); //print $search_text;  print '<br/>';
                $search_text = rtrim($search_text, ','); // remove malviyaa nager, like error
				
	       if( in_array($search_text, $fcat_rootcat) ){
			   
		      $keywords['root_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
			  
			 
	       }
		   if( in_array($search_text, $fcat_subcat) ){
			
		      $keywords['sub_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
	       }
		   
		   if( in_array($search_text, $fcat_leafcat) ){
		      $keywords['leaf_cat_name_ft'][]= ucwords($search_text);  //print $search_text;  print '<br/>';
	       }
	       if( in_array($search_text, $fcat_model) ){
		      $keywords['model_ft'][]= ucwords($search_text);
	       }
		   
		 
		   if( in_array($search_text, $fcat_brand) ){
		      $keywords['brandname_ft'][]= ucwords($search_text);
	       }
	   }
	$j++;
	} 
	
    return (!empty($keywords)) ? $keywords : false;           
	
}
    public function getResults($searchParams = array()) {
		
        try {
	
            if ($this->solr->ping()) {
				$sepllecheckerorgquery='';
				$searchParams['q']=$this->spell_correction($searchParams['q']);
				$sepllecheckerorgquery=$searchParams['q'];
                $this->fq = ' status:1';
				/*
					if(!isset($params['querySegmentation'])){
						$this->fq.=' AND _val_:"scale(quantity,0,1.2)"';
					}*/
					
                if (isset($searchParams['name']) && $searchParams['name'] != '') {
                    $this->fq.=' AND product_name:(' . $this->stripallslashes($searchParams['name']) . ')';
                }

                if (isset($searchParams['q']) && $searchParams['q'] != '') {
				
					$query = $this->stripallslashes(urldecode($searchParams['q']));
                    $searchParams['q'] = implode(' ',array_filter(explode(" ", $query)));
					$keywords ='';
					$keywords =  $this->querySegmentation($searchParams['q']);	
					 if(!empty($keywords)){  
							 
						if(!empty($keywords['root_cat_name_ft'])){
								
							$i = 1;
							$keyword_query .=' AND (';
							foreach($keywords['root_cat_name_ft'] as $key => $category){ 
								if ($i == 1) {
									$keyword_query .=' (root_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
								} else {
									$keyword_query .=' AND (root_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
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
									$keyword_query .=' (sub_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
								} else {
									$keyword_query .=' OR (sub_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
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
									$keyword_query .=' (leaf_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
								} else {
									$keyword_query .=' OR (leaf_cat_name_ft:"' .$category. '" OR    category_all_tight:("'.$category.'"))';
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
									$keyword_query .=' brandname:"' .$brandname. '" OR brandname:("'.$brandname.'")';
								} else {
									$keyword_query .=' OR brandname:"' .$brandname. '" OR brandname:("'.$brandname.'")';
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
						$this->fq.=' AND (nametight:(' . $this->stripallslashes($searchParams['q']) . ')^9199.9';
						$this->fq.=' OR product_name:(' . $this->stripallslashes($searchParams['q']) . ')^999999.4';
						//$this->fq.=' OR name_rev:(' . $this->stripallslashes($searchParams['q']) . ')^999.4';
						
						$this->fq.=' OR brandname:('.$this->stripallslashes($searchParams['q']).')';
						$this->fq.=' OR tag:(' . $this->stripallslashes($searchParams['q']) . ')^928.8';
						$this->fq.=' OR filter_list_all:(' . $this->stripallslashes($searchParams['q']) . ')';
						$this->fq.=' OR attribute_list_all:(' . $this->stripallslashes($searchParams['q']) . ')^927.9';
						//$this->fq.=' OR category_all:('.$this->stripallslashes($searchParams['q']).')';
						//$this->fq.=' OR category_all:('.$this->stripallslashes($searchParams['q']).')';
						//$this->fq.=' OR category_all:('.$this->stripallslashes($searchParams['q']).')';
						$this->fq.=' )';
					}
				
				
					
					
					 $this->fq.= $keyword_query ? $keyword_query : ''; 
					 
                }
			
                if (isset($searchParams['product_ids']) && !empty($searchParams['product_ids'])) {

                    $item_idslist = explode("~", urldecode($searchParams['product_ids']));
                    $i = 1;
                    $this->fq.=' AND (';
                    foreach ($item_idslist as $item_idslistValue) {
                        if ($i == 1) {
                            $this->fq.=' product_id:' . intval($item_idslistValue);
                        } else {
                            $this->fq.=' OR product_id:' . intval($item_idslistValue);
                        }
                        $i++;
                    }
                    $this->fq.=' )';
                }

				if (isset($searchParams['color']) && !empty($searchParams['color'])) {
                    $this->fq.=' AND color_ft:("' . urldecode($searchParams['color']) . '")';
                }
				if (isset($searchParams['offer']) && !empty($searchParams['offer'])) {
                    $this->fq.=' AND offers_ft:("' . urldecode($searchParams['offer']) . '")';
                }
				 if (isset($searchParams['brand']) && !empty($searchParams['brand'])) {
                    $this->fq.=' AND brandname_ft:("' . urldecode($searchParams['brand']) . '")';
                }
				
                if (isset($searchParams['model']) && !empty($searchParams['model'])) {
                    $this->fq.=' AND model_ft:("' . urldecode($searchParams['model']) . '")';
                }
				 if (isset($searchParams['category']) && !empty($searchParams['category'])) {
                    $this->fq.=' AND root_cat_name_ft:("' . urldecode($searchParams['category']) . '")';
                }
               if (isset($searchParams['subcat']) && !empty($searchParams['subcat'])) {
                    $this->fq.=' AND sub_cat_name_ft:("' . urldecode($searchParams['subcat']) . '")';
                }
			    if (isset($searchParams['leafcat']) && !empty($searchParams['leafcat'])) {
                    $this->fq.=' AND leaf_cat_name_ft:("' . urldecode($searchParams['leafcat']) . '")';
                }
				 if (isset($searchParams['price']) && !empty($searchParams['price'])) {
                    $this->fq.=' AND (selling_price:(' . $this->stripallslashes($searchParams['price']) . ') )';
                }
                if (isset($searchParams['root_cat_id']) && !empty($searchParams['root_cat_id'])) {
                    $this->fq.=' AND root_cat_id:"' . intval($searchParams['root_cat_id']) . '"';
                }
               if (isset($searchParams['sub_cat_id']) && !empty($searchParams['sub_cat_id'])) {
                    $this->fq.=' AND sub_cat_id:"' . intval($searchParams['sub_cat_id']) . '"';
                }
			    if (isset($searchParams['leaf_cat_id']) && !empty($searchParams['leaf_cat_id'])) {
                    $this->fq.=' AND leaf_cat_id:"' . intval($searchParams['leaf_cat_id']) . '"';
                }
				
				if (isset($searchParams['category_id']) && $searchParams['category_id']>0) {
                    $this->fq.=' AND (';
					 $this->fq.=' root_cat_id:' . intval($searchParams['category_id']) . ' OR sub_cat_id:' . intval($searchParams['category_id']) . ' OR leaf_cat_id:' . intval($searchParams['category_id']);
					  $this->fq.=')';
                }
				if (isset($searchParams['sku']) && !empty($searchParams['sku'])) {

                    $sku_idslist = explode("~", urldecode($searchParams['sku']));
                    $i = 1;
                    $this->fq.=' AND (';
                    foreach ($sku_idslist as $sku_idslistValue) {
                        if ($i == 1) {
                            $this->fq.=' sku:("' . $sku_idslistValue . '")';
                        } else {
                            $this->fq.=' OR sku:("' . $sku_idslistValue . '")';
                        }
                        $i++;
                    }
                    $this->fq.=' )';
                }
				
                

                if (isset($searchParams['price_range']) && !empty($searchParams['price_range'])) {
                    list($minp, $maxp) = explode("-", $searchParams['price_range']);
                    if ($minp > 0 && $maxp > 0) {
                        $this->fq.=' AND (selling_price:[' . $minp . ' TO ' . $maxp . '])';
                    } else {
                        $this->fq.=' AND (selling_price:[0 TO ' . $searchParams['price_range'] . '])';
                    }
                }
				 if (isset($searchParams['stock_status']) && !empty($searchParams['stock_status'])) {
                    $this->fq.=' AND stock_status:"' .$this->stripallslashes($searchParams['stock_status']).'"';
                }
				if (isset($searchParams['attribute']) && !empty($searchParams['attribute'])) {
                    $this->fq.=' AND attribute_list_all:("' .$this->stripallslashes($searchParams['attribute']).'")';
                }
				if (isset($searchParams['size']) && !empty($searchParams['size'])) {
                    $this->fq.=' AND size_ft:("' .$this->stripallslashes($searchParams['size']).'")';
                }
				if (isset($searchParams['filter']) && !empty($searchParams['filter'])) {
                    $this->fq.=' AND filter_list_all:("' .$this->stripallslashes($searchParams['filter']).'")';
                }
				if (isset($searchParams['type']) && trim($searchParams['type'])>0) {					
                    $this->fq.=' AND type:1';
                } else {
					if(!isset($searchParams['querySegmentation'])){
						$this->fq.=' AND type:0';
					}
                }
				
                if (isset($searchParams["limit"]) && $searchParams["limit"] != "") {
                    $this->limit = $searchParams["limit"];
                } else {
                    $this->limit = 30;
                }

                if (isset($searchParams["start"]) && $searchParams["start"] != "") {
                    $this->start = $searchParams["start"];
                } else {
                    $this->start = 0;
                }


                if (isset($searchParams['is_only_facet']) && $searchParams['is_only_facet'] == 'yes') {
                    $this->start = 0;
                    $this->limit = 0;
                    $this->is_facet = 'true';
					
                } else if (isset($searchParams['is_with_facet']) && $searchParams['is_with_facet'] == 'yes') {
                    $this->is_facet = 'true';
					//$this->fq.=' AND _val_:"scale(quantity,0,999.9)"';
					
                }

                if (isset($searchParams['sort']) && !empty($searchParams['sort'])) {
					
					if(isset($searchParams['orderby']) && !empty($searchParams['orderby'])) {
						if($searchParams['orderby']=='desc' || $searchParams['orderby']=='asc'){
							$this->sorting_order =$searchParams['orderby'];
						}else{
							$this->sorting_order = 'desc';		
						}
					}else{
						$this->sorting_order = 'desc';		
					}
                
				
                    if ($searchParams['sort']== 'price') {
                        $this->sorting_field ='out_of_stock desc, selling_price ' . $this->sorting_order;
                    }
					if ($searchParams['sort']=='percentage') {
                        $this->sorting_field='out_of_stock desc, discount_price ' . $this->sorting_order;
                    }
					if ($searchParams['sort']=='product_id') {
                        $this->sorting_field='out_of_stock desc, product_id ' . $this->sorting_order;
                    }
					
					if ($searchParams['sort']=='name') {
                        $this->sorting_field='out_of_stock desc,product_name_ft ' . $this->sorting_order;
                    }
					
                }else{
					$this->sorting_order='desc';
					$this->sorting_field = 'out_of_stock desc,score asc';
				}

                if (!empty($this->sorting_field) && !empty($this->sorting_order)) {
                    $this->params['sort'] = $this->sorting_field;
                }

			
                if (isset($searchParams['return_field']) && !empty($searchParams['return_field'])) {
                    $this->params['fl'] = $searchParams['return_field'];
                } else {
                   // $this->params['fl'] = 'product_id,product_name,model,brand_id,sku,upc,ean,business_model,return_ploicy,location,quantity,vendor_id,brandname,root_cat_id,root_cat_name,sub_cat_id,sub_cat_name,leaf_cat_id,leaf_cat_name,category,attribute_list,filter_list,image,color,shipping,transfer_price,points,tax_class_id,weight,weight_class_id,length,width,height,length_class_id,shipment_mode,subtract,minimum,status';
					 $this->params['fl'] ='*';
                }

                if (isset($this->is_facet) && $this->is_facet == 'true') {
                    $this->params['facet'] = $this->is_facet;
					if(isset($searchParams['querySegmentation']) && $searchParams['querySegmentation']=='yes'){						
						$this->params['facet.field'] = array('root_cat_name_ft','sub_cat_name_ft','leaf_cat_name_ft','brandname_ft');
						 $this->params['facet.limit'] = '5000';				
						
					}else{
						
						//$this->params['facet.field'] = array('root_cat_name_ft','sub_cat_name_ft','leaf_cat_name_ft','selling_price','brandname_ft','offers_ft','color_ft','size_ft','stock_status','attribute_list_ft');
						$this->params['facet.field'] = array('brandname_ft','offers_ft','color_ft','size_ft','stock_status','attribute_list_ft','category_path_desc','filter_list_ft');
						$this->params['facet.query'] =array('selling_price:[1 TO 500]','selling_price:[500 TO 1000]','selling_price:[1000 TO 1500]','selling_price:[1500 TO 2000]','selling_price:[2000 TO *]');
						$this->params['facet.pivot'] ='root_cat_name_ft,sub_cat_name_ft,leaf_cat_name_ft';
						 $this->params['facet.limit'] = '100';
					}
					  
                  
                    if (isset($searchParams['only_facet_sort']) && $searchParams['only_facet_sort'] == 'true') {
                        $this->params['facet.mincount'] = '1';
                        $this->params['facet.sort'] = 'false';
                    } else {
                        $this->params['facet.mincount'] = '1';
                        $this->params['facet.sort'] = 'true';
                    }
                }

                if (isset($searchParams['parent_id_stats']) && !empty($searchParams['parent_id_stats'])) {
                    $this->params['stats'] = 'true';
                    $this->params['stats.field'] = 'cat_parent_id';
                }
			
				$this->result = $this->solr->search(htmlspecialchars_decode(urldecode($this->fq)), $this->start, $this->limit, $this->params);
                $this->found = $this->result->response->numFound;
								
		if($this->found==0){
			if($sepllecheckerorgquery!=''){
				
				$this->spellparams['spellcheck']='true';
				$this->spellparams['spellcheck.collate']='true';
				$this->spellparams['spellcheck.build']='true';
				$spellq=$sepllecheckerorgquery;
				$splelresult=array();
				$splelresult= $this->solr->spellsearch(htmlspecialchars_decode(urldecode($spellq)), 0, 3, $this->spellparams);
				$spellcount=0;
				if(isset($splelresult->spellcheck->suggestions[1])){
				$spellcount=$splelresult->spellcheck->suggestions[1]->numFound;
				}
				if($spellcount>0){
					
					$spellsearchname=$splelresult->spellcheck->suggestions[1]->suggestion[0]->word;
					if($splelresult->spellcheck->suggestions[3]->suggestion[0]->word!=''){
						$spellsearchname.=" ".$splelresult->spellcheck->suggestions[3]->suggestion[0]->word;
					}
					if($splelresult->spellcheck->suggestions[5]->suggestion[0]->word!=''){
						$spellsearchname.=" ".$splelresult->spellcheck->suggestions[5]->suggestion[0]->word;
					}
					$this->fq=' (product_name:('.$spellsearchname.')^99999';
					$this->fq.=' OR nametight:(' . $spellsearchname . ')^9199.9';
					$this->fq.=' OR category_all_tight:('.$this->stripallslashes($spellsearchname).')';
					$this->fq.=' OR category_all:('.$this->stripallslashes($spellsearchname).'))';
					if (isset($searchParams['type']) && trim($searchParams['type'])>0) {					
						$this->fq.=' AND type:1';
					} else {
						if(!isset($searchParams['querySegmentation'])){
							$this->fq.=' AND type:0';
						}
                }
				
				
					$this->result = $this->solr->search(htmlspecialchars_decode(urldecode($this->fq)), $this->start, $this->limit, $this->params);

					$this->found = $this->result->response->numFound;
					$this->countheadline['didyoumean'] ='Showing results for "'.$spellsearchname.'".Search instead for "'.strip_tags($searchParams['q']).'"';					
				}
		
			}
		}				
                $this->countheadline['status_code'] = $this->result->getHttpStatus();
                if ($this->result->getHttpStatusMessage() == "OK") {
                    $this->countheadline['status_text'] = "Success";
                } else {
                    $this->countheadline['status_text'] = $this->result->getHttpStatusMessage();
                }
                $this->countheadline['count'] = $this->found;


                if ($this->found > 0) {

                    foreach ($this->result->response->docs as $doc) {
                        $doc->_fields = $doc->getFieldNames();
                        $fcount = count($doc->_fields);
                        $jsonarray = array();
                        for ($ctr = 0; $ctr < $fcount; $ctr++) {
                            if (is_array($doc->__get($doc->_fields[$ctr]))) {
                                $jsonarray[$doc->_fields[$ctr]] = strip_tags(implode(', ', $doc->__get($doc->_fields[$ctr])));
                            } else {
                                $jsonarray[$doc->_fields[$ctr]] = strip_tags($doc->__get($doc->_fields[$ctr]));
                            }
                        }
                        $this->results_arr['data'][] = $jsonarray;
                    }

                    if (isset($this->result->facet_counts->facet_fields) && count($this->result->facet_counts->facet_fields) > 0) {
                        foreach ($this->result->facet_counts->facet_fields as $key => $fields) {
                            $facetfield = array();

                            foreach ($fields as $key1 => $value1) {

                                $facetfield[$key1] = $value1;
                            }							
                            $this->facetdatafield['facet_data'][$key] = $facetfield;
                        }
						
                    }
					
					if(isset($this->result->facet_counts->facet_pivot)){
						foreach ($this->result->facet_counts->facet_pivot as $key=>$val){
							
							$this->facetdatafield['facet_data']['facet_new_tree_category']=$val;
						}
					
					}
					
						
				if(count($this->result->facet_counts->facet_queries) > 0){
					$facetRange=array();
					foreach($this->result->facet_counts->facet_queries as $key => $fields){
						
							list($rangeFields,$rangeFieldValue)=explode(":[",$key);
							$rangeFieldValue = substr($rangeFieldValue, 0, -1);
							$rangeFieldValue=str_replace('2000 TO *','2000 TO Above',$rangeFieldValue);
							$facetRange[$rangeFieldValue]=$fields;					
						}
						$this->facetdatafield['facet_data']['price_range']=$facetRange;
					}
                    if (isset($this->result->stats->stats_fields) && count($this->result->stats->stats_fields) > 0) {
                        $this->statsdata['price_stats']['min'] = $this->result->stats->stats_fields->item_selling_price->min;
                        $this->statsdata['price_stats']['max'] = $this->result->stats->stats_fields->item_selling_price->max;
                    }
                    $this->merge_search_data = array_merge($this->countheadline, $this->results_arr, $this->facetdatafield, $this->statsdata);					
				   return json_encode($this->merge_search_data);
                } else {

                    return json_encode($this->countheadline);
                }
            }
        } catch (Exception $e) {
            $this->countheadline['status_code'] = "Caught exception";
            $this->countheadline['status_text'] = $e->getMessage();
            $this->countheadline['search_query'] = http_build_query($searchParams);
            return json_encode($this->countheadline);
        }
    }

    private function unsetsolr() {
        unset($this->solr);
    }

}
