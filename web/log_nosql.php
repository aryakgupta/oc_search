<?php
//define('DB_PREFIX', 'oc');
$enableNosql = 0;
require_once dirname(__FILE__) . '/solr/cacheLayer.php';
$ttl = 1800; // 30 minutes
$redisObj = cacheFactory::create_cache();

require_once('new_config.php'); 
   @mysql_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD);
   @mysql_select_db(DB_DATABASE);
   
  $qqq1=mysql_query("SELECT * from oc_search ORDER BY search_id desc LIMIT 0,10");
 
  $i=0;
   while($rs=mysql_fetch_assoc($qqq1))
   {
    $dtKey = date('Ymd', strtotime($rs['date_added']));
    $tmp['keyword']=$rs['keyword'];
    $tmp['date_added']=$rs['date_added'];
    if( $enableNosql){
        $redisObj->rPushListData($dtKey, array(serialize($tmp)));
        //$redisObj->rPushListData($dtKey, $tmp);
    }
    $i++;

   }
   

$dtKey = date('Ymd');
echo '<pre>Hello';
print_r(unserialize($redisObj->lPopList($dtKey)));
?>

