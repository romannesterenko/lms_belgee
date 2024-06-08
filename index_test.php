<?php

use Teaching\Courses;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$courses = \Models\Course::getList(['ACTIVE' => 'Y', '!ID' => [115482, 423], 'PROPERTY_HAS_RETEST' => 155], ['ID', 'NAME']);
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="text-content text-content--long">
                    <h2 class="h2 center">Рассылка о необходимости ресертификации</h2>
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">Разослать</a>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead>
                                <tr>
                                    <th>Сотрудник</th>
                                    <th>Курс</th>
                                    <th>Дата сертификата</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course) {
                                    $expired_date = \Models\Course::getExpiredDate($course['ID']);
                                    $list = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $course['ID'], 'UF_IS_COMPLETE' => 1]);
                                    $exists_ids = [];
                                    foreach ($list as $item) {
                                        if(in_array($item['UF_COURSE_ID'].'_'.$item['UF_USER_ID'], $exists_ids))
                                            continue;
                                        $exists_ids[] = $item['UF_COURSE_ID'].'_'.$item['UF_USER_ID'];
                                        if(!Courses::isFreeSheduleCourse($course['ID']))
                                            $new_date = DateTime::createFromFormat('d.m.Y', (string)$item["UF_DATE"]);
                                        else
                                            $new_date = DateTime::createFromFormat('d.m.Y H:i:s', (string)$item["UF_COMPLETED_TIME"]);

                                        if(!$item['UF_RETEST_FAILED'] &&  $new_date) {
                                            $new_date->modify('+'.$expired_date.' months');
                                            if((new DateTime())->format('Y-m-d H:i:s') >= $new_date->format('Y-m-d H:i:s')) {?>
                                                <tr>
                                                    <td><?=\Models\User::getFullName($item['UF_USER_ID'])?></td>
                                                    <td><?=$course['NAME']?></td>
                                                    <td><?=$new_date->format('d.m.Y')?></td>
                                                </tr>
                                            <?php }
                                        }
                                        unset($new_date);
                                        unset($original_date);
                                    }
                                }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");