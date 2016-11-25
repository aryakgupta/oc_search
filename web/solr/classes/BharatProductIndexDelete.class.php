<?php 
class BharatProductIndexDelete
{
	private $solr;
	private $solrAuto;
	private $result;
	private $hascode;
	public function __construct(){
		
		require_once('SolrPhpClient/Apache/Solr/Service.php');
		$this->hascode=md5('BharttrendingSearchIndexDelete');
		 $this->solr = new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTSEARCH);		
		$this->solrAuto= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, PRODICTAUTOSEARCH);		
	}
	
	
	public function deleteProductById($params=array()){
		if(isset($params['token']) && $this->hascode==$params['token']){
			if($params['product_id']>0){
			$product_unique_id=$params['product_id']."__".$params['type'];
			$this->solr->deleteByQuery('product_unique_id:'.$product_unique_id);
			$this->solr->commit();
			$this->solr->optimize();			
			$product_auto_pro_id="pro#".$params['product_id'];
			$this->solrAuto->deleteByQuery('id:'.$product_auto_pro_id.' AND data_type:Product');
			$this->solrAuto->commit();
			$this->solrAuto->optimize();	
			
			echo "Deleted this product id: [" .$params['product_id']."]  Successfully";
			}else{
				echo "OPPS!!!";
			}
		}
	}

}
?>


		