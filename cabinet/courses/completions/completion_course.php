<?php
const NEED_AUTH=true;
use Teaching\Completion;
use Teaching\CourseCompletion;
use Teaching\Courses;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
$stage_num = 1;
$stage_all = 1;
$part = 0;
if((int)$_REQUEST['id']>0) {
    $course = Courses::getById((int)$_REQUEST['id']);
    if (!check_full_array($course)) {
        Bitrix\Iblock\Component\Tools::process404(
            GetMessage('COMPLETIONS_COURSE_NOT_FOUND'), //Сообщение
            true, // Нужно ли определять 404-ю константу
            true, // Устанавливать ли статус
            true, // Показывать ли 404-ю страницу
            false // Ссылка на отличную от стандартной 404-ю
        );
    }
    $is_free = Courses::isFreeSheduleCourse((int)$_REQUEST['id']);
    if($is_free) {
        $course = Courses::getById((int)$_REQUEST['id']);
        if(empty($course['PROPERTIES']['SCORM'])) {
            $completion = Completion::getByCourse($course['ID']);
            $result = Completion::getCurStageByCompletion($completion['ID']);
            $stage = $result['completion'];
        }
        $APPLICATION->SetTitle(GetMessage('COMPLETIONS_COURSE_TITLE'). $course['NAME']);
    }?>
    <div class="main-content">
        <div class="content">
            <?php if($is_free) {?>
                <?php if(!empty($course['PROPERTIES']['SCORM'])) {
                    $all_links = $course['PROPERTIES']['SCORM'];
                    $completed = true;
                    $is_retest = false;
                    $current_completion = (new CourseCompletion())->get(['UF_USER_ID' => $USER->GetID(), 'UF_COURSE_ID' => $course['ID']]);
                    if(check_full_array($current_completion)){
                        $current_completion = current($current_completion);
                        if($current_completion['UF_RETEST'] && !empty($course['PROPERTIES']['RETEST_SCORM'])) {
                            $is_retest = true;
                            $all_links = $course['PROPERTIES']['SCORM'] = [0=>['VALUE' => $course['PROPERTIES']['RETEST_SCORM']]];
                        }
                    }
                    $completions = new CourseCompletion();
                    $scorm_string = $completions->getScormCompletionString($course['ID'], $USER->GetID());
                    $scorm_completions_array = explode(';', $scorm_string);
                    $count_scorms = count($course['PROPERTIES']['SCORM']);
                    if($count_scorms==1) {
                        $completed = in_array(0, $scorm_completions_array);
                        $course['PROPERTIES']['SCORM'] = $course['PROPERTIES']['SCORM'][0]['VALUE'];

                    } else {
                        foreach ($course['PROPERTIES']['SCORM'] as $key => $scorm_course) {
                            if(!in_array($key, $scorm_completions_array)) {
                                $part = $key;
                                $completed = false;
                                $course['PROPERTIES']['SCORM'] = $scorm_course['VALUE'];
                                break;
                            }
                        }
                            if (array_key_exists('part', $_REQUEST) && $_REQUEST['part'] >= 0) {
                                if (in_array((int)$_REQUEST['part'], $scorm_completions_array)||(int)$_REQUEST['part']<=count($scorm_completions_array)) {
                                    $course['PROPERTIES']['SCORM'] = $all_links[(int)$_REQUEST['part']]['VALUE'];
                                    if ((int)$_REQUEST['part'] != $part) {
                                        $part = (int)$_REQUEST['part'];
                                        $completed = true;
                                    } else {
                                        $completed = false;
                                    }
                                }
                            }else{
                                LocalRedirect("/cabinet/courses/completions/table_of_contents/".(int)$_REQUEST['id']."/");
                            }
                    }
                    ?>
                    <input type="hidden" name="user_id" id="user_id" value="<?=$USER->GetID()?>">
                    <input type="hidden" name="course_id" id="course_id" value="<?=$course['ID']?>">
                    <input type="hidden" name="part" id="part" value="<?=$part?>">
                    <h1 class="h1 lowercase center"><?=$course['NAME']?><?=$count_scorms>1?' ('.($part+1).' '.GetMessage('COMPLETIONS_COURSE_PART').' из '.$count_scorms.')':''?><?=$is_retest?". Ре-тест":""?></h1>
                    <?php if($count_scorms>1){?>
                        <div class="content-block text-block">
                            <div class="btn-center">
                                <a href="/cabinet/courses/completions/table_of_contents/<?=(int)$_REQUEST['id']?>/" class="btn">К оглавлению</a>
                            </div>
                        </div>
                    <?php }?>
                    <iframe id="scorm" frameborder="0" style="width: 100%; height: 800px"></iframe>
                    <?php if($count_scorms>1){?>
                        <div class="content-block text-block">
                            <div class="btn-center">
                                <a href="/cabinet/courses/completions/table_of_contents/<?=(int)$_REQUEST['id']?>/" class="btn">К оглавлению</a>
                            </div>
                        </div>
                    <?php }
                    $directory = 'scorm';
                    if($USER->GetID()==2){
                        $directory = 'test_scorm';
                    }
                    ?>
                    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/<?=$directory?>/scorm_api_2004.js"></script>
                    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/<?=$directory?>/scorm_api_1_2.js"></script>
                    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/<?=$directory?>/scorm_datamodel.js"></script>
                    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/<?=$directory?>/scorm_debug.js"></script>

                    <script>
                        // на этом адресе держим json для инициализации курса (в начале пустой объект, затем все что сохранили от курса в том виде, в котором получили)
                        cmi.lms_init_url = "/local/templates/geely/ajax/scorm/get_data.php?user_id=<?=$USER->GetID()?>&course_id=<?=$course['ID']?>&part=<?=$part?>";
                        cmi.lms_completed_url = "/courses/completed/";
                        //cmi.lms_completed_auto_exit = true;
                        cmi.learner_id = '<?=$USER->GetID()?>';
                        cmi.learner_name = '<?=$USER->GetFullName()?>';
                        //cmi.lms_completed_auto_exit = false;
                        cmi.lms_commit_url = "/local/templates/geely/ajax/scorm/add_data.php";
                        <?php /* if(!$completed){*/?>/*
                            // на этот адрес отправляются все данные cmi в виде json объекта
                            cmi.lms_commit_url = "/local/templates/geely/ajax/scorm/add_data.php";
                        */<?php /*}*/?>
                        //Пример инициализации, но лучше через делать это через json из lms_init_url
                        //cmi.suspend_data = '{}'

                        // Все готово, запускаем курс
                        document.getElementById("scorm").src="<?=$course['PROPERTIES']['SCORM']?>";
                    </script>
            <?php } else { ?>
                    <h1 class="h1 lowercase center"><?=$course['NAME']?></h1>
                    <div class="course-service-head">
                        <span class="course-service-step-item active">
                          <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt=""></span>
                          <?=GetMessage('COMPLETIONS_COURSE_COURSE')?>
                        </span>
                        <span class="course-service-step-item ">
                          <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt=""></span>
                          <?=GetMessage('COMPLETIONS_COURSE_TITLE_STEP', ['#CURRENT#' => $result['current'], '#ALL#' => $result['all']])?>
                        </span>

                        <span class="course-service-step-item disabled">
                          <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt=""></span>
                          <?=GetMessage('COMPLETIONS_COURSE_TESTING')?>
                        </span>
                    </div>

                    <div class="content-block text-block">
                        <?=$stage['~DETAIL_TEXT']?>
                        <div class="btn-center">
                            <?php $common_completions = new \Teaching\CourseCompletion();
                            $completion = $common_completions->getByCourseAndUser($USER->GetID(), (int)$_REQUEST['id']);
                            if($result['current']==$result['all']){?>
                                <?php if($result['current']>1){?>
                                    <a href="#" class="btn prev_step_course" data-course = "<?=$course['ID']?>" data-user = "<?=$USER->GetID()?>" data-current-step="<?=$result['current']?>"><?=GetMessage('COMPLETIONS_COURSE_PREVIOUS_STEP')?></a>
                                <?php }?>
                                <?php if($completion['UF_IS_COMPLETE'] != 1){?>
                                    <a href="<?= \Teaching\Tests::generateLinkToTest($course['ID'])?>" class="btn"><?=GetMessage('COMPLETIONS_COURSE_TESTING')?></a>
                                <?php }?>
                            <?php } else {
                                if($result['current']>1) {?>
                                    <a href="#" class="btn prev_step_course" data-course = "<?=$course['ID']?>" data-user = "<?=$USER->GetID()?>" data-current-step="<?=$result['current']?>"><?=GetMessage('COMPLETIONS_COURSE_PREVIOUS_STEP')?></a>
                                <?php }?>
                                <a href="#" class="btn process_step_course" data-course = "<?=$course['ID']?>" data-user = "<?=$USER->GetID()?>" data-current-step="<?=$result['current']?>"><?=GetMessage('COMPLETIONS_COURSE_NEXT_STEP')?></a>
                                <?php if($completion['UF_VIEWED']==1 && $completion['UF_IS_COMPLETE'] != 1) {?>
                                    <a href="<?= \Teaching\Tests::generateLinkToTest($course['ID'])?>" class="btn"><?=GetMessage('COMPLETIONS_COURSE_TESTING')?></a>
                                <?php }?>
                            <?php
                            }?>
                        </div>
                    </div>
            <?php }
            }else{?>
                <h1 class="h1 lowercase center"><?=GetMessage('COMPLETIONS_COURSE_NOT_FOR', ['#COURSE_NAME#' => $course['NAME']])?></h1>
                <div class="content-block text-block">
                    <div class="btn-center">
                        <a href="/cabinet/common/" class="btn"><?=GetMessage('COMPLETIONS_COURSE_TO_CABINET')?></a>
                    </div>
                </div>
            <?php }?>
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