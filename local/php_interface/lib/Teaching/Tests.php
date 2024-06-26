<?php
namespace Teaching;
use \Helpers\IBlockHelper as Iblock;
use Helpers\UserHelper;
use Models\Course;

class Tests
{

    public static function getById($id)
    {
        return current(Iblock::getElements(['IBLOCK_ID' => Iblock::getTestsIblock(), 'ID' => $id]));
    }

    public static function generateLinkToTest($course_id){
        $test = current(self::getTestByCourse($course_id));

        $question = current(self::getQuestionsByTest($test['ID']));

        return '/cabinet/courses/testing/'.$test['ID'].'/'.$question['ID'].'/';
    }

    public static function generateLinkToReTest($course_id){
        $test = current(self::getTestByCourse($course_id));

        $question = current(self::getQuestionsByTest($test['ID']));

        return '/cabinet/courses/re-testing/'.$test['ID'].'/'.$question['ID'].'/';
    }

    public static function generateLinkToScormReTest($course_id){
        return '/cabinet/courses/scorm-re-testing/'.$course_id.'/';
    }

    public static function generateLinkToIncomingTest($course_id){

        $test = current(self::getTestByCourse($course_id));

        $question = current(self::getQuestionsByTest($test['ID']));

        return '/cabinet/courses/pretesting/'.$test['ID'].'/'.$question['ID'].'/';
    }

    public static function getTestByCourse($course_id, $select = [])
    {
        if(check_full_array($select))
            return Iblock::getElements(['IBLOCK_ID' => Iblock::getTestsIblock(), 'PROPERTY_COURSE' => $course_id], ['ID' => 'DESC'], $select);
        return Iblock::getElements(['IBLOCK_ID' => Iblock::getTestsIblock(), 'PROPERTY_COURSE' => $course_id]);

    }

    public static function getQuestionsByTest($test_id, $order=[])
    {
        $getlistOrder = ['SORT'=>'ASC'];
        $filter = ['IBLOCK_ID' => Iblock::getTestQuestionsIblock(), 'PROPERTY_TEST' => $test_id];
        $questions = Iblock::getElements($filter, $getlistOrder);
        foreach ($questions as &$question){
            $question = \Helpers\PropertyHelper::collectFields($question);
        }
        $new_q = [];
        if(check_full_array($order)){
            $or_questions = $questions;
            foreach ($order as $or => $rep){
                $new_q[$or] = $or_questions[$rep];
            }
        }
        if(check_full_array($new_q))
            $questions = $new_q;
        return $questions;
    }

    public static function getQuestion($question_id)
    {
        return \Helpers\PropertyHelper::collectFields(
            current(
                Iblock::getElements(
                    ['IBLOCK_ID' => Iblock::getTestQuestionsIblock(), 'ID' => $question_id],
                    ['SORT'=>'ASC']
                )
            )
        );
    }

    public static function getAllSumPoints($questions)
    {
        $points = 0;
        foreach ($questions as $question)
            $points+=(int)$question['PROPERTIES']['POINTS'];
        return $points;
    }

