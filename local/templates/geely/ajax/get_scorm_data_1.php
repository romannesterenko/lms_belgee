<?php define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
$response = [];
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
$response['request'] = $request;
$response['course'] = $course = \Helpers\IBlockHelper::getById((int)$request['course_id'],false,  ['IBLOCK_ID', 'ID', 'NAME']);
$html = '';
if($request['action']=='set_course_for_employee'){
    $all_employees = [];
    $dc_emloyees_ids = [];
    foreach(\Models\User::getEmployeesByAdmin() as $dc_employee)
        $dc_emloyees_ids[] = $dc_employee['ID'];
    foreach (\Models\User::getEmployeesListForCourse($course['ID']) as $employee) {
        if (in_array($employee['ID'], $dc_emloyees_ids))
            $all_employees[] = $employee;
    }

    $type = 'select';
    $html .= '<input type="hidden" id="course_id_hidden" value="' . $request['course_id'] . '">
        <input type="hidden" id="user_id_hidden" value="' . $USER->GetID() . '">
        <div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">назначение курса сотруднику</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
    if(count($all_employees)==0){
        $html .= '<p>Все сотрудники вашего дилерского центра уже назначены на этот курс</p>';
    }else {
        $html .= '<div class="form-group"><label for="">Выберите сотрудника из списка для назначения курса:</label><div class="select select--custom"><select class="select2" name="employee_id">';
        foreach ($all_employees as $employee) {
            $html .= '<option value="' . $employee['ID'] . '">' . $employee['NAME'] . ' ' . $employee['LAST_NAME'] . ' ' . $employee['SECOND_NAME'] . '</option>';
        }
        $html .= '</select></div></div>';
        $html .= '<div class="btn-center">
            <a href="" class="btn set_employee_to_course" data-id="' . $request['course_id'] . '">Назначить сотрудника</a>
        </div>';
    }
}else {
    $type = 'enroll_form';
    if (!\Teaching\Courses::isFreeSheduleCourse($course['ID'])) {
        $schedules = \Teaching\SheduleCourses::getAvailableByCourse($course['ID']);
        $is_free = false;
        if (count($schedules) > 0) {
            $type = 'select';
            $dates = [];
            $count_available = 0;
            foreach ($schedules as $schedule) {
                if (\Teaching\SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT'] ?? 30) > 0) {
                    if ($schedule['PROPERTIES']['BEGIN_DATE'] && $schedule['PROPERTIES']['END_DATE']) {
                        foreach (\Helpers\DateHelper::getIntervalArray($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE'], 'd.n.Y') as $date)
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
        <h3 class="h3 center">регистрация на курс</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
        if(\Teaching\Courses::isPaid($request['course_id'])){
            $html .= '<input type="hidden" name="need_coupon" value="Y">';
            $html .= '<div class="form-group">
                          <label for="">Курс платный, необходимо ввести промокод:</label>
                          <input type="text" name="promo" value="" placeholder="Введите промокод">
                      </div>';
        }
        $html .= '<div class="form-group"><label for="">Выберите удобное для Вас расписание курса из списка:</label><div class="select select--custom"><select class="select2" name="schedule_id">';
        foreach ($schedules as $schedule) {
            $html .= '<option value="' . $schedule['ID'] . '">С ' . \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd.m.Y') . ' по ' . \Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'], 'd.m.Y') . ' года</option>';
        }
        $html .= '</select></div></div>';
        $html .= '<div class="btn-center">
            <a href="" class="btn send_request_to_course" data-user-id="' . $USER->GetID() . '" data-id="' . $request['course_id'] . '">Подтвердить регистрацию</a>
        </div>';
    }
    if ($type == 'enroll_form') {
        $html .= '<div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center">регистрация на курс</h3>
        <p>Вы регистрируетесь на курс "' . $course['NAME'] . '". Подтвердите вашу регистрацию, подробное письмо с инструкциями придет к вам на почту.</p>
        <div class="btn-center">
            <a href="#" data-template="list" data-user-id="' . $USER->GetID() . '" data-id="' . $course['ID'] . '" class="btn send_request_to_free_course">Подтвердить регистрацию</a>
        </div>';
    }
}
$response['type'] = $type;
$response['html'] = $html;
echo json_encode($response);

