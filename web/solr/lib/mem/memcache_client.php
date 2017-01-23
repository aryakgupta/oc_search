<?php
class memcacheClient {
    var $iTtl = 1800; // Time To Live
    var $bEnabled = false; // Memcache enabled?
    var $oCache = null;

    // constructor
    function memcacheClient($mem_server='127.0.0.1', $port=11211, $timeout=1800) {
        if (class_exists('Memcached')) {
            $this->oCache = new Memcached();
            $this->bEnabled = true;
            $this->iTtl = $timeout;

            if (! $this->oCache->addServer($mem_server, $port))  { // Instead 'localhost' here can be IP
                $this->oCache = null;
                $this->bEnabled = false;
            }
        }
    }
 
    // get data from cache server
    function get($sKey) {
        $vData = $this->oCache->get($sKey);
//        print_r($this->oCache->getAllKeys());
//        echo $sKey.$vData;die;
        return false === $vData ? null : $vData;
    }

    // save data to cache server
    function set($sKey, $vData) {
        //Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
        return $this->oCache->set($sKey, $vData, $this->iTtl);
    }

    // delete data from cache server
    function del($sKey) {
        return $this->oCache->delete($sKey);
    }
}