<?php

/**
 * Description of api_layer
 *
 * @author pradeep
 * This class is created to bypass the initiation of magento objects and slim framework
 * It is used to return memcached and solr objects without hitting magento Api's or 
 * without initiating Slim object.
 */
set_include_path(get_include_path() . ':' . dirname(__FILE__) . '/../lib');
require_once dirname(__FILE__) . '/../solr_search/classes/GmaxSearch.class.php';
require_once dirname(__FILE__) . '/../solr_search/classes/GmaxTrendingAutoSearch.class.php';
require_once dirname(__FILE__) . '/cacheLayer.php';

class apiLayer {

    //put your code here
    function __construct() {
        $this->start_time = microtime(TRUE);
        $this->dirFile = dirname(__FILE__) . "/../var/log/mobileApiInfo.log";
        $this->writeLog = 0;    // by default logging is disabled
        // Read json requested data
        $foo = file_get_contents("php://input");
        $foo = json_decode($foo, true);
        $this->JSON_POST_DATA = $foo;
        $this->GET = $_GET;
        $this->requestParm = "Post:" . json_encode($this->JSON_POST_DATA) . " Get:" . json_encode($this->GET);
        $this->cacheEngine = 'cacheMem';     // cacheMem for memcache and cacheRedis for redis
        $this->categoryHashUrlKey = 'categoryurl';
        $this->categoryHashNameKey = 'categoryname';
        $this->productHashUrlKey = 'producturl';
        $this->trendingHashKey = 'trendingkeys';

        // cache server setting  :: do not change it!!!
        $this->cacheEnabled = false;
        $this->cacheObj = null;
        $this->key_prefix = explode('.', $_SERVER['HTTP_HOST'])[0];
        if ($this->key_prefix == 'grocermax') {
            $this->cacheServer = '172.31.12.127';    //live
            $this->webUrl ="https://".$_SERVER['HTTP_HOST'];
        } else {
            $this->cacheServer = 'localhost';   //staging and other server
            $this->webUrl ="http://".$_SERVER['HTTP_HOST'];

        }

        // solr setting
        $this->solrEnabled = false;
    }

    function swingTheBall() {
        // updated notification status
        $this->updateNotification();

        $uriNameArr = explode('?', $_SERVER['REQUEST_URI']);
        $scriptNameArr = explode('/', $uriNameArr[0]);
        $this->apiName = $scriptNameArr[count($scriptNameArr) - 1];
        $suffix = '';
        switch ($this->apiName) {
            case 'category':
            case 'getlocation':
            case 'shopbycategory':
            case 'offerbydealtype':
            case 'dealsbydealtype':
            case 'homepage':
            case 'subcategorybanner':
                $this->generateCacheKey();
                $this->getCacheData();
                break;
            case 'search':
                // code for getting search result from solr
                if ($this->solrEnabled) {
                    $this->sendJsonResponse($this->searchSolr());
                    exit();
                }
                break;
            case 'autocomplete':
                // code for getting search result from solr
                if ($this->solrEnabled) {
                    $this->createAjaxResponse($this->searchSolr(20));
                }else{
                    ob_end_clean();
                    header("Location: /autocomplete/ajax?q=".$this->GET['q']);
                    exit;
                }
                break;
            case 'productdetail':
                if ($this->solrEnabled) {
                    $this->productDetailSolr();
                }
                break;
            case 'productlistall':
                if ($this->solrEnabled) {
                    $this->productListAllSolr();
                }
                break;
            case 'specialdeal1':
                if ($this->solrEnabled) {
                    $this->specialDealSolr();
                }
                break;
            case 'rootcategoryproduct':
                if ($this->solrEnabled) {
                    $this->rootCategoryProductSolr();
                }
                break;
            case 'trending':
                if ($this->solrEnabled) {
                    $this->trendingSolr();
                }
                break;    

        }
    }

