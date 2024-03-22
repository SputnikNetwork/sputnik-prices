<?php
function getPage($url, $proxy = null) {
  $cache_url = 'caches/' . urlencode(basename($url));
  if (strpos($url, 'coingecko') !== false) {
      $cache_url = 'caches/coingecko';
  } else if (strpos($url, "api.etherscan.io/api?module=account&action=balance") !== false) {
      $params = explode("&address=", $url)[1];
      $address = explode("&tag=latest", $params)[0];
      $cache_url = 'caches/balance_' . $address;
  } else if (strpos($url, "api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x446e028f972306b5a2c36e81d3d088af260132b3&address=") !== false) {
      $params = explode("&address=", $url)[1];
      $address = explode("&tag=latest", $params)[0];
      $cache_url = 'caches/patom_' . $address;
  } else if (strpos($url, "api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x44017598f2af1bd733f9d87b5017b4e7c1b28dde&address=") !== false) {
      $params = explode("&address=", $url)[1];
      $address = explode("&tag=latest", $params)[0];
      $cache_url = 'caches/stkatom_' . $address;
  }

  $cache_file = $cache_url . '.cache';

  if (file_exists($cache_file) && time() - filemtime($cache_file) <= 60) {
      // Cache is still fresh
      $cache = file_get_contents($cache_file);
  } else {
      // Cache is stale or doesn't exist
      $ch = curl_init($url);

      if ($proxy !== null) {
          // If proxy is provided, configure cURL to use proxy
          curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
          curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);

          if (isset($proxy['login']) && isset($proxy['password'])) {
              // If proxy requires authentication, provide credentials
              curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['login'] . ':' . $proxy['password']);
          }
      }

      // Устанавливаем заголовки, эмулирующие запрос от браузера Mozilla Firefox
      $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0';
      curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
          'Accept-Language: en-US,en;q=0.5',
          'Connection: keep-alive',
          'Upgrade-Insecure-Requests: 1',
          'Pragma: no-cache',
          'Cache-Control: no-cache'
      ));

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $cache = curl_exec($ch);

      if (curl_errno($ch)) {
          // Handle curl error
          $cache = false;
      } else {
          // Save content to cache file
          file_put_contents($cache_file, $cache);
      }

      curl_close($ch);
  }

  // Decode JSON and return result
  return json_decode($cache, true);
}

function sortPricesById($prices) {
  usort($prices, function($a, $b) {
      return strcmp($a['id'], $b['id']);
  });
  return $prices;
}

function searchToken($id, $prices) {
  $res = -1;
  foreach ($prices as $key => $token) {
if ($token['id'] === $id) $res = $key;
  }
  return $res;
}

function adaptiveFixed($num, $needNonZero) {
  if ($num > 1 || $num < -1) {
      return floatval(number_format($num, $needNonZero, '.', ''));
  }
  
  $res = intval($num);
  $frac = abs($num - $res);
  
  if ($frac === 0) {
      return $res;
  }
  
  $res .= '.';
  $numNonZero = 0;
  
  while ($frac !== 0 && $numNonZero < $needNonZero) {
      $frac *= 10;
      $cur = floor($frac);
      $res .= $cur;
      $frac -= $cur;
      
      if ($cur != 0) {
          $numNonZero++;
      }
  }
  
  if ($num < 0) {
      $res *= -1;
  }
  
  return $res;
}
?>