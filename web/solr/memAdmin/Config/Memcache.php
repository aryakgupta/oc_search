<?php
require dirname(__FILE__).'/../../cacheLayer.php';

$objConfig = cacheFactory::create_cache('cacheMem');

return array (
  'stats_api' => 'Server',
  'slabs_api' => 'Server',
  'items_api' => 'Server',
  'get_api' => 'Server',
  'set_api' => 'Server',
  'delete_api' => 'Server',
  'flush_all_api' => 'Server',
  'connection_timeout' => '1',
  'max_item_dump' => '100',
  'refresh_rate' => 5,
  'memory_alert' => '80',
  'hit_rate_alert' => '90',
  'eviction_alert' => '0',
  'file_path' => 'Temp/',
  'servers' =>
  array (
    'Default' =>
    array (
      'prd-new-trendybharat.ys7b4b.cfg.aps1.cache.amazonaws.com:11211' =>
      array (
        'hostname' => 'prd-new-trendybharat.ys7b4b.cfg.aps1.cache.amazonaws.com',
        'port' => '11211',
      ),
    ),
  ),
);
