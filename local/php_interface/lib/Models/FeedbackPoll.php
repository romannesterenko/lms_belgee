<?php

namespace Models;

use danog\MadelineProto\Broadcast\Status;
use Helpers\IBlockHelper;
use Polls\ProcessFBPOll;

class FeedbackPoll
{

    public static function getFirstQuestion()
    {
        return self::getQuestionByNumber();
    }
    public static function getFirstQuestionNew($iqid , $isid)
    {
        return self::getQuestionByNumberNew($iqid , $isid);
    }

    public static function getQuestionByNumber($question_number = 1)
    {
        return current(self::getList(['SORT' => $question_number, 'ACTIVE' => 'Y'], ['ID', 'NAME', 'SORT']));
    }

    public static function isFinished($completion_id)
    {
        $questions = self::getList(['ACTIVE' => 'Y']);
        $last_question = ProcessFBPOll::getLastQuestion($completion_id);
        return count($questions) == $last_question['UF_QUESTION_NUMBER'];
    }
    public static function isFinishedNew($completion_id , $iSid)
    {
        \CModule::includeModule('iblock');
        $iblock = \Bitrix\Iblock\Iblock::wakeUp(12)->getEntityDataClass();
        $questions = $iblock::getList([
            'filter' => ['ACTIVE' => 'Y' , 'IBLOCK_SECTION_ID' => $iSid],
            'select' => ['*']
        ])->fetchAll();

        $last_question = ProcessFBPOll::getLastQuestion($completion_id ,false, $iSid);
        return count($questions) == $last_question['UF_QUESTION_NUMBER'];
    }

    public static function isEnded($completion_id)
    {
        $last_question = ProcessFBPOll::getLastQuestion($completion_id, true);
        return $last_question['UF_IS_COMENT'] == 1;
    }

    public static function isEndedByCompletion($completion_id)
    {
        $last_question = ProcessFBPOll::getLastQuestionByCompletion($completion_id, true);
        return $last_question['UF_IS_COMENT'] == 1;
    }

    public static function getList($filter, $select = [], $order = ['SORT' => 'ASC'])
    {
        IBlockHelper::includeIBlockModule();
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getFeedbackPollIBlock(), 'ACTIVE' => 'Y'], $filter);
        $res = \CIBlockElement::GetList($order, $arFilter, false, array(), $select);
        $list = [];
        while ($ob = $res->GetNextElement()) {
            $list[] = $ob->GetFields();
        }
        return $list;
    }

    public static function getNextQuestion($completion_id)
    {
        $last_question = ProcessFBPOll::getLastQuestion($completion_id);
        if (check_full_array($last_question) && $last_question['ID'] > 0)
            return self::getQuestionByNumber((++$last_question['UF_QUESTION_NUMBER']));
        return self::getFirstQuestion();
    }

    public static function getAllQuestions($sort_by_themes = false)
    {
        if ($sort_by_themes) {
            $questions = self::getList(['ACTIVE' => 'Y']);
            $questions_data = [];
            $sections = [];
            $themes = [];

            foreach ($questions as $question) {
                $sections[] = $question['IBLOCK_SECTION_ID'];
            }

            $arFilter = array('IBLOCK_ID' => \Helpers\IBlockHelper::getFeedbackPollIBlock(), 'GLOBAL_ACTIVE' => 'Y', 'ID' => array_unique($sections));
            $db_list = \CIBlockSection::GetList(array($by => $order), $arFilter, true);
            while ($result = $db_list->Fetch()) {
                $themes[$result['ID']] = $result;
            }
            foreach ($questions as $question) {
                if (!check_full_array($questions_data[$question['IBLOCK_SECTION_ID']]['THEME']))
                    $questions_data[$question['IBLOCK_SECTION_ID']]['THEME'] = $themes[$question['IBLOCK_SECTION_ID']];
                $questions_data[$question['IBLOCK_SECTION_ID']]['QUESTIONS'][] = $question;
            }
            return $questions_data;
        } else {
            return self::getList(['ACTIVE' => 'Y']);
        }
    }
    public static function getAllQuestionsNew($sort_by_themes = false , $iId)
    {
        if ($sort_by_themes) {
            \CModule::includeModule('iblock');
            $iblock = \Bitrix\Iblock\Iblock::wakeUp(12)->getEntityDataClass();
            $questions = $iblock::getList([
                'filter' => ['ACTIVE' => 'Y' , 'IBLOCK_SECTION_ID' => $iId],
                'select' => ['*']
            ])->fetchAll();
            $questions_data = [];
            $sections = [];
            $themes = [];

            foreach ($questions as $question) {
                $sections[] = $question['IBLOCK_SECTION_ID'];
            }

            $arFilter = array('IBLOCK_ID' => 12, 'GLOBAL_ACTIVE' => 'Y', 'ID' => array_unique($sections));
            $db_list = \CIBlockSection::GetList(array($by => $order), $arFilter, true);
            while ($result = $db_list->Fetch()) {
                $themes[$result['ID']] = $result;
            }
            foreach ($questions as $question) {
                if (!check_full_array($questions_data[$question['IBLOCK_SECTION_ID']]['THEME']))
                    $questions_data[$question['IBLOCK_SECTION_ID']]['THEME'] = $themes[$question['IBLOCK_SECTION_ID']];
                $questions_data[$question['IBLOCK_SECTION_ID']]['QUESTIONS'][] = $question;
            }
            return $questions_data;
        } else {
            \CModule::includeModule('iblock');
            $iblock = \Bitrix\Iblock\Iblock::wakeUp(12)->getEntityDataClass();
            $questions = $iblock::getList([
                'filter' => ['ACTIVE' => 'Y' , 'IBLOCK_SECTION_ID' => $iId],
                'select' => ['*']
            ])->fetchAll();
            return $questions;
        }
    }
    public static function getQuestionByNumberNew($question_number = 1, $iSectionID)
    {
        \CModule::includeModule('iblock');
        $iblock = \Bitrix\Iblock\Iblock::wakeUp(12)->getEntityDataClass();
        $item = $iblock::getList([
            'filter' => ['=IBLOCK_SECTION_ID' => $iSectionID, 'ACTIVE' => 'Y', 'SORT' => $question_number],
            'select' => ['*']
        ])->fetch();
        while (!check_full_array($item)){
            $question_number++;
            $item = $iblock::getList([
                'filter' => ['=IBLOCK_SECTION_ID' => $iSectionID, 'ACTIVE' => 'Y', 'SORT' => $question_number],
                'select' => ['*']
            ])->fetch();
        }
        return $item;
    }

    public static function getNextQuestionNew($completion_id , $iSID)
    {
        $last_question = ProcessFBPOll::getLastQuestion($completion_id, false ,$iSID);

        if(check_full_array($last_question) && $last_question['ID']>0)
            return self::getQuestionByNumberNew((++$last_question['UF_QUESTION_NUMBER']) ,$iSID);
        return self::getFirstQuestionNew(1 ,$iSID );
    }
}