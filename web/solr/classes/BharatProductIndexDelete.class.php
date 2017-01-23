<?php 
class BharatProductIndexDelete
{
	private $solr;
	private $solrAuto;
	private $result;
	private $hascode;
	public function __construct(){
		
		require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');
		$this->hascode=md5('BharttrendingSearchIndexDelete');
		 $this->solr = new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTSEARCH);		
		$this->solrAuto= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTAUTOSEARCH);		
	}
	
	
	public function deleteProductById($params=array()){
		
		if(isset($params['token']) && $this->hascode==$params['token']){
			if(isset($params['product_id']) && !empty($params['product_id'])){
			$ids=array();
			$ids=explode('~',$params['product_id']);
			
			foreach($ids as $product_id){
				
				if($product_id>0){
					$product_unique_id=$product_id."__".$params['type'];
					$this->solr->deleteByQuery('product_unique_id:'.$product_unique_id);								
					$product_auto_pro_id="pro#".$product_id;
					$this->solrAuto->deleteByQuery('id:'.$product_auto_pro_id.' AND data_type:Product');
					echo "Deleted this product id: [" .$product_id."]  Successfully";
				}			
			}
				
				$this->solr->commit();
				$this->solr->optimize();
				$this->solrAuto->commit();
				$this->solrAuto->optimize();	
				
			}else{
				echo "OPPS!!!";
			}
			
		}
	}
	
	function deleteKeywordbasedAutosearchlogs($params=array()){
		if(isset($params['token']) && $this->hascode==$params['token']){
			if(isset($params['keyword']) && !empty($params['keyword']) && $params['data_type']=='searchlogs'){						
				$this->solrAuto->deleteByQuery('name1:"'.$params['keyword'].'" AND data_type:searchlogs');
				echo "Deleted this keywords: [" .$params['keyword']."]  Successfully";
				$this->solrAuto->commit();
				$this->solrAuto->optimize();
			}
		}
		
	}

}
?>


		