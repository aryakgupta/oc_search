<?php
error_reporting(0);
include('solrConfig.php');
$obj = new BharatTrendingAutoSearch();
$params = array();
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $params['q'] = urlencode(strip_tags($_GET['q']));
}
if (isset($_GET['start']) && !empty($_GET['start'])) {
    $params['start'] = $_GET['start'];
}
if (isset($_GET['limit']) && !empty($_GET['limit'])) {
    $params['limit'] = $_GET['limit'];
}
$sdearchdata = $obj->getTrendingAutoSearch($params);
$result = (array) json_decode($sdearchdata);
echo json_encode($result);

?>