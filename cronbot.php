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

$gas_res = getPage('https://api.etherscan.io/api?module=gastracker&action=gasoracle');
$gasprice = (float)$gas_res['result']['ProposeGasPrice'];

$chats_json = file_get_contents('chats.json');
$chats = json_decode($chats_json, true); 
if ($chats && count($chats) > 0) {
foreach($chats as $id => $options) {
    if (isset($options['gas'])) {
        $gases = explode('-', $options['gas']);
        if (!isset($gases[1]) && (int)$options['gas'] === $gasprice || isset($gases[1]) && $gasprice >= (float)$gases[0] && $gasprice <= (float)$gases[1]) {
            $msg = 'Now Ethereum GAS is '.$gasprice.' gwei';
            $arInfo = [];
            $tg->send($id, $msg, 0, $arInfo);
        }
    }
}
}