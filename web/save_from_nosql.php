<?php
require_once dirname(__FILE__) . '/save_search.php';

function saveToDatabase($listKey=''){
    if($listKey){
        $listKey = date('Ymd');
    }
    while( 1 ){
        $searchData = readFromNosql($listKey);
        if( !$searchData ){
            break;
        }
        // insert data into db
        echo $searchKey = $searchData['keyword'];
        echo $searchDate = $searchData['date_added'];

        // save into database


        // end database save operation
        break;
    }
}
$listKey = date('Ymd');
saveToDatabase($listKey);
?>
