<?php


namespace Teaching;


use Helpers\IBlockHelper as IBlock;

class Completion
{
    public static function getByCourse($course_id)
    {
        $sections = IBlock::getSections(['ACTIVE' => 'Y', 'IBLOCK_ID' => IBlock::getCompletionIblock(), 'UF_COURSE' => $course_id], ['UF_*']);
        if(is_array($sections)&&count($sections)>0)
            return array_shift($sections);
        else
            return [];
    }
    public static function get(){

    }
    public static function getCurStageByCompletion($completion_id){

        global $USER;
        $all_stages = array_values(self::getAllStages($completion_id));
        $return_array = [];
        $common_completions = new \Teaching\CourseCompletion();
        $current_completion = $common_completions->getByCourseAndUser(
            $USER->GetID(),
            \Teaching\Courses::getIdByCompletion($completion_id)
        );
        if (count($all_stages)==1) {
            $common_completions->setCurrentStep($current_completion['ID'], 1);
            if ($current_completion['UF_FAILED'] == 1)
                $return_array['attempts'] = $common_completions->incrementAttempt($current_completion['ID']);
            $common_completions->setAllSteps($current_completion['ID'], count($all_stages));
            $return_array['completion'] = self::getFirstStageByCompletion($completion_id);
            $return_array['current'] = 1;
            $return_array['all'] = 1;
        }else{
            if(!$current_completion['UF_CURR_STEP']>0){
                $common_completions->setCurrentStep($current_completion['ID'], 1);
                $return_array['attempts'] = $common_completions->incrementAttempt($current_completion['ID']);
                $common_completions->setAllSteps($current_completion['ID'], count($all_stages));
                $return_array['completion'] = self::getFirstStageByCompletion($completion_id);
                $return_array['current'] = 1;
                $return_array['all'] = count($all_stages);
            }else{
                $return_array['completion'] = self::getStageOfCompletionByNum($completion_id, $current_completion['UF_CURR_STEP']);
                $return_array['current'] = $current_completion['UF_CURR_STEP'];
                $return_array['all'] = count($all_stages);
            }
        }
        return $return_array;
    }
    public static function getFirstStageByCompletion($completion_id)
    {
        return current(IBlock::getElements(['ACTIVE' => 'Y', 'IBLOCK_ID' => IBlock::getCompletionIblock(), 'SECTION_ID' => $completion_id], ['SORT' => 'ASC', 'ID' => 'ASC']));
    }

    public static function getAllStages($completion_id)
    {
        return IBlock::getElements(['ACTIVE' => 'Y', 'IBLOCK_ID' => IBlock::getCompletionIblock(), 'SECTION_ID' => $completion_id], ['SORT' => 'ASC', 'ID' => 'ASC']);
    }

    public static function getCourseID($ID)
    {
        $completion_array = current(IBlock::getSections(['ACTIVE' => 'Y', 'IBLOCK_ID' => IBlock::getCompletionIblock(), 'ID' => $ID], ['UF_*']));
        return $completion_array['UF_COURSE'];
    }

    private static function getStageOfCompletionByNum($completion_id, $step)
    {
        $list = array_values(self::getAllStages($completion_id));
        return $list[--$step];
    }
}