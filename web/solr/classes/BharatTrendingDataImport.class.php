<?php 
class BharatTrendingDataImport
{
    private $solr;
    private $result;
    private $countheadline=array();
    public function __construct(){
        require_once(dirname(__FILE__) . '/../SolrPhpClient/Apache/Solr/Service.php');		
        $this->solr= new Apache_Solr_Service(SOLRSERVERIP, SOLRPORT, TRENDINGSEARCH);
    }

    public function indexTrendingKeyWord($resultdata=array()){
        $i=1;
        $total=0; 
        foreach($resultdata as $rs){
            try { 
                $document = new Apache_Solr_Document ();
				$document->id=md5($rs['keyword']);
                $document->name1 =trim($rs['keyword']);
                $document->name2 =trim($rs['keyword']);
                $document->popularity=intval($rs['popularity']);
				$document->type=intval($rs['type']);
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