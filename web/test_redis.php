<?php
require_once dirname(__FILE__) . '/save_search.php';

$ttl = 1800; // 30 minutes
$redisObj = cacheFactory::create_cache();

/*
// key value
$key='name';
$data='My name is Pradeep';
$keyval = $redisObj->getCacheData($key);
if ( !$keyval ){
    $redisObj->setCacheData($key, $data, $ttl);
    echo 'value set';
}else{
    // value already set
    echo "value for key:: $key is ::".$keyval; 
}
*/
/*
// set hashkey
$hashkey = 'category';
$key = 'men';
$data = 'http://datahere';
$resKey = $redisObj->getHashData($hashkey, $key);
if( !$resKey ){
    $redisObj->setHashData($hashkey, $key, $data, 0);
    echo 'hash key set';
}else{
    // value already set
    echo "value of hashkey:: $hashkey for key:: $key is ::".$resKey; 
}
*/
/*
//Increment data
$incrementHashkey = 'search';
$key = 'red belt';
$redisObj->incrementHashData($incrementHashkey, $key, 1, 0);    /// increment value by 1
*/

// set list
//$listKey = date('Ymd');
//$data = serialize(array('keyword' => 'wachs', 'date_added' => '2017-01-11 09:17:22'));
saveToNosql("this is test data");


//get saved searches
$listKey = date('Ymd');
while( 1 ){
    $searchData = readFromNosql($listKey);
    if( !$searchData ){
        break;
    }
    // insert data into db
    echo $searchData['keyword'];
    echo $searchData['date_added'];

}
?>