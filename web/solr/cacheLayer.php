<?php
/**
 * Description of cache layer
 *
 * @author Pradeep Gupta
 * This class is created to use primery cache to store and retrive objects
 * It supports memcache and redis as primery cache.. other cache systems can be added in future
 * without any cahnge in invoking mechanism
 */
require_once dirname(__FILE__) . '/redisConfig.php';
require_once dirname(__FILE__) . '/lib/mem/memcache_client.php';
require_once dirname(__FILE__) . '/lib/Credis/Client.php';


class cacheFactory{

    public static function create_cache($cacheEngine='cacheRedis'){
        try{
            if (class_exists($cacheEngine)){
                return new $cacheEngine();
            }else{
                return false;
            }
        }catch(Exception $e){
            return false;
        }
    }
}


abstract class abstractCache{
    public $cacheObj=null;
    //abstract function getCacheData();
    abstract function setCacheData($key, $data, $ttl);

    function getCacheServer(){
        $this->key_prefix = explode('.', $_SERVER['HTTP_HOST'])[0];
        $this->cacheServer = 'localhost';
        if (REDISSERVERIP) {
            $this->cacheServer = REDISSERVERIP;
        }

        return $this->cacheServer;
    }

    function getCacheData($key){
        try{
            //print_r($this->cacheObj->get($key));
            return $this->cacheObj->get($key);
        }catch(Exception $e){
            //echo $e->getMessage();
            return false;
        }
    }
}


class cacheMem extends abstractCache{

    function __construct(){
        $this->cacheServer = $this->getCacheServer();
        $this->cachePort = 11211;
        $this->connectionTimeout = 2;
        $this->cacheTimeout = 1800;  // 30 Minutes
        $this->cacheObj = new memcacheClient($this->cacheServer, $this->cachePort, $this->cacheTimeout);
    }

    function setCacheData($key, $data, $ttl=false){
        try{
            $this->cacheObj->iTtl = $ttl === false ? $this->cacheObj->iTtl : $ttl;
            $this->cacheObj->set($key, $data);
        }catch(Exception $e){
            // do nothing   .. we can setup log here or send notification to admin
            //echo $e->getMessage();
        }

    }
}


class cacheRedis extends abstractCache{

    function __construct(){
        $this->cacheServer = $this->getCacheServer();
        $this->cachePort = 6379;
        if(REDISPORT) {
            $this->cachePort = REDISPORT;   //staging and other server
        }
        $this->connectionTimeout = 2;
        $this->cacheTimeout = 1800;  // 30 Minutes
        try{
            $this->cacheObj = new Credis_Client($this->cacheServer, $this->cachePort, $this->connectionTimeout);
        }catch(exception $e){
            $this->cacheObj = false;
            //echo $e->getMessage(); die;
        }
    }

    function setCacheData($key, $data, $ttl=false){
        // var_dump($ttl);
        try{
            if($ttl !== false) {
                $this->cacheTimeout = $ttl;
            }
            //die("didnt matched.".$this->cacheTimeout);
            $this->cacheObj->set($key, $data, $this->cacheTimeout);
        }catch(Exception $e){
            // do nothing   .. we can setup log here or send notification to admin
            //echo $e->getMessage();
        }
    }

    function getHashData($hashKey, $key, $ttl=false){
        try{
            return $this->cacheObj->hGet($hashKey, $key);
        }catch(Exception $e){
            //echo $e->getMessage(); die;
            return false;
        }
    }

    function setHashData($hashKey, $key, $data, $ttl=false){
        // var_dump($ttl);
        try{
            if($ttl !== false) {
                $this->cacheTimeout = $ttl;
            }
            $this->cacheObj->hSet($hashKey, $key, $data);
        }catch(Exception $e){
            // do nothing   .. we can setup log here or send notification to admin
            //echo $e->getMessage();
        }
    }

    function incrementHashData($hashKey, $key, $data=1, $ttl=false){
        // var_dump($ttl);
        try{
            if($ttl !== false) {
                $this->cacheTimeout = $ttl;
            }
            $this->cacheObj->HINCRBY($hashKey, $key, $data);
        }catch(Exception $e){
            // do nothing   .. we can setup log here or send notification to admin
            //echo $e->getMessage();
        }
    }

    function rPushListData($listKey, $data, $ttl=false){
        /*
        @listKey: list key
        @data: array of data to be pushed
        */
        try{
            if($ttl !== false) {
                $this->cacheTimeout = $ttl;
            }
            $this->cacheObj->rPush($listKey, $data[0]);
        }catch(Exception $e){
            // do nothing   .. we can setup log here or send notification to admin
            //echo $e->getMessage();
        }
    }

    function lPopList($key){
        try{
            return $this->cacheObj->lPop($key);
        }catch(Exception $e){
            //echo $e->getMessage(); die;
            return false;
        }
    }
}