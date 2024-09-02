<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
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
$response['shedule'] = $schedule = current(SheduleCourses::getArray(['ID' => $request['shedule_id']]));
$response['course'] = $course = IBlockHelper::getById((int)$schedule['PROPERTIES']['COURSE'],false,  ['IBLOCK_ID', 'ID', 'NAME']);
$need_courses = \Teaching\Courses::getCoursesBefore($course['ID']);
        $html = '<div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center" style="margin-bottom: 15px">'.Loc::getMessage('COURSE_REGISTER').'</h3>';
        $html.= '<h4 class="h4 center">'.$course['NAME'].'</h4>';
        $html.= '<h5 class="h4 center">'.Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE']).' - '.Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE']).'</h5>';
        $html.='<input type="hidden" name="schedule_id" value="'.$schedule['ID'].'">';
        if(!\Models\Course::allowToFreeEnroll($course['ID']) && Courses::isPaid($course['ID'])){
            $payment_methods = \Models\Course::getPaymentMethodsList();
            if(\Models\Course::isAllPayment($course['ID']) && !\Teaching\Courses::isAllowToEnrollByCourseAndBalance($course['ID'])){
                $html .= '<p style="margin-bottom: 10px; color: red">Оплата с баланса счета недоступна из за отрицательного баланса дилера</p>';
                unset($payment_methods[160]);
            }
            if(\Models\Course::isTestCourse($course['ID'])) {
                $html .= '<div class="form-group payment_method_select"><label for="">Метод оплаты</label><div class="select select--custom">';
                if(\Models\Course::isAllPayment($course['ID']) && count($payment_methods)>1){
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
        $html.='<p>'.Loc::getMessage('YOU_REGISTER').'"' . $course['NAME'] . '". '.Loc::getMessage('APPROVE_TEXT').'</p>';
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
            $html.='<h5 class="h5 center answer_title" style="margin-bottom: 10px;">'.$question['PROPERTY_TITLE_VALUE'].'</h5>';
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
                    $checked = $key==0?' checked':'';

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
        $html.='<div class="btn-center">
            <a href="#" data-template="list" data-user-id="' . $USER->GetID() . '" data-id="' . $course['ID'] . '" class="btn send_request_to_course">'.Loc::getMessage('APPROVE_REGISTER').'</a>
        </div>';
$allow_to_enroll = true;
if(check_full_array($need_courses)){
    $completions = new \Teaching\CourseCompletion();
    foreach ($need_courses as $need_course){
        if($allow_to_enroll)
            $allow_to_enroll = $completions->isCompleted((int)$need_course);
    }
}
$response['allow_to_enroll'] = $allow_to_enroll;
//if($USER->GetID()==2) {
    if ($request['action']!='set_course_for_employee'&&!$allow_to_enroll) {
        $html = '<div class="modal-icon"><img src="' . SITE_TEMPLATE_PATH . '/images/education-icon.svg" alt=""></div>';
        $html .= '<h3 class="h3 center">Невозможно записаться на курс</h3><h4 class="h4 center">"' . $course['NAME'] . '"</h4>';
        $html .= '<p>для записи на курс и дальнейшего его прохождения необходимо пройти следующие курсы:</p>';
        $html .= '<ul class="marker-list">';
        foreach ($need_courses as $need_course){
            $course = \Models\Course::find((int)$need_course, ['ID', 'NAME', 'CODE']);
            $html .= '<li><a href="/courses/'.$course['CODE'].'/">'.$course['NAME'].'</a></li>';
        }
        $html .= '</ul>';
    }
//}
$response['type'] = $type;
$response['html'] = $html;
echo json_encode($response);