    function generateCacheKey() {
        $suffix = '';
        switch ($this->apiName) {
            case 'category':
                $suffix = isset($this->GET['parentid']) ? $this->GET['parentid'] : '';
                break;
            case 'getlocation':
            case 'shopbycategory':
                break;
            case 'offerbydealtype':
            case 'subcategorybanner':
                $suffix = isset($this->GET['cat_id']) ? $this->GET['cat_id'] : '';
                break;
            case 'dealsbydealtype':
                $suffix = isset($this->GET['deal_type_id']) ? $this->GET['deal_type_id'] : '';
                break;
            case 'homepage':
                break;
        }
        $this->cacheKey = $this->storeId . $this->device . $this->version . $this->apiName . $suffix;
    }

    function getCacheObj() {
        $this->cacheObj = cacheFactory::create_cache($this->cacheEngine);
        return $this->cacheObj;
    }

    function getKey($key) {
        if ($key) {
            return $this->key_prefix . '_' . $key;
        }
        return $this->key_prefix . '_' . $this->cacheKey;
    }

    function getCacheData($key = '') {
        $key = $this->getKey($key);
        if ($this->cacheEnabled) {   //Process request if cache is enabled
            if (!$this->cacheObj) {
                if( !$this->getCacheObj() ){
                    return;
                }
            }
            $this->response = $this->cacheObj->getCacheData($key);
            if ($this->response===false) {
                return;
            }elseif($this->response){
                $this->sendJsonResponse(unserialize($this->response));
                exit;
            }
        }
    }

    function getHashData($hashKey, $key){
        if ($this->cacheEnabled) {   //Process request if cache is enabled
            if (!$this->cacheObj) {
                if( !$this->getCacheObj() ){
                    return;
                }
            }
            return $this->cacheObj->getHashData($hashKey, $key);
        }
    }

    function setCacheData($data, $key = '', $ttl = false) {
        $key = $this->getKey($key);
        if ($this->cacheEnabled) {   //Process request if cache is enabled
            if (!$this->cacheObj) {
                if( !$this->getCacheObj() ){
                    return;
                }
            }
            $this->cacheObj->setCacheData($key, serialize($data), $ttl);
        }
    }

    function setHashData($hashKey, $data, $key, $ttl = false){
        if ($this->cacheEnabled) {   //Process request if cache is enabled
            if (!$this->cacheObj) {
                if( !$this->getCacheObj() ){
                    return;
                }
            }
            return $this->cacheObj->setHashData($hashKey, $key, $data, 0);
        }
    }

    function incrementHashData($hashKey, $key, $data=1, $ttl = false){
        if ($this->cacheEnabled) {   //Process request if cache is enabled
            if (!$this->cacheObj) {
                if( !$this->getCacheObj() ){
                    return;
                }
            }
            return $this->cacheObj->incrementHashData($hashKey, $key, $data, 0);
        }
    }

