<?php

use Helpers\HLBlockHelper as HLBlock;

$update = json_decode(file_get_contents('php://input'), true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
// Функция для обработки входящих сообщений
$token = \Settings\Common::get('telegram_antispam_bot_token');
$apiURL = "https://api.telegram.org/bot$token/";

function logError($expr): void
{
    \Helpers\Log::writeCommon($expr, 'telegram');
}

function getUserName($user) {
    if (isset($user['first_name']) && isset($user['last_name'])) {
        return $user['first_name'] . ' ' . $user['last_name'];
    } elseif (isset($user['first_name'])) {
        return $user['first_name'];
    } elseif (isset($user['username'])) {
        return '@' . $user['username'];
    } else {
        return 'Пользователь';
    }
}

function containsBannedWord($text, $bannedWords) {
    foreach ($bannedWords as $word) {
        $pattern = '/(?<!\p{L})' . preg_quote($word, '/') . '(?!\p{L})/iu';
        if (preg_match($pattern, $text)) {
            return $word;
        }
    }
    return false;
}

function processMessage($message) {
    $chatId = $message['chat']['id'];
    $text = $message['text'];
    $userId = $message['from']['id'];
    $filename = $_SERVER["DOCUMENT_ROOT"] . "/upload/stop_words.txt";
    $bannedWords = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $userName = getUserName($message['from']);
    if($word = containsBannedWord($text, $bannedWords)){
        // Удаление сообщения
        sendRequest('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $message['message_id']
        ]);
        //Мьют пользователя
        $result = muteUser($userId, $chatId, $userName);
        if($result){
            $text = "$userName, до ".date('d.m.Y H:i:s', $result)." Вам запрещено писать в группе за использование недопустимой лексики.";
        } else {
            $text = "$userName, Использование запрещенных слов не допускается. Предупреждение! В случае повторения, будут применены меры в виде ограничения общения";
        }
        //Логирование
        logError([
            'user' => $userName,
            'message' => $message['text'],
            'text' => $text,
            'chat_id' => $chatId,
            'word' => $word
        ]);
        //Уведомление в чат
        sendRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }
}

// Функция для отправки запросов к API Telegram
function sendRequest($method, $parameters) {
    global $apiURL;
    if (!is_string($method)) {
        logError("Метод должен быть строкой\n");
        return false;
    }
    if (!$parameters) {
        $parameters = [];
    } else if (!is_array($parameters)) {
        logError("Параметры должны быть массивом\n");
        return false;
    }

    $handle = curl_init($apiURL . $method);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
    $response = curl_exec($handle);

    if ($response === false) {
        logError(curl_error($handle));
        return false;
    }

    $httpCode = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    if ($httpCode >= 500) {
        sleep(10);
        return false;
    } else if ($httpCode != 200) {
        logError($method);
        logError($response);
        logError("Запрос не удался с кодом HTTP $httpCode\n");
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            logError("Запрос не удался: " . $response['description'] . "\n");
        }
        if (isset($response['error_code'])) {
            logError("Код ошибки: " . $response['error_code'] . "\n");
        }
        return $response;
    }
}

function muteUser($user_id, $group_id, $userName)
{
    $duration = 60 * 60 * 24;
    $exists = HLBlock::get(
        HLBlock::initialize('telegram_bans'),
        ["UF_USER_ID" => $user_id, "UF_GROUP_ID" => $group_id]
    );
    $fields = [
        'UF_GROUP_ID' => $group_id,
        'UF_USER_ID' => $user_id,
        'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
    ];
    HLBlock::add($fields, HLBlock::initialize('telegram_bans'));
    if(count($exists) > 0) {
        $untilDate = match (count($exists)) {
            1 => time() + $duration,
            2 => time() + ($duration * 7),
            default => time() + ($duration * 30),
        };
        sendRequest('restrictChatMember', [
            'chat_id' => $group_id,
            'user_id' => $user_id,
            'until_date' => $untilDate,
            'permissions' => json_encode([
                'can_send_messages' => false,
                'can_send_media_messages' => false,
                'can_send_polls' => false,
                'can_send_other_messages' => false,
                'can_add_web_page_previews' => false,
                'can_change_info' => false,
                'can_invite_users' => false,
                'can_pin_messages' => false
            ])
        ]);
        return $untilDate;
    } else
        return false;


}




if(check_full_array($update['message'])){

    processMessage($update['message']);
}
