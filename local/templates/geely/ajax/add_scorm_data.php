<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Helpers\DateHelper;
use Helpers\IBlockHelper;
use Models\User;
use Teaching\Courses;
use Teaching\SheduleCourses;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = $course = IBlockHelper::getById((int)$request['course_id'],false,  ['IBLOCK_ID', 'ID', 'NAME']);
$html = '';
if($request['action']=='set_course_for_employee'){
    $all_employees = [];
    $dc_emloyees_ids = [];
    foreach(User::getEmployeesByAdmin() as $dc_employee)
        $dc_emloyees_ids[] = $dc_employee['ID'];
    foreach (User::getEmployeesListForCourse($course['ID']) as $employee) {
        if (in_array($employee['ID'], $dc_emloyees_ids)) {
            $all_employees[] = $employee;
        }
    }

    $type = 'select';
    $html .= '<input type="hidden" id="course_id_hidden" value="' . $request['course_id'] . '">
        <input type="hidden" id="user_id_hidden" value="' . $USER->GetID() . '">
        <div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">' . GetMessage('H3_HEADER') . '</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
    if(count($all_employees)==0) {
        $html .= '<p>'.Loc::getMessage('NO_EMPLOYESS').'</p>';
    } else {
        $html .= '<div class="form-group"><label for="">'.Loc::getMessage('SELECT_EMPLOYEE').'</label><div class="select select--custom"><select class="select2" name="employee_id">';
        foreach ($all_employees as $employee) {
            $html .= '<option value="' . $employee['ID'] . '">' . $employee['NAME'] . ' ' . $employee['LAST_NAME'] . ' ' . $employee['SECOND_NAME'] . '</option>';
        }
        $html .= '</select></div></div>';
        $html .= '<div class="btn-center">
            <a href="" class="btn set_employee_to_course" data-id="' . $request['course_id'] . '">'.Loc::getMessage('SET_EMPLOYEE').'</a>
        </div>';
    }
}else {
    $type = 'enroll_form';
    if (!Courses::isFreeSheduleCourse($course['ID'])) {
        $schedules = SheduleCourses::getAvailableByCourse($course['ID']);
        $is_free = false;
        if (count($schedules) > 0) {
            $type = 'select';
            $dates = [];
            $count_available = 0;
            foreach ($schedules as $schedule) {
                if (SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT'] ?? 30) > 0) {
                    if ($schedule['PROPERTIES']['BEGIN_DATE'] && $schedule['PROPERTIES']['END_DATE']) {
                        foreach (DateHelper::getIntervalArray($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE'], 'd.n.Y') as $date)
                            $dates[] = $date;
                    }
                    $count_available++;
                }
            }
        }
    }
    if ($type == 'select') {
        $html .= '<input type="hidden" id="course_id_hidden" value="' . $request['course_id'] . '">
        <input type="hidden" id="user_id_hidden" value="' . $USER->GetID() . '">
        <div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">'.Loc::getMessage('COURSE_REGISTER').'</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
        if(Courses::isPaid($request['course_id'])){
            $html .= '<input type="hidden" name="need_coupon" value="Y">';
            $html .= '<div class="form-group">
                          <label for="">'.Loc::getMessage('NEED_PROMO').'</label>
                          <input type="text" name="promo" value="" placeholder="'.Loc::getMessage('PROMO_PLACEHOLDER').'">
                      </div>';
        }
        $html .= '<div class="form-group"><label for="">'.Loc::getMessage('GET_SCHEDULE').'</label><div class="select select--custom"><select class="select2" name="schedule_id">';
        foreach ($schedules as $schedule) {
            $html .= '<option value="' . $schedule['ID'] . '">'.Loc::getMessage('FROM_TEXT').DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd.m.Y') . Loc::getMessage('TO_TEXT') . DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'], 'd.m.Y') . Loc::getMessage('YEAR_TEXT').'</option>';
        }
        $html .= '</select></div></div>';
        $html .= '<div class="btn-center">
            <a href="" class="btn send_request_to_course" data-user-id="' . $USER->GetID() . '" data-id="' . $request['course_id'] . '">'.Loc::getMessage('APPROVE_REGISTER').'</a>
        </div>';
    }
    if ($type == 'enroll_form') {
        $html .= '<div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">'.Loc::getMessage('COURSE_REGISTER').'</h3>
        <p>'.Loc::getMessage('YOU_REGISTER').'"' . $course['NAME'] . '". '.Loc::getMessage('APPROVE_TEXT').'</p>
        <div class="btn-center">
            <a href="#" data-template="list" data-user-id="' . $USER->GetID() . '" data-id="' . $course['ID'] . '" class="btn send_request_to_free_course">'.Loc::getMessage('APPROVE_REGISTER').'</a>
        </div>';
    }
}
$response['type'] = $type;
$response['html'] = $html;
echo json_encode($response);

