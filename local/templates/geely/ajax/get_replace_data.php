<?php

use Bitrix\Main\Application;
use Helpers\IBlockHelper;
use Models\User;
use Teaching\Enrollments;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$enrollments = new Enrollments();
$completions = new \Teaching\CourseCompletion();
$enroll = current($enrollments->get(['ID'=>(int)$request['id']]));
$all_enrolls = $enrollments->getAllBySheduleId($enroll['UF_SHEDULE_ID']);
$exists_users = [];
foreach ($all_enrolls as $all_enroll) {
    $exists_users[] = $all_enroll['UF_USER_ID'];
}


$response['course'] = $course = IBlockHelper::getById($enroll['UF_COURSE_ID'],false,  ['IBLOCK_ID', 'ID', 'NAME']);
$html = '';
    $all_employees = [];
    $dc_emloyees_ids = [];
    foreach(User::getEmployeesByAdmin() as $dc_employee) {
        //$html.= '<input type="hidden" name="all_in_dc" value="'.$dc_employee['ID'].'">';
        $dc_emloyees_ids[] = $dc_employee['ID'];
    }
    foreach (User::getNeedEmployeesListForCourse($course['ID']) as $employee) {
        //$html.= '<input type="hidden" name="exists" value="'.$employee['ID'].'">';
        if (in_array($employee['ID'], $exists_users)) {
            continue;
        }
        if (in_array($employee['ID'], $dc_emloyees_ids))
            $all_employees[] = $employee;
    }
    $response['need_courses'] = $need_courses = \Teaching\Courses::getCoursesBefore($course['ID']);
    $has_before_courses = false;
    $new_all_employees = [];
    foreach ($all_employees as $all_employee){
        $allow_to_enroll = true;
        foreach ($need_courses as $need_course){
            if($allow_to_enroll)
                $allow_to_enroll = $completions->isCompleted((int)$need_course, $all_employee['ID']);
        }
        if($allow_to_enroll)
            $new_all_employees[] = $all_employee;
        else
            $has_before_courses = true;
    }
    $all_employees = $new_all_employees;
    $type = 'select';
    $html .= '<input type="hidden" id="enroll_id_hidden" value="' . $enroll['ID'] . '">
        <input type="hidden" id="user_id_hidden" value="' . $USER->GetID() . '">
        <div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">'.GetMessage('PP_TITLE').'</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
    if(count($all_employees)==0){
        if($has_before_courses)
            $html .= '<p>'.GetMessage('HAS_COURSES_BEFORE').'</p>';
        else
            $html .= '<p>'.GetMessage('NO_EMPLOYEES').'</p>';
    }else {
        $html .= '<div class="form-group"><label for="">'.GetMessage('PICK_EMPLOYEE').'</label><div class="select select--custom"><select class="select2" name="employee_id">';
        foreach ($all_employees as $employee) {
            $html .= '<option value="' . $employee['ID'] . '">' . $employee['NAME'] . ' ' . $employee['LAST_NAME'] . ' ' . $employee['SECOND_NAME'] . '</option>';
        }
        $html .= '</select></div></div>';
        $html .= '<div class="btn-center">
            <a href="" class="btn replace_employee" data-id="' . $enroll['ID'] . '">'.GetMessage("ENR_EMPLOYEE").'</a>
        </div>';
    }
$response['type'] = $type;
$response['html'] = $html;
echo json_encode($response);

