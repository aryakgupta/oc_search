<?php
// this file is used to store site searches in nosql database rather than mysql
require_once dirname(__FILE__) . '/solr/cacheLayer.php';

function saveToNosql($search=''){
    //$ttl = 1800; // 30 minutes
    if( $search ){
        $redisObj = cacheFactory::create_cache();
        $listKey = date('Ymd');
        $curDate = date('Y-m-d H:i:s');
        $data = serialize(array('keyword' => $search, 'date_added' => $curDate));
        $redisObj->rPushListData($listKey, $data);
    }
}


function readFromNosql($listKey=''){
    if( !$listKey ){    // fetch saved searches of today
        $listKey = date('Ymd');
    }
    $redisObj = cacheFactory::create_cache();
    return unserialize($redisObj->lPopList($listKey));
}
