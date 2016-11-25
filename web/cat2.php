<?php
error_reporting(0);
set_time_limit(0);
ob_start();
header('Content-Type: application/json');
try{
$autopush = 0;
$pushlimit = 1000;
$showTime = 0;
ini_set('memory_limit','1024M');
if( $autopush ){
  //require_once 'solr/solr_operations.php';
  //$solrObj = new solrCRUD();
  include_once(dirname(__FILE__)."/solr/classes/BharatSolrDataImport.class.php");
  $obj=new BharatSolrDataImport();
}
//define('DB_PREFIX', 'oc');
$prodid = '';
if($_GET['prod']){ $prodid = $_GET['prod']; }
require_once('config.php'); 
   @mysql_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD);
   @mysql_select_db(DB_DATABASE);
$start = isset($_GET['start']) ? $_GET['start'] : 50000;
$end = isset($_GET['end']) ? $_GET['end'] : 50000; 
if(isset($argv['1']) && !empty( $argv['1'])){	  
	$start=intval($argv['1']);     
}

if(isset($argv['2']) && !empty( $argv['2'])){	  
	$end=intval($argv['2']);  
}	
	
//$start = $page*$end;
$qqqqqq="SELECT DISTINCT
    *,
    pd.name AS name,
    p.image,
    (SELECT 
            price
        FROM
            oc_product_discount pd2
        WHERE
            pd2.product_id = p.product_id
            
               
        ORDER BY pd2.priority ASC , pd2.price ASC
        LIMIT 1) AS discount,

     (SELECT 
            brand_name
        FROM
            oc_brand b
        WHERE
            b.brand_id = p.brand_id
            
        LIMIT 1) AS brandname,
    (SELECT 
            price
        FROM
            oc_product_special ps
        WHERE
            ps.product_id = p.product_id
                AND ps.customer_group_id = '1'
                
        ORDER BY ps.priority ASC , ps.price ASC
        LIMIT 1) AS special,
    (SELECT 
            ss.name
        FROM
            oc_stock_status ss
        WHERE
            ss.stock_status_id = p.stock_status_id
                AND ss.language_id = '1') AS stock_status,
    (SELECT 
            wcd.unit
        FROM
            oc_weight_class_description wcd
        WHERE
            p.weight_class_id = wcd.weight_class_id
                AND wcd.language_id = '1') AS weight_class,
    (SELECT 
            lcd.unit
        FROM
            oc_length_class_description lcd
        WHERE
            p.length_class_id = lcd.length_class_id
                AND lcd.language_id = '1') AS length_class,
    p.sort_order
FROM
    oc_product p
        LEFT JOIN
    oc_product_description pd ON (p.product_id = pd.product_id)

        
WHERE
    p.status = '1' ";
