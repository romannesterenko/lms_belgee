<?php


namespace Integrations;

use Helpers\HLBlockHelper as HLBlock;
use Models\Course;
use Teaching\CourseCompletion;
use Teaching\Courses;

class Scorm
{
    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('scorm_data');
    }

    private function addElem($fields)
    {
        HLBlock::add($fields, $this->dataClass);
    }

    public function addData($request): void
    {
        $names = ['log', 'user_id', 'course_id', 'created_at', 'part'];
        $fields['UF_USER_ID'] = $request['user_id'];
        $fields['UF_COURSE_ID'] = $request['course_id'];
        $fields['UF_CREATED_AT'] = $request['created_at'];
        $fields['UF_PART'] = $request['part'];
        foreach ($request as $name => $val) {
            if (in_array($name, $names))
                continue;
            $fields['UF_KEY'] = $name;
            $fields['UF_VALUE'] = $val;
            if (!is_array($val))
                $this->addElem($fields);
        }
        if ($request['success_status'] == 'passed' && $request['score_raw'] >= 80) {
            $request['completion_status'] = 'completed';
        }
        if ($request['success_status'] == 'passed' && Course::isIgnoreStatus($request['course_id']) && Course::getIgnorePoints($request['course_id'], $request['part']) > 0) {
            $request['completion_status'] = Course::getIgnorePoints($request['course_id'], $request['part']) <= (int)$request['score_raw'] ? 'completed' : "unknown";
        }
        $this_completion = $current_completion = current((new CourseCompletion())->get(['UF_USER_ID' => $request['user_id'], 'UF_COURSE_ID' => $request['course_id']]));
        $is_retest = false;
        $link = [];
        if(check_full_array($current_completion)){
            if($current_completion['UF_RETEST'] && !empty($course['PROPERTIES']['RETEST_SCORM'])) {
                $is_retest = true;
                $link = [0=>['VALUE' => $course['PROPERTIES']['RETEST_SCORM']]];
            }
        }
        if (($request['completion_status'] == 'completed' || $request['completion_status'] == 'in_progress') && ($request['success_status'] == 'passed' || $request['lesson_status'] == 'completed')) {

            $course = Courses::getById($fields['UF_COURSE_ID']);
            $all_completed = false;
            $completions = new CourseCompletion();
            if($is_retest && check_full_array($link))
                $course['PROPERTIES']['SCORM'] = $link;
            if (!empty($course['PROPERTIES']['SCORM'])) {
                $completed_scorm = [];

                $scorm_string = $completions->getScormCompletionString($request['course_id'], $request['user_id']);
                if (!empty($scorm_string) && $scorm_string != '') {
                    $completed_scorm = array_unique(explode(';', $scorm_string));
                }
                if (empty($scorm_string) || $scorm_string == 0) {
                    $completed_scorm = [0];
                }
                if (!$completions->isCompleted($request['course_id'], $request['user_id'], $this_completion['ID'])) {
                    if (!in_array($request['part'], $completed_scorm))
                        $completed_scorm[] = $request['part'];
                    $completions->setCompleteScorm($request['course_id'], $request['user_id'], implode(';', array_unique($completed_scorm)));
                    if (count($course['PROPERTIES']['SCORM']) == count($completed_scorm)) {
                        $all_completed = true;
                    }
                }
            }
            if($is_retest && check_full_array($link))
                $all_completed = true;
            if ($all_completed) {
                if (!$completions->isCompleted($request['course_id'], $request['user_id'], $this_completion['ID'])) {
                    $completions->setCompletedScormCourse($request['course_id'], (int)$request['score_raw'], $request['user_id']);
                }
            }
        } elseif (($request['completion_status'] == 'completed' || $request['completion_status'] == 'in_progress') || $request['success_status'] == 'passed' && (int)$request['score_raw'] > 0 && Course::isIgnoreStatus($request['course_id']) && Course::getIgnorePoints($request['course_id'], $request['part']) > 0 && Course::getIgnorePoints($request['course_id'], $request['part']) <= (int)$request['score_raw']) {
            $course = Courses::getById($fields['UF_COURSE_ID']);
            $all_completed = false;
            $completions = new CourseCompletion();
            if($is_retest && check_full_array($link))
                $course['PROPERTIES']['SCORM'] = $link;
            if (!empty($course['PROPERTIES']['SCORM'])) {
                $completed_scorm = [];
                $scorm_string = $completions->getScormCompletionString($request['course_id'], $request['user_id']);
                if (!empty($scorm_string) && $scorm_string != '') {
                    $completed_scorm = array_unique(explode(';', $scorm_string));
                }
                if ($scorm_string == 0) {
                    $completed_scorm = [0];
                }
                if (!$completions->isCompleted($request['course_id'], $request['user_id'], $this_completion['ID'])) {
                    $completed_scorm[] = $request['part'];
                    $completions->setCompleteScorm($request['course_id'], $request['user_id'], implode(';', array_unique($completed_scorm)));
                    if (count($course['PROPERTIES']['SCORM']) == count($completed_scorm)) {
                        $all_completed = true;
                    }
                }
            }
            if($is_retest && check_full_array($link))
                $all_completed = true;
            if ($all_completed) {
                if (!$completions->isCompleted($request['course_id'], $request['user_id'], $this_completion['ID'])) {
                    $completions->setCompletedScormCourse($request['course_id'], (int)$request['score_raw'], $request['user_id']);
                }
            }
        } else {
            if ((int)$request['score_raw'] > 0) {
                $current_completion = current((new CourseCompletion())->get(['UF_COURSE_ID' => $request['course_id'], 'UF_USER_ID' => $request['user_id'], '!UF_IS_COMPLETE' => 1]));
                if (check_full_array($current_completion) && $current_completion['ID'] > 0) {
                    (new CourseCompletion())->update($current_completion['ID'], ['UF_POINTS' => (int)$request['score_raw']]);
                }
            }
        }
    }

    public function get($filter = [], $select = ['*'], $order = [])
    {
        return HLBlock::get($this->dataClass, $filter, $select, $order);
    }

    public function limit($filter = [], $select = ['*'], $order = [], $limit = 10)
    {
        return HLBlock::get($this->dataClass, $filter, $select, $order);
    }

    public function getData($user_id, $course_id, $part)
    {
        $data_array = $this->getLast($user_id, $course_id, $part);
        return $data_array;
    }

    public function getData1($user_id, $course_id, $part)
    {
        ///$data_array = $this->getFirst($user_id, $course_id, $part);
        $filter = [
            'UF_USER_ID' => $user_id,
            'UF_COURSE_ID' => $course_id,
            'UF_PART' => $part
        ];
        return $this->get($filter, ['*'], ['ID' => 'DESC']);
        //return $data_array;
    }

    public function getById($id)
    {
        $filter = [
            'ID' => $id
        ];
        return $this->get($filter, ['*'], ['ID' => 'DESC']);
    }

    public function getLast(int $user_id, int $course_id, int $part)
    {
        $filter = [
            'UF_USER_ID' => $user_id,
            'UF_COURSE_ID' => $course_id,
            'UF_PART' => $part
        ];
        $date_rows = [];
        $list = $this->get($filter, ['*'], ['ID' => 'ASC']);
        foreach ($list as $row) {
            $date_rows[$row['UF_CREATED_AT']->toString()][$row['UF_KEY']] = $row['UF_VALUE'];
        }
        return count($date_rows) > 0 ? end($date_rows) : [];
    }

    public function getFirst(int $user_id, int $course_id, int $part)
    {
        $filter = [
            'UF_USER_ID' => $user_id,
            'UF_COURSE_ID' => $course_id,
            'UF_PART' => $part
        ];
        $date_rows = [];
        $list = $this->get($filter, ['*'], ['ID' => 'DESC']);
        foreach ($list as $row) {
            $date_rows[$row['UF_CREATED_AT']->toString()][$row['UF_KEY']] = $row['UF_VALUE'];
        }
        return count($date_rows) > 0 ? end($date_rows) : [];
    }
}