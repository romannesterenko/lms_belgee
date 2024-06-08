<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;

use Bitrix\Main\Application;
use Helpers\DateHelper;
use Helpers\IBlockHelper;
use Models\User;
use Teaching\Courses;
use Teaching\SheduleCourses;

$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = $course = IBlockHelper::getById((int)$request['course_id'],false,  ['IBLOCK_ID', 'ID', 'NAME']);
$completions = new \Teaching\CourseCompletion();
$html = '';

$all_employees = [];
$req_ids = array_unique(array_merge(
    User::getEmployeesByRoleToCourse($course['ID'], true),
    User::getRecommendEmployeesByRoleToCourse($course['ID'], true),
    User::getEmployeesByCourse($course['ID'], true),
));
$completed_ids = [];
if(!SheduleCourses::isExistsCheckedByCourse($course['ID'])) {
    $completed_ids = User::getCompletedEmployeesToCourse($course['ID'], true);
    $without_ids = array_merge(
        [$USER->GetID()],
        User::getEnrolledEmployeesToCourse($course['ID'], true),
        $completed_ids,
        User::getCompletingEmployeesToCourse($course['ID'], true),
    );
} else {
    $without_ids = array_merge(
        [$USER->GetID()],
        User::getCompletingEmployeesToCourse($course['ID'], true),
    );
}

$response['without_ids'] = $without_ids;
/*print_r($without_ids);*/
$need_ids = array_diff($req_ids, $without_ids);
$need_courses = \Teaching\Courses::getCoursesBefore($course['ID']);
$has_before_courses = false;
foreach (User::getEmployeesByAdmin() as $employee) {
    if (in_array($employee['ID'], $need_ids)) {
        $allow_to_enroll = true;
        foreach ($need_courses as $need_course) {
            if($allow_to_enroll)
                $allow_to_enroll = $completions->isCompleted((int)$need_course, $employee['ID']);
        }
        if($allow_to_enroll)
            $all_employees[] = $employee;
        else
            $has_before_courses = true;
    }
}
$response['$all_employees'] = $all_employees;
$html = '<input type="hidden" id="course_id_hidden" value="' . $request['course_id'] . '">
        <input type="hidden" id="user_id_hidden" value="' . $USER->GetID() . '">
        <div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">'.GetMessage('H3_TITLE').'</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
