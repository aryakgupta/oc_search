<html>
<table>
<?php
require_once 'solr/solr_operations.php';
$solrObj = new solrCRUD();

//echo '<pre>';
// get search data
$sortby = trim($_GET['sort']);
$searchStr = trim($_GET['search']);
if( $searchStr ){
    switch($_GET['searcht']){
        case 'name':
            $searchArray = array('q'=>$searchStr, 'start'=>0, 'limit'=>20, 'order'=>'asc', 'sort'=>$sortby, 'type'=>0);
            $result = $solrObj->search($searchArray);
            break;
        case 'sku':
            $searchArray = array('sku'=>$searchStr, 'start'=>0, 'limit'=>20, 'order'=>'asc', 'sort'=>$sortby, 'type'=>0);
            $result = $solrObj->search();
            break;

        case 'catid':
            $searchArray = array('category_id'=>$searchStr, 'start'=>0, 'limit'=>20, 'order'=>'asc', 'sort'=>$sortby, 'type'=>0);
            $result = $solrObj->search($searchArray);
            break;

        case 'productid':
            $searchArray = array('ids'=>$searchStr, 'start'=>0, 'limit'=>20, 'order'=>'asc', 'sort'=>$sortby, 'type'=>0);
            $result = $solrObj->search($searchArray);
            break;
        default:
            $searchArray = array('q'=>$searchStr, 'start'=>0, 'limit'=>20, 'order'=>'asc', 'sort'=>$sortby, 'type'=>0);
            $result = $solrObj->search($searchArray);
            break;
    }
    echo '<pre>Search data::';print_r($searchArray);
}else{
    echo "<tr><td colspan=2 style='font-color:red;'>Search string missing!!! enter search string in input box...</td></tr>";

}
?>

<h1>Solr opetation</h1>
<form id="searchsolr">
<table>
<tr><td>Search By</td><td>Name: <input type="radio" name="searcht" value="name"> &nbsp; Product id:<input type="radio" name="searcht" value="productid">&nbsp;Product sku:<input type="radio" name="searcht" value="sku">&nbsp;Category id:<input type="radio" name="searcht" value="catid">
<tr><td>Search Data(name/cat id/sku..)::</td><td><input type="text" name="search"></td></tr>
<tr><td>Sort by::</td><td><select name="sort"><option value="">Default</option>
<option value="name">Name</option>
<option value="price">Price</option>
<option value="discount">Discount</option>
</select>&nbsp;<input type="submit" value="Search"></td></tr>
<tr><td>Result::</td><td><textarea rows="20" cols="120"><?php print_r($result);?></textarea></td></tr> 
<tr><td>Json Result::</td><td><textarea rows="20" cols="120"><?php echo json_encode($result);?></textarea></td></tr> 
</table></form>
</html>