<?php
header('Content-Type: text/html; charset=utf-8');
// Основные данные
define('TITLE', 'Сообщение с сайта Black Time Coffee');
define('EMAIL', 'EMAIL');

define('EMAIL_SEND', true);
define('TELEGRAM_SEND', false);

// API reCAPTCHA для проверки подлинности капчи
define('RECAPTCHA_URL', 'https://www.google.com/recaptcha/api/siteverify');
define('RECAPTCHA_KEY', 'SECRET_KEY');

// TELEGRAM
define('TELEGRAM_TOKEN', '');
define('TELEGRAM_CHATID', '');

//* Проверка на присутствие параметров
if ( !empty( $_POST ) ) {
    $post = [];

    foreach ( $_POST as $key => $param ) {
        $param = htmlspecialchars($param);
        $param = urldecode($param);
        $param = trim($param);
        $post[$key] = $param;
    }

    sendform($post);
} else {
    exit(json([
        'code' => 2,
        'text' => 'Вы отправили пустую форму.'
    ]));
}

//* Функция отправки
function sendform($data) {

    /* INPUTS START */
    //* Возможные поля
    $inputs = [
        'input_name' => '',
        'input_phone' => '',
        'input_dish' => '',
        'input_address' => '',
        'g-recaptcha-response' => ''
    ];

    //* Обязательные поля
    if (
        empty($data['input_name']) || 
        empty($data['input_phone']) 
        // || empty($data['g-recaptcha-response'])
    ) {
        exit(json([
            'code' => 2,
            'text' => 'Заполните обязательные поля и капчу.'
        ]));
    }

    //Code Перебор обязательных параметров
    foreach ( $inputs as $key => $value ) {
        $inputs[$key] = (isset($data[$key]) && !empty($data[$key]) ? mb_convert_encoding($data[$key], "UTF-8" , "auto") : $inputs[$key]);
    }
    /* INPUTS END */


    /* RECAPTCHA START */
    $ch = curl_init();

    $query = [
        "secret" => RECAPTCHA_KEY,
        "response" => $inputs["g-recaptcha-response"],
        "remoteip" => $_SERVER['REMOTE_ADDR']
    ];

    curl_setopt($ch, CURLOPT_URL, RECAPTCHA_URL); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query); 
    $googledata = json_decode(curl_exec($ch), $assoc=true); 
    curl_close($ch);
    //@ Отрицательный ответ от сервера
    if (!$googledata['success']) {
        exit(json([
            'code' => 2,
            'text' => 'Проверка на робота не пройдена.'
        ]));
    }
    /* RECAPTCHA END */

    /* TELEGRAM START */
    if (TELEGRAM_SEND) {
        $message = [];
        $message[] = TITLE;
        $message[] = 'Имя: '.$inputs["input_name"];
        $message[] = 'Номер телефона: '.$inputs["input_phone"];
        $message[] = 'Блюдо: '.$inputs["input_dish"];
        $message[] = 'Адрес: '.$inputs["input_address"];
        
        $query = [
            'chat_id' => TELEGRAM_CHATID,
            'text' => implode("\r\n",$message),
            'disable_web_page_preview' => true,
            'parse_mode' => 'html',
        ];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.TELEGRAM_TOKEN.'/sendMessage?'.http_build_query($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    /* TELEGRAM END */


    /* MAIL START */
    if (EMAIL_SEND) {
        $message = [];
        $message[] = TITLE;
        $message[] = 'Имя: '.$inputs["input_name"];
        $message[] = 'Номер телефона: '.$inputs["input_phone"];
        $message[] = 'Блюдо: '.$inputs["input_dish"];
        $message[] = 'Адрес: '.$inputs["input_address"];

        if  (!mail(EMAIL,TITLE,implode("\r\n",$message))) {
            exit(json([
                'code' => 2,
                'text' => 'При отправке заявки возникла ошибка.'
            ]));
        }
    }
    /* MAIL END */

    exit(json([
        'code' => 1,
        'text' => 'Заявка отправлена.'
    ]));
}

//* Быстрый json_encode в нормальный вид
function json($a) {
    echo json_encode( $a, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
}

?>