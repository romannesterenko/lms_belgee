<?php

namespace Polls;

use CIBlockElement;
use Helpers\IBlockHelper,
    Helpers\UserHelper,
    Polls\ProcessPoll;

class PollQuestions
{
    private array $list;
    private array $first_elem;
    private int $count_questions = 0;
    private array $prev_elem;
    public array $next_elem;

    public function __construct()
    {
        IBlockHelper::includeIBlockModule();
    }

    public function getByPoll($poll_id)
    {
        return $this->get(['IBLOCK_SECTION_ID' => (int)$poll_id]);
    }

    private function get($filter)
    {
        $list = [];
        $arFilter = array('IBLOCK_ID' => \Helpers\IBlockHelper::getPollsIBlock());
        $filter = array_merge($arFilter, $filter);
        $res = CIBlockElement::GetList(array('SORT' => 'ASC'), $filter);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[] = $arFields;
            $this->count_questions++;
        }
        return $list;
    }

    public function getArray()
    {
        return $this->list;
    }

    public function getCount()
    {
        return count($this->list);
    }

    public function getOne()
    {
        $this->first_elem = $this->list[0];
        return $this;
    }

    public function getFirst()
    {
        $this->first_elem = $this->list[0];
        return $this;
    }

    public function isFirst()
    {
        return $this->getNumber() == 1;
    }

    public function getUrl()
    {
        return $this->first_elem['DETAIL_PAGE_URL'];
    }

    public function getByPollAndCode($poll, $code)
    {
        return $this->get(['SECTION_ID' => $poll, 'CODE' => $code]);
    }

    public function getNumber()
    {
        return (int)$this->first_elem['SORT'];
    }

    public function getId()
    {
        return $this->first_elem['ID'];
    }

    public function getName()
    {
        return $this->first_elem['NAME'];
    }

    public function getPrev()
    {
        if ($this->first_elem['SORT'] == 1) {
            return '#';
        } else {
            \Helpers\IBlockHelper::includeIBlockModule();
            $arFilter = array('IBLOCK_ID' => \Helpers\IBlockHelper::getPollsIBlock(), 'SORT' => --$this->first_elem['SORT']);
            $res = \CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                return $arFields['DETAIL_PAGE_URL'];
            }
        }
    }

    public function getNext($question_array, $curr_question, $all_questions)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        $arFilter = array('IBLOCK_ID' => \Helpers\IBlockHelper::getPollsIBlock(), 'SECTION_ID' => $question_array[0]['IBLOCK_SECTION_ID'], 'SORT' => ++$question_array[0]['SORT']);
        $res = \CIBlockElement::GetList(array('SORT' => 'ASC'), $arFilter);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            return $arFields['DETAIL_PAGE_URL'];
        }
    }

    public function getVariants($id)
    {
        $return_array = [];
        \Helpers\IBlockHelper::includeIBlockModule();
        $db_props = \CIBlockElement::GetProperty(\Helpers\IBlockHelper::getPollsIBlock(), $id, array("sort" => "asc"), array("CODE" => "ANSWER"));
        while ($ar_props = $db_props->Fetch()) {
            $return_array[] = ['id' => $ar_props["PROPERTY_VALUE_ID"], 'text' => $ar_props['VALUE']];
        }
        return $return_array;
    }

    /**
     * @return array
     */
    public function getCountQuestions()
    {
        return $this->count_questions;
    }
}