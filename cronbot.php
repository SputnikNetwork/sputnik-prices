<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$api_key = $conf['api_key'];

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
include_once ('telegramgclass.php');   

$tg = new tg($api_key);

$gas_res = getPage('https://ethgasstation.info/api/ethgasAPI.json');
$gasprice = $gas_res['average'];

$chats_json = file_get_contents('chats.json');
$chats = json_decode($chats_json, true); 
if ($chats && count($chats) > 0) {
foreach($chats as $id => $options) {
    if (isset($options['gas']) && (int)$options['gas'] === $gasprice) {
        $msg = 'Now Ethereum GAS is '.$gasprice.' gwei';
        $arInfo = [];
        $tg->send($id, $msg, 0, $arInfo);
    }
}
}