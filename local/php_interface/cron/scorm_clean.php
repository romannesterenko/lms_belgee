<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
$_SERVER["REQUEST_METHOD"] = 'GET';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Integrations\Scorm;
$courses = \Models\Course::getAll(['ID', 'NAME']);
$counter = 0;
foreach ($courses as $course) {
    if (\Models\Course::isScormCourse($course['ID'])) {
        if ($counter==5000)
            continue;
        $completions = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $course['ID'], 'UF_IS_COMPLETE' => 1]);

        foreach ($completions as $completion) {
            $filter = [
                'UF_USER_ID' => $completion['UF_USER_ID'],
                'UF_COURSE_ID' => $completion['UF_COURSE_ID'],
                '>UF_CREATED_AT' => date('d.m.Y H:i:s', strtotime('-6 months')),
            ];
            $already_added = [];
            $scorms = (new Scorm())->get($filter, ['*'], ['ID' => 'DESC']);
            foreach ($scorms as $scorm) {
                if (in_array($scorm['UF_KEY'], $already_added)) {
                    if ($counter==5000)
                        break 2;
                    (new Scorm())->delete($scorm['ID']);
                    $counter++;
                } else {
                    $already_added[] = $scorm['UF_KEY'];
                }
            }
            unset($already_added);
        }
    }
}
\Notifications\EmailNotifications::sentCommonEmail('romannesterenko87@gmail.com', 'Удаление записей', 'Обработка завершена. Удалено '.$counter.' записей');