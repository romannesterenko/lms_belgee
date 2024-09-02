<?php

use Helpers\HLBlockHelper as HLBlock;
use Integrations\Scorm;
use Settings\Common;
use Teaching\SheduleCourses;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

/*if(\Models\Course::allowToFreeEnroll(423)){
    dump($_SESSION);
}

die();*/
$date = date('Y-m-d 00:00:00');
echo $date;

dump(date('d.m.Y', strtotime('1.2.2023')));

/*$schedules = SheduleCourses::getArray(
    ['>=PROPERTY_BEGIN_DATE' => $date]
);
foreach ($schedules as $schedule) {
    $approved_applications = (new \Teaching\Enrollments())->get(['UF_SHEDULE_ID' => $schedule['ID'], 'UF_IS_APPROVED' => 1]);
    $completions = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $schedule['ID']]);

    if ($schedule['PROPERTIES']['LIMIT'] < count($completions)) {
        echo "-----------<br/>";
        echo $schedule['NAME']."(".$schedule['ID'].") - ".$schedule['PROPERTIES']['BEGIN_DATE']."<br/>";
        echo "Одобренных заявок - " . count($approved_applications) . " шт <br/>";
        echo "Прохождений - " . count($completions) . " шт <br/>";
        echo "Лимит - " . $schedule['PROPERTIES']['LIMIT'] . " шт <br/>";

    }
}*/

