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
$poll_obj = new Polls();
$poll_processed = new ProcessPoll();
$poll_questions_obj = new PollQuestions();
$poll = current($poll_obj->getByCode($arResult['VARIABLES']['SECTION_CODE']));
$poll_questions = $poll_questions_obj->getByPoll($poll['ID']);

if(!$poll_processed->isFinished($poll['ID'])&&$poll_processed->isBegined($poll['ID'])){
    $need_question = $poll_processed->getCurrentQuestionNumber($poll['ID']);
    $poll_obj->goToQuestionOfPollByNumber($poll_questions, $need_question);
}

?>
<h1 class="h1"><?=GetMessage('POLL_LABEL')?> «<?=$poll['NAME']?>» </h1>

<div class="content-block">
    <div class="poll">
        <div class="poll__step">
            <?=GetMessage('STEPS_LABEL')?>
            <span><?=count($poll_questions)?></span>
        </div>
        <div class="poll__content">
            <p><?=$poll['DESCRIPTION']?></p>
            <?php if($poll_processed->isFinished($poll['ID'])){?>
                <div class="btn-center pb-10">
                    <?=GetMessage('POLL_COMPLETE_MESSAGE')?>
                </div>
                <div class="btn-center">
                    <a href="/cabinet/common/" class="btn"><?=GetMessage('TO_CABINET_BUTTON')?></a>
                </div>
            <?php }else{?>
                <div class="btn-center">
                    <a href="<?=$poll_questions[0]['DETAIL_PAGE_URL']?>" class="btn"><?=GetMessage('BEGIN_POLL_BUTTON')?></a>
                </div>
            <?php }?>
        </div>
    </div>
</div>
