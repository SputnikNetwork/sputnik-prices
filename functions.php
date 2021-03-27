<?php
function getPage($url) {
    $cache_file = '.cache';
    if(file_exists($cache_file)) {
      if(time() - filemtime($cache_file) > 60) {
         // too old , re-fetch
         $cache = file_get_contents($url);
         file_put_contents($cache_file, $cache);
      } else {
        $cache = file_get_contents('.cache');
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