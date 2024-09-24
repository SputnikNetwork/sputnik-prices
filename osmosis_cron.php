<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$tokens = str_replace(",", "%2C", $conf['tokens']);

  $all_tokens = getPage('https://api-osmosis.imperator.co/tokens/v2/all');
  $cache_file = 'caches/osmosis.cache';
  if (file_exists($cache_file) && time() - filemtime($cache_file) <= 60) {
    error_log('Cache is still fresh');
  } else {
    error_log('Cache is stale or does not exist');
    file_put_contents($cache_file, json_encode($all_tokens));
  }