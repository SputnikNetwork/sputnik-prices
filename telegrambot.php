<?php
require_once('functions.php');
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$conf = Yaml::parseFile('config.yaml');
$api_key = $conf['api_key'];
$tokens = str_replace(",", "%2C", $conf['tokens']);

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
    $action = mb_substr($text, 1);
    $action = explode("@", $action)[0];
    $chats_projects = file_get_contents('chats_projects.json');
    $cp = json_decode($chats_projects, true); 
    if (isset($cp[$chat_id]) && $cp[$chat_id] !== '') {
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$cp[$chat_id].'&order=id_asc&per_page=250&page=1&sparkline=false&price_change_percentage=24h');
    $tokens_array = explode(",", $cp[$chat_id]);
} else {
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$tokens.'&order=id_asc&per_page=250&page=1&sparkline=false&price_change_percentage=24h');
    $tokens_array = explode(",", $conf['tokens']);
}
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
if ($arr['message']['chat']['id'] === $arr['message']['from']['id']) {
    $arInfo2["keyboard"][0][0]["text"] = "help";
    $arInfo2["keyboard"][0][1]["text"] = "filter the list";
    $arInfo2["keyboard"][0][2]["text"] = "My chats";
    $arInfo2["keyboard"][1][0]["text"] = "Check ETH address";
    $arInfo2["keyboard"][1][0]["text"] = "Set GAS notify";
    $tg->send($chat_id, "Main menu...", 0, $arInfo2);
    sleep(3);
}
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/help' || $text && strpos($text, 'help') !== false || $text && $text === 'help') {
    $msg = $conf['help_text'];
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Home';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/chats' || $text && strpos($text, '/chats') !== false || $text && $text === 'My chats' || $text && strpos($text, 'My chats') !== false) {
    $chats_users = file_get_contents('users.json');
    $cu = json_decode($chats_users, true);
$msg = 'Select chat with administration status.';

$arInfo["inline_keyboard"] = [];
$arInfo["inline_keyboard"][0][0]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][0]["text"] = "Help";
$arInfo["inline_keyboard"][0][1]["callback_data"] = '/start';
$arInfo["inline_keyboard"][0][1]["text"] = "Home";
$arInfo["inline_keyboard"][0][2]["url"] = 'https://t.me/SputnikNetworkBot';
$arInfo["inline_keyboard"][0][2]["text"] = "Sputnik";
$tokens_count = count($tokens_array);
$row = 1;
$b_counter = 1;
$user_chats = $cu[$chat_id];
foreach($user_chats as $chatId => $login) {
    $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/chat '.$chatId;
    $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = '@'.$login;
    if ($b_counter % 3 == 0) {
        $row++;
        $b_counter = 1;
    } else {
        $b_counter++;
    }
} // end foreach.
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if (strpos($action, 'chat ') !== false) {
$chatId = explode(' ', $action)[1];
$chats_users = file_get_contents('users.json');
$cu = json_decode($chats_users, true);
$user_chat = $cu[$chat_id][$chatId];
$chat_user = $tg->getChatMember($arr['message']['chat']['id'], $arr['message']['from']['id']);
if ($chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $arr['message']['chat']['id'] === $arr['message']['from']['id']) {
    $msg = 'Your chat is @'.$user_chat.' ('.$chatId.'). '.$conf['projects_text'].$conf['tokens'];
} else {
unset($cu[$chat_id][$chatId]);
$msg = 'You are no longer the administrator or owner of the chat @'.$user_chat;
}
$arInfo = [];
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/projects' || $text && strpos($text, '/projects') !== false || $text && $text === 'filter the list' || $text && strpos($text, 'filter the list') !== false) {
        $chat_user = $tg->getChatMember($arr['message']['chat']['id'], $arr['message']['from']['id']);
        if ($chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $arr['message']['chat']['id'] === $arr['message']['from']['id']) {
            if ($arr['message']['chat']['id'] !== $arr['message']['from']['id']) {
                $chats_users = file_get_contents('users.json');
                $cu = json_decode($chats_users, true);
                    $cu[$arr['message']['from']['id']][$arr['message']['chat']['id']] = $arr['message']['chat']['username'];
                                file_put_contents('users.json', json_encode($cu));
            }

                            $msg = $conf['projects_text'].$conf['tokens'];
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Home';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} else if (isset($arr['message']['reply_to_message']) && strpos($arr['message']['reply_to_message']['text'], $conf['projects_text'].$conf['tokens']) !== false) {
    $chatId = $arr['message']['chat']['id'];
    if (strpos($arr['message']['reply_to_message']['text'], '(-') !== false) {
        $id_str = explode('(', $arr['message']['reply_to_message']['text'])[1];
    $chatId = explode(').', $id_str)[0];
    }
    $chat_user = $tg->getChatMember($chatId, $arr['message']['from']['id']);
    if ($chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $chatId === $arr['message']['from']['id']) {
        if ($arr['message']['text'] === '0') {
            unset($cp[$chatId]);
        } else {
            $chat_prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$arr['message']['text'].'&order=id_asc&per_page=250&page=1&sparkline=false&price_change_percentage=24h');
            $new_prices = [];
            foreach ($chat_prices as $price) {
                array_push($new_prices, $price['id']);
}
$cp[$chatId] = implode(',', $new_prices);
        }
        file_put_contents('chats_projects.json', json_encode($cp));
        $msg = $conf['projects_added'];
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
        $arInfo["inline_keyboard"][0][0]['text'] = 'Home';
        $sended = $tg->send($chat_id, $msg, 0, $arInfo);
    }
} else if ($text && $text === '/send_address' || $text && strpos($text, 'Check ETH address') !== false || $text && $text === 'Check ETH address') {
    $msg = 'Send Ethereum address.';
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Home';
        $arInfo["inline_keyboard"][0][1]["callback_data"] = '/help';
    $arInfo["inline_keyboard"][0][1]['text'] = 'Help';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if (strpos($text, '0x') !== false) {
    $arInfo = [];
    $tg->send($chat_id, 'Please wait. The data is being loaded...', 0, $arInfo);
    $eth_res = getPage('https://api.etherscan.io/api?module=account&action=balance&address='.$text.'&tag=latest');
    $eth_balance = (float)$eth_res['result'] / (10 ** 18);
    sleep(1);
    $patom_res = getPage('https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x446e028f972306b5a2c36e81d3d088af260132b3&address='.$text.'&tag=latest');
    $p_atom = (float)$patom_res['result'] / (10 ** 18);
    sleep(1);
    $stkatom_res = getPage('https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x44017598f2af1bd733f9d87b5017b4e7c1b28dde&address='.$text.'&tag=latest');
        $stk_atom = (float)$stkatom_res['result'] / (10 ** 18);
    $msg = 'Ethereum address <a href="https://etherscan.io/address/'.$text.'">'.$text.'</a>.
ETH balance: '.$eth_balance.',
pATOM: '.$p_atom.',
stkATOM: '.$stk_atom.'.';
$arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
$arInfo["inline_keyboard"][0][0]['text'] = 'Home';
    $arInfo["inline_keyboard"][0][1]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][1]['text'] = 'Help';
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/set_gas' || $text && strpos($text, 'Set GAS notify') !== false || $text && $text === 'Set GAS notify') {
    $msg = $conf['gas_text'];
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/off_gass';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Off';
    $arInfo["inline_keyboard"][0][1]["callback_data"] = '/start';
    $arInfo["inline_keyboard"][0][1]["callback_data"] = '/start';
$arInfo["inline_keyboard"][0][1]['text'] = 'Home';
    $arInfo["inline_keyboard"][0][2]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][2]['text'] = 'Help';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if (isset($arr['message']['reply_to_message']) && strpos($arr['message']['reply_to_message']['text'], $conf['gas_text']) !== false) {
    $chats_json = file_get_contents('chats.json');
    $chats = json_decode($chats_json, true); 
    $isValid = false;
$gases = explode('-', $text);
if (isset($gases) && count($gases) > 1 && is_numeric($gases[0]) && is_numeric($gases[1])) {
$isValid = true;
} else if (isset($gases) && count($gases) === 1 && is_numeric($gases[0])) {
    $isValid = true;
}
$msg = 'GAS installed. You will receive a notification if it is reached.';

    if ($chats && count($chats) > 0 && isset($chats[$chat_id]) && $isValid === true) {
        $chats[$chat_id]['gas'] = $text;
    } else if ($chats && count($chats) > 0 && !isset($chats[$chat_id]) && $isValid === true) {
        $chats[$chat_id] = [];
        $chats[$chat_id]['gas'] = $text;
    } else if ($isValid === false) {
        $msg = 'You entered the data with an error. Please try again.';
    }
    
    file_put_contents('chats.json', json_encode($chats));
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
$arInfo["inline_keyboard"][0][0]['text'] = 'Home';
    $arInfo["inline_keyboard"][0][1]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][1]['text'] = 'Help';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($text && $text === '/off_gas' || $text && strpos($text, '/off_gas') !== false) {
    $chats_json = file_get_contents('chats.json');
    $chats = json_decode($chats_json, true); 
    $msg = "You didn't add any GAS settings";
    if ($chats && count($chats) > 0 && isset($chats[$chat_id]) && isset($chats[$chat_id]['gas'])) {
unset($chats[$chat_id]['gas']);
    
file_put_contents('chats.json', json_encode($chats));
$msg = 'GAS settings was deleted';
}
$arInfo["inline_keyboard"][0][0]["callback_data"] = '/start';
$arInfo["inline_keyboard"][0][0]['text'] = 'Home';
$arInfo["inline_keyboard"][0][1]["callback_data"] = '/help';
$arInfo["inline_keyboard"][0][1]['text'] = 'Help';
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if ($search_token !== false && $search_token >= 0) {
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
// $sended = $tg->photo($chat_id, $msg, 0, $arInfo, __DIR__.'/images/'.mb_strtolower($token['symbol']).'.png');
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} // end if prices.
} // end if

$chats_json = file_get_contents('chats.json');
$chats = json_decode($chats_json, true); 
if ($chats && count($chats) > 0 && isset($chats[$chat_id])) {
$msg_id = $chats[$chat_id]['msg_id'];
$tg->delete($chat_id, $msg_id);
}

$chats[$chat_id]['msg_id'] = $sended['result']['message_id'];
file_put_contents('chats.json', json_encode($chats));
?>