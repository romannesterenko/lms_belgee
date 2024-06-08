<?php

namespace Polls;

use CIBlockSection;
use Helpers\IBlockHelper,
    Helpers\UserHelper;

class Polls
{
    private array $list;

    public function __construct()
    {
        IBlockHelper::includeIBlockModule();
    }

    public function getByUser($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $this->getPolls(['UF_ROLE' => UserHelper::getRoleByUser($user_id)]);
        return $this;
    }

    public function getByCode($code)
    {
        return $this->getPolls(['CODE' => $code]);
    }

    public function getPolls($filter, $select=[])
    {
        $list = [];
        $select = count($select)==0?[]:array_merge(['ID', 'NAME', 'CODE'], $select);
        $arFilter = array('IBLOCK_ID' => \Helpers\IBlockHelper::getPollsIBlock());
        $filter = array_merge($arFilter, $filter);
        $db_list = CIBlockSection::GetList(['ID' => 'DESC'], $filter, false, $select);
        while ($ar_result = $db_list->GetNext()) {
            $list[] = $ar_result;
        }
        return $list;
    }

    public function getArray()
    {
        return $this->list;
    }

    public function getOne()
    {
        return $this->list[0];
    }

    public function getById($id)
    {
        return $this->getPolls(['ID' => $id])->getOne();
    }

    public function goToFirstQuestionOfPoll($all_questions)
    {
        LocalRedirect($all_questions[0]['DETAIL_PAGE_URL']);
    }

    public function goToQuestionOfPollByNumber(array $all_questions, int $need_question)
    {
        /*dump($all_questions[$need_question]);
        die();*/
        LocalRedirect($all_questions[$need_question]['DETAIL_PAGE_URL']);
    }
}