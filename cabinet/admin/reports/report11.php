<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Models\User;
use Teaching\CourseCompletion;
use Teaching\Courses;

global $USER;
$_REQUEST['report_id'] = 9999;
$month = $_REQUEST['month']??date('m');
$year = $_REQUEST['year']??date('Y');
?>

<?php
$rows = [];
$data = [];
$by_days = false;
$courses = \Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
if((int)$_REQUEST['course']>0){
    $current_course = $courses[(int)$_REQUEST['course']];
} else {
    $current_course = current($courses);
}
if(Courses::isFreeSheduleCourse($current_course['ID'])){
    $max_ball = 0;
    if(\Models\Course::isScormCourse($current_course['ID'])){
        $max_ball = 100;
    } else {
        $test = current(\Teaching\Tests::getTestByCourse($current_course['ID']));
        if(check_full_array($test)) {
            $questions = \Teaching\Tests::getQuestionsByTest($test['ID']);
            if(check_full_array($questions)){
                foreach ($questions as $question){
                    $max_ball+=$question['PROPERTIES']['POINTS'];
                }
            }
        }
    }

}

//получим курсы доступные для роли
$filter = ['UF_COURSE_ID' => $current_course['ID']];
if(!empty($_REQUEST['date_from']))
    $filter['>UF_DATE'] = date('d.m.Y', strtotime($_REQUEST['date_from']));
if(!empty($_REQUEST['date_to']))
    $filter['<UF_DATE'] = date('d.m.Y', strtotime($_REQUEST['date_to']));
$list = (new CourseCompletion)->get($filter);
$data = [];
foreach ($list as $item){
    $item['USER'] = User::find($item['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'WORK_POSITION', 'EMAIL', 'UF_DEALER']);
    if((int)$item['USER']['UF_DEALER']==0)
        continue;
    $item['USER']['DEALER'] = \Models\Dealer::find((int)$item['USER']['UF_DEALER']);
    $data[] = $item;
}
$months = [
    "01" => "Январь",
    "02" => "Февраль",
    "03" => "Март",
    "04" => "Апрель",
    "05" => "Май",
    "06" => "Июнь",
    "07" => "Июль",
    "08" => "Август",
    "09" => "Сентябрь",
    "10" => "Октябрь",
    "11" => "Ноябрь",
    "12" => "Декабрь",
];
$years = range((int)date('Y')-5, (int)date('Y'));
?>
    <div class="content-block">
        <div class="text-content text-content--long">
            <h2 class="h2 center lowercase">Отчет по тестированию курса "<?=$current_course['NAME']?>"</h2>
            <div class="table-block">
                <form class="report_generator" action="" method="get">
                    <span style="display: flex">
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Курс</label>
                            <div class="select">
                                <select class="js-example-basic-multiple" name="course">
                                    <?php foreach ($courses as $id => $course){?>
                                        <option value="<?=$id?>"<?=$current_course['ID']==$id?' selected':''?>><?=$course['NAME']?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Диапазон дат от</label>
                            <input type="date" name="date_from" value="<?=$_REQUEST['date_from']?>">

                        </div>
                        <div class="form-group selectable" style="width: 100%; margin-right: 10px;">
                            <label for="">Диапазон дат до</label>
                            <input type="date" name="date_to" value="<?=$_REQUEST['date_to']?>">
                        </div>
                        <div class="form-group selectable">
                            <label for="">&nbsp;</label>
                            <button class="btn" style="height: 36px">Генерировать</button>
                        </div>
                    </span>
                </form>
                <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                    <thead class="thead-dark">
                        <tr>
                            <th>Фамилия</th>
                            <th>Имя</th>
                            <th>Дата</th>
                            <th>Прошел</th>
                            <th>Полученные баллы</th>
                            <th>Максимальный балл</th>
                            <?php /*<th>Попытка 1</th>
                            <th>Попытка 2</th>*/?>
                            <th>Email</th>
                            <th>Дилерский центр</th>
                            <th>Должность</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $key => $row){?>
                        <tr style="height: 50px">
                            <td><?=$row['USER']['LAST_NAME']?></td>
                            <td><?=$row['USER']['NAME']?></td>
                            <td><?=$row['UF_COMPLETED_TIME']??$row['UF_DATE']?></td>
                            <td><?=$row['UF_IS_COMPLETE']?'Да':'Нет'?></td>
                            <?php if($max_ball>0&&$row['UF_POINTS']>$max_ball){
                                $row['UF_POINTS'] = $max_ball;
                                (new CourseCompletion())->setPoints($max_ball, $row['ID']);
                                ?>
                            <?php }?>
                            <td><?=$row['UF_POINTS']?></td>
                            <td><?=$max_ball?></td>
                            <?php /*<td></td>
                            <td></td>*/?>
                            <td><?=$row['USER']['EMAIL']?></td>
                            <td><?=$row['USER']['DEALER']['NAME']?> (<?=$row['USER']['DEALER']['CODE']?>)</td>
                            <td><?=$row['USER']['WORK_POSITION']?></td>
                        </tr>
                    <?php
                    }?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>