if( count($all_employees) == 0 ) {
    if($has_before_courses)
        $html .= '<p>'.GetMessage('HAS_COURSES_BEFORE').'</p>';
    else
        $html .= '<p>'.GetMessage('ALL_EMPS_SETTED').'</p>';
} else {
    if( Courses::isPaid($course['ID']) ) {
        $payment_methods = \Models\Course::getPaymentMethodsList();
        if(\Models\Course::isAllPayment($course['ID']) && !\Teaching\Courses::isAllowToEnrollByCourseAndDealer($course['ID'])){
            $html .= '<p style="margin-bottom: 10px; color: red">Оплата с баланса счета недоступна из за отрицательного баланса дилера</p>';
            unset($payment_methods[160]);
        }
        if(\Models\Course::isTestCourse($course['ID'])) {
            $html .= '<div class="form-group payment_method_select"><label for="">Метод оплаты</label><div class="select select--custom">';
            if(\Models\Course::isAllPayment($course['ID']) && count($payment_methods) > 1){
                $html .= '<input type="hidden" name="from_balance" class="rendered_by_payment_method" value="Y">';

                $html .= '<select class="select2" name="payment_method">';
                foreach ($payment_methods as $payment_method_id => $payment_method_name) {
                    $html .= '<option value="' . $payment_method_id . '">' . $payment_method_name . '</option>';
                }
                $html .= '</select></div></div>';
            } else {
                if(\Models\Course::isBalancePayment($course['ID'])){
                    $html .= '<input type="hidden" name="from_balance" class="rendered_by_payment_method" value="Y">';

                    $html .= '<select class="select2" name="payment_method" disabled>';
                    foreach ($payment_methods as $payment_method_id => $payment_method_name) {
                        $html .= '<option value="' . $payment_method_id . '">' . $payment_method_name . '</option>';
                    }
                    $html .= '</select></div></div>';
                } else {
                    $html .= '<select class="select2" name="payment_method" disabled>';
                    $html .= '<option value="159">Сертификат</option>';
                    $html .= '</select></div></div>';
                    $html .= '<input type="hidden" name="need_coupon" value="Y">';
                    $html .= '<div class="form-group">
                              <label for="">' . GetMessage('NEED_PROMO') . '</label>
                              <input type="text" name="promo" value="" placeholder="' . GetMessage('NEED_PROMO_PLACEHOLDER') . '">
                          </div>';
                }
            }

        } else {
            $html .= '<input type="hidden" name="need_coupon" value="Y">';
            $html .= '<div class="form-group">
                          <label for="">' . GetMessage('NEED_PROMO') . '</label>
                          <input type="text" name="promo" value="" placeholder="' . GetMessage('NEED_PROMO_PLACEHOLDER') . '">
                      </div>';
        }
    }
    $html .= '<div class="form-group"><label for="">'.GetMessage('SELECT_EMPLOYEE').'</label><div class="select select--custom"><select class="select2" name="employee_id">';
    foreach ( $all_employees as $employee ) {
        $html .= '<option value="' . $employee['ID'] . '">' . $employee['LAST_NAME'] . ' ' . $employee['NAME'] . ' ' . $employee['SECOND_NAME'] . '</option>';
    }
    $html .= '</select></div></div>';


if ( !Courses::isFreeSheduleCourse($course['ID']) ) {
    $schedules = SheduleCourses::getAvailableByCourse($course['ID'], true);
    $is_free = false;
    if (count($schedules) > 0) {
        $dates = [];
        $count_available = 0;
        foreach ($schedules as $key => $schedule) {
            if (SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT'] ?? 30) > 0) {
                if ($schedule['PROPERTIES']['BEGIN_DATE'] && $schedule['PROPERTIES']['END_DATE']) {
                    foreach (DateHelper::getIntervalArray($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE'], 'd.n.Y') as $date)
                        $dates[] = $date;
                }
                $count_available++;
            }
        }
    }
    if (count($schedules) > 0) {
        $html .= '<div class="form-group"><label for="">'.GetMessage('SELECT_SCHEDULE').'</label><div class="select select--custom"><select class="select2" name="schedule_id">';
        foreach ($schedules as $schedule) {
            $select = $request['need_schedule']>0&&$request['need_schedule']==$schedule['ID']?' selected ':'';
            $html .= '<option data-date="'.$schedule['PROPERTY_BEGIN_REGISTRATION_DATE_VALUE'].'" value="' . $schedule['ID'] . '"'.$select.'>'.GetMessage('FROM_TEXT'). DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd.m.Y') . GetMessage('TO_TEXT') . DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'], 'd.m.Y') . GetMessage('YEAR_TEXT').'</option>';
        }
        $html .= '</select></div></div>';
    }
}
    $has_question = false;
    $question = current(\Models\RegisterQuestion::findByCourse($course['ID']));
    if(check_full_array($question)&&$question['ID']>0){
        $has_question = true;
        if($question['PROPERTY_TYPE_ENUM_ID']==120){
            $res = \CIBlockElement::GetProperty(18, $question['ID'], "sort", "asc", array("CODE" => "VARIANTS"));
            while ($ob = $res->GetNext()){
                $VALUES[] = $ob['VALUE'];
            }
            $question['VARIANTS'] = $VALUES;
        }
        if($question['PROPERTY_TYPE_ENUM_ID']==119){
            $res = \CIBlockElement::GetProperty(18, $question['ID'], "sort", "asc", array("CODE" => "VARIANTS"));
            while ($ob = $res->GetNext()){
                $VALUES[] = $ob['VALUE'];
            }
            $question['VARIANTS'] = $VALUES;
        }
    }
    if ($has_question){

        $html.='<h5 class="h5 center answer_title" style="margin-bottom: 10px">'.$question['PROPERTY_TITLE_VALUE'].'</h5>';
        if($question['PROPERTY_TYPE_ENUM_ID']==120&&check_full_array($question['VARIANTS'])){
            $html.='<input type="hidden" id="need_answer" value="radio"><input type="hidden" id="question" value="'.$question['PROPERTY_TITLE_VALUE'].'"/>';
            foreach ($question['VARIANTS'] as $key => $variant) {
                $checked = $key==0?' checked':'';
                $html .= '<div class="form-group" style="margin-left: 15px;">
                                  <div class="radio-item">
                                    <input type="radio" id="poll-question-'.$key.'" value="'.$variant.'" name="reg_answer"'.$checked.'>
                                    <label for="poll-question-'.$key.'">'.$variant.'</label>
                                  </div>
                                </div>';
            }
        }
        if($question['PROPERTY_TYPE_ENUM_ID']==119&&check_full_array($question['VARIANTS'])){
            $html.='<input type="hidden" id="need_answer" value="checkbox"><input type="hidden" id="question" value="'.$question['PROPERTY_TITLE_VALUE'].'"/>';
            foreach ($question['VARIANTS'] as $key => $variant) {
                $html .= '<div class="form-group">
                            <div class="checkbox-item">
                              <input type="checkbox" id="poll-question-'.$key.'" name="reg_answer[]" value="'.$variant.'">
                              <label for="poll-question-'.$key.'">'.$variant.'</label>
                            </div>
                          </div>';
            }
        }
        if($question['PROPERTY_TYPE_ENUM_ID']==121){
            $html.='<input type="hidden" id="need_answer" value="text"><input type="hidden" id="question" value="'.$question['PROPERTY_TITLE_VALUE'].'"/>';
            $html .= '<div class="form-group">
                          <input type="text" name="reg_answer" placeholder="Впишите ответ на вопрос">
                        </div>';
        }
    }
$html .= '<div class="btn-center">
            <a href="" class="btn enroll_employee_to_course" data-id="' . $request['course_id'] . '">'.GetMessage('ENROLL_EMPLOYEE').'</a>
        </div>';
}
$response['html'] = $html;
echo json_encode($response);

