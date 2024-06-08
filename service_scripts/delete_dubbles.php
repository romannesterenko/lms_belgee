<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Teaching\CourseCompletion;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
$last_id = 0;
$go = true;
if($USER->IsAdmin()){
    $completions = new CourseCompletion();
    $from = $_REQUEST['last_id']??0;
    $deleted = [];
    foreach ($completions->limit(['>ID' => $from, 'UF_FROM_EXPORT' => true], ['*'], 20, ['ID' => 'ASC']) as $completion) {
        if(in_array($completion['ID'], $deleted))
            continue;
        $copies = $completions->get(
            [
                '!ID'=>$completion['ID'],
                'UF_USER_ID' => $completion['UF_USER_ID'],
                'UF_COURSE_ID' => $completion['UF_COURSE_ID'],
                'UF_SHEDULE_ID' => $completion['UF_SHEDULE_ID'],
                'UF_IS_COMPLETE' => $completion['UF_IS_COMPLETE'],
                'UF_FROM_EXPORT' => $completion['UF_FROM_EXPORT'],
                'UF_DATE' => (string)$completion['UF_DATE'],
                'UF_COMPLETED_TIME' => (string)$completion['UF_COMPLETED_TIME'],
            ]
        );
        if($copies!==[]){
            foreach ($copies as $copied) {
                $deleted[] = $copied['ID'];
                //$completions->delete($copied['ID']);
            }
            //$go = false;
            dump($completion);
            dump($copies);
        }
        $last_id = $completion['ID'];
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}if($go){?>
    <script>
        $(function (){
            location.href = '?last_id=<?=$last_id?>'
        })
    </script>
<?php }
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");