/*$completion = current((new \Teaching\CourseCompletion())->get(['ID' => 7285]));

$test_process_info = (new \Teaching\ProcessTest())->getRetestByCompletion($completion['ID']);
dump($completion);
dump($test_process_info);
$min_points = \Models\Course::getMaxPoints($completion['UF_COURSE_ID'], true);
$test_is_correct = $test_process_info['UF_POINTS']>=$min_points;
if($test_is_correct) {
    $test_is_correct = $test_process_info['UF_FAILED_BY_TIME']!=1;
}
if($test_is_correct) {
    $fields = [
        'from_retest' => true,
        'course_id' => $completion['UF_COURSE_ID'],
        'employee_id' => $completion['UF_USER_ID']
    ];
    //Если тест корректен (пройден) создаем новое успешное прохождение, отмечаем его пройденным, генерируем сертификат
    $result = (new \Teaching\CourseCompletion())->create($fields);
    if($result->isSuccess()){
        (new \Teaching\CourseCompletion())->update($completion['ID'], ['UF_RETEST_FAILED' => false]);
        (new \Teaching\CourseCompletion())->setCompletedCourse($completion['UF_COURSE_ID'], $test_process_info['UF_POINTS'], $completion['UF_USER_ID'], $result->getId(), true);
    }
}
dump($min_points);
dump($test_is_correct);*/
die();
if(Common::get('enable_subscription_mode') == 'Y') {
    $oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . "/upload/Технический_тренер_новые_модели_2024.xlsx");
    $cells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $max = $cells->getHighestRow();
    for ($row = 3; $row <= $max; $row++) {
        if ($cells->get('H' . $row) && (int)$cells->get('H' . $row)->getValue() > 0) {
            $user = \Models\User::find((int)$cells->get('H' . $row)->getValue());
            if (check_full_array($user)) {
                foreach (range('I', 'Z') as $symbol) {
                    if ($cells->get($symbol . "2") && $cells->get($symbol . "2")->getValue()) {
                        $string = $cells->get($symbol . "2")->getValue();
                        $pattern = '/\((\d+)\)/';
                        $matches = [];
                        if (preg_match($pattern, $string, $matches)) {
                            $course_id = $matches[1];
                            $course = \Models\Course::find($course_id);
                            if (check_full_array($course)) {
                                $time_value = $cells->get($symbol . $row)->getFormattedValue();
                                if (time() < strtotime($time_value)) {
                                    $fields = [
                                        'UF_COURSE_ID' => $course_id,
                                        'UF_USER_ID' => $user['ID'],
                                        'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                                        'UF_DATE' => date('d.m.Y H:i:s', strtotime($time_value)),
                                    ];
                                    $exists = HLBlock::get(HLBlock::initialize('course_subscription'), [
                                        'UF_COURSE_ID' => $course_id,
                                        'UF_USER_ID' => $user['ID'],
                                        'UF_DATE' => date('d.m.Y H:i:s', strtotime($time_value)),
                                    ]);
                                    if (!check_full_array($exists)) {
                                        HLBlock::add($fields, HLBlock::initialize('course_subscription'));
                                    }
                                    dump($fields);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
die();
function generateUniqueToken($user_id, $schedule_id): string {
    $uniqueId = uniqid(bin2hex(random_bytes(5)), true);
    $fields = [
        'UF_USER_ID' => $user_id,
        'UF_SCHEDULE_ID' => $schedule_id,
        'UF_CREATED_AT' => date("d.m.Y H:i:s"),
        'UF_HASH' => $uniqueId,
    ];
    HLBlock::add($fields, HLBlock::initialize('register_urls'));
    return "https://".$_SERVER["HTTP_HOST"]."/register/".$uniqueId."/";
}
function sendSms($number, $text): void
{
    $login = \Settings\Common::get('sms_smsc_login');
    $password = \Settings\Common::get('sms_smsc_password');
    $link = "https://smsc.ru/sys/send.php?login=$login&psw=$password&phones=$number&mes=$text";
    dump($link);
    //(new \Bitrix\Main\Web\HttpClient())->get($link);
}
if(\Settings\Common::get('sms_links_enabled') == 'Y') {
    if(!empty(\Settings\Common::get('sms_smsc_login')) && !empty(\Settings\Common::get('sms_smsc_password'))) {
        $direction = \Settings\Common::get('sms_courses_directions');
        $course_type = \Settings\Common::get('sms_courses_types');
        $courses_filter = [
            '!PROPERTY_COURSE_TYPE' => 6
        ];
        switch ($course_type) {
            case 'offline':
                $courses_filter['PROPERTY_COURSE_FORMAT'] = 4;
                break;
            case 'online':
                $courses_filter['PROPERTY_COURSE_FORMAT'] = 3;
                break;
        }

        switch ($direction) {
            case 'op':
                $courses_filter['SECTION_ID'] = 4;
                $courses_filter['INCLUDE_SUBSECTIONS'] = 'Y';
                break;
            case 'ppo':
                $courses_filter['SECTION_ID'] = 17;
                $courses_filter['INCLUDE_SUBSECTIONS'] = 'Y';
                break;
            case 'marketing':
                $courses_filter['SECTION_ID'] = 138;
                $courses_filter['INCLUDE_SUBSECTIONS'] = 'Y';
                break;
        }
        $courses = \Models\Course::getList($courses_filter, ['ID', 'NAME']);
        $course_ids = array_keys($courses);
        $from = date('Y-m-d H:i:00', strtotime('+1 hour'));
        $schedules = SheduleCourses::getArray(
            [
                'PROPERTY_BEGIN_DATE' => $from,
                'PROPERTY_COURSE' => $course_ids
            ],
        );
        $text = \Settings\Common::get('sms_sender_text');
        if (check_full_array($schedules)) {
            foreach ($schedules as $schedule) {
                $completions = (new \Teaching\CourseCompletion())->get([
                    'UF_SHEDULE_ID' => $schedule['ID'],
                ]);
                if (check_full_array($completions)) {
                    foreach ($completions as $completion) {
                        $user = \Models\User::find($completion['UF_USER_ID']);
                        if (check_full_array($user) && !empty($user['PERSONAL_MOBILE'])) {
                            $sms_text = str_replace('#COURSE_NAME#', $schedule['NAME'], $text);
                            $link = generateUniqueToken($user['ID'], $schedule['ID']);
                            $sms_text = str_replace('#REGISTER_LINK#', $link, $sms_text);
                            sendSms($user['PERSONAL_MOBILE'], $sms_text);
                        }
                        dump($user);
                    }
                }
                dump($completions);
            }
        }
    }
}


die();
$completions = (new \Teaching\CourseCompletion())->get([
    'UF_COURSE_ID' => 85,
    'UF_IS_COMPLETE' => 1,
]);

dump($completions);
//\Helpers\Pdf::generateCertFromCompletionId(62404);
foreach ($completions as $completion) {
    //Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
}
die();
$text = "У кого-были случаи на Атлас ПРО с г греБля, лох нущим мотором после заправки?
_______
Клиент дефект продемонстрировал. Заезжаем вместе на заправку, заливаем топливо, заводим мотор. Двигатель завелся и через 1-2 секунды заглох. Запускаешь повторно - все тоже самое, пока не нажмешь на педаль акселератора. Коды ошибок отсутствуют. На СТО дефект не проявляется. Пробовали заливать топливо из канистры - все работает. Склоняемся к вентиляции топливного бака. Визуально там все хорошо. Порекомендовали клиенту понаблюдать: чуть топлива выкатать, открыть горловину топливного бака и потом попробовать запустить.
Из допа установлена не нами Пандора";

function containsBannedWord($text, $bannedWords) {
    foreach ($bannedWords as $word) {
        $pattern = '/(?<!\p{L})' . preg_quote($word, '/') . '(?!\p{L})/iu';
        if (preg_match($pattern, $text)) {
            return $word;
        }
    }
    return false;
}

$filename = $_SERVER["DOCUMENT_ROOT"] . "/upload/stop_words.txt";
$bannedWords = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if($word = containsBannedWord($text, $bannedWords)){
    dump($word);
} else {
    dump("Не найдено");
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");