    function searchSolr($limit=0) {
        if(isset($this->GET['keyword'])){
            $keyword = isset($this->GET['keyword']) ? $this->GET['keyword'] : '';
            $limit = 1000;
        }else{
            $keyword = isset($this->GET['q']) ? $this->GET['q'] : '';
        }
        $p = isset($this->GET['page']) ? $this->GET['page'] : '';
        
        if ($p == 1 || $p == '' || $p == 0) {
            $p = 1;
        }
        $obj = new GmaxSearch();
        $sdearchdata = $obj->getResults(array('search' => urlencode($keyword), 'storeId' => $this->storeId,'limit'=>$limit,'sort'=>'rank desc'));
        $result = (array) json_decode($sdearchdata);
        $flag_arr = array();
        if ($result['count'] <= 0) {
            $this->response = array("Result" => "No Result Found 1", "Product" => array(), "flag" => 0);
            $this->sendJsonResponse($this->response);
            exit();
        }
        $master_data = $result['data'];
        $master_data_limit_count = count($master_data);
        $master_data_count = $result['count'];
        if ($master_data_count <= 0) {
            $this->response = array("Result" => "No Result Found 2", "Product" => array(), "flag" => 0);
            $this->sendJsonResponse($this->response);
            exit();
        }
        $item_send_array = array();
        $category_array = array();
        $cat_list = array();
        if ($master_data_count > 0) {
            $flag = 1;
            $flag_arr[] = $flag;
            for ($i = 0; $i < $master_data_limit_count; $i++) {
                $category_send_array = array();
                $looping_array = (array) $master_data[$i];
                //if($looping_array['web_qty']>0){ $status = 'In stock';}else{$status = 'Sold Out';}
                $category_array[$i] = $looping_array['root_parent_id'];
                $item_send_array[$i]['Name'] = $looping_array['name'];
                $item_send_array[$i]['p_brand'] = $looping_array['product_name_line_1'];
                $item_send_array[$i]['p_name'] = $looping_array['product_name_line_2'];
                $item_send_array[$i]['p_pack'] = $looping_array['product_name_line_3'];
                $item_send_array[$i]['Status'] = ($looping_array['web_qty'] > 0) ? 'In stock' : 'Sold Out';
                $item_send_array[$i]['productid'] = $looping_array['entity_type_id'];
                $category_send_array[] = $looping_array['root_parent_id'];
                $item_send_array[$i]['categoryid'] = $category_send_array;
                $item_send_array[$i]['Price'] = $looping_array['price'];
                $item_send_array[$i]['sale_price'] = $looping_array['special_price'];
                $item_send_array[$i]['discount'] = $looping_array['price'] - $looping_array['special_price'];
                if ($looping_array['small_image'] != '') {
                    $item_send_array[$i]['Image'] = $looping_array['small_image'];
                } else {
                    $item_send_array[$i]['Image'] = '';
                }
                if (empty($looping_array['promotion_level']) && ($looping_array['promotion_level'] == '')) {
                    $promotion = null;
                } else {
                    $promotion = $looping_array['promotion_level'];
                }
                $item_send_array[$i]['url_path'] = $this->webUrl.'/'.$looping_array['url_path'];
                $item_send_array[$i]['cart_image'] = $looping_array['cart_image'];
                $item_send_array[$i]['webqty'] = $looping_array['web_qty'];
                $item_send_array[$i]['promotion_level'] = $promotion;
                $item_send_array[$i]['rank'] = ($looping_array['web_qty'] > 0) ? $looping_array['rank'] : '0';
                $item_send_array[$i]['currencycode'] = '₹';
                
                if(!empty($looping_array['root_parent_id'])){
                    $cat_list[$looping_array['root_parent_id']]['category_id'] = $looping_array['root_parent_id'];
                    $cat_list[$looping_array['root_parent_id']]['parent_id'] = $looping_array['cat_parent_id'];
                    $cat_list[$looping_array['root_parent_id']]['name'] = $looping_array['root_parent_name'];
                    $cat_list[$looping_array['root_parent_id']]['is_active'] = $looping_array['cat_is_active'];
                    $cat_list[$looping_array['root_parent_id']]['position'] = $looping_array['cat_position'];
                    $cat_list[$looping_array['root_parent_id']]['level'] = $looping_array['cat_level'];
                    $cat_children_arr = array();
                    if (!empty($looping_array['cat_children']) && $looping_array['cat_children'] != null) {
                        $cat_children_arr[] = $looping_array['cat_children'];
                    }
                    $cat_list[$looping_array['root_parent_id']]['children'] = array_values($cat_children_arr);
                    unset($cat_children_arr);
                }
            }
        } else {
            $flag_arr[] = 0;
        }

        $main_content['Result'] = 'Categories Found';
        if (count($cat_list) > 0) {
            $flag_arr[] = 1;
            $main_content['Category'] = array_values($cat_list);
        } else {
            $main_content['Category'] = $cat_list;
            $flag_arr[] = 0;
        }
        $main_content['flag'] = $flag_arr;
        $main_content['Product'] = $item_send_array;
        $main_content['Totalcount'] = $master_data_count;
        //echo json_encode($main_content);

        return $main_content;
    }

