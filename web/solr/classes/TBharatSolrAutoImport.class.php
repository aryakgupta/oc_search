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
        require_once(dirname(__FILE__).'/../SolrPhpClient/Apache/Solr/Service.php');
		
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
			$document->id ="pro#".md5($rs['name']."__".$rs['type']);
			$document->name1=trim($rs['name']);
			$document->name2=trim($rs['name']);
			$document->model=trim($rs['model']);
			$document->brandname=trim($rs['brandname']);
			$document->data_type='Product';
			$document->type=$rs['type'];
			
				$root_cat_id=array();
				$root_cat_name=array();
				$sub_cat_id=array();
				$sub_cat_name=array();
				$leaf_cat_id=array();
				$leaf_cat_name=array();
				
               foreach($rs['category'] as $key=> $category){
				   $root_cat_id[$key]=$category['root_cat_id'];
				   $root_cat_name[$key]=$category['root_cat_name'];
				   $sub_cat_id[$key]=$category['sub_cat_id'];
				   $sub_cat_name[$key]=$category['sub_cat_name'];
				   $leaf_cat_id[$key]=$category['leaf_cat_id'];
				   $leaf_cat_name[$key]=$category['leaf_cat_name'];
				   
				   if(!in_array($leafcategory_val,$category['leaf_cat_name'])){
					$this->leafcategory_list[$category['leaf_cat_id']]=$category['leaf_cat_name']."~~".$category['sub_cat_name']."~~".$category['root_cat_name'];
					}
				
					   if(!in_array($subcategory_val,$category['sub_cat_name'])){
							$this->subcategory_list[$category['sub_cat_id']]=$category['sub_cat_name']."~~".$category['root_cat_name']."~~".$category['leaf_cat_name'];
						}
				   if(!in_array($rootcategory_val,$category['root_cat_name'])){
						$this->rootcategory_list[$category['root_cat_id']]=$category['root_cat_name']."~~".$category['sub_cat_name']."~~".$category['leaf_cat_name'];
					}
			   }
				$document->root_cat_name=$root_cat_name;
				$document->sub_cat_name=$sub_cat_name;
				$document->leaf_cat_name=$leaf_cat_name;
			
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
		$catlist=explode('~~',$rootcategory_list_val);
			$document = new Apache_Solr_Document ();
			$document->id ="rcat#".md5(trim($catlist[0]));
			$document->name1=trim($catlist[0]);
			$document->name2=trim($catlist[0]);
			$document->category_type='root';
			$document->root_cat_name=trim($catlist[0]);
			$document->sub_cat_name=trim($catlist[1]);
			$document->leaf_cat_name=trim($catlist[2]);
			$document->root_cat_name_ft=trim($catlist[0]);
			$document->sub_cat_name_ft=trim($catlist[1]);
			$document->leaf_cat_name_ft=trim($catlist[2]);
			$document->data_type='category';
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
			$catlist=explode('~~',$subcategory_list_val);
			$document = new Apache_Solr_Document ();
			$document->id ="rcat#".md5(trim($catlist[0]));
			$document->name1=trim($catlist[0]);
			$document->name2=trim($catlist[0]);
			$document->sub_cat_name=trim($catlist[0]);
			$document->root_cat_name=trim($catlist[1]);
			$document->leaf_cat_name=trim($catlist[2]);
			$document->sub_cat_name_ft=trim($catlist[0]);
			$document->root_cat_name_ft=trim($catlist[1]);
			$document->leaf_cat_name_ft=trim($catlist[2]);
			$document->data_type='category';
			$document->category_type='sub';
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
			$catlist=explode('~~',$subcategory_list_val);
			$document = new Apache_Solr_Document ();
			$document->id ="rcat#".md5(trim($catlist[0]));
			$document->name1=trim($catlist[0]);
			$document->name2=trim($catlist[0]);
			$document->leaf_cat_name=trim($catlist[0]);
			$document->sub_cat_name=trim($catlist[1]);
			$document->root_cat_name=trim($catlist[2]);
			$document->leaf_cat_name_ft=trim($catlist[0]);
			$document->sub_cat_name_ft=trim($catlist[1]);
			$document->root_cat_name_ft=trim($catlist[2]);
			$document->data_type='category';
			$document->category_type='leaf';
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
			$document->id ="brand#".md5(trim($rs['brandname']));
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
			$document->id ="model#".md5($rs['model']);
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

	public function indexSearchKeyWord($resultdata=array()){
        $i=1;
        $total=0; 
        foreach($resultdata as $rs){
            try { 
                $document = new Apache_Solr_Document ();
				$document->id=md5($rs['keyword']);
                $document->name1 =trim($rs['keyword']);
                $document->name2 =trim($rs['keyword']);
                $document->data_type='searchlogs';
				$document->type=(int)$rs['type'];
				$document->popularity=(int)$rs['popularity'];
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
        $this->solr->optimize();
        $this->countheadline['Total_index']['Total']=$total;
        return json_encode($this->countheadline);

    }
	
}

?>


		