    public static function getCourseByTest($test_id)
    {
        return \Teaching\Courses::getById(\Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'COURSE'));
    }

    public static function checkCorrectAnswer($value)
    {
        $question_id = 0;
        if(strpos($value, ';')!==false){
            $values = explode(';', $value);
            $ans_array = [];
            foreach($values as $val){
                $array = explode('_', $val);
                $question_id = $array[0];
                $ans_array[] = $array[1];
            }
            $correct = implode(',', $ans_array);
        }else{
            $array = explode('_', $value);
            $question_id = $array[0];
            $correct = $array[1];
        }
        $question = self::getQuestion($question_id);
        if($question['PROPERTIES']['CORRECT_NUM']==$correct)
            return $question['PROPERTIES']['POINTS'];
        return 0;
    }

    public static function getMinPointsForComplete($test_id)
    {
        return (int)\Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'POINTS');
    }

    public static function getNextQuestionLink($test_id, $value)
    {
        $questions = self::getQuestionsByTest($test_id);
        $array = explode('_', $value);
        $current_id = $array[0];
        $curr_key = 0;
        foreach ($questions as $id => $question){
            $curr_key++;
            if($id==$current_id)
                break;
        }
        if($curr_key<count($questions))
            $need_key = $curr_key+1;
        else
            $need_key = $curr_key;
        $tmp = 0;
        foreach ($questions as $id => $question){
            $tmp++;
            if($need_key==$tmp) {
                return '/cabinet/courses/testing/'.$test_id.'/'.$id.'/';
            }
        }
    }

    public static function getNextReTestQuestionLink($test_id, $value)
    {
        $questions = self::getQuestionsByTest($test_id);
        $array = explode('_', $value);
        $current_id = $array[0];
        $curr_key = 0;
        foreach ($questions as $id => $question){
            $curr_key++;
            if($id==$current_id)
                break;
        }
        if($curr_key<count($questions))
            $need_key = $curr_key+1;
        else
            $need_key = $curr_key;
        $tmp = 0;
        foreach ($questions as $id => $question){
            $tmp++;
            if($need_key==$tmp) {
                return '/cabinet/courses/re-testing/'.$test_id.'/'.$id.'/';
            }
        }
    }
    public static function getNextPreTestQuestionLink($test_id, $value)
    {
        $questions = self::getQuestionsByTest($test_id);
        $array = explode('_', $value);
        $current_id = $array[0];
        $curr_key = 0;
        foreach ($questions as $id => $question){
            $curr_key++;
            if($id==$current_id)
                break;
        }
        if($curr_key<count($questions))
            $need_key = $curr_key+1;
        else
            $need_key = $curr_key;
        $tmp = 0;
        foreach ($questions as $id => $question){
            $tmp++;
            if($need_key==$tmp) {
                return '/cabinet/courses/pretesting/'.$test_id.'/'.$id.'/';
            }
        }
    }

    public static function getTimeForCompleting($test_id) {
        return (int)\Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'TIME_FOR_COMPLETION');
    }

    public static function isRandomQuestions($test_id) {
        return \Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'RANDOM_QUESTIONS')==124;
    }

    public static function getRandomQuestions($test_id) {
        return \Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'RANDOM_QUESTIONS');
    }

    public static function getLimitQuestions($test_id) {
        return (int)\Helpers\PropertyHelper::getPropertyValue(Iblock::getTestsIblock(), $test_id, 'LIMIT');
    }

    public static function randomizeQuestions($test_id) {
        $return_array = [];
        $user_id = UserHelper::prepareUserId(0);
        $already_string = \COption::GetOptionString('common.settings', 'rand_questions_'.$user_id.'_'.$test_id);

        if(!empty($already_string)) {
            $array = explode('|', $already_string);
            foreach ($array as $k => $value){
                if($k==0)
                    continue;
                $ids = explode('_', $value);
                $return_array[$ids[0]] = $ids[1];
            }
        } else {
            $questions = self::getQuestionsByTest($test_id);
            $orig_ids = $random_ids = array_keys($questions);

            shuffle($random_ids);

            if(self::getLimitQuestions($test_id)>0){

                $result_array = array_chunk($random_ids, self::getLimitQuestions($test_id));
                $random_ids = $result_array[0];
            }

            $new_arr[] = $user_id;
            foreach ($orig_ids as $key => $orig_id) {
                if($random_ids[$key])
                    $new_arr[] = $orig_id . "_" . $random_ids[$key];
            }

            \COption::SetOptionString('common.settings', 'rand_questions_'.$user_id.'_'.$test_id, implode('|', $new_arr));
            $already_string = \COption::GetOptionString('common.settings', 'rand_questions_'.$user_id.'_'.$test_id);

            $array = explode('|', $already_string);

            foreach ($array as $k => $value){
                if($k==0)
                    continue;
                $ids = explode('_', $value);
                $return_array[$ids[0]] = $ids[1];
            }

        }

        return $return_array;
    }

    public static function resetRandomizeQuestions($test_id) {
        $user_id = UserHelper::prepareUserId(0);
        \COption::SetOptionString('common.settings', 'rand_questions_'.$user_id.'_'.$test_id, false);
    }

    public static function getMaxPointsByCourse($id) {
        $points = 0;
        $test = current(self::getTestByCourse($id));
        if(!check_full_array($test))
            return $points;
        foreach(self::getQuestionsByTest($test['ID']) as $question){
            $points+=(int)$question['PROPERTIES']['POINTS'];
        }
        return $points;
    }

    public static function getQuestionsCntByCourse($id) {
        $test = current(self::getTestByCourse($id));
        if(!check_full_array($test))
            return 0;
        return count(self::getQuestionsByTest($test['ID']));
    }

    public static function getIncomingTestInfo($course_id, $user_id) {

    }

    public static function getMaxPoints($test_id) {
        $points = 0;
        foreach(self::getQuestionsByTest($test_id) as $question) {
            $points+=(int)$question['PROPERTIES']['POINTS'];
        }
        return $points;
    }
}