    function productDetailSolr() {
        $obj = new GmaxSearch();
        $params = array();
        $params['storeId'] = $this->storeId;
        if (isset($this->GET['pro_id']) && !empty($this->GET['pro_id'])) {
            $params['item_ids'] = $this->GET['pro_id'];
        }
        $sdearchdata = $obj->getResults($params);
        $result = (array) json_decode($sdearchdata);
        $flag_arr = array();
        $master_data = $result['data'];
        $master_data_limit_count = count($master_data);
        $master_data_count = $result['count'];
        $item_send_array = array();
        $category_array = array();
        $cat_list = array();
        if ($master_data_count > 0) {
            $flag = 1;
            $flag_arr = $flag;
            foreach ($master_data as $looping_array) { //echo "<pre>"; print_r($looping_array);
                //echo $master_data_limit_count;
                if (empty($looping_array->promotion_level) && ($looping_array->promotion_level == '')) {
                    $promotion = null;
                } else {
                    $promotion = $looping_array->promotion_level;
                }
                $item_send_array['product_name'] = $looping_array->name;
                $item_send_array['p_brand'] = $looping_array->product_name_line_1;
                $item_send_array['p_name'] = $looping_array->product_name_line_2;
                $item_send_array['p_pack'] = $looping_array->product_name_line_3;
                $item_send_array['Status'] = ($looping_array->web_qty > 0) ? 'In stock' : 'Sold Out';
                $item_send_array['is_in_stock'] = $looping_array->is_in_stock;
                $item_send_array['product_id'] = $looping_array->entity_type_id;
                $item_send_array['categoryid'] = $looping_array->category_ids;
                $item_send_array['product_price'] = $looping_array->price;
                $item_send_array['sale_price'] = $looping_array->special_price;
                if ($looping_array->small_image != '') {
                    $item_send_array['product_thumbnail'] = $looping_array->image;
                } else {
                    $item_send_array['product_thumbnail'] = '';
                }
                $item_send_array['product_qty'] = $looping_array->web_qty;
                $item_send_array['promotion_level'] = $promotion;
                $item_send_array['rank'] = $looping_array->rank;
                $item_send_array['currencycode'] = "\u20b9";
                $item_send_array['product_description'] = $looping_array->description;
            }
        } else {
            $flag_arr = 0;
        }
        $main_content['Result'] = 'Product detail Found';
        $main_content['flag'] = $flag_arr;
        $main_content['Product_Detail'] = array($item_send_array);
        //$main_content['Totalcount'] = $master_data_count;
        $this->sendJsonResponse($main_content);
        exit();
    }

