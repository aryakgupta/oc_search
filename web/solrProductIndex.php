<?php
require_once 'cat2.php';
$start = isset($_GET['start']) ? $_GET['start'] : 0;
$end = isset($_GET['end']) ? $_GET['end'] : 20000;
//print_r($argv);
if(isset($argv['1']) && !empty( $argv['1'])){
    $start=intval($argv['1']);
}

if(isset($argv['2']) && !empty( $argv['2'])){
    $end=intval($argv['2']);
}
$prodid = '';
if($_GET['prod']){ $prodid = $_GET['prod']; }
//print_r($_GET);
//echo 'hello...'.$prodid;
autoIndex($prodid, $start, $end);
?>