if( !empty($prodid)){ $qqqqqq .= " and p.product_id in ($prodid)"; }
else{
   $qqqqqq.=" ORDER BY p.product_id DESC LIMIT $start, $end"; 
}
//echo $qqqqqq;
$time_start = microtime(true);

   $qqq1=mysql_query($qqqqqq);
   $master=array();
   $i=0;

   if( $showTime ){
      echo 'time taken in main query execution::'.$time_elapseid_secs = microtime(true) - $time_start;
   }
   while($rs=mysql_fetch_assoc($qqq1))
   {
    try{
    $time_start = microtime(true);

    $master[$i]['product_id']=$rs['product_id'];
    $master[$i]['product_path']=productUrlAlias($rs['product_id']);
    $master[$i]['model']=$rs['model'];
    $master[$i]['brand_id']=$rs['brand_id'];
    
    $master[$i]['sku']=$rs['sku'];
    $master[$i]['upc']=$rs['upc'];
    $master[$i]['ean']=$rs['ean'];
    $master[$i]['business_model']=$rs['business_model'];
    $master[$i]['return_ploicy']=$rs['return_ploicy'];
    $master[$i]['location']=$rs['location'];
    $master[$i]['quantity']=$rs['quantity'];
     
    $master[$i]['vendor_id']=$rs['vendor_id'];
    $master[$i]['brandname']=$rs['brandname'];
    $master[$i]['category']=getProductCategories($rs['product_id']);

       
    $master[$i]['image']=$rs['image'];
    $master[$i]['images']=getProductImages($rs['product_id']);
    $master[$i]['option']=option($rs['product_id']);
    $master[$i]['filter']=product_filter($rs['product_id']);
    $master[$i]['attribute']=product_attribute($rs['product_id']);
    $filtert_attrubte=array_merge(product_filter($rs['product_id']),product_attribute($rs['product_id']));
    $master[$i]['filter_attribute']=$filtert_attrubte;
    

    $master[$i]['shipping']=$rs['shipping'];
    $master[$i]['transfer_price']=$rs['transfer_price'];
    $master[$i]['points']=$rs['points'];
    $master[$i]['tax_class_id']=$rs['tax_class_id'];

     $master[$i]['date_available']=$rs['date_available'];
     $master[$i]['weight']=$rs['weight'];
     $master[$i]['weight_class_id']=$rs['weight_class_id'];
     $master[$i]['length']=$rs['length'];
     $master[$i]['width']=$rs['width'];
     $master[$i]['height']=$rs['height'];
     $master[$i]['length_class_id']=$rs['length_class_id'];
     $master[$i]['shipment_mode']=$rs['shipment_mode'];
     $master[$i]['subtract']=$rs['subtract'];

    $master[$i]['minimum']=$rs['minimum'];
    $master[$i]['status']=$rs['status'];
    $master[$i]['hs_code']=$rs['hs_code'];
    $master[$i]['type']=$rs['type'];
    $master[$i]['viewed']=$rs['viewed'];
    $master[$i]['offers']=$rs['offers'];
    

    
    $master[$i]['delivery_charge']=$rs['delivery_charge'];
    $master[$i]['product_for_id']=$rs['product_for_id'];
    $master[$i]['product_usability_id']=$rs['product_usability_id'];
    $master[$i]['language_id']=$rs['language_id'];
    $master[$i]['name']=$rs['name'];
    $master[$i]['description']=$rs['description'];
    $master[$i]['tag']=$rs['tag'];
    $master[$i]['meta_title']=$rs['meta_title'];
    $master[$i]['meta_description']=$rs['meta_description'];
     
    $master[$i]['mrp']=$rs['price'];
    
    $master[$i]['discount_price']=$rs['discount'];
    $master[$i]['selling_price']=$rs['special'];
     
    $master[$i]['stock_status']=$rs['stock_status'];
    $master[$i]['weight_class']=$rs['weight_class'];
    $master[$i]['length_class']=$rs['length_class'];
    $master[$i]['size_chart']=getSizeChart($rs['product_id']);
    if( $i>=$pushlimit && $autopush ){
        //$data=$obj->indexIntoSolr($master);
        //create(json_encode($master));
        if($showTime){
           $time_elapsed_secs = microtime(true) - $time_start;
           echo "row processing time taken:: $i ::".$time_elapsed_secs;
           $time_start = microtime(true);
        }
        $master = array();
        $i = 0;
    }

    $i++;
    }catch(Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

   }
   if( $i>=$pushlimit && $autopush ){
        //$data=$obj->indexIntoSolr($master);
        //create(json_encode($master));
        $master = array();
        $i = 0;
    }
	
	include_once(dirname(__FILE__)."/solr/classes/BharatSolrDataImport.class.php");
        $obj=new BharatSolrDataImport();
        if(is_array($master) || is_object($master)){
            $data=$obj->indexIntoSolr($master);
	    print_r($data);
        }

}catch(Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";exit;
}


 function getProductImages($product_id) {
  $imagedata=array();
    $query = mysql_query("SELECT * FROM oc_product_image WHERE product_id = '".$product_id."' ORDER BY sort_order ASC");
    while($rs=mysql_fetch_assoc($query))
    {
    $imagedata[]=$rs;
   }
   return $imagedata;
  }



