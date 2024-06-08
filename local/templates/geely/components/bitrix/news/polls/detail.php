<?php use Polls\PollQuestions;
use Polls\Polls;
use Polls\ProcessPoll;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$questions = new PollQuestions();
$poll_processed = new ProcessPoll();
$polls = new Polls();
$poll = current($polls->getByCode($arResult['VARIABLES']['SECTION_CODE']));

$all_questions = $questions->getByPoll($poll['ID']);

$question = $questions->getByPollAndCode($poll['ID'], $arResult['VARIABLES']['ELEMENT_CODE']);

$curr_question = $question[0]['SORT'];

$curr_question1 = --$question[0]['SORT'];
if($curr_question1==0&&!$poll_processed->isBegined($poll['ID'])){
    $poll_processed->startProcess($poll['ID']);
}

$need_question = $poll_processed->getCurrentQuestionNumber($poll['ID']);

if($poll_processed->isFinished($poll['ID'])) {
    LocalRedirect($poll['SECTION_PAGE_URL']);
}

if($curr_question1!=$need_question){
    $polls->goToQuestionOfPollByNumber($all_questions, $need_question);
}

if($curr_question1!=0){
    if(!$poll_processed->isBegined($poll['ID'])){
        $polls->goToFirstQuestionOfPoll($all_questions);
    }
}
if($poll_processed->isFinished($poll['ID'])){
    LocalRedirect($poll['SECTION_PAGE_URL']);
}else{?>
    <h1 class="h1"><?=GetMessage('POLL_DETAIL_POLL')?> «<?=$poll['NAME']?>» </h1>

    <div class="content-block">
        <div class="poll">
            <div class="poll__step">
                <?=GetMessage('POLL_DETAIL_STEP')?> <span><?=$curr_question?></span> <?=GetMessage('POLL_DETAIL_FROM')?> <?=count($all_questions)?>
            </div>
            <div class="poll__content">
                <h3 class="h3 lowercase center"><?=$question[0]['NAME']?></h3>
                <div class="poll-item">
                    <?php foreach ($questions->getVariants($question[0]['ID']) as $variant){?>
                        <div class="radio-item">
                            <input type="radio" id="poll-question-<?=$variant['id']?>" class="poll_question_radio" value="<?=$question[0]['ID']?>_<?=$variant['id']?>" name="poll-<?=$poll['ID']?>" />
                            <label for="poll-question-<?=$variant['id']?>"><?=$variant['text']?></label>
                        </div>
                    <?php }?>
                </div>

                <div class="btn-center">
                    <?php if($curr_question<count($all_questions)){?>
                        <a href="<?=$questions->getNext($question, $curr_question, $all_questions)?>" data-poll-id="<?=$poll['ID']?>" data-user-id="<?=$USER->GetID()?>" data-current-question="<?=$curr_question1?>" data-all-questions="<?=count($all_questions)?>" class="btn next_question_btn disabled_link"><?=GetMessage('POLL_DETAIL_NEXT_BUTTON')?></a>
                    <?php }else{?>
                        <a href="#" data-poll-id="<?=$poll['ID']?>" data-user-id="<?=$USER->GetID()?>" data-current-question="<?=$curr_question1?>" data-all-questions="<?=count($all_questions)?>" class="btn next_question_btn disabled_link"><?=GetMessage('POLL_DETAIL_FINISH_POLL')?></a>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
<?php }?>
