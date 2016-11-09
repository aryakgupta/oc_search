<?php
define('SOLRSERVERIP', '52.74.73.206'); 
define('SOLRPORT', '8084');
define('PRODICTSEARCH', 'solr/products_details'); 
define('PRODICTAUTOSEARCH', 'solr/autosuggest'); 
define('TRENDINGSEARCH', 'solr/trendingsearch');
//define('DOCUMENT_ROOT',dirname(dirname(__FILE__)).'/search');
define('DOCUMENT_ROOT','/mnt/trendybharat/public_html/solr');
/*<auto_including_class_files>*/
	function __autoload($className)
	{		
		if ( file_exists(DOCUMENT_ROOT . '/classes/' . $className . '.class.php') )
		{
			require_once(DOCUMENT_ROOT . '/classes/' . $className . '.class.php');
		}
	}
/*</auto_including_class_files>*/


?>
