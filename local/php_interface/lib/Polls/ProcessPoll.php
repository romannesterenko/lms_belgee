<?php

namespace Polls;

use CIBlockSection;
use Helpers\IBlockHelper,
    Helpers\UserHelper;

class ProcessPoll
{
    private string $HlDataClass;
    private array $list;

    public function __construct()
    {
        $this->HlDataClass = \Helpers\HLBlockHelper::initialize('process_poll');
    }

    public function isBegined($poll_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = $this->getByPollAndUser($poll_id, $user_id);
        return $list[0]['ID'] > 0;
    }

    public function isFinished($poll_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = $this->getByPollAndUser($poll_id, $user_id);
        if (empty($list[0]['ID']))
            return false;
        return !empty($list[0]['UF_FINISHED']);
    }

    private function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        $this->list = $this->HlDataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
        return $this;
    }

    public function startProcess($poll_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if (!$this->isExists($poll_id, $user_id))
            $this->startPollSession($poll_id, $user_id);
    }

    public function process(array $request)
    {
        $list = $this->getByPollAndUser($request['poll_id'], $request['user_id']);
        if ($list[0]['ID'] > 0) {
            $answers = is_array($list[0]['UF_ANSWERS']) ? array_merge($list[0]['UF_ANSWERS'], [$request['value']]) : [$request['value']];
            $fields = [
                'UF_CURRENT_QUESTION' => ++$request['cur_question'],
                'UF_LAST_ACTIVE_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_ANSWERS' => $answers,
            ];
            if ($request['cur_question'] == $request['all_questions'])
                $fields['UF_FINISHED'] = 1;
            $result = $this->HlDataClass::update($list[0]['ID'], $fields);
            return $result->isSuccess();
        }
    }

    public function getCurrentQuestionNumber($ID, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $poll = $this->getByPollAndUser($ID, $user_id);
        return (int)$poll[0]['UF_CURRENT_QUESTION'];
    }

    public function getCompletePollsByUser($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(['!UF_FINISHED' => false, 'UF_USER_ID' => $user_id])->getArray();
    }

    public function getCompletePollIdsByCurrentUser()
    {
        $ids = [];
        foreach ($this->getCompletePollsByUser() as $poll) {
            $ids[] = $poll['UF_POLL_ID'];
        }
        return $ids;
    }

    public function getCompletePolls($poll_id)
    {
        return $this->get(['!UF_FINISHED' => false, 'UF_POLL_ID' => $poll_id])->getArray();
    }

    private function getByUser($user_id)
    {
        return $this->get(['UF_USER_ID' => $user_id])->getArray();
    }

    private function getByPollAndUser($poll_id, $user_id)
    {
        return $this->get(['UF_POLL_ID' => $poll_id, 'UF_USER_ID' => $user_id])->getArray();
    }

    private function getArray()
    {
        return $this->list;
    }

    private function isExists($poll_id, $user_id)
    {
        $list = $this->getByPollAndUser($poll_id, $user_id);
        return $list[0]['ID'] > 0;
    }

    private function startPollSession($poll_id, $user_id)
    {
        $this->HlDataClass::add(
            [
                'UF_POLL_ID' => $poll_id,
                'UF_CURRENT_QUESTION' => 0,
                'UF_USER_ID' => $user_id,
                'UF_BEGIN_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_LAST_ACTIVE_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_FINISHED' => false,
            ]
        );
    }
}