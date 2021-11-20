<?php
function getPage($url) {
  $cache_url = 'caches/'.urlencode(basename($url));
  if (strpos($url, 'coingecko') !== false) {
    $cache_url = 'caches/coingecko';
  } else if (strpos($url, "api.etherscan.io/api?module=account&action=balance") !== false) {
$params = explode("&address=", $action)[1];
$address = explode("&tag=latest", $params)[0];
$cache_url = 'caches/balance_' + $address;
} else if (strpos($url, "api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x446e028f972306b5a2c36e81d3d088af260132b3&address=") !== false) {
  $params = explode("&address=", $action)[1];
  $address = explode("&tag=latest", $params)[0];
  $cache_url = 'caches/patom_' + $address;
} else if (strpos($url, "api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x44017598f2af1bd733f9d87b5017b4e7c1b28dde&address=") !== false) {
  $params = explode("&address=", $action)[1];
  $address = explode("&tag=latest", $params)[0];
  $cache_url = 'caches/stkatom_' + $address;
}
  $cache_file = $cache_url.'.cache';
    if(file_exists($cache_file)) {
      if(time() - filemtime($cache_file) > 60) {
         // too old , re-fetch
         $cache = file_get_contents($url);
         file_put_contents($cache_file, $cache);
      } else {
        $cache = file_get_contents($cache_url.'.cache');
      }
    } else {
      // no cache, create one
      $cache = file_get_contents($url);
      file_put_contents($cache_file, $cache);
    }
$res = json_decode($cache, true);
return $res;
}
?>