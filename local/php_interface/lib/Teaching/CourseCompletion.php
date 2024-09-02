<?php

namespace Teaching;

use DateTime;
use Helpers\DateHelper;
use Helpers\HLBlockHelper;
use Helpers\Log;
use Helpers\Tasks;
use Helpers\UserHelper;
use Models\Course;
use Models\Employee;
use Models\Reserve;
use Teaching\TestDrive\Group;

class CourseCompletion
{
    private $items;
    private string $completionsHlDataClass;

    public function __construct()
    {
        $this->completionsHlDataClass = HLBlockHelper::initialize('course_completion');
    }
    public function limit($filter, $select = ['*'], $limit = 10, $order = ["ID" => "DESC"]){
        $filter = array_merge(['UF_WAS_ARCHIVED'=>0], $filter);
        return $this->completionsHlDataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter,
            'limit' => $limit
        ))->fetchAll();
    }
    public function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        $filter = array_merge(['UF_WAS_ARCHIVED'=>0], $filter);
        return $this->completionsHlDataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }
    public function getAndCourses($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        $filter = array_merge(['UF_WAS_ARCHIVED'=>0], $filter);
        $list = $this->completionsHlDataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
        $courses_completions = [];
        foreach ($list as $it){
            $courses_completions[$it['UF_USER_ID']][] = $it;
        }
        return $courses_completions;
    }
    public function getAllByUser($user_id){
        $filter['UF_USER_ID'] = $user_id;
        return $this->get($filter);
    }
    public function getListByUser($user_id)
    {
        $schedule_ids = [];
        $enrollments = $this->get(['UF_USER_ID' => $user_id]);
        foreach ($enrollments as $enrollment) {
            $schedule_ids[$enrollment['UF_SHEDULE_ID']] = $enrollment;
        }
        $schedules = SheduleCourses::getById(array_keys($schedule_ids));

        foreach ($enrollments as &$enrollment) {
            if (empty($schedules[$enrollment['UF_SHEDULE_ID']]))
                continue;
            $schedules[$enrollment['UF_SHEDULE_ID']]['PERIOD'] = DateHelper::getIntervalArray($schedules[$enrollment['UF_SHEDULE_ID']]['PROPERTY_BEGIN_DATE_VALUE'], $schedules[$enrollment['UF_SHEDULE_ID']]['PROPERTY_END_DATE_VALUE']);
            $enrollment['SCHEDULE'] = $schedules[$enrollment['UF_SHEDULE_ID']];
        }
        $date_array = [];
        unset($enrollment);
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment["SCHEDULE"]["PERIOD"] as $date)
                $date_array[$date][] = $enrollment;
        }
        return $date_array;
    }

    public function getCompletedItems($user_id = 0)
    {
        $filter['UF_USER_ID'] = UserHelper::prepareUserId($user_id);
        $filter['UF_IS_COMPLETE'] = 1;
        $none_active_courses = \Models\Course::getList(['ACTIVE' => 'N'], ['ID']);
        if(count($none_active_courses)>0) {
            $filter['!UF_COURSE_ID'] = [];
            foreach ($none_active_courses as $course){
                $filter['!UF_COURSE_ID'][] = $course['ID'];
            }
        }
        $filter['UF_RETEST_FAILED'] = false;
        $this->items = $this->get($filter);
        return $this;
    }

    public function addWithoutSchedule($request)
    {
        $fields = [
            'UF_USER_ID' => $request['user'],
            'UF_SHEDULE_ID' => false,
            'UF_IS_COMPLETE' => 0,
            'UF_COURSE_ID' => $request['id'],
            'UF_DATE' => date('d.m.Y'),
            'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
            'UF_MADE_ATTEMPTS' => 0,
            'UF_TOTAL_ATTEMPTS' => \Models\Course::getTotalAttempts((int)$request['id'])>0?\Models\Course::getTotalAttempts((int)$request['id']):false,
            'UF_REGISTER_ANSWER' => $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false,
            'UF_PAYMENT_FROM_BALANCE' => $request['from_balance'] == 'Y' ? 1 : 0
        ];
        $this->add($fields);
    }

    public function add($fields, $need_paid = true)
    {
        $error = [
            'type' => 'method_add',
            'fields' => $fields,
        ];
        Log::writeCommon($error, 'add_completion_errors');
        if((int)$fields['UF_USER_ID']>0) {
            $exists_completion = $this->get(
                [
                    'UF_USER_ID' => $fields['UF_USER_ID'],
                    'UF_COURSE_ID' => $fields['UF_COURSE_ID'],
                    'UF_DATE' => $fields['UF_DATE'],
                    'UF_IS_COMPLETE' => $fields['UF_IS_COMPLETE']??false,
                    'UF_FAILED' => $fields['UF_FAILED']??false,
                ]
            );
            if(!check_full_array($exists_completion)) {
                $result = $this->completionsHlDataClass::add($fields);
                if ($result->getId() > 0 && $need_paid) {
                    if (!Course::allowToFreeEnroll($fields['UF_COURSE_ID'], $fields['UF_USER_ID']) && Courses::isPaid($fields['UF_COURSE_ID']) && $fields['UF_PAYMENT_FROM_BALANCE'] == 1 && Course::hasBalancePayment($fields['UF_COURSE_ID'])) {
                        \Models\Invoice::createFromCompletion($result->getId());
                    }
                }
                return $result;
            }
            return false;
        } else {
            $error = [
                'type' => 'no_user_id',
                'fields' => $fields,
            ];
            Log::writeCommon($error, 'add_completion_errors');
            return false;
        }
    }


    public function getFullApprListBySchedule($schedule_id){
        return $this->get(['UF_SHEDULE_ID' => $schedule_id]);
    }
    public function addFromRequest($request)
    {
        $exists = $this->get(['UF_USER_ID' => $request['user'], 'UF_SHEDULE_ID' => $request['id'], '!UF_FAILED' => 1]);
        if(count($exists)==0 && (int)$request['user'] > 0) {
            $ans = $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false;
            if($request['answer_arr'])
                $ans = $request['answer_arr'];
            if((int)$request['id']>0) {
                if(Course::isEvent(SheduleCourses::getCourseIdBySchedule($request['id']))){
                    \Teaching\TestDrive\Group::setEmployeeToRandGroup(((int)$request['id']), $request['user']);
                }
            }
            $fields = [
                'UF_USER_ID' => $request['user'],
                'UF_SHEDULE_ID' => $request['id'],
                'UF_IS_COMPLETE' => 0,
                'UF_COURSE_ID' => SheduleCourses::getCourseIdBySchedule($request['id']),
                'UF_DATE' => \Helpers\DateHelper::getHumanDate(\Teaching\SheduleCourses::getBeginDateBySchedule($request['id']), 'd.m.Y'),
                'UF_TOTAL_ATTEMPTS' => \Models\Course::getTotalAttempts(SheduleCourses::getCourseIdBySchedule($request['id']))>0?\Models\Course::getTotalAttempts(SheduleCourses::getCourseIdBySchedule($request['id'])):false,

                'UF_MADE_ATTEMPTS' => 0,
                'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
                'UF_REGISTER_ANSWER' => $ans,
                'UF_PAYMENT_FROM_BALANCE' => $request['from_balance'] == 'Y' ? 1 : 0
            ];
            $this->add($fields);
        }
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return count($this->items);
    }

    /**
     * @return mixed
     */
    public function getArray()
    {
        return $this->items;
    }
    public function getIds(){
        $ids = [];
        foreach ($this->items as $item)
            $ids[] = $item['ID'];
        return $ids;
    }
    public function getCourseIds(){
        $ids = [];
        foreach ($this->items as $item)
            $ids[] = $item['UF_COURSE_ID'];
        return $ids;
    }
    public function getCommonScore()
    {
        if (count($this->items) > 0) {
            $all = 0;
            foreach ($this->items as $item) {
                $all += $item['UF_POINTS'];
            }
            return ceil($all / count($this->items));
        }
        return 0;
    }

    public function getAllStartedCompletions()
    {
        $user_ids = Employee::getEmployeesIdsByAdmin();
        if(!check_full_array($user_ids))
            return [];
        return $this->get(['UF_IS_COMPLETE' => false, 'UF_USER_ID' => $user_ids]);
    }

    public function getAllCompletions()
    {
        return $this->get(['>ID' => 0]);
    }

    public function getAllStartedCompletionsByUser($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        return $this->get(['UF_USER_ID' => $user_id, 'UF_IS_COMPLETE' => false, '!UF_FAILED' => true, '!UF_DIDNT_COM' => true]);
    }

    public function delete($id, $reset = true, $delete_reserve = true)
    {
        if((int)$id>0) {
            $item = current($this->get(['ID' => $id]));
            if($reset) {
                if ($item['UF_SHEDULE_ID'] > 0 && SheduleCourses::getFreePlacesBySchedule($item['UF_SHEDULE_ID']) == 0) {
                    Tasks::setRemainTask($item);
                }
            }

            if(Course::isEvent($item['UF_COURSE_ID']) && $item['UF_SHEDULE_ID'] > 0 && $item['UF_USER_ID'] > 0) {
                Group::deleteEmployeeFromGroup($item['UF_SHEDULE_ID'], $item['UF_USER_ID']);
            }
            if($delete_reserve) {
                \Models\Certificate::resetByUserAndCourse($item['UF_USER_ID'], $item['UF_COURSE_ID']);
                Reserve::deleteByCompletion((int)$id);
            }

            $this->completionsHlDataClass::delete((int)$id);
        }
    }

    public function wasStarted($id)
    {
        $user_id = \Helpers\UserHelper::prepareUserId(0);
        $completion = self::get(['UF_COURSE_ID' => $id, 'UF_USER_ID' => $user_id, 'UF_IS_COMPLETE' => false, '!UF_FAILED' => true, 'UF_DIDNT_COM' => false]);
        $cmpl = self::get(['UF_COURSE_ID' => $id, 'UF_USER_ID' => $user_id]);

        if(check_full_array($cmpl) && $cmpl[0]['ID'] > 0 && $cmpl[0]['UF_IS_COMPLETE'] == 0 && $cmpl[0]['UF_FAILED'] == 0 && $cmpl[0]['UF_DIDNT_COM'] == 0 ){
               $completion = current($cmpl);
               if((int)$completion['UF_SHEDULE_ID']>0){
                   $begin_time_schedule = SheduleCourses::getBeginDateBySchedule((int)$completion['UF_SHEDULE_ID']);

                   if($begin_time_schedule&&time()>strtotime($begin_time_schedule)){
                       return true;
                   } else {
                       return false;
                   }
               } else {
                   return true;
               }
        }
        return false;
    }

    public function setCompletedCourse($course_id, $points, $user_id=0, $completion_id = 0 , $retest = false)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        if($completion_id>0)
            $list = $this->get(['ID' => $completion_id]);
        else
            $list = $this->get(['UF_IS_COMPLETE' => false, 'UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        $common_score = (int)$list[0]['UF_POINTS'];
        $common_score+=$points;
        if ($retest){
            $this->setCompleted($list[0]['ID'], $common_score, true);
        } else {
            $this->setCompleted($list[0]['ID'], $common_score);
        }
    }

    public function setCompletedScormCourse($course_id, $points, $user_id=0, $completion_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        if($completion_id>0)
            $list = $this->get(['ID' => $completion_id]);
        else
            $list = $this->get(['UF_IS_COMPLETE' => false, 'UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        $this->setCompleted($list[0]['ID'], $points);
    }
    public function setCompleted($id, $points, $retest = false){
        $current = (new CourseCompletion())->find($id);
        $course = Course::find($current['UF_COURSE_ID'], ['ID', 'NAME', 'PROPERTY_CERT_EXP']);
        $expire_period = $course["PROPERTY_CERT_EXP_VALUE"]??12;
        $need_date = date('d.m.Y H:i:s', strtotime('+'.$expire_period.' months'));
        $fields = ['UF_IS_COMPLETE'=>1, 'UF_POINTS' => $points, 'UF_COMPLETED_TIME' => date('d.m.Y H:i:s'), 'UF_EXPIRED_DATE' => $need_date];
        if($retest)
            $fields['UF_RETEST'] = 1;
        $this->update($id, $fields);
        \Helpers\Pdf::generateCertFromCompletionId($id);
    }
    public function update($ID, $fields)
    {
        $fields['UF_DATE_UPDATE'] = date('d.m.Y H:i:s');
        $this->completionsHlDataClass::update($ID, $fields);
    }

    public function isCompleted($course_id, $user_id=0, $completion_id=0)
    {
        if($course_id==0)
            return false;
        $user_id = UserHelper::prepareUserId($user_id);
        if($completion_id>0) {
            $list = $this->get(['ID' => $completion_id]);
        } else {
            $list = $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        }
        return $list[0]['UF_IS_COMPLETE']==1;
    }

    public function isCompleted1($course_id, $user_id=0, $completion_id=0)
    {

        if($course_id==0)
            return false;
        $user_id = UserHelper::prepareUserId($user_id);

        $course = Course::find($course_id, ['PROPERTY_CERT_EXP']);
        $expire_period = $course["PROPERTY_CERT_EXP_VALUE"]??12;
        if($completion_id > 0) {
            $list = $this->get(['ID' => $completion_id, 'UF_IS_COMPLETE'=>1]);
        } else {
            $list = $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        }
        if(!Courses::isFreeSheduleCourse($list[0]["UF_COURSE_ID"]))
            $new_date = DateTime::createFromFormat('d.m.Y', (string)$list[0]["UF_DATE"]);
        else
            $new_date = DateTime::createFromFormat('d.m.Y H:i:s', (string)$list[0]["UF_COMPLETED_TIME"]);
        if($list[0]['ID']>0 && $list[0]['UF_IS_COMPLETE'] == 1 && $new_date) {

            $new_date->modify('+'.$expire_period.' months');
            return (new DateTime())->format('Y-m-d H:i:s') <= $new_date->format('Y-m-d H:i:s');
        } else {
            return false;
        }
    }

    public function getCompletedItemsByCourseID($course_id)
    {
        $return_array = [];
        $items = $this->get(['UF_IS_COMPLETE'=>1, 'UF_COURSE_ID' => $course_id, 'UF_DIDNT_COM' => false, 'UF_RETEST_FAILED' => false], ['ID', 'UF_USER_ID']);
        foreach ($items as $item){
            if(!self::isExpired($course_id, $item["UF_USER_ID"], $item['ID'])) {
                $return_array[] = $item;
            }
        }
        return $return_array;
    }

    public function getCompletedItemsArrayByCourseID($course_id)
    {
        return $this->get(['UF_IS_COMPLETE'=>1, 'UF_COURSE_ID' => $course_id, 'UF_DIDNT_COM' => false]);
    }

    public function getCompletedItemsArrayByCourseIDA($course_id)
    {
        return $this->get(['UF_IS_COMPLETE'=>1, 'UF_COURSE_ID' => $course_id], ['*'], ["ID" => "ASC"]);
    }

    public function getCompletingItemsByCourseID($course_id)
    {
        return $this->get(['UF_IS_COMPLETE'=>false, 'UF_COURSE_ID' => $course_id, '!UF_FAILED' => 1, 'UF_DIDNT_COM' => false], ['UF_USER_ID']);
    }
    public function getCompletingItemsBySheduleID($shedule_id)
    {
        return $this->get(['UF_IS_COMPLETE'=>false, 'UF_SHEDULE_ID' => $shedule_id, '!UF_FAILED' => 1, 'UF_DIDNT_COM' => false], ['UF_USER_ID']);
    }

    public function getItemsByCourseID($course_id)
    {
        return $this->get(['UF_COURSE_ID' => $course_id], ['*']);
    }

    public function create(array $request)
    {
        $fields = [
            'UF_USER_ID' => $request['employee_id'],
            'UF_IS_COMPLETE' => 0,
            'UF_COURSE_ID' => $request['course_id'],
            'UF_DATE' => date('d.m.Y'),
            'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
            'UF_MADE_ATTEMPTS' => 0,
            'UF_TOTAL_ATTEMPTS' => \Models\Course::getTotalAttempts((int)$request['course_id'])>0?\Models\Course::getTotalAttempts((int)$request['course_id']):false,
            'UF_REGISTER_ANSWER' => $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false,
            'UF_PAYMENT_FROM_BALANCE' => $request['from_balance'] == 'Y' ? 1 : 0
        ];
        $error = [
            'type' => 'method_create',
            'request' => $request,
        ];
        Log::writeCommon($error, 'add_completion_errors');

        if((int)$fields['UF_USER_ID'] > 0) {
            $error = [
                'type' => 'method_create_exists_user',
                'request' => $request,
            ];
            Log::writeCommon($error, 'add_completion_errors');
            if (!empty($request['schedule_id']) && $request['schedule_id'] > 0) {
                $fields['UF_SHEDULE_ID'] = $request['schedule_id'];
                $fields['UF_DATE'] = \Helpers\DateHelper::getHumanDate(\Teaching\SheduleCourses::getBeginDateBySchedule($request['schedule_id']), 'd.m.Y');
                $exists = $this->get(['UF_USER_ID' => $request['employee_id'], 'UF_SHEDULE_ID' => $request['schedule_id'], '!UF_FAILED' => 1]);
                $error = [
                    'type' => 'method_create_exists_schedule',
                    'request' => $request,
                    'fields' => $fields,
                    'exists' => $exists,
                ];
                Log::writeCommon($error, 'add_completion_errors');
                $all_schedule_completions = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE_ID' => $request['schedule_id']]);
                $schedule = current(SheduleCourses::getById($request['schedule_id']));
                $free_places = (int)$schedule['PROPERTIES']['LIMIT'] - count($all_schedule_completions);
                if ($free_places > 0 && count($exists) == 0) {
                    if(Course::isEvent($request['course_id'])){
                        Group::setEmployeeToRandGroup(($request['schedule_id']), $request['employee_id']);
                    }
                    $error = [
                        'type' => 'method_create_has_free_places_and_not_exists',
                        'request' => $request,
                        'fields' => $fields,
                    ];
                    Log::writeCommon($error, 'add_completion_errors');
                    return $this->add($fields);
                }
            } else {
                return $this->add($fields);
            }
        }

    }

    public function getAllStartedCompletionsBySheduleAndUserIds($ID, $ids)
    {
        if(!check_full_array($ids))
            return [];
        return $this->get(['UF_IS_COMPLETE'=>false, '!UF_FAILED' => 1, 'UF_SHEDULE_ID' => $ID, 'UF_USER_ID' => $ids], ['ID', 'UF_USER_ID']);

    }

    public function getCountCompletionsByDC($ID):int
    {
        $ids = Employee::getEmployeesIdsByAdmin();
        return count(self::getAllStartedCompletionsBySheduleAndUserIds($ID, $ids));
    }

    public function setCompleteScorm($course_id, $user_id, $part)
    {
        $list = $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id, '!UF_FAILED' => 1]);
        $this->update($list[0]['ID'], ['UF_SCORM_COMPLETION' => $part]);
    }

    public function getScormCompletionString($course_id, $user_id)
    {
        $list = $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        return (string)$list[0]['UF_SCORM_COMPLETION'];
    }

    public function getByCourseAndUser($user_id, $course_id, $select=['*'])
    {
        return current($this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id, 'UF_FAILED' => false], $select));
    }

    public function getListByCourseAndUser($user_id, $course_id)
    {
        return $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
    }

    public function getListByScheduleAndUser($user_id, $schedule_id)
    {
        return $this->get(['UF_USER_ID' => $user_id, 'UF_SHEDULE_ID' => $schedule_id]);
    }

    public function setCurrentStep($ID, int $num_of_step)
    {
        $this->update($ID, ['UF_CURR_STEP' => $num_of_step]);
    }

    public function setAllSteps($ID, int $num_of_steps)
    {
        $this->update($ID, ['UF_ALL_STEPS' => $num_of_steps]);
    }

    public function getCompletedItemsByCourseIDAndMonth(mixed $ID, bool|int $strtotime)
    {
        return $this->get(
            [
                'UF_IS_COMPLETE'=>true,
                'UF_COURSE_ID' => $ID,
                '>UF_COMPLETED_TIME' => date('01.m.Y 00:00:00', $strtotime),
                '<UF_COMPLETED_TIME' => date('t.m.Y 23:59:59', $strtotime),
            ]
        );
    }

    public function getLastWeekCompletedItems(mixed $ID)
    {
        return $this->get(
            [
                'UF_IS_COMPLETE'=>true,
                'UF_COURSE_ID' => $ID,
                '>UF_COMPLETED_TIME' => date('d.m.Y 00:00:00', strtotime("last week monday")),
                '<UF_COMPLETED_TIME' => date('d.m.Y 23:59:59', strtotime("last week sunday")),
            ]
        );
    }

    public function getThisWeekCompletedItems(mixed $ID)
    {
        return $this->get(
            [
                'UF_IS_COMPLETE'=>true,
                'UF_COURSE_ID' => $ID,
                '>UF_COMPLETED_TIME' => date('d.m.Y 00:00:00', strtotime("monday this week")),
                '<UF_COMPLETED_TIME' => date('d.m.Y 23:59:59', strtotime("sunday this week")),
            ]
        );
    }

    public function getCompletedItemsBySchedule(mixed $ID)
    {
        return $this->get(
            [
                'UF_IS_COMPLETE'=>true,
                'UF_SHEDULE_ID' => $ID,
            ]
        );
    }

    public function getAllCompletedItems()
    {
        return $this->get(
            [
                'UF_IS_COMPLETE'=>true,
            ]
        );
    }

    public function addFromFields(array $fields_add)
    {
        return $this->completionsHlDataClass::add($fields_add);
    }

    public function setCompletedTime(mixed $ID, $format)
    {
        $this->update($ID, ['UF_COMPLETED_TIME' => $format]);
    }

    public function getBySchedule(mixed $ID)
    {
        return $this->get(['UF_SHEDULE_ID' => $ID, '!UF_FAILED' => 1]);
    }

    public function setViewedCourse(mixed $ID)
    {
        $this->update($ID, ['UF_VIEWED' => true]);
    }

    public function find(mixed $param)
    {
        return !($param>0)?[]:current($this->get(['ID' => $param]));
    }

    public function getCountOfCompetedCourse($user_id=0):int
    {
        $count = 0;
        $list = $this->getCompletedItems($user_id)->getArray();
        foreach ($list as $item){
            if($item['UF_COURSE_ID']&&\Models\Course::isActive($item['UF_COURSE_ID']))
                $count++;
        }
        return $count;
    }

    public function incrementAttempt($ID)
    {
        $item = current($this->get(['ID' => $ID]));
        if((int)$item['UF_MADE_ATTEMPTS']==1&&(int)$item['UF_VIEWED']==0){
            $new_attempts = 1;
        } else {
            $new_attempts = (int)$item['UF_MADE_ATTEMPTS'] + 1;
        }
        $this->setCurrentAttempt($ID, $new_attempts);
    }

    private function setCurrentAttempt($id, $new_attempts)
    {
        $this->update($id, ['UF_MADE_ATTEMPTS' => $new_attempts]);
    }

    public function setFailedCourse($id, $points = 0 , $retest = false)
    {
        $fields = ['UF_FAILED' => 1];
        if ($retest)
            $fields = ['UF_RETEST' => 1];
        
        if($points>0){
            $fields['UF_POINTS'] = $points;
        }
        $this->update($id, $fields);
    }

    public function setFailedRetest($id, $points = 0)
    {
        $fields = ['UF_RETEST_FAILED' => 1];
        if($points>0){
            $fields['UF_POINTS'] = $points;
        }
        $this->update($id, $fields);
    }

    public function setDidntComCourse($id)
    {
        $this->update($id, ['UF_DIDNT_COM' => 1]);
    }

    public function isExistsByCourseAndUser($id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        if(!$id>0)
            return false;
        $elem = $this->getByCourseAndUser($user_id, $id);
        return check_full_array($elem);
    }

    public function setDate($id, $getHumanDate)
    {
        $this->update($id, ['UF_DATE' => $getHumanDate]);
    }

    public function setFromCronUpdate($id)
    {
        $this->update($id, ['UF_SETTED_FROM_CRON' => 1]);
    }
    public function setEmployeeToCompletion($id, $employee):void
    {
        $completion = $this->find($id);
        if((int)$completion['UF_SHEDULE_ID']){
            if(Course::isEvent($completion['UF_COURSE_ID'])){
                Group::setEmployeeToRandGroup(($completion['UF_SHEDULE_ID']), $employee);
            }
        }
        $this->update($id, ['UF_USER_ID' => $employee]);
    }

    public function setArchived($id):void
    {
        $this->update($id, ['UF_WAS_ARCHIVED' => 1]);
    }

    public function isFailed(mixed $enroll):bool
    {
        $item = current($this->get([
            'UF_COURSE_ID' => $enroll['UF_COURSE_ID'],
            'UF_USER_ID' => $enroll['UF_USER_ID'],
            'UF_SHEDULE_ID' => $enroll['UF_SHEDULE_ID'],
            'UF_DATE' => $enroll['UF_DATE']->format('d.m.Y'),
            'UF_DIDNT_COM' => 1,
        ]));
        return (int)$item['ID']>0;
    }

    public function isDidntCom(mixed $enroll):bool
    {
        $item = current($this->get([
            'UF_COURSE_ID' => $enroll['UF_COURSE_ID'],
            'UF_USER_ID' => $enroll['UF_USER_ID'],
            'UF_SHEDULE_ID' => $enroll['UF_SHEDULE_ID'],
            'UF_DATE' => date('d.m.Y', strtotime($enroll['UF_DATE'])),
            'UF_DIDNT_COM' => 1,
        ]));
        return (int)$item['ID']>0;
    }

    public function resetPoints($completion_id)
    {
        $this->update($completion_id, ['UF_POINTS' => false]);
        $this->update($completion_id, ['UF_CUR_STEP' => false]);
    }

    public function setPoints($max_ball, $completion_id)
    {
        $this->update($completion_id, ['UF_POINTS' => $max_ball]);
    }

    public function getNotDidntCom()
    {
        return $this->get([
            '!UF_SHEDULE_ID' => false,
            'UF_DIDNT_COM' => 1,
        ]);
    }

    public function getLast($course_id, $user_id = 0)
    {
        return current($this->get(['UF_USER_ID' => UserHelper::prepareUserId($user_id), 'UF_COURSE_ID' => $course_id]));
    }
    public function missAttempts(mixed $ID)
    {
        $last = $this->getLast($ID);
        if($last['ID']>0&&$last['UF_IS_COMPLETE']!=1&&$last['UF_FAILED']==1){
            return $last['UF_TOTAL_ATTEMPTS']==$last['UF_MADE_ATTEMPTS'];
        }
        return false;
    }
    public function missAttemptsBySchedule(mixed $ID)
    {
        if(!$this->isCompleted($ID)){
            $compl = current($this->get([
                'UF_SHEDULE_ID' => $ID,
                'UF_USER_ID' => UserHelper::prepareUserId(0),
                'UF_FAILED' => 1]));
            return $compl['ID']>0&&$compl['UF_TOTAL_ATTEMPTS']==$compl['UF_MADE_ATTEMPTS'];
        }
        return false;
    }

    public function setWasOnCourse(mixed $ID)
    {
        $this->update($ID, ['UF_WAS_ON_COURSE' => 1]);

    }

    public function isExpired($course_id, $user_id=0, $completion_id=0)
    {
        if($course_id==0)
            return false;
        $user_id = UserHelper::prepareUserId($user_id);
        //TODO убрать это, когда доделаем ретест Scorm-курсов
        /*if(Course::isScormCourse($course_id))
            return false;*/
        $course = Course::find($course_id, ['PROPERTY_CERT_EXP']);
        $expire_period = $course["PROPERTY_CERT_EXP_VALUE"]??12;

        if($completion_id > 0) {
            $list = $this->get(['ID' => $completion_id, 'UF_IS_COMPLETE'=>1]);
        } else {
            $list = $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id]);
        }
        if(!Courses::isFreeSheduleCourse($list[0]["UF_COURSE_ID"]))
            $new_date = DateTime::createFromFormat('d.m.Y', (string)$list[0]["UF_DATE"]);
        else
            $new_date = DateTime::createFromFormat('d.m.Y H:i:s', (string)$list[0]["UF_COMPLETED_TIME"]);

        if($list[0]['ID']>0 && !$list[0]['UF_RETEST_FAILED'] &&  $new_date &&  $list[0]["UF_IS_COMPLETE"] == 1) {
            $new_date->modify('+'.$expire_period.' months');
            return (new DateTime())->format('Y-m-d H:i:s') >= $new_date->format('Y-m-d H:i:s');
        }
        return false;
    }

    public function setReTest(mixed $ID)
    {
        $this->update($ID, ['UF_RETEST' => 1]);
    }
}