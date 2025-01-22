<?php

require "Bot.php";


$bot = new Bot();

$update = json_decode(file_get_contents('php://input')); // Hamma malumot va o'zgarishlar keldi keldi // Xabarlarni qayta ishlash


        $message = $update->message;
        $chat_id = $message->chat->id;
        $name = $message->chat->first_name;
        $user_name = $message->chat->username;
        $text = $update->message->text; // Kelgan so'zni $text ga ozlashtirib qo'yamiz
        $contact = $update->message->contact;
        $messageId = $update->message->message_id; // Xabarni ID sini ushlab olish



    // INLINE KAYBOARD LARNI USHLAB OLISH
    if ($update->callback_query) {
        $callbackQuery = $update->callback_query; // Update ichida Callback query ni ushlab olib Callback queryni ishlatamiz
        $callbackText = $callbackQuery->data; // Tugma bosilgandagi so'z
        $callbackChatId = $callbackQuery->message->chat->id; // Foiydalanuvchini ID si
        $callMid = $callbackQuery->message->message_id; // Xabarni ID sini ushlab olish

    }


      // BUTTONS
// Telefon raqamini olish uchun tugma
    $phone_request_button = json_encode([
        'resize_keyboard' => true,
        'keyboard' => [
            [['text' => "📱 Telefon raqam yuborish", 'request_contact' => true]],
        ],
    ]);
    $courses = json_encode([
        'resize_keyboard' => true,
        'keyboard' => [
            [['text' => "✖️ Matematika"], ['text' => "🧪 Kimyo"]],
            [['text' => "🇺🇿 Ona tili va Adabiyot"],['text' => "🏴󠁧󠁢󠁥󠁮󠁧󠁿 Ingliz tili"]],
        ],
    ]);
    $pay = json_encode([
        'resize_keyboard' => true,
        'keyboard' => [
            [['text' => "Payme orqali to'lash"], ['text' => "Click orqali to'lash"]],
            [['text' => "Otmen"]]
            ],
    ]);



    if ($text == '/start')
    {
        // 1-qadam: Agar User database da bo'lsa ism va nomeri kerak emas
        if (!$bot->getUser($chat_id)) {
            // Ro'yxatdan o'tmagan bo'lsa, ism va telefon raqamini so'rash
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Assalomu aleykum. Siz ro'yxatdan otishingiz kerak. Iltimos ismingizni kiriting: "
            ]);
            //ask_name: 2312312
            file_put_contents("step/$chat_id.txt", "ask_name");
        } else {
            // Ro'yxatdan o'tgan bo'lsa, menyuni ko'rsatish
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Assalomu alaykum, $name! Siz allaqachon ro'yxatdan o'tgansiz. Menyudan tanlang:",
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [['text' => "Balansni tekshirish"]]
                    ],
                    'resize_keyboard' => true,
                ]),
            ]);
        }
    }
    elseif (file_get_contents("step/$chat_id.txt") == "ask_name" )
    {
            // Foydalanuvchi ismni kiritmoqda
            file_put_contents("step/$chat_id.txt", "ask_phone");
            file_put_contents("step/{$chat_id}_name.txt", $text);  // Nurdavlat--------------------------------------
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Rahmat! Endi telefon raqamingizni yuboring: (masalan: +998901234567)",
                'reply_markup' => $phone_request_button,
            ]);
    }
    elseif (file_get_contents("step/$chat_id.txt") == "ask_phone")
    {
        // Telefon raqamini qabul qilish va ma'lumotlarni saqlash
        $name = file_get_contents("step/{$chat_id}_name.txt");
        $phone = $contact->phone_number;


        if ($bot->saveUser($chat_id, $name, $phone))
        {
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Tabriklaymiz, $name! Ro'yxatdan o'tdingiz. Menyudan foydalanishni boshlashingiz mumkin.",
                'reply_markup' => json_encode([
                    'keyboard' => [['Balansni tekshirish']],
                    'resize_keyboard' => true,
                ]),
            ]);
            unlink("step/$chat_id.txt");  // step ichidagi filelari o'chirvorishimiz kerak
            unlink("step/{$chat_id}_name.txt");
            $bot->makeRequest('sendVideo',[
                'chat_id' => $chat_id,
                'video' => "https://t.me/nurdavlatBlog/103",
                "caption" => "Markazimiz haqida tanishib chiqishingiz mumkun",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => "Ommaviy guruhga o'tish", 'url' => 'https://t.me/nurdavlatBlog']]
                    ]
                ]),
            ]);
        }  else {
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Xatolik yuz berdi! Keyinroq urinib ko'ring.",
            ]);
        }
    }



    // Ro'yxatdan o'tdi. Balance bo'icha ishlash
    if ($text == 'Balansni tekshirish')
    {
        $balance = $bot->getBalance($chat_id);
        if ($balance >= 150000 )
        {
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Hisobingizda: " .$balance . "so'm bor. Qaysi kursni sotib olmoqchisiz.",
                'reply_markup' => $courses,
            ]);
        }else {
            $bot->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Mablag' yetarli emas. Hozirda hisobingizda " .$balance. " so'm bor. Hisobingizni to'ldiring. Kursni sotib olish uchun hisobingizda 149.000 ming so'm bolishi kerak.
Siz faqat Payme yoki Click orqali hisobingizni to'ldirishingiz mumkun.",
                'reply_markup' => $pay,
            ]);
        }
    }


    if ($text == '✖️ Matematika')
    {
        $bot->makeRequest('sendPhoto', [
            'chat_id'=> $chat_id,
            'photo'=> 'https://img.lovepik.com//photo/50053/6684.jpg_860.jpg',
            'caption'=> "<i>Bizning Matematika kursimizda sizga</i> <b>'Algebra, Geometriya, Trigonometriya'</b> <i>haqida o'rgatiladi</i>.
Kursimiz 3 ta bo‘lim va 70 ta darsdan iborat. Agar siz ushbu kursni sotib olsangiz, kitoblar va daftarlar bepul taqdim etiladi.
Kursni sotib olishni xohlasangiz, 'SOTIB OLISH' tugmasini bosing.",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "💳 SOTIB OLISH", 'callback_data'=>'SOTIB OLISH MATH']],
                    [['text' => "👉Bizning guruhga qo'shilish👈", 'callback_data'=>'allCategories']],
                    ]
                ]),
        ]);

    }
    if ($text == '🧪 Kimyo')
    {
        $bot->makeRequest('sendPhoto', [
            'chat_id'=> $chat_id,
            'photo'=> 'https://img.freepik.com/premium-photo/detailed-model-chemical-reaction-used-educational-purposes_1162141-56330.jpg',
            'caption'=> "<i>Bizning Kimyo kursimizda sizga</i> <b>'Mendeleyev jadvali, Murakkab formulalarni thushunish, Formulalar asosida masalalar yechish'</b> <i>haqida o'rgatiladi.</i>
Kursimiz 2 ta bo‘lim va 50 ta darsdan iborat. Agar siz ushbu kursni sotib olsangiz, kitoblar va daftarlar bepul taqdim etiladi.
Kursni sotib olishni xohlasangiz, 'SOTIB OLISH' tugmasini bosing.",
            'parse_mode'=> 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "💳 SOTIB OLISH", 'callback_data'=>'SOTIB OLISH KIMYO']],
                    [['text' => "👉Bizning guruhga qo'shilish👈", 'callback_data'=>'allCategories']],
                ]
            ]),
        ]);
}
    if ($text == '🇺🇿 Ona tili va Adabiyot')
{
    $bot->makeRequest('sendPhoto', [
        'chat_id'=> $chat_id,
        'photo'=> 'https://daryo.uz/static/2017/07/2-42.jpg',
        'caption'=> "Bizning Ona tili va Adabiyot kursimizda sizga 'Ona tilidagi zamonlar, Kishilik olmoshlari, Buyuk Alomalar, Jadidlar' haqida o'rgatiladi.
Kursimiz 2 ta bo‘lim va 50 ta darsdan iborat. Agar siz ushbu kursni sotib olsangiz, kitoblar va daftarlar bepul taqdim etiladi.
Kursni sotib olishni xohlasangiz, 'SOTIB OLISH' tugmasini bosing.",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "💳 SOTIB OLISH", 'callback_data'=>'SOTIB OLISH ONATILI']],
                [['text' => "👉Bizning guruhga qo'shilish👈", 'callback_data'=>'allCategories']],
            ]
        ]),
    ]);
}
    if ($text == "🏴󠁧󠁢󠁥󠁮󠁧󠁿 Ingliz tili")
    {
    $bot->makeRequest('sendPhoto', [
        'chat_id'=> $chat_id,
        'photo'=> 'https://www.dbackdrop.com/cdn/shop/files/dcd859eb223ba5987ed8faab0397a895_896fbe57-cecc-4e04-8763-22fe60a7fec3.jpg?v=1720689886',
        'caption'=> "Bizning Ingliz tili kursimizda sizga 'Zamonlar, To'gri va Noto'g'ri fe'llar, So'z birikmalari' haqida o'rgatiladi.
Kursimiz 2 ta bo‘lim va 70 ta darsdan iborat. Agar siz ushbu kursni sotib olsangiz, kitoblar va daftarlar bepul taqdim etiladi.
Kursni sotib olishni xohlasangiz, 'SOTIB OLISH' tugmasini bosing.",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "💳 SOTIB OLISH", 'callback_data'=>'SOTIB OLISH ENG']],
                [['text' => "👉Bizning guruhga qo'shilish👈", 'callback_data'=>'allCategories']],
            ]
        ]),
    ]);
}


    if ($callbackText == 'SOTIB OLISH MATH'){

        if ($bot->cutBalance($callbackChatId))
        {
            $res = $bot->makeRequest('sendMessage', [
                'chat_id' => $callbackChatId, // callbackChatId beriladi chunki INLINE tugma bosilganda chat_id o'zgarib callbackChatId bo'ladi
                'text' => "Tabriklaymiz siz MATEMATIKA kursimizni sotib oldingiz🥳🥳.  
<b>Tezda shu kanalga otib azo bo'lib oling 1 daqiqadan so'ng bu kanal silkasi o'chib ketadi:</b> https://t.me/nurdavlatBlog",
                'parse_mode' => 'HTML',
                'content_protected' => true,
            ]);
        }


        $respons_messageId = $res->result->message_id;  // O'chirilishi kerak bo'lgan habarni ID si shunaqa qilib ushlanadi. Botni javobini shunaqa qilib ushlab olamiz.
                                            // NGROK dagi habarlar bu userniki. USER habarini xech qachon o'chira olmaymiz

        sleep(2);

        $res_chatID = json_encode($res->result->chat->id);  // Botdagi userID sini olish kerak o'sha userdan o'chirish uchun

        $bot->makeRequest('deleteMessage', [
            'chat_id' => $res_chatID,
            'message_id' => $respons_messageId,
        ]);

         $bot->makeRequest('sendMessage', [
            'chat_id' => $callbackChatId,
            'text' => "Kursimizni sotib olganingiz uchun raxmat☺️. O'qishlarizga omad tilab qolaman🏆",
        ]);


        // ADMINGA BOG"LANISH UCHUN TUGMA VA NOMER
    }
    if ($callbackText == 'SOTIB OLISH KIMYO'){
                if ($bot->cutBalance($callbackChatId)){
                    $res = $bot->makeRequest('sendMessage', [
                        'chat_id' => $callbackChatId, // callbackChatId beriladi chunki INLINE tugma bosilganda chat_id o'zgarib callbackChatId bo'ladi
                        'text' => "Tabriklaymiz siz KIMYO kursimizni sotib oldingiz🥳🥳 .  
<b>Tezda shu kanalga otib azo bo'lib oling 1 daqiqadan so'ng bu kanal silkasi o'chib ketadi:</b> https://t.me/nurdavlatBlog",
                        'parse_mode' => 'HTML',
                        'protect_content' => true,
                    ]);
                }


    $respons_messageId = $res->result->message_id;  // O'chirilishi kerak bo'lgan habarni ID si shunaqa qilib ushlanadi. Botni javobini shunaqa qilib ushlab olamiz.
                                            // NGROK dagi habarlar bu userniki. USER habarini xech qachon o'chira olmaymiz
        sleep(2);

        $res_chatID = json_encode($res->result->chat->id);  // Botdagi userID sini olish kerak o'sha userdan o'chirish uchun

        $bot->makeRequest('deleteMessage', [
            'chat_id' => $res_chatID,
            'message_id' => $respons_messageId,
        ]);

         $bot->makeRequest('sendMessage', [
            'chat_id' => $callbackChatId,
            'text' => "Kursimizni sotib olganingiz uchun raxmat☺️. O'qishlarizga omad tilab qolaman🏆",
        ]);


        // ADMINGA BOG"LANISH UCHUN TUGMA VA NOMER
}
    if ($callbackText == 'SOTIB OLISH ONATILI')
    {
                if ($bot->cutBalance($callbackChatId)){
                    $res = $bot->makeRequest('sendMessage', [
                        'chat_id' => $callbackChatId, // callbackChatId beriladi chunki INLINE tugma bosilganda chat_id o'zgarib callbackChatId bo'ladi
                        'text' => "Tabriklaymiz siz ONA TILI, ADABIYOT kursimizni sotib oldingiz🥳🥳.  
<b>Tezda shu kanalga otib azo bo'lib oling 1 daqiqadan so'ng bu kanal silkasi o'chib ketadi:</b> https://t.me/nurdavlatBlog",
                        'parse_mode' => 'HTML',
                        'protect_content' => true,
                    ]);
                }


    $respons_messageId = $res->result->message_id;  // O'chirilishi kerak bo'lgan habarni ID si shunaqa qilib ushlanadi. Botni javobini shunaqa qilib ushlab olamiz.
                                                    // NGROK dagi habarlar bu userniki. USER habarini xech qachon o'chira olmaymiz

        sleep(2);

        $res_chatID = json_encode($res->result->chat->id);  // Botdagi userID sini olish kerak o'sha userdan o'chirish uchun

        $bot->makeRequest('deleteMessage', [
            'chat_id' => $res_chatID,
            'message_id' => $respons_messageId,
        ]);

         $bot->makeRequest('sendMessage', [
            'chat_id' => $callbackChatId,
            'text' => "Kursimizni sotib olganingiz uchun raxmat☺️. O'qishlarizga omad tilab qolaman🏆",
        ]);


        // ADMINGA BOG"LANISH UCHUN TUGMA VA NOMER
    }
    if ($callbackText == 'SOTIB OLISH ENG')
    {
                if ($bot->cutBalance($callbackChatId)){
                    $res = $bot->makeRequest('sendMessage', [
                        'chat_id' => $callbackChatId, // callbackChatId beriladi chunki INLINE tugma bosilganda chat_id o'zgarib callbackChatId bo'ladi
                        'text' => "Tabriklaymiz siz INGLIZ TILI kursimizni sotib oldingiz🥳🥳.  
<b>Tezda shu kanalga otib azo bo'lib oling 1 daqiqadan so'ng bu kanal silkasi o'chib ketadi:</b> https://t.me/nurdavlatBlog",
                        'parse_mode' => 'HTML',
                        'protect_content' => true,
                    ]);
                }


        $respons_messageId = $res->result->message_id;  // O'chirilishi kerak bo'lgan habarni ID si shunaqa qilib ushlanadi. Botni javobini shunaqa qilib ushlab olamiz.
        // NGROK dagi habarlar bu userniki. USER habarini xech qachon o'chira olmaymiz

        sleep(2);

        $res_chatID = json_encode($res->result->chat->id);  // Botdagi userID sini olish kerak o'sha userdan o'chirish uchun

        $bot->makeRequest('deleteMessage', [
            'chat_id' => $res_chatID,
            'message_id' => $respons_messageId,
        ]);


        $bot->makeRequest('sendMessage', [
            'chat_id' => $callbackChatId,
            'text' => "Kursimizni sotib olganingiz uchun raxmat☺️. O'qishlarizga omad tilab qolaman🏆",
        ]);

        // ADMINGA BOG"LANISH UCHUN TUGMA VA NOMER
    }





    if ($callbackText == "allCategories")
    {
        $bot->makeRequest('sendMessage', [
            'chat_id' => $callbackChatId,
            'text' => 'Asosiy guruhga o\'tish:   https://t.me/nurdavlatBlog'
        ]);
    }
    if ($text == 'Otmen')
    {
        $bot->makeRequest('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "Nimalar haqida bilib olmoqchisiz",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => "Balansni tekshirish"]]
                ],
                'resize_keyboard' => true,
                ])
        ]);
    }



//    else {
//        $bot->makeRequest('sendMessage', [
//            'chat_id' => $chat_id,
//            'text' => "Men sizni tushunmadim. Iltimos, ko'rsatmalarga rioya qiling.",
//        ]);
