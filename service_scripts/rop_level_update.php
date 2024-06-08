<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$users = \Models\User::getListByRole(2);
//echo "<pre>";
$property_ids = [
    1 => 44,
    2 => 45,
    3 => 46,
];
$cron_data = [];
foreach ($users as $user) {
    $full = false;
    $courses = [46, 29, 8, 7, 122659];
    $completions = (new \Teaching\CourseCompletion())->get(['UF_IS_COMPLETE' => 1, 'UF_COURSE_ID' => $courses, 'UF_USER_ID' => $user["ID"]], ['ID', 'UF_USER_ID', 'UF_COURSE_ID']);
    $course_ids = array_unique(array_column($completions, 'UF_COURSE_ID'));
    $level = 0;
    $b = 0;
    if(count($course_ids) >= 0) {
        if(in_array(122659, $course_ids))
            $full = true;
        else
            $b = count($course_ids);
        if($full) {
            $level = 3;
        } elseif ($b >= 2) {
            $level = $b == 4 ? 2 : 1;
        }

    }
    if($level > 0) {
        $cron_data[] = ['user' => $user["ID"], 'level' => $property_ids[$level]];
        (new CUser)->Update($user["ID"], ["UF_USER_RATING" => $property_ids[$level]]);
    }

}


