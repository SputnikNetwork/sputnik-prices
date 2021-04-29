<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$api_key = $conf['api_key'];
$tokens = str_replace(",", "%2C", $conf['tokens']);
$prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$tokens.'&order=id_asc&per_page=250&page=1&sparkline=false&price_change_percentage=24h');

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
 
include_once ('telegramgclass.php');   

$tg = new tg($api_key);
$sended = [];
$chat_id;
$text;
if (isset($arr['message']['chat']['id'])) $chat_id = $arr['message']['chat']['id'];
if (isset($arr['callback_query']['message']['chat']['id'])) $chat_id = $arr['callback_query']['message']['chat']['id'];
    if (isset($arr['callback_query']['data'])) $text = $arr['callback_query']['data'];
if (isset($arr['message']['text'])) $text = $arr['message']['text'];
    $tokens_array = explode(",", $conf['tokens']);
    $action = mb_substr($text, 1);
    $action = explode("@", $action)[0];
    $search_token = array_search($action, $tokens_array);
    if ($text && $text === '/start' || $text && strpos($text, '/start') !== false) {
    $msg = $conf['home_text'];
    $arInfo["inline_keyboard"] = [];
    $tokens_count = count($tokens_array);
$row = 0;
    $b_counter = 1;
foreach ($prices as $token) {
    $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/'.$token['id'];
    $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = strtoupper($token['symbol']);
    if ($b_counter % 3 == 0) {
        $row++;
        $b_counter = 1;
    } else {
        $b_counter++;
    }
}
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/help' || $text && strpos($text, '/help') !== false) {
    $msg = $conf['help_text'];
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Home';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($search_token >= 0) {
    if (isset($prices) && count($prices) > 0) {
        $token = $prices[$search_token];
        $token_price = $token['current_price'];
    $msg = strtoupper($token['symbol']).'/USD: '.$token_price.'
24 hours price change: '.$token['price_change_percentage_24h'].'%
';
$tokens_sites = explode(",", $conf['tokens_sites']);
$arInfo["inline_keyboard"] = [];
$arInfo["inline_keyboard"][0][0]["url"] = $tokens_sites[$search_token];
$arInfo["inline_keyboard"][0][0]["text"] = "Url";
$arInfo["inline_keyboard"][0][1]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][1]["text"] = "Help";
$arInfo["inline_keyboard"][0][2]["url"] = 'https://t.me/SputnikNetworkBot';
$arInfo["inline_keyboard"][0][2]["text"] = "Sputnik";
$tokens_count = count($tokens_array);
$row = 1;
$b_counter = 1;
foreach ($prices as $ot) {
    if ($ot['id'] !== $action) {
        $ot_price = $ot['current_price'];
    $result_price = round($token_price / $ot_price, 4);
        $msg .= '
    '.strtoupper($token['symbol']).'/'.strtoupper($ot['symbol']).': '.$result_price;
    } // end if symbol === text.

        $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/'.$ot['id'];
    $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = strtoupper($ot['symbol']);
    if ($b_counter % 3 == 0) {
        $row++;
        $b_counter = 1;
    } else {
        $b_counter++;
    }
} // end foreach.
$sended = $tg->photo($chat_id, $msg, 0, $arInfo, __DIR__.'/images/'.mb_strtolower($token['symbol']).'.png');
} // end if prices.
} // end if

$chats_json = file_get_contents('chats.json');
$chats = json_decode($chats_json, true); 
if ($chats && count($chats) > 0 && isset($chats[$chat_id])) {
$msg_id = $chats[$chat_id];
$tg->delete($chat_id, $msg_id);
}
$chats[$chat_id] = $sended['result']['message_id'];
file_put_contents('chats.json', json_encode($chats));
?>