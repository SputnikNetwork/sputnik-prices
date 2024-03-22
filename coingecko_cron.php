<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$tokens = str_replace(",", "%2C", $conf['tokens']);

$proxy = array(
    'host' => 'http://190.185.108.176',
    'port' => 9798,
    'login' => 'TK6rD8',
    'password' => 'vahX6X'
  );

  $all_tokens = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$tokens.'&order=id_asc&per_page=250&page=1&sparkline=false&price_change_percentage=24h', $proxy);
  $cache_url = 'caches/coingecko.cache';
  if (file_exists($cache_file) && time() - filemtime($cache_file) <= 60) {
    error_log('Cache is still fresh');
  } else {
    error_log('Cache is stale or does not exist');
  }