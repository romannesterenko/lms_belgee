<?php
namespace Teaching;
use CIBlockSection;
use Helpers\IBlockHelper,
    Helpers\UserHelper;

class ProcessTest
{
    private $HlDataClass;
    private $list;

    public function __construct()
    {
        $this->HlDataClass = \Helpers\HLBlockHelper::initialize('process_test');
    }

    public function isBegined($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = $this->getByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    public function isRetestBegined($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = $this->getRetestByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    public function isPreBegined($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = $this->getPreTestByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    public function isFinished($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = current($this->get(['UF_TEST_ID' => $test_id, 'UF_USER_ID' => $user_id, 'UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => false, 'UF_IS_RETEST' => false])->getArray());
        if (empty($list['ID']))
            return false;
        return !empty($list['UF_FINISHED']);
    }

    public function isRetestFinished($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = current($this->get(['UF_TEST_ID' => $test_id, 'UF_USER_ID' => $user_id, 'UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => false, 'UF_IS_RETEST' => true])->getArray());
        if (empty($list['ID']))
            return false;
        return !empty($list['UF_FINISHED']);
    }

    public function isPreFinished($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $list = current($this->get(['UF_TEST_ID' => $test_id, 'UF_USER_ID' => $user_id, 'UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => true])->getArray());
        if (empty($list['ID']))
            return false;
        return !empty($list['UF_FINISHED']);
    }

    public function getByCompletion($completion_id)
    {
        return current($this->get(['UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => false, "UF_IS_RETEST" => false])->getArray());
    }

    public function getRetestByCompletion($completion_id)
    {
        return current($this->get(['UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => false, "UF_IS_RETEST" => true])->getArray());
    }

    public function getPreTestByCompletion($completion_id)
    {
        return current($this->get(['UF_COMPLETION'=>$completion_id, 'UF_IS_PRE' => true, "UF_IS_RETEST" => false])->getArray());
    }

    public function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        $this->list = $this->HlDataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
        return $this;
    }

    public function delete($id)
    {
        $list = $this->getByTestAndUser($id);
        if($list[0]['ID']>0)
            $this->HlDataClass::delete($list[0]['ID']);
    }

    public function startProcess($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if (!$this->isExists($test_id, $user_id, $completion_id)) {
            (new CourseCompletion())->resetPoints($completion_id);
            $this->startTestSession($test_id, $user_id, $completion_id);
            $common_completions = new \Teaching\CourseCompletion();
            $common_completions->incrementAttempt($completion_id);

        }
    }

    public function startRetestProcess($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if (!$this->isRetestExists($test_id, $user_id, $completion_id)) {
            (new CourseCompletion())->resetPoints($completion_id);
            $this->startRetestSession($test_id, $user_id, $completion_id);
        }
    }

    public function startPreProcess($test_id, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if (!$this->isPreExists($test_id, $user_id, $completion_id)) {
            $this->startPreTestSession($test_id, $user_id, $completion_id);
        }
    }

    public function process($request)
    {
        $list = $this->getByTestAndUser($request['test_id'], $request['user_id']);
        if ($list[0]['ID'] > 0) {
            $old_points = $list[0]['UF_POINTS'];
            $old_points+=\Teaching\Tests::checkCorrectAnswer($request['value']);
            $max_points = \Teaching\Tests::getMaxPoints($request['test_id']);
            if($old_points>$max_points) {
                $old_points = $max_points;
            }
            if($old_points>0)
                (new CourseCompletion())->setViewedCourse($list[0]['UF_COMPLETION']);
            if(check_full_array($list[0]['UF_ANSWERS'])) {
                if(!in_array($request['value'], $list[0]['UF_ANSWERS']))
                    $answers = array_merge($list[0]['UF_ANSWERS'], [$request['value']]);
                else
                    $answers = $list[0]['UF_ANSWERS'];
            } else {
                $answers = [$request['value']];
            }
            $fields = [
                'UF_CURRENT_QUESTION' => $request['cur_question'] == $request['all_questions']?$request['cur_question']:($request['cur_question']+1),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_ANSWERS' => $answers,
                'UF_POINTS' => $old_points,
            ];
            if ($request['cur_question'] == $request['all_questions']) {
                if(\Teaching\Tests::getMinPointsForComplete($request['test_id'])>$old_points){
                    $fields['UF_FINISHED'] = 1;
                    $response['finished'] = true;
                    $response['correct'] = false;
                } else {
                    $fields['UF_FINISHED'] = 1;
                    $response['finished'] = true;
                    $response['correct'] = true;
                }

            } else {
                $response['href'] = \Teaching\Tests::getNextQuestionLink($request['test_id'], $request['value']);
            }
            $result = $this->HlDataClass::update($list[0]['ID'], $fields);
            $response['request'] = $request;
            $response['success'] = $result->isSuccess();
            return $response;
        }
    }
    public function processReTest($request)
    {
        $list = $this->getRetestByTestAndUser($request['test_id'], $request['user_id']);
        if ($list[0]['ID'] > 0) {
            $old_points = $list[0]['UF_POINTS'];
            $old_points+=\Teaching\Tests::checkCorrectAnswer($request['value']);
            $max_points = \Teaching\Tests::getMaxPoints($request['test_id']);
            if($old_points>$max_points) {
                $old_points = $max_points;
            }
            if(check_full_array($list[0]['UF_ANSWERS'])) {
                if(!in_array($request['value'], $list[0]['UF_ANSWERS']))
                    $answers = array_merge($list[0]['UF_ANSWERS'], [$request['value']]);
                else
                    $answers = $list[0]['UF_ANSWERS'];
            } else {
                $answers = [$request['value']];
            }
            //$answers = is_array($list[0]['UF_ANSWERS']) ? array_merge($list[0]['UF_ANSWERS'], [$request['value']]) : [$request['value']];
            $fields = [
                'UF_CURRENT_QUESTION' => $request['cur_question'] == $request['all_questions']?$request['cur_question']:($request['cur_question']+1),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_ANSWERS' => $answers,
                'UF_POINTS' => $old_points,
            ];
            if ($request['cur_question'] == $request['all_questions']) {

                    $fields['UF_FINISHED'] = 1;
                    $response['finished'] = true;
                    $response['correct'] = true;

            } else {
                $response['href'] = \Teaching\Tests::getNextReTestQuestionLink($request['test_id'], $request['value']);
            }
            $result = $this->HlDataClass::update($list[0]['ID'], $fields);
            $response['request'] = $request;
            $response['success'] = $result->isSuccess();
            return $response;
        }
    }
    public function processPreTest($request)
    {
        $list = $this->getPreByTestAndUser($request['test_id'], $request['user_id']);
        if ($list[0]['ID'] > 0) {
            $old_points = $list[0]['UF_POINTS'];
            $old_points+=\Teaching\Tests::checkCorrectAnswer($request['value']);
            $max_points = \Teaching\Tests::getMaxPoints($request['test_id']);
            if($old_points>$max_points) {
                $old_points = $max_points;
            }
            if(check_full_array($list[0]['UF_ANSWERS'])) {
                if(!in_array($request['value'], $list[0]['UF_ANSWERS']))
                    $answers = array_merge($list[0]['UF_ANSWERS'], [$request['value']]);
                else
                    $answers = $list[0]['UF_ANSWERS'];
            } else {
                $answers = [$request['value']];
            }
            //$answers = is_array($list[0]['UF_ANSWERS']) ? array_merge($list[0]['UF_ANSWERS'], [$request['value']]) : [$request['value']];
            $fields = [
                'UF_CURRENT_QUESTION' => $request['cur_question'] == $request['all_questions']?$request['cur_question']:($request['cur_question']+1),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_ANSWERS' => $answers,
                'UF_POINTS' => $old_points,
            ];
            if ($request['cur_question'] == $request['all_questions']) {

                    $fields['UF_FINISHED'] = 1;
                    $response['finished'] = true;
                    $response['correct'] = true;

            } else {
                $response['href'] = \Teaching\Tests::getNextPreTestQuestionLink($request['test_id'], $request['value']);
            }
            $result = $this->HlDataClass::update($list[0]['ID'], $fields);
            $response['request'] = $request;
            $response['success'] = $result->isSuccess();
            return $response;
        }
    }

    public function getCurrentQuestionNumber($ID, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $poll = $this->getByCompletion($completion_id);
        if((int)$poll['UF_CURRENT_QUESTION']>0)
            return (int)$poll['UF_CURRENT_QUESTION'];
        else
            return 1;
    }

    public function getRetestCurrentQuestionNumber($ID, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $poll = $this->getRetestByCompletion($completion_id);
        if((int)$poll['UF_CURRENT_QUESTION']>0)
            return (int)$poll['UF_CURRENT_QUESTION'];
        else
            return 1;
    }

    public function getPreCurrentQuestionNumber($ID, $user_id = 0, $completion_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $poll = $this->getPreTestByCompletion($completion_id);
        if((int)$poll['UF_CURRENT_QUESTION']>0)
            return (int)$poll['UF_CURRENT_QUESTION'];
        else
            return 1;
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
            $ids[] = $poll['UF_TEST_ID'];
        }
        return $ids;
    }

    public function goToQuestionOfTestByNumber($need_question, $test_id)
    {
        $questions = array_values(\Teaching\Tests::getQuestionsByTest($test_id));

        if($need_question>count($questions))
            $need_question=count($questions);
        LocalRedirect('/cabinet/courses/testing/' . $test_id.'/'.$questions[($need_question-1)]['ID'].'/');
    }

    public function goToQuestionOfReTestByNumber($need_question, $test_id)
    {
        $questions = array_values(\Teaching\Tests::getQuestionsByTest($test_id));
        if($need_question>count($questions))
            $need_question=count($questions);
        LocalRedirect('/cabinet/courses/re-testing/' . $test_id.'/'.$questions[($need_question-1)]['ID'].'/');
    }

    public function goToQuestionOfPreTestByNumber($need_question, $test_id)
    {
        $questions = array_values(\Teaching\Tests::getQuestionsByTest($test_id));
        if($need_question>count($questions))
            $need_question=count($questions);
        LocalRedirect('/cabinet/courses/pretesting/' . $test_id.'/'.$questions[($need_question-1)]['ID'].'/');
    }

    public function getCurrentPoints($test_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $list = $this->getByTestAndUser($test_id, $user_id);
        if ($list[0]['ID'] > 0)
            return $list[0]['UF_POINTS'];
        return 0;
    }

    public function getResetCurrentPoints($test_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $list = $this->getRetestByTestAndUser($test_id, $user_id);
        if ($list[0]['ID'] > 0)
            return $list[0]['UF_POINTS'];
        return 0;
    }

    public function getPreCurrentPoints($test_id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $list = $this->getPreByTestAndUser($test_id, $user_id);
        if ($list[0]['ID'] > 0)
            return $list[0]['UF_POINTS'];
        return 0;
    }

    public function setFinished($id)
    {
        $fields['UF_FINISHED'] = 1;
        $fields['UF_FAILED_BY_TIME'] = 1;
        $result = $this->HlDataClass::update($id, $fields);
    }

    public function setRetestFinished($id)
    {
        $fields['UF_FINISHED'] = 1;
        $fields['UF_FAILED_BY_TIME'] = 1;
        $result = $this->HlDataClass::update($id, $fields);
    }

    public function setPreTestFinished($id)
    {
        $fields['UF_FINISHED'] = 1;
        $fields['UF_FAILED_BY_TIME'] = 1;
        $result = $this->HlDataClass::update($id, $fields);
    }

    private function getByUser($user_id)
    {
        return $this->get(['UF_USER_ID' => $user_id, 'UF_IS_PRE' => false])->getArray();
    }

    public function getByTestAndUser($test_id, $user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(
            [
                'UF_TEST_ID' => $test_id,
                'UF_USER_ID' => $user_id,
                'UF_IS_PRE' => false,
                'UF_IS_RETEST' => false,
            ]
        )->getArray();
    }

    public function getRetestByTestAndUser($test_id, $user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(
            [
                'UF_TEST_ID' => $test_id,
                'UF_USER_ID' => $user_id,
                'UF_IS_PRE' => false,
                'UF_IS_RETEST' => true,
            ]
        )->getArray();
    }

    public function getPreByTestAndUser($test_id, $user_id=0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(
            [
                'UF_TEST_ID' => $test_id,
                'UF_USER_ID' => $user_id,
                'UF_IS_PRE' => true,
                'UF_IS_RETEST' => false,
            ]
        )->getArray();
    }

    public function getArray()
    {
        return $this->list;
    }

    private function isExists($test_id, $user_id, $completion_id)
    {
        $list = $this->getByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    private function isRetestExists($test_id, $user_id, $completion_id)
    {
        $list = $this->getRetestByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    private function isPreExists($test_id, $user_id, $completion_id)
    {
        $list = $this->getPreTestByCompletion($completion_id);
        return $list['ID'] > 0;
    }

    private function startTestSession($test_id, $user_id, $completion_id)
    {
        $this->HlDataClass::add(
            [
                'UF_TEST_ID' => $test_id,
                'UF_CURRENT_QUESTION' => 1,
                'UF_USER_ID' => $user_id,
                'UF_BEGIN_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_FINISHED' => false,
                'UF_POINTS' => 0,
                'UF_COMPLETION' => $completion_id,
                'UF_IS_PRE' => false,
                'UF_IS_RETEST' => false,
            ]
        );
    }

    private function startRetestSession($test_id, $user_id, $completion_id)
    {
        $this->HlDataClass::add(
            [
                'UF_TEST_ID' => $test_id,
                'UF_CURRENT_QUESTION' => 1,
                'UF_USER_ID' => $user_id,
                'UF_BEGIN_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_FINISHED' => false,
                'UF_POINTS' => 0,
                'UF_COMPLETION' => $completion_id,
                'UF_IS_PRE' => false,
                'UF_IS_RETEST' => true,
            ]
        );
    }
    private function startPreTestSession($test_id, $user_id, $completion_id)
    {
        $this->HlDataClass::add(
            [
                'UF_TEST_ID' => $test_id,
                'UF_CURRENT_QUESTION' => 1,
                'UF_USER_ID' => $user_id,
                'UF_BEGIN_DATETIME' => \Helpers\DateHelper::getCurDateTime(),
                'UF_LAST_ACTIVE' => \Helpers\DateHelper::getCurDateTime(),
                'UF_FINISHED' => false,
                'UF_POINTS' => 0,
                'UF_COMPLETION' => $completion_id,
                'UF_IS_PRE' => true,
                'UF_IS_RETEST' => false,
            ]
        );
    }
}