<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Polls\ProcessFBPOll;
use Teaching\CourseCompletion;

if (!is_numeric($_REQUEST['completion_id']) && !(int)$_REQUEST['completion_id'] > 0) {
    die('Bad parameter');
}
global $USER;
$current_completion = (new CourseCompletion())->find((int)$_REQUEST['completion_id']);
$course = \Models\Course::find($current_completion['UF_COURSE_ID'], ['NAME', 'PROPERTY_COURSE_TYPE' , 'PROPERTY_COURSE_FORMAT', 'PROPERTY_EVENTS_COURSE']);
$current_question = [];


CModule::IncludeModule('iblock');


$arTestConstructor = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 20, 'PROPERTY_COURSE' => $current_completion['UF_COURSE_ID']],
    ['ID', 'CODE', 'IBLOCK_ID', 'PROPERTY_TEST_QUSTION', 'PROPERTY_COURSE']
)->Fetch();


if (!$arTestConstructor) {
   if ($course['PROPERTY_COURSE_TYPE_VALUE'] == 'Гибридный' && $course['PROPERTY_EVENTS_COURSE_VALUE'] == 'Да'){
       $type = 149;//ФОС офлайн выездной
   }elseif ($course['PROPERTY_COURSE_FORMAT'] == 'Online'){
       $type = 148;//ФОС онлайн регулярный
	 }elseif ($course['PROPERTY_COURSE_TYPE_VALUE'] == 'Гибридный' ){
       $type = 147;//ФОС офлайн регулярный
   }
    $arTestConstructor = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 20, 'PROPERTY_TYPED_TYPE' => $type , 'ACTIVE' => 'Y'],
        ['ID', 'CODE', 'IBLOCK_ID', 'PROPERTY_TEST_QUSTION', 'PROPERTY_COURSE']
    )->Fetch();
}

if (empty($arTestConstructor) || empty($arTestConstructor['PROPERTY_TEST_QUSTION_VALUE'])) {
	LocalRedirect(str_replace(  'new/' , '' ,$APPLICATION->GetCurPage()));
}

$questions_count = \Models\FeedbackPoll::getAllQuestionsNew( false, $arTestConstructor['PROPERTY_TEST_QUSTION_VALUE']);
$number = count($questions_count);
dd($arTestConstructor);
dd(count($questions_count));

if ($_REQUEST['sended'] == 'Y') {
    $url = '/cabinet/courses/feedback_poll/new/' . (int)$_REQUEST['form_completion'] . '/';
    if ($_REQUEST['coment_poll'] == 'Y') {
        $fields = [
            'UF_COMPLETION_ID' => (int)$_REQUEST['form_completion'],
            'UF_USER_ID' => (int)$_REQUEST['form_user'],
            'UF_QUESTION_NUMBER' => ++$number,
            'UF_IS_COMENT' => 1,
            'UF_COMENT' => trim($_REQUEST['comment']),
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
        ];
    } else {
        $fields = [
            'UF_COMPLETION_ID' => (int)$_REQUEST['form_completion'],
            'UF_USER_ID' => (int)$_REQUEST['form_user'],
            'UF_QUESTION_NUMBER' => (int)$_REQUEST['form_question_number'],
            'UF_QUESTION_ID' => (int)$_REQUEST['question_id'],
            'UF_POINTS' => (int)$_REQUEST['points'],
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
        ];
    }
    ProcessFBPOll::addFromRequest($fields);
    header('Location: ' . $url);
}

$comment_page = false;
$finish_page = false;
var_dump(\Models\FeedbackPoll::isFinishedNew($current_completion['ID'] , $arTestConstructor['PROPERTY_TEST_QUSTION_VALUE']));
if (ProcessFBPOll::isRunning($current_completion['ID'])) {
    if (\Models\FeedbackPoll::isFinishedNew($current_completion['ID'] , $arTestConstructor['PROPERTY_TEST_QUSTION_VALUE'])) {
        if (\Models\FeedbackPoll::isEnded($current_completion['ID'])) {
            $finish_page = true;
        } else {
            $comment_page = true;
        }
    } else {
        $current_question = \Models\FeedbackPoll::getNextQuestionNew($current_completion['ID'], $arTestConstructor['PROPERTY_TEST_QUSTION_VALUE']);
    }
} else {
    $current_question = \Models\FeedbackPoll::getNextQuestionNew($current_completion['ID'], $arTestConstructor['PROPERTY_TEST_QUSTION_VALUE']);
}
?>
<div class="container">
	<h1 class="h1">Обратная связь по качеству проведения курса "<?= $course['NAME'] ?>"</h1>
	<div class="content-block">
      <?php

      if ($finish_page) {
          ?>
				<div class="poll">
					<h3 class="h3 lowercase center">Опрос завершен</h3>
					<p class="text-center">Вы ответили на все вопросы опроса. Спасибо за уделенное нам время</p>
					<div class="btn-center" style="margin-top: 15px">
						<a href="/cabinet/common/" class="btn">В кабинет</a>
					</div>
				</div>
      <?php } else {
          if ($comment_page) {
              ?>
						<form action="" method="get" enctype="multipart/form-data">
							<input type="hidden" name="sended" value="Y">
							<input type="hidden" name="form_completion" value="<?= $current_completion['ID'] ?>">
							<input type="hidden" name="form_user" value="<?= $USER->GetID() ?>">
							<input type="hidden" name="coment_poll" value="Y">
							<h3 class="h3 lowercase center">Введите комментарий к тренингу (не обязательно)</h3>
							<div class="form-group">
								<textarea name="comment" placeholder="Текст комментария"></textarea>
							</div>
							<div class="btn-center" style="justify-content: end">
								<button type="submit" class="btn">Завершить опрос</button>
							</div>
						</form>
          <?php } else { ?>
						<div class="poll">
							<div class="poll__step">
								шаг <span><?= $current_question['SORT'] ?></span> из <?= count($questions_count) ?>
							</div>

							<form class="poll__content" action="" method="post">
								<input type="hidden" name="sended" value="Y">
								<input type="hidden" name="form_completion" value="<?= $current_completion['ID'] ?>">
								<input type="hidden" name="form_user" value="<?= $USER->GetID() ?>">
								<input type="hidden" name="form_question_number" value="<?= $current_question['SORT'] ?>">

								<input type="hidden" name="question_id" value="<?= $current_question['ID'] ?>">

								<h3 class="h3 lowercase center"><?= $current_question['NAME'] ?></h3>


								<div class="poll-item" style="display: flex; justify-content: space-between">
                    <?php foreach (range(1, 5) as $point) { ?>
											<div class="radio-item">
												<input type="radio" value="<?= $point ?>" id="poll-question-<?= $point ?>" name="points"
															 checked />
												<label for="poll-question-<?= $point ?>"><?= $point ?></label>
											</div>
                    <?php } ?>
								</div>
								<div class="btn-center" style="justify-content: end">
									<button type="submit" class="btn">Следующий вопрос</button>
								</div>
							</form>
						</div>
          <?php }
      } ?>
	</div>

</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
