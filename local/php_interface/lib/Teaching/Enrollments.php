<?php

namespace Teaching;

use \Helpers\HLBlockHelper as HLBlock;
use Helpers\Log;
use Models\Course;
use Models\User;
use Notifications\EmailNotifications;
use Settings\Notifications;

class Enrollments
{
    private string $dataClass;

    public function getNotApprovedListByScheduleId($schedule_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $schedule_id, 'UF_IS_APPROVED' => 0]);
    }

    public function getAllListByScheduleId($schedule_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $schedule_id,]);
    }

    public function getApprovedListByScheduleId($schedule_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $schedule_id, 'UF_IS_APPROVED' => 1, 'UF_DIDNT_COM' => false]);
    }

    public function getApprovedUserListByScheduleId($schedule_id)
    {
        $return_ids = [];
        foreach ($this->get(['UF_SHEDULE_ID' => $schedule_id, 'UF_IS_APPROVED' => 1, 'UF_DIDNT_COM' => false], ['UF_USER_ID']) as $enroll)
            $return_ids[] = $enroll['UF_USER_ID'];
        return array_unique($return_ids);
    }
    public function getFullApprListBySchedule($schedule_id){
        return $this->get(['UF_SHEDULE_ID' => $schedule_id, 'UF_IS_APPROVED' => 1]);
    }
    public function getAllApprovedEnrollsBySheduleAndUserIds($ID, $ids)
    {
        if(!check_full_array($ids))
            return [];
        return $this->get(['UF_IS_APPROVED'=>1, 'UF_SHEDULE_ID' => $ID, 'UF_USER_ID' => $ids], ['ID', 'UF_USER_ID']);

    }
    public function getCountApprovedEnrollsByDC($ID):int
    {
        $ids = \Models\Employee::getEmployeesIdsByAdmin();
        return count($this->getAllApprovedEnrollsBySheduleAndUserIds($ID, $ids));
    }
    public function getApprovedListByScheduleIdAndUserIds($schedule_id, $user_ids=[])
    {
        if(count($user_ids)>0)
            return [];
        return $this->get(['UF_SHEDULE_ID' => $schedule_id, 'UF_USER_ID' => $user_ids, 'UF_IS_APPROVED' => 1]);
    }

    public function addFromRequest($request)
    {
        if ($request['with_coupon']==true){
            if((int)$request['schedule']==0){
                $completion = new \Teaching\CourseCompletion();
                $completion->addWithoutSchedule($request);
            } else {
                $exists = $this->get(['UF_USER_ID' => $request['user'], 'UF_SHEDULE_ID' => $request['schedule']]);
                if (count($exists) == 0) {
                    $fields = [
                        'UF_USER_ID' => $request['user'],
                        'UF_SHEDULE_ID' => $request['schedule'],
                        'UF_DATE' => \Teaching\SheduleCourses::getBeginDateBySchedule($request['schedule']),
                        'UF_COURSE_ID' => \Teaching\SheduleCourses::getCourseIdBySchedule($request['schedule']),
                        'UF_IS_APPROVED' => (!empty( User::getTeachingType($request['user'])))? 1 : 0,
                        'UF_IS_FREE_SCHEDULE' => 0,
                        'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
                        'UF_REGISTER_ANSWER' => $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false,
                    ];
                    if ($fields['UF_SHEDULE_ID'] == 0 ) {
                        $completion = new \Teaching\CourseCompletion();
                        $completion->addFromRequest($request);
                    } else {
                        if (\Models\User::getTeachingType($request['user'])){
                            $completion = new \Teaching\CourseCompletion();
                            $completion->addFromRequest(['id' => $request['schedule'] , 'user' =>$request['user'], 'from_balance' => $request['from_balance']]);
                        }
                        $this->add($fields);
                    }

                } else {
                    $error = [
                        'type' => 'exists_completion',
                        'request' => $request,
                        'exists' => $exists,
                    ];
                    Log::writeCommon($error, 'add_completion_errors');
                }
            }
        } else {
            if((int)$request['schedule']==0){
                $completion = new \Teaching\CourseCompletion();
                $completion->addWithoutSchedule($request);
            } else {
                $exists = $this->get(['UF_USER_ID' => $request['user'], 'UF_SHEDULE_ID' => $request['schedule']]);
                //$log = date('Y-m-d H:i:s') . ' ' . print_r($exists, true);
                //file_put_contents(__DIR__ . '/log$requestEnrollments.txt', $log . PHP_EOL, FILE_APPEND);
                if (count($exists) == 0) {
                    $fields = [
                        'UF_USER_ID' => $request['user'],
                        'UF_SHEDULE_ID' => $request['schedule'],
                        'UF_DATE' => \Teaching\SheduleCourses::getBeginDateBySchedule($request['schedule']),
                        'UF_COURSE_ID' => \Teaching\SheduleCourses::getCourseIdBySchedule($request['schedule']),
                        'UF_IS_APPROVED' => (!empty( User::getTeachingType($request['user'])))? 1 : 0,
                        'UF_IS_FREE_SCHEDULE' => 0,
                        'UF_DATE_CREATE' => date('d.m.Y H:i:s'),
                        'UF_REGISTER_ANSWER' => $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false,
                        'UF_PAYMENT_FROM_BALANCE' => $request['from_balance'] == 'Y' ? 1 : 0
                    ];
                    if ($fields['UF_SHEDULE_ID'] == 0 ) {
                        $completion = new \Teaching\CourseCompletion();
                        $completion->addFromRequest($request);
                    } else {
                        if (\Models\User::getTeachingType($request['user'])){
                            $completion = new \Teaching\CourseCompletion();
                            $completion->addFromRequest(['id' => $request['schedule'] , 'user' =>$request['user'], 'from_balance' => $request['from_balance']]);
                        }
                        $this->add($fields);
                    }
                }
            }
        }
    }

    public function approve($id)
    {
        $this->update($id, ['UF_IS_APPROVED' => 1]);
        $notifications = new \Notifications\SiteNotifications();
        $item = $this->get(['ID' => $id]);

        $data = [
            'user' => $item[0]['UF_USER_ID'],
            'course_id' => $item[0]['UF_USER_ID'],
            'id' => $item[0]['UF_SHEDULE_ID'],
            'answer_arr' => $item[0]['UF_REGISTER_ANSWER'],
            'from_balance' => $item[0]['UF_PAYMENT_FROM_BALANCE'] == 1?"Y":"N"
        ];
        $completion = new \Teaching\CourseCompletion();
        $completion->addFromRequest($data);
        $course = \Teaching\Courses::getById($item[0]['UF_COURSE_ID']);
        $template = \Settings\Common::getApproveEventTextMessage();
        $template = str_replace('#COURSE_NAME#', $course['NAME'], $template);
        $text_schedule = '';
        $topic = '';
        if($item[0]['UF_SHEDULE_ID']>0){
            $text_schedule = SheduleCourses::getTextToEmail($item[0]['UF_SHEDULE_ID']);
            if($text_schedule!='')
                $topic = str_replace('#ADDITIONAL_TEXT#', '', $template);
        }
        $text = str_replace('#ADDITIONAL_TEXT#', '<br>'.$text_schedule, $template);
        if($item[0]['UF_SHEDULE_ID']>0){
            $schedule = current(SheduleCourses::getById($item[0]['UF_SHEDULE_ID']));
        }
        $user = User::find($item[0]['UF_USER_ID']);
        $email_params = [
            'COURSE_NAME' => $schedule['NAME']??$course['NAME'],
            'COURSE_DATE' => $item[0]['UF_DATE'],
            'FIO' => $user['LAST_NAME']." ".$user['NAME'],
            'PLACE' => $course['PROPERTIES']['ADDRESS'],
            'SHEDULE_ADDITIONAL_INFO' => "",
        ];
        if($item[0]['UF_SHEDULE_ID']>0) {
            $email_params['SHEDULE_ADDITIONAL_INFO'] = SheduleCourses::getTextToEmail($item[0]['UF_SHEDULE_ID'])??"";
        }
        $bResponse = EmailNotifications::send('SUCCESS_COURSE_REGISTER', $user['EMAIL'], $email_params);
        //TODO добавить дебаг класс
        $log = date('Y-m-d H:i:s') . ' ' . print_r([$email_params , $email_params , $bResponse], true);

        file_put_contents(__DIR__ . '/EnrollmentsMail.log', $log . PHP_EOL, FILE_APPEND);

        \Notifications\Common::sendToUser($item[0]['UF_USER_ID'], $text, $topic);
        if($text_schedule!='')
            $notifications->addNotification($item[0]['UF_USER_ID'], $topic);
        else
            $notifications->addNotification($item[0]['UF_USER_ID'], $text);
    }

    public function add($fields)
    {
        $fields['UF_CREATED_AT'] = date('d.m.Y H:i:s');
        if((int)$fields['UF_USER_ID'] > 0) {
            $this->dataClass::add($fields);
        }
    }

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('course_registration');
    }

    public function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return $this->dataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public function getListByUser($user_id = 0, $need_dates = true)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        $enrollments = $this->get(['UF_USER_ID' => $user_id]);
        if ($need_dates) {
            $schedule_ids = [];
            foreach ($enrollments as $enrollment) {
                $schedule_ids[$enrollment['UF_SHEDULE_ID']] = $enrollment;
            }
            $schedules = \Teaching\SheduleCourses::getById(array_keys($schedule_ids));

            foreach ($enrollments as &$enrollment) {
                if (empty($schedules[$enrollment['UF_SHEDULE_ID']]))
                    continue;
                if (!empty($schedules[$enrollment['UF_SHEDULE_ID']]['PROPERTY_BEGIN_DATE_VALUE']))
                    $schedules[$enrollment['UF_SHEDULE_ID']]['PERIOD'] = \Helpers\DateHelper::getIntervalArray($schedules[$enrollment['UF_SHEDULE_ID']]['PROPERTY_BEGIN_DATE_VALUE'], $schedules[$enrollment['UF_SHEDULE_ID']]['PROPERTY_END_DATE_VALUE']);
                $enrollment['SCHEDULE'] = $schedules[$enrollment['UF_SHEDULE_ID']];
            }
            $date_array = [];
            unset($enrollment);
            foreach ($enrollments as $enrollment) {
                if(is_array($enrollment["SCHEDULE"]["PERIOD"])) {
                    if (count($enrollment["SCHEDULE"]["PERIOD"]) == 0)
                        $date_array[$enrollment['SCHEDULE']['PROPERTY_END_DATE_VALUE']][] = $enrollment;
                    else
                        foreach ($enrollment["SCHEDULE"]["PERIOD"] as $date)
                            $date_array[$date][] = $enrollment;
                }
            }
            return $date_array;
        } else {
            return $enrollments;
        }
    }

    public function isCompleted()
    {

    }

    public function getCountCompletedCourses($user_id = 0): int
    {
        return count($this->getCompletedItems($user_id));
    }

    public function getCompletedItems($user_id)
    {
        if ($user_id == 0) {
            global $USER;
            $user_id = $USER->GetID();
        }
        return $this->get(['UF_USER_ID' => $user_id, 'UF_IS_COMPLETED' => 1]);
    }

    public function getMyScheduledIds()
    {
        global $USER;
        $ids = [];
        foreach ($this->get(['UF_USER_ID' => $USER->GetID(), 'UF_IS_APPROVED' => 1], ['UF_SHEDULE_ID']) as $item) {
            $ids[] = $item['UF_SHEDULE_ID'];
        }
        return $ids;
    }

    public function getMyCourseIds()
    {
        global $USER;
        $ids = [];
        foreach ($this->get(['UF_USER_ID' => $USER->GetID(), 'UF_IS_APPROVED' => 1, 'UF_DIDNT_COM' => false], ['UF_COURSE_ID']) as $item) {
            $ids[] = $item['UF_COURSE_ID'];
        }
        $completions = new \Teaching\CourseCompletion();
        $complete_course_ids = $completions->getCompletedItems()->getCourseIds();
        return array_diff($ids, $complete_course_ids);
    }

    public function getInfoById($user_id, $COURSE_ID)
    {
        return $this->get(['UF_USER_ID' => $user_id, 'UF_SHEDULE_ID' => $COURSE_ID]);
    }

    public function getListByScheduleId($course_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $course_id,  'UF_DIDNT_COM' => false]);
    }

    public function getAlllListByScheduleId($course_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $course_id]);
    }

    public function getAllApprovedListByScheduleId($course_id)
    {
        return $this->get(['UF_IS_APPROVED' => 1,'UF_SHEDULE_ID' => $course_id]);
    }

    public function getNewListByScheduleId($course_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $course_id, '>UF_CREATED_AT' => (time() - (86400 * 7))]);
    }

    public function getApprovedEnrollsWithDate($user_id = 0)
    {
        return $this->get(['UF_USER_ID' => \Helpers\UserHelper::prepareUserId($user_id), 'UF_IS_APPROVED' => 1, 'UF_DATE' => false, 'UF_DIDNT_COM' => false]);
    }

    public function getApprovedEnrolls($user_id = 0)
    {
        return $this->get(['UF_USER_ID' => \Helpers\UserHelper::prepareUserId($user_id), 'UF_IS_APPROVED' => 1, 'UF_DIDNT_COM' => false]);
    }

    public function getNotApprovedEnrolls($user_id = 0)
    {
        return $this->get(['UF_USER_ID' => \Helpers\UserHelper::prepareUserId($user_id), 'UF_IS_APPROVED' => 0]);
    }

    public function getAllNoneApprovedEnrolls()
    {
        $ids = \Models\User::getEmployeesIdsByAdmin();
        if(!check_full_array($ids))
            return [];
        return $this->get(['UF_IS_APPROVED' => false, 'UF_USER_ID' => $ids]);
    }

    public function delete($id, $from_cron=false, $expired = false, $reset_cert = true)
    {
        global $USER;
        $notifications = new \Notifications\SiteNotifications();
        $item = $this->get(['ID' => $id]);
        $course = \Teaching\Courses::getById($item[0]['UF_COURSE_ID']);
        if($item[0]['UF_SHEDULE_ID']>0){
            $schedule = current(SheduleCourses::getById($item[0]['UF_SHEDULE_ID']));
        }
        $user = User::find($item[0]['UF_USER_ID']);
        $text = 'Ваша заявка на прохождение курса '.$course['NAME'].' отклонена';
        $email_params = [
            'COURSE_NAME' => $schedule['NAME']??$course['NAME'],
            'COURSE_DATE' => $item[0]['UF_DATE'],
            'FIO' => $user['LAST_NAME']." ".$user['NAME'],
        ];
        $event = 'DENIE_ENROLL';
        if($expired)
            $event = 'EXPIRED_ENROLL';
        EmailNotifications::send($event, $user['EMAIL'], $email_params);
        if(!$from_cron) {
            if ($USER->GetID() != $item[0]['UF_USER_ID'])
                $notifications->addNotification($item[0]['UF_USER_ID'], $text);
        }
        if($reset_cert)
            \Models\Certificate::resetByUserAndCourse($item[0]['UF_USER_ID'], $item[0]['UF_COURSE_ID']);
        $this->dataClass::delete($id);
    }

    public function getArray()
    {
        return $this->get();
    }

    public function setCourseIdBySchedule($enroll_array)
    {
        $this->update($enroll_array['ID'], ['UF_COURSE_ID' => \Teaching\SheduleCourses::getCourseIdBySchedule($enroll_array['UF_SHEDULE_ID'])]);
    }

    public function update($id, $fields)
    {
        if (count($fields) > 0) {
            $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
            $this->dataClass::update($id, $fields);
        }
    }

    public function getNotFreeByUserAndCourse($course_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id, 'UF_IS_APPROVED' => false]);
    }

    public function getByUserAndCourse($course_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(['UF_USER_ID' => $user_id, 'UF_COURSE_ID' => $course_id, 'UF_DIDNT_COM' => false]);
    }

    public function getByUserAndSchedule($course_id, $user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return $this->get(['UF_USER_ID' => $user_id, 'UF_SHEDULE_ID' => $course_id, 'UF_DIDNT_COM' => false]);
    }

    public function getAllBySheduleId($schedule_id)
    {
        return $this->get(['UF_SHEDULE_ID' => $schedule_id], ['*']);
    }

    public function getAllByCourseId($course_id)
    {
        return $this->get(['UF_COURSE_ID' => $course_id, 'UF_DIDNT_COM' => false], ['*']);
    }

    public function create($request)
    {
        if(!empty($request['schedule_id'])&&$request['schedule_id']>0){
            $exists = $this->get(['UF_USER_ID' => $request['employee_id'], 'UF_SHEDULE_ID' => $request['schedule_id']]);
            $schedule_id = $request['schedule_id'];
            $course_id = \Teaching\SheduleCourses::getCourseIdBySchedule($request['schedule_id']);
            if (count($exists) == 0) {
                $fields = [
                    'UF_USER_ID' => $request['employee_id'],
                    'UF_SHEDULE_ID' => $schedule_id,
                    'UF_DATE' => \Teaching\SheduleCourses::getBeginDateBySchedule($request['schedule_id']),
                    'UF_COURSE_ID' => $course_id,
                    'UF_IS_APPROVED' => 1,
                    'UF_IS_FREE_SCHEDULE' => 0,
                    'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                    'UF_REGISTER_ANSWER' => $request['need_answer']?json_encode(['question' => $request['title'], 'answer' => $request['answer']]):false,
                    'UF_PAYMENT_FROM_BALANCE' => $request['from_balance'] == 'Y' ? 1 : 0
                ];
                $this->add($fields);
            }
        }
    }

    public function getAll()
    {
        return $this->get();
    }

    public function getExpired()
    {
        $hours_expired = (int)\Settings\Common::getEnrollLife();
        if($hours_expired>0){
            $date = time()-3600*$hours_expired;
            $filter_date = date('d.m.Y H:i:s', $date);
            return $this->get(['!UF_IS_APPROVED' => 1, '<UF_CREATED_AT' => $filter_date]);
        }else{
            return [];
        }
    }

    public function isAllowUnenroll($enrollment){
        if($enrollment['UF_SHEDULE_ID']>0){
            $schedule = current(\Teaching\SheduleCourses::getById($enrollment['UF_SHEDULE_ID']));
            if(!empty($schedule['PROPERTIES']['NOT_UNENROLL_DATE'])) {
                return time()<(int)strtotime($schedule['PROPERTIES']['NOT_UNENROLL_DATE']);
            }else {
                return true;
            }
        }else{
            return true;
        }
    }

    public function getById($id)
    {
        return current(self::get(['ID'=>$id]));
    }

    public function setEmployeeToEnroll($id, $employee)
    {
        $enroll = self::getById($id);
        if($enroll['ID']>0) {
            self::update($id, ['UF_USER_ID' => $employee]);
        }
    }

    public function addFromData($fields_add)
    {
        $fields = [
            'UF_USER_ID' => $fields_add['UF_USER_ID'],
            'UF_SHEDULE_ID' => $fields_add['UF_SHEDULE_ID'],
            'UF_DATE' => $fields_add['UF_DATE'],
            'UF_COURSE_ID' => $fields_add['UF_COURSE_ID'],
            'UF_IS_APPROVED' => 1,
            'UF_IS_FREE_SCHEDULE' => 0,
            'UF_CREATED_AT' => date('d.m.Y H:i:s', strtotime($fields_add['UF_DATE'].' 12:00:00')),
        ];
        $this->add($fields);
    }

    public function find($param)
    {
        if((int)$param>0)
            return current($this->get(['ID' => $param]));
    }

    public function setFailed($id)
    {
        $this->update($id, ['UF_FAILED' => 1]);
    }

    public function unsetFailed($id)
    {
        $this->update($id, ['UF_FAILED' => 0]);
    }

    public function getListByCourseAndUser($user_ids, $course_id)
    {
        return $this->get(['UF_USER_ID' => $user_ids, 'UF_COURSE_ID' => $course_id, '!UF_IS_APPROVED' => 1]);
    }

    public function setNotCome($ID)
    {
        $this->update($ID, ['UF_DIDNT_COM' => 1]);
    }

    public function unsetNotCome($ID)
    {
        $this->update($ID, ['UF_DIDNT_COM' => false]);
    }

    public function getAllByUser($user_id)
    {
        $filter['UF_USER_ID'] = $user_id;
        return $this->get($filter);
    }

    private function getCourseById($id)
    {
        $item = $this->get(['ID' => $id]);
        return $item[0]['UF_COURSE_ID'];
    }
}