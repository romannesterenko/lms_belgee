<?php
const NEED_AUTH=true;
use Teaching\Completion;
use Teaching\CourseCompletion;
use Teaching\Courses;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$stage_num = 1;
$stage_all = 1;
$is_free = Courses::isFreeSheduleCourse((int)$_REQUEST['id']);
$part = 0;
if((int)$_REQUEST['id']>0){
    if($is_free) {
        $course = Courses::getById((int)$_REQUEST['id']);
        if(empty($course['PROPERTIES']['SCORM'])) {
            $completion = Completion::getByCourse($course['ID']);
            $result = Completion::getCurStageByCompletion($completion['ID']);
            $stage = $result['completion'];
        }
        $APPLICATION->SetTitle(GetMessage('COMPLETIONS_COURSE_TITLE'). $course['NAME']);
    } else {
        LocalRedirect('/cabinet/courses/completions/'.(int)$_REQUEST['id'].'/');
    }?>
    <div class="main-content">
        <div class="content">
            <?php if($is_free) {?>
                <?php if(!empty($course['PROPERTIES']['SCORM'])){
                    $all_links = $course['PROPERTIES']['SCORM'];
                    $completed = true;
                    $completions = new CourseCompletion();
                    $scorm_string = $completions->getScormCompletionString($course['ID'], $USER->GetID());
                    $scorm_completions_array = explode(';', $scorm_string);
                    $first_part = $scorm_string == '';

                    $count_scorms = count($course['PROPERTIES']['SCORM']);
                    if($count_scorms==1){
                        LocalRedirect('/cabinet/courses/completions/'.(int)$_REQUEST['id'].'/');
                    }
                    foreach ($course['PROPERTIES']['SCORM'] as $key => $scorm_course){
                        if(!in_array($key, $scorm_completions_array)){
                            $part = $key;
                            $completed = false;
                            $course['PROPERTIES']['SCORM'] = $scorm_course['VALUE'];
                            break;
                        }
                    }?>
                    <h1 class="h1 lowercase center"><?=$course['NAME']?></h1>
                    <h3 class="h3 lowercase center">Оглавление</h3>

                    <div class="content-block text-block">
                        <?php if($count_scorms>1){?>
                            <div class="">
                                <ul style="padding: 5px 0">
                                    <?php if($first_part){?>
                                        <?php foreach ($all_links as $key => $link){
                                            if($key == 0){?>
                                                <li style="padding: 5px 0"><a href="/cabinet/courses/completions/<?=(int)$_REQUEST['id']?>/?part=<?=$key?>"><?=$link['DESCRIPTION']??$course['NAME'].', '.GetMessage('COMPLETIONS_COURSE_PART').' '.($key+1)?> <?='('.GetMessage('IN_COMPLETING').')'?></a></li>
                                            <?php } else {?>
                                                <li style="padding: 5px 0"><span><?=$course['NAME'].', '.GetMessage('COMPLETIONS_COURSE_PART').' '.($key+1)?> </span></li>
                                            <?php }?>
                                        <?php }?>
                                    <?php } else {?>
                                        <?php foreach ($all_links as $key => $link){
                                            if($key <= count($scorm_completions_array)){?>
                                                <li style="padding: 5px 0"><a href="/cabinet/courses/completions/<?=(int)$_REQUEST['id']?>/?part=<?=$key?>"><?=$link['DESCRIPTION']??$course['NAME'].', '.GetMessage('COMPLETIONS_COURSE_PART').' '.($key+1)?> <?=($key)==count($scorm_completions_array)?'('.GetMessage('IN_COMPLETING').')':''?></a></li>
                                            <?php } else {?>
                                                <li style="padding: 5px 0"><span><?=$course['NAME'].', '.GetMessage('COMPLETIONS_COURSE_PART').' '.($key+1)?> </span></li>
                                            <?php }?>
                                        <?php }?>
                                    <?php }?>
                                </ul>
                            </div>
                        <?php }?>
                    </div>
            <?php }?>
            <?php } else {
                LocalRedirect('/cabinet/courses/completions/'.(int)$_REQUEST['id'].'/');
            }?>
        </div>
    </div>
<?php }else{
    Bitrix\Iblock\Component\Tools::process404(
        GetMessage('COMPLETIONS_COURSE_NOT_FOUND'), //Сообщение
        true, // Нужно ли определять 404-ю константу
        true, // Устанавливать ли статус
        true, // Показывать ли 404-ю страницу
        false // Ссылка на отличную от стандартной 404-ю
    );
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>