    function productListAllSolr() {
        $obj = new GmaxSearch();
        $params = array();
        $params['storeId'] = $this->storeId;
        if (isset($this->GET['cat_id']) && !empty($this->GET['cat_id'])) {
            $params['cat_id'] = intval($this->GET['cat_id']);
        }
        $params['is_with_facet'] = 'yes';
        $sdearchdata = $obj->getResults($params); 
        $result = (array) json_decode($sdearchdata);
        $master_data_count = $result['count'];
        $catcountlist = array();
        
        $temp_product_list = array();
        $cat_list = array();
        if ($master_data_count > 0) {
            
            foreach ($result['facet_data']->category_ids as $catname => $catcount) {
                $catcountlist[$catname] = $catcount;
            }
            
            $cat_list['Result'] = 'Products list found';
            $cat_list['Breadcrumb'] = array();
            $master_data = $result['data'];
            $master_data_limit_count = count($master_data);
            $category_send_array = array();
            $category_array = array();
            $hotProducts = array();
            for ($i = 0; $i < $master_data_limit_count; $i++) {

                $prodyctlist = array();
                $mlooping_array = (array) $master_data[$i];
                $item_send_array = array();
                $item_send_array['productid'] = $mlooping_array['entity_type_id'];
                $item_send_array['Name'] = $mlooping_array['name'];
                $item_send_array['p_brand'] = $mlooping_array['product_name_line_1'];
                $item_send_array['p_name'] = $mlooping_array['product_name_line_2'];
                $item_send_array['p_pack'] = $mlooping_array['product_name_line_3'];
                $item_send_array['Status'] = ($mlooping_array['web_qty'] > 0) ? 'In stock' : 'Sold Out';
                $item_send_array['is_in_stock'] = $mlooping_array['is_in_stock'];
                $item_send_array['Price'] = $mlooping_array['price'];
                $item_send_array['sale_price'] = $mlooping_array['special_price'];
                $item_send_array['discount'] = $mlooping_array['price']-$mlooping_array['special_price'];
                if ($mlooping_array['small_image'] != '') {
                    $item_send_array['Image'] = $mlooping_array['small_image'];
                } else {
                    $item_send_array['Image'] = '';
                }

                $item_send_array['webqty'] = $mlooping_array['web_qty'];
                $item_send_array['rank'] = ($mlooping_array['web_qty'] > 0) ? $mlooping_array['rank'] : '0';
                $item_send_array['currencycode'] = '₹';
                if (empty($mlooping_array['promotion_level']) && ($mlooping_array['promotion_level'] == '')) {
                    $promotion = null;
                    $item_send_array['promotion_level'] = $promotion;
                } else {
                    $promotion = $mlooping_array['promotion_level'];
                    $item_send_array['promotion_level'] = $promotion;
                    $hotProducts[] = $item_send_array;
                }
                if (array_key_exists($mlooping_array['category_ids'], $category_send_array)) {
                    $temp_product_list[$mlooping_array['category_ids']][] = $item_send_array;
                } else {
                    $category_send_array[$mlooping_array['category_ids']] = $mlooping_array['category_ids'];
                    $category_array[$mlooping_array['category_ids']]['Totalcount'] = $catcountlist[$mlooping_array['category_ids']];
                    $category_array[$mlooping_array['category_ids']]['category_id'] = $mlooping_array['category_ids'];
                    $category_array[$mlooping_array['category_ids']]['cat_name'] = $mlooping_array['cat_name'];
                    $category_array[$mlooping_array['category_ids']]['product'] = $master_data[$i];
                    $temp_product_list[$mlooping_array['category_ids']][] = $item_send_array;
                }
            }

            if (!empty($category_array)) {
                if (!empty($hotProducts)){
                    $cat_list_list = array();
                    $cat_list_list['category_id'] = 0;
                    $cat_list_list['Totalcount'] = "1"; //$category_array[$catkey]['Totalcount'];
                    $cat_list_list['category_name'] = "Hot Offer";
                    $cat_list_list['product'] = $hotProducts;
                    $cat_list['ProductList'][] = $cat_list_list;
                }

                foreach ($category_array as $catkey => $catdetails) {
                    $cat_list_list = array();
                    $cat_list_list['category_id'] = $category_array[$catkey]['category_id'];
                    $cat_list_list['Totalcount'] = "1"; //$category_array[$catkey]['Totalcount'];
                    $cat_list_list['category_name'] = $category_array[$catkey]['cat_name'];
                    $cat_list_list['product'] = $temp_product_list[$catkey];
                    $cat_list['ProductList'][] = $cat_list_list;
                }
            }
            $cat_list['flag'] = 1;
        } else {
            $cat_list['Result'] = 'No response!!';
            $cat_list['flag'] = 0;
            $cat_list['hotproduct'] = array();
        }

        //echo json_encode($cat_list);
        $this->sendJsonResponse($cat_list);
        exit();
    }
    