function option($product_id)
{


$data['options'] = array();
             

           $optiondata= getProductOptions($product_id);
           //echo '<pre>';
           //print_r($optiondata);
           //exit;
         if(!empty($optiondata)){
        foreach ( $optiondata as $option) {
        $product_option_value_data = array();

        foreach ($option['product_option_value'] as $option_value) {
         
            

            $product_option_value_data[$option_value['name']] =$option_value['quantity'];

             
          
        }

        $data['options'][] = array(
 
          $option['name'] => $product_option_value_data,
          
                    
          
        );
      }
    }

 return $data['options']; 
}


 function getProductOptions($product_id) {
    $product_option_data = array();
 
   $product_option_value_query=array();
    $query = mysql_query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "'   ORDER BY o.sort_order");
     $num=mysql_num_rows($query);
    if($num>0)
    { 
      while($op=mysql_fetch_assoc($query))
      {
        $product_option_query[]=$op;
      }
    }else{

      return ;
    }

     //print_r($product_option_query);
     //exit;
    if(!empty($product_option_query))
    { 
    foreach ($product_option_query as $product_option) {
      $product_option_value_data = array();



      $product_option_value_query = mysql_query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "'    ORDER BY ov.sort_order");
      
       //$product_option_value_query=mysql_fetch_assoc($product_option_value_query);
       // $opqry=mysql_fetch_assoc($product_option_value_query);
        //print_r($opqry);
        //exit;

       while($opqry=mysql_fetch_assoc($product_option_value_query))
       {
            
            $final[] = $opqry;
             
       }
//print_r($product_option_value_query);die;
      foreach ($final as $product_option_value) {
        $product_option_value_data[] = array(
          'product_option_value_id' => $product_option_value['product_option_value_id'],
          'option_value_id'         => $product_option_value['option_value_id'],
          'name'                    => $product_option_value['name'],
          'image'                   => $product_option_value['image'],
          'quantity'                => $product_option_value['quantity'],
          'subtract'                => $product_option_value['subtract'],
          'price'                   => $product_option_value['price'],
          'price_prefix'            => $product_option_value['price_prefix'],
          'weight'                  => $product_option_value['weight'],
          'weight_prefix'           => $product_option_value['weight_prefix']
        );
      }

      $product_option_data[] = array(
        'product_option_id'    => $product_option['product_option_id'],
        'product_option_value' => $product_option_value_data,
        'option_id'            => $product_option['option_id'],
        'name'                 => $product_option['name'],
        'type'                 => $product_option['type'],
        'value'                => $product_option['value'],
        'required'             => $product_option['required']
      );
    }
}//if(!empty($product_option_query))
    return $product_option_data;
  }
//// filter
function  product_filter($product_id)
{
   $filter=array();
   $qq=" SELECT fgd.name as filter_group_name,fd.name as filter_name FROM oc_product_filter as pf INNER JOIN oc_filter_description as fd ON(pf.filter_id=fd.filter_id) 
 INNER JOIN oc_filter_group_description as fgd ON(fgd.filter_group_id=fd.filter_group_id)  WHERE product_id='".$product_id."' ";

  $rrr=mysql_query($qq);
  $num=mysql_num_rows($rrr);
  if($num >0)
  {
      while($rs=mysql_fetch_assoc($rrr))
      {
       $filter[$rs['filter_group_name']][]=$rs['filter_name'];

      }
 }
  return $filter;
}
///category data statr  here

 function getProductCategories($product_id) {
    $product_category_data = array();

    $query = mysql_query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
     
     while($result=mysql_fetch_assoc($query))
    
     {
      $product_category_data[] = $result['category_id'];
     }
     /////////////

   $categories=$product_category_data;
    //echo '<pre>';
//print_r($categories);
//exit;
  
   $arr=array();
    foreach ($categories as $category_id) {

        $category_info=getCategory($category_id);
         //$category_info['path'];
         $pos = strpos($category_info['path'], '>');
         if($pos)
         {
          
              $path=explode('>',$category_info['path']);
              $product_categories = array();
              $y=0;
              foreach($path as $key=>$val)
              {
                   $rs=explode('-', $val);
                   if($y==0){
                    $root_cat_id='root_cat_id';
                    $catname='root_cat_name'; 
                    $caturl='root_cat_url';
                   }else{ 
                    $root_cat_id='sub_cat_id'; 
                    $catname='sub_cat_name';
                    $caturl='sub_cat_url';
                  }
                   //if($y==0){ $root_cat_id='root_cat_id'; $catname='sub_cat_name';}else{ $root_cat_id='sub_cat_id'; $catname='sub_cat_name';}

                   $product_categories[$root_cat_id]=$rs['1'];
                   $product_categories[$catname]=$rs['0'];
                  $product_categories[$caturl]=categoryUrlAlias($rs['1']);
                  $y++;
              }
              $product_categories['leaf_cat_id']=$category_info['category_id'];
              $product_categories['leaf_cat_name']=$category_info['name'];
              $product_categories['leaf_cat_url']=categoryUrlAlias($category_info['category_id']);
              $arr[] = $product_categories;
         }else{
              

              if ($category_info) {
                  $arr[] = array(
                  'category_id' => $category_info['category_id'],
                  'name' => $category_info['name'],
        
              );
            }
   
         }
       

      
    }

////////////
  


    return $arr;
  }

 function getCategory($category_id) {
    /*echo "SELECT DISTINCT *, (SELECT GROUP_CONCAT(CONCAT(cd1.name,'-', cd1.category_id) ORDER BY level SEPARATOR ' > ') FROM oc_category_path cp LEFT JOIN oc_category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id GROUP BY cp.category_id) AS path FROM oc_category c LEFT JOIN oc_category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id ='".(int)$category_id."'  ";

    exit;*/ 
    $query = mysql_query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(CONCAT(cd1.name,'-', cd1.category_id) ORDER BY level SEPARATOR '>') FROM oc_category_path cp LEFT JOIN oc_category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id GROUP BY cp.category_id) AS path FROM oc_category c LEFT JOIN oc_category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id ='".(int)$category_id."' ");

    $rs=mysql_fetch_assoc($query);
    
    return $rs;
  }



