<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Teaching\Tests;

$test_processed = new \Teaching\ProcessTest();
$new_order = [];

if(Tests::getLimitQuestions((int)$_REQUEST['test_id'])>0||Tests::isRandomQuestions((int)$_REQUEST['test_id'])) {
    $new_order = Tests::randomizeQuestions((int)$_REQUEST['test_id']);
} else {
    Tests::resetRandomizeQuestions((int)$_REQUEST['test_id']);
}
$course = Tests::getCourseByTest((int)$_REQUEST['test_id']);
if(!check_full_array($course))
    \Helpers\PageHelper::set404(Loc::getMessage('PAGE_NOT_FOUND'));
$completion = new \Teaching\CourseCompletion();
$current_completion = current($completion->get(['UF_USER_ID' => $USER->GetID(), "UF_COURSE_ID"=>$course['ID'], "UF_FAILED" => false, "UF_IS_COMPLETE"=>false]));
if(!check_full_array($current_completion))
    LocalRedirect('/cabinet/common/');
if($current_completion['UF_SHEDULE_ID']>0&&!\Teaching\SheduleCourses::isAllowMainTest($current_completion['UF_SHEDULE_ID']))
    LocalRedirect('/cabinet/common/');
if($test_processed->isFinished($_REQUEST['test_id'], $USER->GetID(), $current_completion['ID'])){
    Tests::resetRandomizeQuestions($_REQUEST['test_id']);
    $APPLICATION->SetTitle(GetMessage('TESTING').' '.$course['NAME']);
    $test_process_info = $test_processed->getByCompletion($current_completion['ID']);

    $questions = Tests::getQuestionsByTest($_REQUEST['test_id'], $new_order);
    if($test_process_info['UF_POINTS']>0) {
        $completion->setWasOnCourse($current_completion['ID']);
    }

    $test_is_correct = $test_process_info['UF_POINTS']>= \Models\Course::getMaxPoints($course['ID']);
    if($test_is_correct) {
        $test_is_correct = $test_process_info['UF_FAILED_BY_TIME']!=1;
    }
    $failed = false;
    if($test_is_correct) {
        if(!$completion->isCompleted($course['ID'], 0, $current_completion['ID']))
            $completion->setCompletedCourse($course['ID'], $test_process_info['UF_POINTS'], 0, $current_completion['ID']);
    } else {
        $max_attempts = \Models\Course::getTotalAttempts($course['ID']);
        if($max_attempts>0){
            if((int)$current_completion['UF_MADE_ATTEMPTS']>=$max_attempts){
                $completion->setFailedCourse($current_completion['ID'], $test_process_info['UF_POINTS']);
                $failed = true;
            } else {
                (new \Teaching\CourseCompletion())->setPoints((int)$test_process_info['UF_POINTS'], $current_completion['ID']);
            }
        } else {
            (new \Teaching\CourseCompletion())->setPoints((int)$test_process_info['UF_POINTS'], $current_completion['ID']);
        }
    }?>
    <div class="main-content">
        <div class="content">
            <h1 class="h1 lowercase center"><?=$course['NAME']?></h1>
            <div class="course-service-head">
                <span class="course-service-step-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt=""></span>
                  <?=GetMessage('COURSE')?>
                </span>
                <span class="course-service-step">
                  <?=GetMessage('POINTS')?> <span><?=$test_process_info['UF_POINTS']?></span> <?=GetMessage('FROM')?> <?= \Teaching\Tests::getAllSumPoints($questions);?>
                </span>
            </div>

            <div class="content-block">
                <div class="text-content">
                    <div class="course-completed">
                        <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/like-blue.svg" alt=""<?=!$test_is_correct?'style="transform: scale(1, -1);"':''?>></span>
                        <?=GetMessage('TEST')?> <?=!$test_is_correct?GetMessage('NOT'):''?><?=GetMessage('COMPLETED')?>! <?=$test_process_info['UF_FAILED_BY_TIME']==1?"Время истекло":""?>
                    </div>
                    <div class="course-completed-result">
                        <h3 class="h3 lowercase center"><?=GetMessage('RESULTS_TITLE')?></h3>
                        <div class="course-completed-result__content">
                            <div class="course-result-item">
                                <span class="course-result-item__title"><?=GetMessage('DATE')?></span>
                                <?=\Helpers\DateHelper::getHumanDate($test_process_info['UF_LAST_ACTIVE']->toString())?> <?=GetMessage('YEAR')?>
                            </div>
                            <div class="course-result-item">
                                <span class="course-result-item__title"><?= Loc::getMessage('POINTS') ?></span>
                                <span class="course-result-item__text">
                                    <span><?=$test_process_info['UF_POINTS']?></span> <small><?= Loc::getMessage('FROM') ?></small> <?= \Teaching\Tests::getAllSumPoints($questions);?>
                                </span>
                            </div>
                            <div class="course-result-item">
                                <span class="course-result-item__title"><?= Loc::getMessage('ATTEMPTS') ?></span>
                                <span class="course-result-item__text">
                                    <span><?=$current_completion['UF_MADE_ATTEMPTS']?></span><?php if((int)$current_completion['UF_TOTAL_ATTEMPTS']>0){?> <small><?= Loc::getMessage('FROM') ?></small> <?=$current_completion['UF_TOTAL_ATTEMPTS']?><?php }?>
                                </span>
                            </div>
                            <?php if(!$test_is_correct){
                                if((int)$current_completion['UF_TOTAL_ATTEMPTS']>0&&(int)$current_completion['UF_MADE_ATTEMPTS'] == (int)$current_completion['UF_TOTAL_ATTEMPTS']){?>
                                    <div class="course-result-item">
                                        <small style="font-size: 15px; text-align: justify; color: #0070b5;"><?= Loc::getMessage('COURSE_FAILED_INFO') ?></small>
                                    </div>
                                <?php }?>
                            <?php }?>
                        </div>
                    </div>
                    <div class="btn-center mt-20">
                        <a href="/cabinet/common/" class="btn "><?= Loc::getMessage('TO_CABINET') ?></a>
                        <?php if(!$test_is_correct&&!$failed) {
                            if(\Teaching\Courses::isHybridCourse($course['ID'])) {?>
                                <a href="<?= \Teaching\Tests::generateLinkToTest($course['ID'])?>" class="btn "><?= Loc::getMessage('AGAIN') ?></a>
                            <?php } else { ?>
                                <a href="<?= \Teaching\Tests::generateLinkToTest($course['ID'])?>" class="btn "><?= Loc::getMessage('AGAIN') ?></a>
                            <?php }?>
                        <?php } else {?>
                            <a href="/cabinet/courses/feedback_poll/new/<?= $current_completion['ID'] ?>/" class="btn ">Форма обратной связи</a>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if(!$test_is_correct) {
        $test_processed->delete($_REQUEST['test_id']);
        if((int)$current_completion['UF_TOTAL_ATTEMPTS']>0&&(int)$current_completion['UF_MADE_ATTEMPTS'] == (int)$current_completion['UF_TOTAL_ATTEMPTS']){
            $completion->setFailedCourse($current_completion['ID'], $test_process_info['UF_POINTS']);
        }
    }?>
<?php } else {
$test = Tests::getById($_REQUEST['test_id']);
$questions = Tests::getQuestionsByTest($test['ID'], $new_order);
$id = 0;
$num = 1;
$need_question = $test_processed->getCurrentQuestionNumber($_REQUEST['test_id'], 0, $current_completion['ID']);

foreach ($questions as $key => $one_question){
    $id = $key;
    if($key==(int)$_REQUEST['question_id'])
        break;
    $num++;
}
if($num>count($questions))
    $num=count($questions);
if($num==1&&!$test_processed->isBegined($_REQUEST['test_id'], 0, $current_completion['ID']))
    $test_processed->startProcess($_REQUEST['test_id'], 0, $current_completion['ID']);
else{
    $need_question = $test_processed->getCurrentQuestionNumber($_REQUEST['test_id'], 0, $current_completion['ID']);
    if($num!=$need_question) {
        $test_processed->goToQuestionOfTestByNumber($need_question, $_REQUEST['test_id']);
    }
}
$question = $questions[$_REQUEST['question_id']];
$need_test_timer = false;
$need_question_timer = false;
$time_for_completing_test = Tests::getTimeForCompleting($_REQUEST['test_id']);
$time_for_completing_answer = (int)$question['PROPERTIES']['TIME_TO_ANSWER'];
$process = new \Teaching\ProcessTest();
if($time_for_completing_test>0){
    $pr_test = current($process->get(['UF_TEST_ID' => $_REQUEST['test_id'], 'UF_USER_ID' => $USER->GetID()])->getArray());
    $start_timestamp = $pr_test['UF_BEGIN_DATETIME']->getTimestamp();
    $fact_different = time()-$start_timestamp;
    $need_different = $time_for_completing_test*60;
    if($fact_different>$need_different){
        $test = current($test_processed->getByTestAndUser($_REQUEST['test_id']));
        $test_processed->setFinished($test['ID']);
        LocalRedirect($APPLICATION->GetCurPage());
    } else {
        $test_time = $need_different-$fact_different;
        $need_test_timer = true;
    }

}
if($time_for_completing_answer>0){

    $pr_test = current($process->get(['UF_TEST_ID' => $_REQUEST['test_id'], 'UF_USER_ID' => $USER->GetID()])->getArray());

    $start_timestamp_a = $pr_test['UF_LAST_ACTIVE']->getTimestamp();
    $fact_different_a = time()-$start_timestamp_a;
    $need_different_a = $time_for_completing_answer*60;
    if($fact_different_a>$need_different_a){
        $test = current($test_processed->getByTestAndUser($_REQUEST['test_id']));
        $test_processed->setFinished($test['ID']);
        LocalRedirect($APPLICATION->GetCurPage());
    } else {
        $need_question_timer = true;
        $question_time = $need_different_a-$fact_different_a;
    }

}

if($need_question_timer&&$question_time<=0){
    $test = current($test_processed->getByTestAndUser($_REQUEST['test_id']));
    $test_processed->setFinished($test['ID']);
    LocalRedirect($APPLICATION->GetCurPage());
}
$APPLICATION->SetTitle(Loc::getMessage('PROCESS_TESTING_TITLE', ['#NAME#' => $course['NAME']]));
?>
	<div class="main-content">
		<div class="content">
			<h1 class="h1 lowercase center"><?=$course['NAME']?></h1>
            <input type="hidden" id="test_id_hid" value="<?=$_REQUEST['test_id']?>">
			<div class="course-service-head">
            <span class="course-service-step-item">
              <span class="icon">
                  <img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt="">
              </span><?= Loc::getMessage('COURSE') ?>
            </span>
			<span class="course-service-step-item active">
              <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check.svg" alt=""></span><?= Loc::getMessage('TESTING') ?></span>
			<span class="course-service-step">
				  <?= Loc::getMessage('POINTS') ?>  <span><?=$test_processed->getCurrentPoints($_REQUEST['test_id'])?></span>  <?= Loc::getMessage('FROM') ?>  <?= \Teaching\Tests::getAllSumPoints($questions);?>
				</span>
                <?php if($need_test_timer&&$test_time>0){?>
                    <span class="course-service-step-item ">
                        Осталось времени на тест <div id="timer_all" data-all-time="<?=$test_time?>" style="display: flex; padding-left: 10px;">
                            <div class="timer_all__hours">00</div>:
                            <div class="timer_all__minutes">00</div>:
                            <div class="timer_all__seconds">00</div>
                        </div>
                    </span>
                <?php }?>

			</div>
			<div class="content-block">
				<div class="text-content">
					<div class="poll">
						<div class="poll__step">
                            <?= Loc::getMessage('STEP') ?> <span><?=$num?></span> <?= Loc::getMessage('FROM') ?> <?=count($questions)?>
						</div>
						<div class="poll__content">
							<h3 class="h3 lowercase center"><?=$question['NAME']?></h3>
                            <?php if(check_full_array($question['PREVIEW_PICTURE'])) {?>
                                <div style="width: 100%; text-align: center; margin: 10px">
                                    <img src="<?=$question['PREVIEW_PICTURE']['SRC']?>" alt="">
                                </div>
                            <?php } elseif($question['PREVIEW_PICTURE'] > 0){?>
                                <div style="width: 100%; text-align: center; margin: 10px">
                                    <img src="<?=CFile::GetPath($question['PREVIEW_PICTURE'])?>" alt="">
                                </div>
                            <?php }?>

                            <?php if(!empty($question['PROPERTIES']['CORRECT_NUM'])){
                                $many_answers = false;
                                if(strpos($question['PROPERTIES']['CORRECT_NUM'], ',')!==false){
                                    $many_answers=true;?>
                                    <div class="poll-item">
                                        <?php foreach ($question['PROPERTIES']['ANSWERS'] as $key=> $answer){?>
                                            <div class="form-group">
                                                <div class="checkbox-item">
                                                    <input type="checkbox" id="poll-question-<?=$key?>" name="poll[]" value="<?=$question['ID']?>_<?=($key+1)?>" class="test_question_checkbox">
                                                    <label for="poll-question-<?=$key?>"><?=$answer['VALUE']?> </label>
                                                </div>
                                            </div>
                                        <?php
                                        }?>
                                    </div>
                                <?php
                                }else{?>
                                    <div class="poll-item">
                                        <?php foreach ($question['PROPERTIES']['ANSWERS'] as $key=> $answer){?>
                                            <div class="radio-item">
                                                <input type="radio" id="poll-question-<?=$key?>" value="<?=$question['ID']?>_<?=($key+1)?>" class="test_question_radio" name="poll-1">
                                                <label for="poll-question-<?=$key?>"><?=$answer['VALUE']?></label>
                                            </div>
                                        <?php }?>
                                    </div>
                                <?php
                                }
                            }?>
                            <?php if($need_question_timer&&$question_time>0){?>
                                    <div class="btn-center">
                                        <span class="course-service-step-item">
                                            Осталось времени на вопрос <div id="timer_question" data-all-time="<?=$question_time?>" style="display: flex; padding-left: 10px;">
                                                <div class="timer_question__hours">00</div>:
                                                <div class="timer_question__minutes">00</div>:
                                                <div class="timer_question__seconds">00</div>
                                            </div>
                                        </span>
                                    </div>
                            <?php }?>
							<div class="btn-center">
                                <?php
                                if(\Teaching\Courses::isHybridCourse($current_completion['UF_COURSE_ID'])){?>
                                    <a href="/shedules/<?=$current_completion['UF_SHEDULE_ID']?>/" class="btn"><?= Loc::getMessage('BACK_TO_COURSE') ?></a>

                                <?php } else {?>
                                    <a href="#" class="btn return_to_course" data-course = "<?=$course['ID']?>" data-user = "<?=$USER->GetID()?>"><?= Loc::getMessage('BACK_TO_COURSE') ?></a>

                                <?php }?>
								<a
                                    href="#"
                                    class="btn next_test_question_btn disabled_link"
                                    data-test-id="<?=$_REQUEST['test_id']?>"
                                    data-user-id="<?=$USER->GetID()?>"
                                    data-current-question="<?=$num?>"
                                    data-many-answers="<?=$many_answers?1:0?>"
                                    data-all-questions="<?=count($questions)?>"
                                ><?= Loc::getMessage('NEXT_QUESTION') ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
    <script>
        let timerId = null;
        let timerQuestionId = null;
        // склонение числительных
        function declensionNum(num, words) {
            return words[(num % 100 > 4 && num % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][(num % 10 < 5) ? num % 10 : 5]];
        }
        function countdownTimer() {
            let seconds = $("#timer_all").data('all-time');
            if (seconds <= 0) {
                clearInterval(timerId);
                setTestFailed($('#test_id_hid').val(), 'question');
            }
            const $hours = document.querySelector('.timer_all__hours');
            const $minutes = document.querySelector('.timer_all__minutes');
            const $seconds1 = document.querySelector('.timer_all__seconds');
            const hours = seconds > 0 ? Math.floor(seconds / 60 / 60) % 24 : 0;
            const minutes = seconds > 0 ? Math.floor(seconds / 60) % 60 : 0;
            const seconds1 = seconds > 0 ? Math.floor(seconds) % 60 : 0;
            $hours.textContent = hours < 10 ? '0' + hours : hours;
            $minutes.textContent = minutes < 10 ? '0' + minutes : minutes;
            $seconds1.textContent = seconds1 < 10 ? '0' + seconds1 : seconds1;
            $hours.dataset.title = declensionNum(hours, ['час', 'часа', 'часов']);
            $minutes.dataset.title = declensionNum(minutes, ['минута', 'минуты', 'минут']);
            $seconds1.dataset.title = declensionNum(seconds1, ['секунда', 'секунды', 'секунд']);
            $("#timer_all").data('all-time', seconds-1);
        }
        function setTestFailed(test_id, type) {
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/setFailedAttemptByTime.php',
                data: {
                    id: test_id,
                    user: '<?=$USER->GetID()?>',
                    type: type
                },
                dataType: 'json',
                beforeSend: function () {
                },
                success: function(response){
                    if(response.success)
                        location.href = location.href;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
        function countdownQuestionTimer() {
            let seconds = $("#timer_question").data('all-time');
            if (seconds <= 0) {
                clearInterval(timerQuestionId);
                setTestFailed($('#test_id_hid').val(), 'question');

            }
            const $hours = document.querySelector('.timer_question__hours');
            const $minutes = document.querySelector('.timer_question__minutes');
            const $seconds1 = document.querySelector('.timer_question__seconds');
            const hours = seconds > 0 ? Math.floor(seconds / 60 / 60) % 24 : 0;
            const minutes = seconds > 0 ? Math.floor(seconds / 60) % 60 : 0;
            const seconds1 = seconds > 0 ? Math.floor(seconds) % 60 : 0;
            $hours.textContent = hours < 10 ? '0' + hours : hours;
            $minutes.textContent = minutes < 10 ? '0' + minutes : minutes;
            $seconds1.textContent = seconds1 < 10 ? '0' + seconds1 : seconds1;
            $hours.dataset.title = declensionNum(hours, ['час', 'часа', 'часов']);
            $minutes.dataset.title = declensionNum(minutes, ['минута', 'минуты', 'минут']);
            $seconds1.dataset.title = declensionNum(seconds1, ['секунда', 'секунды', 'секунд']);
            $("#timer_question").data('all-time', seconds-1);
        }
        $(function (){
            if($("#timer_all").length>0) {
                countdownTimer();
                timerId = setInterval(countdownTimer, 1000);
            }
            if($("#timer_question").length>0) {
                countdownQuestionTimer();
                timerQuestionId = setInterval(countdownQuestionTimer, 1000);
            }
        })
    </script>
<?php } require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>