    function specialDealSolr($keyword,$val,$dealPageImage,$dealName){
        //$keyword = isset($this->GET['sku']) ? $this->GET['sku'] : '';
        $p = isset($this->GET['page']) ? $this->GET['page'] : '';
        $limit = 100;
        if ($p == 1 || $p == '' || $p == 0) {
            $p = 1;
        }
        $obj = new GmaxSearch();
        $sdearchdata = $obj->getResults(array('search' => urlencode($keyword), 'storeId' => $this->storeId));
        if(count($val)==1){
            $sdearchdata = $obj->getResults(array('search' => urlencode($val), 'storeId' => $this->storeId));
        }
        $result = (array) json_decode($sdearchdata);
        
        $flag_arr = array();
        if ($result['count'] <= 0) {
            $this->response = array("Result" => "No Result Found", "Product" => array(), "flag" => 0);
            $this->sendJsonResponse($this->response);
            exit();
        }
        $master_data = $result['data'];
        $master_data_limit_count = count($master_data);
        $master_data_count = $result['count'];
        if ($master_data_count <= 0) {
            $this->response = array("Result" => "No Result Found", "Product" => array(), "flag" => 0);
            $this->sendJsonResponse($this->response);
            exit();
        }
        $item_send_array = array();
        $category_array = array();
        $cat_list = array();
        if ($master_data_count > 0) {
            $flag = 1;
            
            for ($i = 0; $i < $master_data_limit_count; $i++) {
                $category_send_array = array();
                $looping_array = (array) $master_data[$i];
                //if($looping_array['web_qty']>0){ $status = 'In stock';}else{$status = 'Sold Out';}
                $category_array[$i] = $looping_array['category_ids'];
                $item_send_array['items'][$i]['Name'] = $looping_array['name'];
                $item_send_array['items'][$i]['p_brand'] = $looping_array['product_name_line_1'];
                $item_send_array['items'][$i]['p_name'] = $looping_array['product_name_line_2'];
                $item_send_array['items'][$i]['p_pack'] = $looping_array['product_name_line_3'];
                $item_send_array['items'][$i]['Status'] = ($looping_array['web_qty'] > 0) ? 'In stock' : 'Sold Out';
                $item_send_array['items'][$i]['productid'] = $looping_array['entity_type_id'];
                $category_send_array[] = $looping_array['category_ids'];
                $item_send_array['items'][$i]['categoryid'] = $category_send_array;
                $item_send_array['items'][$i]['Price'] = $looping_array['price'];
                $item_send_array['items'][$i]['sale_price'] = $looping_array['special_price'];
                $item_send_array['items'][$i]['discount'] = $looping_array['price']-$looping_array['special_price'];
                if ($looping_array['small_image'] != '') {
                    $item_send_array['items'][$i]['Image'] = $looping_array['small_image'];
                } else {
                    $item_send_array['items'][$i]['Image'] = '';
                }
                if (empty($looping_array['promotion_level']) && ($looping_array['promotion_level'] == '')) {
                    $promotion = null;
                } else {
                    $promotion = $looping_array['promotion_level'];
                }
                $item_send_array['items'][$i]['webqty'] = $looping_array['web_qty'];
                $item_send_array['items'][$i]['promotion_level'] = $promotion;
                $item_send_array['items'][$i]['rank'] = $looping_array['rank'];
                $item_send_array['items'][$i]['currencycode'] = '₹';
            }
            $this->response = array("Result" => "Products found.","Breadcrumb"=>array(), "dealPageImage"=>$dealPageImage,"dealName"=>$dealName, "Product" => $item_send_array, "flag" => 1);
            $this->sendJsonResponse($this->response);
            exit();
        } else {
            $this->response = array("Result" => "Products found.","Breadcrumb"=>array(), "dealPageImage"=>$dealPageImage,"dealName"=>$dealName, "Product" => $productlist, "flag" => 0);
            $this->sendJsonResponse($this->response);
            exit();
        }

        
        $main_content['flag'] = $flag_arr;
        $main_content['Product'] = $item_send_array;
        $main_content['Totalcount'] = $master_data_count;
        //echo json_encode($main_content);

        $this->response = array("Result" => "Products found.","Breadcrumb"=>array(),"dealPageImage"=>$dealPageImage,"dealName"=>$dealName, "Product" => $productlist, "flag" => 1);
        $this->sendJsonResponse($this->response);
        exit();
    }
    
