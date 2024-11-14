<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$tokens = str_replace(",", "%2C", $conf['tokens']);

$proxy = array(
  'host' => 'http://45.95.98.54',
  'port' => 8000,
  'login' => 'FSyqoA',
  'password' => 'W82wkp'
);

$all_tokens = getPage('https://data.osmosis.zone/tokens/v2/all', $proxy);
$all_tokens = array_filter($all_tokens, function ($token) {
  return isset($token['price']) && $token['price'] !== null && $token['price'] !== 'null';
});
$cache_file = 'caches/osmosis.cache';
  if (file_exists($cache_file) && time() - filemtime($cache_file) <= 60) {
    error_log('Cache is still fresh');
  } else {
    error_log('Cache is stale or does not exist');
    file_put_contents($cache_file, json_encode($all_tokens));
  }