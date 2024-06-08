<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Models\User;
use Teaching\CourseCompletion;
use Teaching\SheduleCourses;

global $USER;
$response = [];
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$html = '';
if($request['id']>0){
    $completions = new CourseCompletion();
    $completion = $completions->find(($request['id']));
    if($completion['UF_SHEDULE_ID']>0){
        $schedule = current(SheduleCourses::getById($completion['UF_SHEDULE_ID']));
        $begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
        $now_stmp = time();
        $end_tmsmp = strtotime($schedule['PROPERTIES']['END_DATE']);
        $not_started = $begin_tmstmp>$now_stmp;
        $started = $begin_tmstmp<$now_stmp&&$end_tmsmp>$now_stmp;
        $ended = $begin_tmstmp<$now_stmp&&$end_tmsmp<$now_stmp;
    }
    $user = User::find($completion['UF_USER_ID'], ['NAME', 'LAST_NAME']);
    $was_on_course_checked = $completion['UF_WAS_ON_COURSE']==1?'checked':'';
    $completed_checked = $completion['UF_IS_COMPLETE']==1?'checked':'';
    $html .= '<div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center" style="margin-bottom: 15px">'.Loc::getMessage('INFO').'</h3>
        <h5 class="pb-10 center">'.Loc::getMessage('ADDITIONAL_INFO').'</h5>
        <h4 class="h4 center">' . $user['NAME'] . ' ' . $user['LAST_NAME'] . '</h4>
        <form action="" class="course_user_form" method="post">
        <input type="hidden" id="completion_id_hidden" name="completion_id" value="' . $completion['ID'] . '">';
    if($not_started){
        $html .= '<p>'.Loc::getMessage('NOT_STARTED').'</p>';
    }
    if($schedule['ID']>0&&!$not_started) {
             $html .= '<div class="form-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="was_on_course" name="was_on_course" ' . $was_on_course_checked . '>
                                <label for="was_on_course">'.Loc::getMessage('WAS_ON_COURSE').'</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="completed_course" name="completed_course" ' . $completed_checked . '>
                                <label for="completed_course">'.Loc::getMessage('WAS_COMPLETE_COURSE').'</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">'.Loc::getMessage('POINTS').'</label>
                            <input type="number" value="' . $completion['UF_POINTS'] . '" name="points">
                        </div>';
        }
         $html.='<div class="form-group">
                  <label for="">'.Loc::getMessage('COMENT').'</label>
                  <textarea placeholder="" name="coment">'.$completion['UF_COMMENT'].'</textarea>
                </div>
                <div class="form-group">
                  <div class="btn-center">
                    <button type="submit" class="btn">'.Loc::getMessage('ADD_INFO').'</button>
                  </div>
                </div>
        </form>';

}
$response['html'] = $html;
echo json_encode($response);