    function rootCategoryProductSolr(){
            if (isset($_GET['cat_id']) && !empty($_GET['cat_id'])) {
                $params['category_id'] = intval($_GET['cat_id']);
            }
            if (isset($_GET['limit']) && !empty($_GET['limit'])) {
                $params['limit'] = $_GET['limit'];
            }else{
                    $params['limit'] =50;
            }
            if (isset($_GET['storeId']) && !empty($_GET['storeId'])) {
                $params['storeId'] = intval($_GET['storeId']);
            }else{
                $params['storeId'] =1;
            }
            $obj = new GmaxSearch();
            $sdearchdata = $obj->getResults($params);
            $result = (array) json_decode($sdearchdata);

            $master_data_count = $result['count'];
            $temp_product_list=array();
            $cat_list = array();
            if ($master_data_count > 0) {
                $cat_list['Result'] = 'Products found';
                $cat_list['Breadcrumb'] = array();
                $master_data = $result['data'];
                $master_data_limit_count = count($master_data);
                $category_send_array = array();
                $category_array = array();

                for ($i = 0; $i < $master_data_limit_count; $i++) {
                    $item_send_array = array();
                    $prodyctlist = array();
                    $mlooping_array = (array) $master_data[$i];

                    $item_send_array['productid'] = $mlooping_array['entity_type_id'];
                    $item_send_array['Name'] = $mlooping_array['name'];
                    $item_send_array['p_brand'] = $mlooping_array['product_name_line_1'];
                    $item_send_array['p_name'] = $mlooping_array['product_name_line_2'];
                    $item_send_array['p_pack'] = $mlooping_array['product_name_line_3'];
                    $item_send_array['Status'] = ($mlooping_array['web_qty'] > 0) ? 'In stock' : 'Sold Out';
                    $item_send_array['is_in_stock'] = $mlooping_array['is_in_stock'];
                    $item_send_array['Price'] = $mlooping_array['price'];
                    $item_send_array['sale_price'] = $mlooping_array['special_price'];
                    $item_send_array['discount'] = $mlooping_array['price']-$mlooping_array['special_price'];
                    if ($mlooping_array['small_image'] != '') {
                        $item_send_array['Image'] = $mlooping_array['small_image'];
                    } else {
                        $item_send_array['Image'] = '';
                    }
                    if (empty($mlooping_array['promotion_level']) && ($mlooping_array['promotion_level'] == '')) {
                    $promotion = null;
                    } else {
                        $promotion = $mlooping_array['promotion_level'];
                    }

                    $item_send_array['webqty'] = $mlooping_array['web_qty'];
                    $item_send_array['promotion_level'] = $promotion;
                    $item_send_array['rank'] = $mlooping_array['rank'];
                    $item_send_array['currencycode'] = '₹';

                    $cat_list['Product'][] = $item_send_array;
                }
                $cat_list['Totalcount'] = $master_data_count;
                $cat_list['flag'] = 1;
            } else {
                $cat_list['Result'] = 'No response!!';
                $cat_list['flag'] = 0;
                $cat_list['hotproduct'] = array();
            }

        $this->sendJsonResponse($cat_list);
        exit();
    }
    
    function trendingSolr(){
        
        $obj = new TrendingAutosuggest();
        $params = array();
        if (!isset($_GET['q'])){$_GET['q'] ='';}
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
        return array("Result" => $sdearchdata,  "flag" => 1);
        //$this->sendJsonResponse($this->response);
       // exit();
        
        
    }
            
    function logWrite($caller = "", $parameter = "") {
        if ($this->writeLog) {
            $date = date('d.m.Y h:i:s');
            $this->end_time = microtime(TRUE);
            $time_taken = $this->end_time - $this->start_time;
            $log = "Date:  " . $date . " | Caller:" . $caller . " | Request Parms:" . $this->requestParm . "  |  Response:  " . $parameter . " | Take Time:" . $time_taken . "\n";
            error_log($log, 3, $this->dirFile);
        }
    }

    function errorLogWrite() {
        $msg = $this->GET['error'];
        $parameter = "";
        $date = date('d.m.Y h:i:s');
        $log = $msg . "   |  Date:  " . $date . "  |  Parameters:  " . $parameter . "\n";
        error_log($log, 3, $this->dirFile);
    }

    function sendJsonResponse($response) {
        // log before sending response to client
        $caller = '';
        @$trace = debug_backtrace();
        @$caller = $trace[1]['function'];
        $this->logWrite($caller, json_encode($response));
        echo json_encode($response);
        exit;
    }

    function sendPreJsonResponse($response) {
        // log before sending response to client
        $caller = '';
        @$trace = debug_backtrace();
        @$caller = $trace[1]['function'];
        $this->logWrite($caller, json_encode($response));
        echo json_encode($response);
    }