function getCategoryPath($product_id) {
          $query = mysql_query("SELECT COUNT(product_id) AS total, category_id as catid FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
         
         $row=mysql_fetch_assoc($query);
          
          if(isset($row['total'])){
             $path = array();
             $path[0] = $row['catid'];
             
             $query2 = mysql_query("SELECT parent_id AS pid FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$path[0] . "'");
             $row2=mysql_fetch_assoc($query2);

             $parent = $row2['pid'];
             
             $p = 1;
             while($parent>0){
                $path[$p] = $parent;
               
                $query3 = mysql_query("SELECT parent_id AS pid FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$parent . "'");
                $row3=mysql_fetch_assoc($query3);
                $parent = $row3['pid'];
                $p++;
             }
         
             $path = array_reverse($path);
             
             $fullpath = '';
             
             foreach($path as $val){
                $fullpath .= '_'.$val;
             }
          //    echo '<br>';
          //print_r($fullpath);
          //exit;
             return ltrim($fullpath, '_');
          }else{
             return '0';
          }

       }


function categoryname($catid)
{
  $qqq=" SELECT name FROM oc_category_description WHERE category_id='".$catid."' LIMIT 1";
  $ee=mysql_query($qqq);
  $num=mysql_num_rows($ee); 
  if($num>0)
  { 
  $rs=mysql_fetch_assoc($ee);
  return $rs['name'];
  }else{
   return ;

  }
}
 ///category data end here
 function product_attribute($product_id)
{
  $attribute=array();
    $qq=" SELECT * FROM oc_product_attribute as pa LEFT JOIN  oc_attribute_description as a ON(pa.attribute_id = a.attribute_id)  WHERE pa.product_id='".$product_id."'";
  $ex=mysql_query($qq);
  $num=mysql_num_rows($ex);
  if($num >0)
  {
   while($rs=mysql_fetch_assoc($ex))
   {
     $attribute[$rs['name']]=$rs['text'];
   }
   return $attribute;
  }else{
    return $attribute;
  }
  
}

function productUrlAlias($proid)
{
 $urlq ="SELECT keyword FROM `oc_url_alias` WHERE query LIKE 'product_id=".$proid."'";
 $urlq = mysql_query($urlq);
 $row=mysql_fetch_assoc($urlq);

 return $row['keyword'];
}

function categoryUrlAlias($catid)
{
 $urlq ="SELECT keyword FROM `oc_url_alias` WHERE query LIKE 'category_id=".$catid."'";
 $urlq = mysql_query($urlq);
 $row=mysql_fetch_assoc($urlq);

 return $row['keyword'];
}

function getSizeChart($product_id)
    {
        $sql = "SELECT  c.* FROM " . DB_PREFIX . "size_chart_values c JOIN " . DB_PREFIX . "size_chart_option o ON(c.template_id = o.template_id) WHERE o.product_id = '" . $product_id . "'";        
        $query = mysql_query($sql);

        $count = mysql_num_rows($query);
        $sizechart = array();

      if($count > 0)
      {
        $sizechart = mysql_fetch_assoc($query);
      }
       return $sizechart;
    }



// echo '<pre>';
// print_r($master);
// //echo json_encode($master);


   
?>

