<?php 
class TBharatSolrAutoImport
{
	private $solr;
	public $db;
	private $result;
	private $countheadline=array();
	private $rootcategory_list=array();
	private $subcategory_list=array();
	private $leafcategory_list=array();
	public function __construct(){
        require_once('SolrPhpClient/Apache/Solr/Service.php');
		
        require_once(dirname(__FILE__).'/../solrConfig.php');
        $this->solr= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTAUTOSEARCH);
		 
		
    }
	
	
	public function index_auto_produt($resultdata)
	{
	
	$i=1;
	$total=0;
	 foreach($resultdata as $rs){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="pro#".$rs['product_id'];
			$document->name1=trim($rs['name']);
			$document->name2=trim($rs['name']);
			$document->model=trim($rs['model']);
			$document->brandname=trim($rs['brandname']);
			$document->data_type='Product';
			$document->root_cat_name=explode(',',$rs['root_cat_name']);
			$document->sub_cat_name=explode(',',$rs['sub_cat_name']);
			$document->leaf_cat_name=explode(',',$rs['leaf_cat_name']);
			$leafcategory=explode(',',$rs['leaf_cat_name']);
			$leaf_cat_id=explode(',',$rs['leaf_cat_id']);
			foreach($leafcategory as $leafid=> $leafcategory_val){
				if(!in_array($leafcategory_val,$this->leafcategory_list)){
					$this->leafcategory_list[$leaf_cat_id[$leafid]]=$leafcategory_val;
				}
			}
			$subcategory=explode(',',$rs['sub_cat_name']);
			$sub_cat_id=explode(',',$rs['sub_cat_id']);
			foreach($subcategory as $subcatid=> $subcategory_val){
				if(!in_array($subcategory_val,$this->subcategory_list)){
				$this->subcategory_list[$sub_cat_id[$subcatid]]=$subcategory_val;
				}
			}
			
			$rootcategory=explode(',',$rs['root_cat_name']);
			$root_cat_id=explode(',',$rs['root_cat_id']);
			foreach($rootcategory as $rotid=> $rootcategory_val){
				if(!in_array($rootcategory_val,$this->rootcategory_list)){
				$this->rootcategory_list[$root_cat_id[$rotid]]=$rootcategory_val;
				}
			}
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){	
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
				
				$i++;
			}else{					
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
				
			}
			
		}catch(Exception $e){				
				$this->countheadline['status_text'][]=$e->getMessage();
				
			
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
		
		$this->countheadline['Total_index'][]=$total;
		
	
		// index_auto_root_category
if(isset($this->rootcategory_list) && !empty($this->rootcategory_list)){	
		$i=1;
	$total=0;
	foreach($this->rootcategory_list as $catid=>$rootcategory_list_val){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="rcat#".$catid;
			$document->name1=trim($rootcategory_list_val);
			$document->name2=trim($rootcategory_list_val);
			//$document->model=trim($rs['model']);
			//$document->brandname=trim($rs['brandname']);
			$document->data_type='root_category';
			//$document->sub_cat_name=trim($rs['sub_cat_name']);
			//$document->leaf_cat_name=trim($rs['leaf_cat_name']);
			
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){						
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();				
				$i++;
			}else{								
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();				
			}
			
		}catch(Exception $e){ 										
				$this->countheadline['status_text'][]=$e->getMessage();	
			
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
		$this->countheadline['Total_index'][]=$total;
		
}

	
	//index_auto_subcategory
if(isset($this->subcategory_list) && !empty($this->subcategory_list)){		
	$i=1;
	$total=0;
	foreach($this->subcategory_list as $scatid=>$subcategory_list_val){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="scat#".$scatid;
			$document->name1=trim($subcategory_list_val);
			$document->name2=trim($subcategory_list_val);
			$document->data_type='sub_category';
			//$document->root_cat_name=trim($rs['root_cat_name']);
			//$document->leaf_cat_name=trim($rs['leaf_cat_name']);
			
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){
				
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
	
				$i++;
			}else{				
	
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
	
			}
			
		}catch(Exception $e){ 						
	
				$this->countheadline['status_text'][]=$e->getMessage();
	
			
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
	
		
	}
	
	
	
//index_auto_laefcategory
if(isset($this->leafcategory_list) && !empty($this->leafcategory_list)){		
	$i=1;
	$total=0;
	foreach($this->leafcategory_list as $leafcatid=>$leafcategory_list_val){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="leafcat#".$leafcatid;
			$document->name1=trim($leafcategory_list_val);
			$document->name2=trim($leafcategory_list_val);
			$document->data_type='leaf_category';
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){
				
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
	
				$i++;
			}else{				
	
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
	
			}
			
		}catch(Exception $e){ 						
	
				$this->countheadline['status_text'][]=$e->getMessage();
	
			
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
		
	
	}
		return json_encode($this->countheadline);
		
	}
	
	public function index_auto_brand($resultdata){
	
	$i=1;
	$total=0;
	foreach($resultdata as $rs){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="brand#".$rs['brand_id'];
			$document->name1=trim($rs['brandname']);
			$document->name2=trim($rs['brandname']);
			$document->model=trim($rs['model']);
			$document->data_type='Brand';
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){				
				$this->countheadline['status_code'][]=$return->getHttpStatus();				
				$i++;
			}else{				
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
				}
			
		}catch(Exception $e){ 										
				$this->countheadline['status_text'][]=$e->getMessage();
				
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
		$this->countheadline['Total_index'][]=$total;
		return json_encode($this->countheadline);
		
	}
	public function index_auto_module($resultdata){
	
	$i=1;
	$total=0;
	foreach($resultdata as $rs){
		try { 
			$document = new Apache_Solr_Document ();
			$document->id ="model#".$rs['modile_id'];
			$document->name1=trim($rs['model']);
			$document->name2=trim($rs['model']);
			$document->brandname=trim($rs['brandname']);
			$document->data_type='Model';
			$return=$this->solr->addDocument($document);
			if($return->getHttpStatusMessage()=="OK"){				
				$this->countheadline['status_code'][]=$return->getHttpStatus();				
				$i++;
			}else{				
				$this->countheadline['status_text'][]=$return->getHttpStatusMessage();
				}
			
		}catch(Exception $e){ 										
				$this->countheadline['status_text'][]=$e->getMessage();
				
		}
			$total=$i-1;
					
		}
		$this->solr->commit(); 
		$this->countheadline['Total_index'][]=$total;
		return json_encode($this->countheadline);
		
	}


	
}

?>


		