    function updateNotification(){
        $key = "notificationid";
        if( array_key_exists($key, $this->GET) && !empty($this->GET[$key]) ){
            // update notification opened
            // add it to resque queue
            if( $this->device == 'ios'){
                $device = 1;
            }elseif($this->device == 'android'){
                $device = 0;
            }
            @exec("/usr/bin/php ".dirname(__FILE__) . '/../shell/update_notification.php' . "  --notificationid ".$this->GET[$key]." --devicetype $device > /dev/null &");
        }
    }

    function createAjaxResponse($searchResult){
        // use $searchResult and form ajax search result similar to the one written in Mageworks extension
        //echo '<pre>'; print_r($searchResult['Product']); exit;
        $i=1;
        $row=array();
        $formKey = $_GET['key'];
        $row['content'] ='<div class="searchautocomplete-container">
<div id="close" onclick="closeFunction()"  style=" background:#db5433; border-radius:50%; text-align:center; float:right; color:#FFF; font-size:15px; width:26px; font-weight:bold;height:26px;  cursor:pointer;margin-top:5px; ">x</div>
<div class="searchautocomplete-search" id="searchautocomplete-search-1">
    <div class="search-results">
        <h3 class="search-header">Products</h3>
        <div class="search-container">';
        
        foreach($searchResult['Product'] as $product){
            
           $row['content'].= '<div class="s_item">
                <div class="s_icon 2">
                    <a href="'.$product['url_path'].'">  
                        <img id="image" src="'.$product["cart_image"].'" alt="" title="" />
                    </a>
                </div>
                <div class="s_details">
                    <div class="pname">
                        <a href="'.$product['url_path'].'">
                            <div class="s_item_name">
                             <span class="s_brand">'.$product["p_brand"].'</span>
                             <span class="s_name">'.$product["p_name"]." ".$product["p_pack"].'</span>
                            </div>
                        </a>
                    </div>
                    <div class="s_price">
                        <div class="price-box">
                            <span class="old-price 2">
                                <span class="price" id="old-price-'.$product["productid"].'">
                                    ₹ '. $product["Price"].'</span>
                            </span>
                            <span class="special-price 2">
                                <span class="special-price" id="product-price-'.$product["productid"].'">
                                    <span class="price">₹ '.$product["sale_price"].'</span>
                                </span>
                            </span>
                            
                        </div>
                    </div>';
                   if($product["webqty"]>0){
                        $row['content'].='
                    <div class="s_button">
                    <div class="qty-set">
                        <span class="quantity-box">
                            <input type="text" name="qty" maxlength="12" value="1" title="Qty" class="quantity-input qty pd-'.$i.'" onclick="">
                            <input type="button" value="'.$i.'" onclick="" class="quantity-controls quantity-plus btnPlus">
                            <input type="button" value="'.$i.'" onclick="" class="quantity-controls quantity-minus">
                        </span>
                    </div><button id="'.$this->webUrl.'/checkout/cart/add/product/'.$product["productid"].'/form_key/'.$formKey.'/" onclick="" class="button btn-cart btn-cartn s_button_button addc-'.$i.'" title="Add to Cart" name="'.$product["Name"].'"  pid="'.$product["productid"].'" type="button" rel="'.$i.'" >
                            <span class="s_button_span1"><span class="s_button_span2">Add to Cart</span></span>
                        </button></div>';
                    }else{
                        $row['content'] .='Sold Out';
                    }
                    $row['content'] .='</div>
                </div>'; 
           $i++;
         
        }
    $keyword = isset($this->GET['q']) ? $this->GET['q'] : '';
    if( $i && $keyword){
        $this->incrementHashData($this->trendingHashKey, $keyword, 1);
    }

    $row['content'].='</div>';
    if($i >= 20){
        $row['content'].='<div class="resultbox-b">
            <a href="/catalogsearch/result/?q='.$this->GET['q'].'&amp;qty=1&amp;order=name" class="search-more">More results</a>
        </div>';
    }
   $row['content'].='</div>
</div>
</div>
<script>
    function closeFunction() {    document.getElementById("search_autocomplete").style.display = "none"; }
</script>';
        $row['callback']="";
        $this->sendJsonResponse($row);
        exit();

    }

}
