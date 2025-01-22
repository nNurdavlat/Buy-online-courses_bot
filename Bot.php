<?php

require "DB.php";

class Bot
{
    const  API_URL = 'https://api.telegram.org/bot';
    private string $bot_token = '7576113596:AAETEjGA41JMKhPj1yw15tLZGUORjeTr6EY';


    public function makeRequest($method, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $this->bot_token . '/' . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $responses = curl_exec($ch);
        curl_close($ch);
        return json_decode($responses);
    }

    public function getUser($chat_id){
        $query = "SELECT * FROM users WHERE chat_id = :chat_id";
        $db = new DB();
        $stmt = $db->conn->prepare($query);
        $stmt->execute([
            ":chat_id" => $chat_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveUser($chat_id, $name, $phone_number): bool
    {
        $query = "INSERT INTO users (chat_id, name, phone_number) VALUES (:chat_id, :name, :phone_number)";
        $db = new DB();
        return $db->conn->prepare($query)->execute([
            ':chat_id' => $chat_id,
            ':name' => $name,
            ':phone_number' => $phone_number
        ]);
    }

    public function getBalance($chat_id)
    {
        $query = "SELECT * FROM users WHERE chat_id = :chat_id";
        $db = new DB();
        $stmt = $db->conn->prepare($query);
        $stmt->execute([
            ":chat_id" => $chat_id
        ]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);
        return $array['balance'];
    }

    public function cutBalance($chatId): bool
    {
        $query = "UPDATE users SET balance = balance - 150000  WHERE chat_id = :chatId";
        $db = new DB();
        $stmt = $db->conn->prepare($query);
        return $stmt->execute([
            ":chatId" => $chatId,
        ]);
    }

}



















//$mmid = $callback->inline_message_id;
//$cbid = $callback->from->id;
//$cbuser = $callback->from->username;
//$ida = $callback->id;
//$cqid = $update->callback_query->id;
//$cbins = $callback->chat_instance;
//$cbchtyp = $callback->message->chat->type;
//$admin = "-1001782995893"; // admin idsi
//$adminuser = ""; // admin user
//$message = $update->message;
//$cidtyp = $message->chat->type;
//$miid = $message->message_id;
//$name = $message->chat->first_name;
//$user = $message->from->username;
//$tx = $message->text;
//$callback = $update->callback_query;
//$mmid = $callback->inline_message_id;
//$mes = $callback->message;
//$mid = $mes->message_id;
//$cmtx = $mes->text;