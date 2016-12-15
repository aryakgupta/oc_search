<?php 
class BharatSolrDataImport
{
    private $solr;
    private $result;
    private $countheadline=array();


    public function __construct(){
        require_once(dirname(__FILE__).'/../SolrPhpClient/Apache/Solr/Service.php');
		
        require_once(dirname(__FILE__).'/../solrConfig.php');
        $this->solr= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTSEARCH);
		
    }

    public function indexIntoSolr($resultdata=array()){
        $i=1;
        $total=0; 
	
		
        foreach($resultdata as $rs){
			
		
			
		  try { 
                $document = new Apache_Solr_Document ();
                $document->product_unique_id=$rs['product_id']."__".$rs['type'];
				$document->product_id=$rs['product_id'];
				$document->product_name=trim($rs['name']);
				$document->name_rev=trim($rs['name']);				
                $document->model =trim($rs['model']);
				$document->model_ft =trim($rs['model']);
                $document->brand_id =(int)$rs['brand_id'];
                $document->sku=trim($rs['sku']);
                $document->upc=trim($rs['upc']);
                $document->ean=trim($rs['ean']);
                $document->business_model=trim($rs['business_model']);
                $document->return_ploicy=trim($rs['return_ploicy']);
                $document->location=trim($rs['location']);
                 $document->quantity=(int)$rs['quantity'];
                $document->vendor_id=(int)$rs['vendor_id'];
                $document->brandname=trim($rs['brandname']);
				 $document->brandname_ft=trim($rs['brandname']);
				$root_cat_id=array();
				$root_cat_name=array();
				$sub_cat_id=array();
				$sub_cat_name=array();
				$leaf_cat_id=array();
				$leaf_cat_name=array();
				$cat_path=array();
               foreach($rs['category'] as $key=> $category){
				   $root_cat_id[$key]=$category['root_cat_id'];
				   $root_cat_name[$key]=$category['root_cat_name'];
				   $sub_cat_id[$key]=$category['sub_cat_id'];
				   $sub_cat_name[$key]=$category['sub_cat_name'];
				   $leaf_cat_id[$key]=$category['leaf_cat_id'];
				   $leaf_cat_name[$key]=$category['leaf_cat_name'];
				   $cat_path[$key]=$category['root_cat_name']."/".$category['sub_cat_name']."/".$category['leaf_cat_name'];
			   }
			   $document->category_path_asc=$cat_path;
			   $document->category_path_desc=$cat_path;
			   $document->category_path_ft=$cat_path;
                $document->root_cat_id=$root_cat_id;
                $document->root_cat_name=$root_cat_name;
				$document->root_cat_name_ft=$root_cat_name;
                $document->sub_cat_id=$sub_cat_id;
                $document->sub_cat_name=$sub_cat_name;
				 $document->sub_cat_name_ft=$sub_cat_name;
                $document->leaf_cat_id=$leaf_cat_id;
                $document->leaf_cat_name=$leaf_cat_name;
				$document->leaf_cat_name_ft=$leaf_cat_name;
				$document->category=json_encode($rs['category']);
				
				$document->attribute_list=json_encode($rs['attribute']);
				$document->filter_list=json_encode($rs['filter']);
				$document->option_list=json_encode($rs['option']);
                 $attributelistft=array();
				 $attributelist=array();
				foreach($rs['attribute'] as $key=> $attribute){
						$attributelistft[]=$key.':'.$attribute;
						$attributelist[]=$attribute;
				}
				$document->attribute_list_ft=$attributelistft;
				$document->attribute_list_all=$attributelist;
				
				$filterlistft=array();
				$filterlist=array();
				
				foreach($rs['filter'] as $key=> $filter){
					
					foreach($filter as $filter_val){
						$filterlistft[]=$key.':'.$filter_val;
						$filterlist[]=$filter_val;
					}
				}
				
				$document->filter_list_ft=$filterlistft;
				$document->filter_list_all=$filterlist;
				
				/*
				
				$optionlistft=array();
				$optionlist=array();
				foreach($rs['option'] as $key=> $option){
						$optionlistft[]=$key.':'.$option;
						$optionlist[]=$option;
				}
				
				
				$document->option_list_ft=$optionlistft;
				$document->option_list_all=$optionlist;
								
				*/
				
               $document->image=$rs['image'];
			   
			   if(count($rs['images'])>0){
                $document->images=json_encode($rs['images']);
			   }
			  
			   //$document->last_modified=trim($rs['last_modified']);
			 
			  
			   if($rs['filter']['Color'][0]!=''){
				   $document->color=$rs['filter']['Color'];
				   $document->color_ft=$rs['filter']['Color'];
			   }
			  
			  
			  $size_list=array();
			  
			   if($rs['option'][0]['Size']!=''){
				    foreach($rs['option'][0]['Size'] as $sizekey=>$sizekeyval){
					if($sizekeyval>0){
						$size_list[]=$sizekey;
					   }
				   }
					   $document->size=$size_list;
					   $document->size_ft=$size_list;
			   }
			   
			   
                $document->shipping=trim($rs['shipping']);
                $document->transfer_price=(float)$rs['transfer_price'];
                $document->points=trim($rs['points']);
				$document->tax_class_id=intval($rs['tax_class_id']);
				
				/*if($rs['date_available']!='0000-00-00 00:00:00')
					{
						 list($start_date,$start_time)=explode(" ",$rs['date_available']);
						 if($start_date!=''){
							 $document->date_available = $start_date . "T" . $start_time . "Z";
						 }
					
					}*/
				

                $document->weight=trim($rs['weight']);
				$document->weight_class_id=intval($rs['weight_class_id']);
				$document->length=trim($rs['length']);
                $document->width=trim($rs['width']);
                $document->height=trim($rs['height']);
                $document->length_class_id=intval(trim($rs['length_class_id']));
                $document->shipment_mode=trim(trim($rs['shipment_mode']));
                $document->subtract=trim(trim($rs['subtract']));
                $document->minimum=trim($rs['minimum']);           


                $document->status=(int)$rs['status'];
                $document->hs_code=trim($rs['hs_code']);
                $document->type=trim($rs['type']);
                $document->viewed=(int)$rs['viewed'];
                $document->offers=$rs['offers'];
				 $document->offers_ft=$rs['offers'];
				$document->delivery_charge=trim($rs['delivery_charge']);
				$document->product_for_id=(int)$rs['product_for_id'];
				$document->product_usability_id=(int)$rs['product_usability_id'];
				$document->language_id=(int)$rs['language_id'];
				
				$document->description=trim($rs['description']);
				if($rs['tag']!=''){
					$document->tag=explode(',',$rs['tag']);
				}
				$document->meta_title=trim($rs['meta_title']);
				$document->meta_description=trim($rs['meta_description']);
				$document->mrp=(float)$rs['mrp'];
				
				//$document->discount_price=(float)$rs['discount_price'];
				$document->discount_price=(float)$rs['percentagediscount'];
				
				$document->selling_price=(float)$rs['selling_price'];
				$document->stock_status=$rs['stock_status'];
				$document->weight_class=$rs['weight_class'];
				$document->length_class=$rs['length_class'];
				if($rs['quantity']>0){
					$document->out_of_stock=1;
				}else{
					$document->out_of_stock=0;	
				}
                $return=$this->solr->addDocument($document);

                if($return->getHttpStatusMessage()=="OK"){

                        $this->countheadline['status_code'][]=$return->getHttpStatus();                     
                        $this->countheadline['item id'][]=$rs['product_id'];
                        $i++;
                }else{
                        $this->countheadline['status_code'][]=$return->getHttpStatus();
                        $this->countheadline['not index item id'][]=$rs['product_id'];
                }
				

            }catch(Exception $e){ 
               
                $this->countheadline['status_text'][]=$e->getMessage();
                $this->countheadline['not index item id'][]=$rs['product_id'];
                echo $e->getMessage();

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
