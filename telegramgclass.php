<?php

class TG
{
    public $token = '';

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function send($id, $message, $reply_to_message_id, $keyboard)
    {
        //Удаление клавы
        if ($keyboard == "DEL") $keyboard = array('remove_keyboard' => true);

        if ($keyboard) { //Отправка клавиатуры
            $keyboard["resize_keyboard"] = true;
            $encodedMarkup = json_encode($keyboard);
            $data = array(
                'chat_id' => $id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => $encodedMarkup
            );
        } else { //Отправка сообщения
            $data = array(
                'chat_id' => $id,
                'text' => $message,
                'parse_mode' => 'HTML',
            );
        }
        if ($reply_to_message_id != 0) $data['reply_to_message_id'] = $reply_to_message_id;

        $out = $this->request('sendMessage', $data);
        return $out;
    }

    public function getChatMember($id, $user_id)
    {
        $data = array(
            'chat_id' => $id,
            'user_id' => $user_id
        );

        $out = $this->request('getChatMember', $data);
        return $out;
    }

    public function request($method, $data = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->token . '/' . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $out = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $out;
    }
}

