<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;

$schedule = current(\Teaching\SheduleCourses::getById($_REQUEST['id']));
if($schedule['PROPERTIES']['TRAINERS'][0]['VALUE']>0) {
    $trainer = current(\Teaching\Trainers::get(['ID' => $schedule['PROPERTIES']['TRAINERS'][0]['VALUE']]));
    //dump($trainer);
}
if(!is_array($schedule)||$schedule===[]){
    Helpers\PageHelper::set404(Loc::getMessage('SCHEDULE_NOT_FOUND'));
}
$begin_tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
$now_stmp = time();
$end_tmsmp = strtotime($schedule['PROPERTIES']['END_DATE'].' 23:59:59');
$started = $begin_tmstmp<$now_stmp;
$ended = $begin_tmstmp<$now_stmp&&$end_tmsmp<$now_stmp;
$course = \Teaching\Courses::getByScheduleId($schedule['ID']);
$is_hybrid = \Teaching\Courses::isHybridCourse($course['ID']);
if(!is_array($course)||$course===[]){
    Helpers\PageHelper::set404(Loc::getMessage('COURSE_NOT_FOUND'));
}
$list = [];
$completions = new \Teaching\CourseCompletion();
$role_ids = [];
foreach ($completions->getFullApprListBySchedule($schedule['ID']) as $item) {
    $item['USER'] = \Models\User::find($item['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_ROLE']);
    if(check_full_array($item['USER']['UF_ROLE']))
        $role_ids = array_merge($role_ids, $item['USER']['UF_ROLE']);
    $item['USER']['DEALER'] = $item['USER']['UF_DEALER']>0?\Models\Dealer::find($item['USER']['UF_DEALER'], ['ID', 'NAME', 'CODE']):[];
    $item['LAST_NAME'] = $item['USER']['LAST_NAME'];
    $list[] = $item;
}

usort($list, function($a, $b) {
    //$a = preg_split('/\s+/', $a['LAST_NAME'], 1);
    $as = mb_substr($a['LAST_NAME'], 0, 1);
    //$b = preg_split('/\s+/', $b['LAST_NAME'], 1);
    $bs = mb_substr($b['LAST_NAME'], 0, 1);
    return $as <=> $bs;
});
$roles = \Teaching\Roles::getById($role_ids);
if(check_full_array($roles)) {
    foreach ($list as &$l) {
        $role_arr = [];
        if (check_full_array($l['USER']['UF_ROLE'])) {
            foreach ($l['USER']['UF_ROLE'] as $r_id) {
                $role_arr[] = $roles[$r_id];
            }
        }
        $l['USER']['UF_ROLE_LIST'] = implode(', ', $role_arr);
    }
}
if($_REQUEST['update_schedule']=='Y'&&$_REQUEST['schedule']>0){
    if($_REQUEST['update_schedule']=='Y'){
        if($_REQUEST['update_files']=='Y'){
            $files = [];
            for ($i = 0; $i <= 100; $i++) {
                if ($_REQUEST[$i] && ($_REQUEST[$i] > 0)) {
                    if ($_REQUEST[$i . '_del'] == 'Y') {
                        $file = CFile::GetFileArray($_REQUEST[$i]);
                        $picture = [
                            'name' => $file['ORIGINAL_NAME'],
                            'type' => $file['CONTENT_TYPE'],
                            'tmp_name' => $_SERVER["DOCUMENT_ROOT"] . $file['SRC'],
                            'size' => $file['FILE_SIZE'],
                            'del' => 'Y'
                        ];
                        $files[] = $picture;
                    } else {
                        $file = CFile::GetFileArray($_REQUEST[$i]);
                        $picture = [
                            'name' => $file['ORIGINAL_NAME'],
                            'type' => $file['CONTENT_TYPE'],
                            'tmp_name' => $_SERVER["DOCUMENT_ROOT"] . $file['SRC'],
                            'size' => $file['FILE_SIZE'],
                        ];
                        $files[] = $picture;
                    }
                } else {
                    continue;
                }
            }
            foreach ($_REQUEST['pictures'] as $key => $picture) {
                $picture['tmp_name'] = $_SERVER["DOCUMENT_ROOT"] . '/upload/tmp' . $picture['tmp_name'];
                $files[$key] = $picture;
            }
            \Teaching\SheduleCourses::updatePictures($schedule['ID'], $files);
            LocalRedirect('' . $APPLICATION->GetCurPage());
        }else{
            \Teaching\SheduleCourses::updateComent($schedule['ID'], trim($_REQUEST['comment']));
            LocalRedirect('' . $APPLICATION->GetCurPage());
        }

    }
}
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?= Loc::getMessage('SCHEDULE_INFO_TITLE', ['#COURSE_NAME#' => $schedule['NAME']]) ?><br/><?=Helpers\DateHelper::printDates($schedule['PROPERTIES']['BEGIN_DATE'], $schedule['PROPERTIES']['END_DATE'])?></h2>
        <div class="content-block">
            <h3 class="h3 center"><?= Loc::getMessage('H3_COURSE_FILES') ?></h3>
            <?php
            $ids = [];
            if(is_array($schedule['PROPERTIES']['FILES'])&&count($schedule['PROPERTIES']['FILES'])>0){
                foreach ($schedule['PROPERTIES']['FILES'] as $file)
                    $ids[] = $file['VALUE'];
            }
            if($_REQUEST['files_edit_form']=='Y'){?>
                <style>
                    .adm-fileinput-item-wrapper{
                        width: 201px!important;
                    }
                    div.adm-fileinput-item-saved{
                        box-shadow: 0 1px 1px 0 rgb(0 0 0 / 40%), inset 0 0 0 3px #cfe2f0!important;
                    }
                    div.adm-fileinput-item{
                        width: 189px!important;
                    }
                    input.adm-fileinput-item-description,
                    div.adm-fileinput-item-preview
                    {
                        width: 164px!important;
                    }
                    .adm-fileinput-btn-panel{
                        display: none;
                    }
                </style>
                <form action="" method="get" enctype="multipart/form-data">
                    <input type="hidden" name="update_schedule" value="Y">
                    <input type="hidden" name="update_files" value="Y">
                    <input type="hidden" name="schedule" value="<?=$schedule['ID']?>">
                    <?=\Bitrix\Main\UI\FileInput::createInstance([
                        "name" => "pictures[n#IND#]",
                        "description" => false,
                        "upload" => true,
                        "allowUpload" => "F",
                        "medialib" => false,
                        "fileDialog" => false,
                        "cloud" => false,
                        "delete" => true,
                    ])->show($ids);?>
                    <a href="<?=$APPLICATION->GetCurPage()?>" class="btn btn--fixed my-20"><?= Loc::getMessage('BACK_TO_PAGE') ?></a>
                    <button type="submit" class="btn btn--fixed my-20"><?= Loc::getMessage('SAVE_FILES') ?></button>
                </form>
            <?php }else{?>
                <div class="text-center pb-10"><a href="?files_edit_form=Y" class="btn"><?= Loc::getMessage('EDIT_FILES_BLOCK') ?></a></div>
                <?php if(is_array($schedule['PROPERTIES']['FILES'])&&count($schedule['PROPERTIES']['FILES'])>0){?>
                    <div class="materials" style="margin-bottom: 40px">
                        <?php foreach ($schedule['PROPERTIES']['FILES'] as $file){
                            $file = \CFile::GetFileArray($file['VALUE']);
                            ?>
                            <a href="<?=$file['SRC']?>" download="<?=$file['ORIGINAL_NAME']?>" class="material-download-item three_on_row">
                                <span class="material-download-item__title"><?=$file['ORIGINAL_NAME']?></span>
                                <span class="material-download-item__icon">
                                    <span class="icon">
                                        <img src="<?= \Teaching\MaterialsFiles::$icons[$file['CONTENT_TYPE']] ?? \Teaching\MaterialsFiles::$icons['default']?>" alt="">
                                    </span>
                                    (<?= \Teaching\MaterialsFiles::resizeBytes($file['FILE_SIZE']);?> <?= Loc::getMessage('MB') ?>)
                                </span>
                            </a>
                        <?php }?>
                    </div>
                <?php }
            }?>
            <h3 class="h3 center">Комментарий тренера к курсу</h3>
            <?php
            if($_REQUEST['comment_edit_form']=='Y'){
                //dump($schedule['PROPERTIES']);?>
            <form action="" method="get" enctype="multipart/form-data">
                <input type="hidden" name="update_schedule" value="Y">
                <input type="hidden" name="update_coment" value="Y">
                <input type="hidden" name="schedule" value="<?=$schedule['ID']?>">
                <div class="form-group">
                    <label for="">Введите комментарий:</label>
                    <textarea name="comment" placeholder="Текст комментария"><?=$schedule['PROPERTIES']['TRAINER_COMENT']['TEXT']?></textarea>
                </div>
                <a href="<?=$APPLICATION->GetCurPage()?>" class="btn btn--fixed my-20"><?= Loc::getMessage('BACK_TO_PAGE') ?></a>
                <button type="submit" class="btn btn--fixed my-20">Сохранить</button>
            </form>
            <?php } else {
                if(empty($schedule['PROPERTIES']['TRAINER_COMENT']['TEXT'])){?>
                    <p>Комментарий не добавлен</p>
                    <div class="text-center pb-10" style="padding-top: 20px;"><a href="?comment_edit_form=Y" class="btn">Добавить комментарий</a></div>
                <?php } else {?>
                    <p><?=$schedule['PROPERTIES']['TRAINER_COMENT']['TEXT']?></p>
                    <div class="text-center pb-10" style="padding-top: 20px;"><a href="?comment_edit_form=Y" class="btn">Изменить комментарий</a></div>
                <?php }
            }?>
            <h3 class="text-center mt-20"><?= Loc::getMessage('SCHEDULE_INFO_TITLE_PARTICIPANTS') ?></h3>
            <div class="table-block">
                <?php if(check_full_array($list)){?>
                <?php if($started){
                    $checked = \Teaching\SheduleCourses::isAllowMainTest($schedule['ID']);
                    ?>

                    <div class="for_admin">
                        <div class="checkbox-toggle" style="display: flex; flex-direction: column">
                            <span id="main_test_text_alert" style="padding-bottom: 15px; color: <?=$checked?'green':'red'?>">Выходное тестирование <?=$checked?'разрешено':'запрещено'?></span>
                            <label class="switch" for="checkbox2">
                                <input type="checkbox" data-schedule = "<?=$schedule['ID']?>" id="checkbox2"<?=$checked?" checked":""?>>
                                <div class="slider round">
                                    <span class="switch-text allow_testing_block" style="white-space: nowrap"><?=$checked?"Запретить":"Разрешить"?> выходное тестирование</span>
                                </div>
                            </label>
                        </div>
                        <a href="#" class="btn edit_all" style="padding: 5px; height: 30px">Редактировать всех</a>
                    </div>
                <?php }?>
                <table class="table table-bordered table-striped table-responsive-stack" id="table-3">
                    <thead class="thead-dark">
                        <tr>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_DEALER') ?></th>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_NAME') ?></th>
                            <th>Роль</th>
                            <th>ФИО тренера</th>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_WAS_ON_COURSE') ?></th>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_STATUS') ?></th>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_POINTS') ?></th>
                            <th><?= Loc::getMessage('SCHEDULE_INFO_TABLE_TH_COMENT') ?></th>
                            <th class="fact_value"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $item){?>
                        <tr data-id="<?=$item['ID']?>" class="record_completion">
                            <td>
                                <?=$item['USER']['DEALER']['NAME']?>
                            </td>
                            <td>
                                <span class="user-name">
                                    <?=$item['USER']['LAST_NAME']?> <?=$item['USER']['NAME']?>
                                </span>
                            </td>
                            <td>
                                <?=$item['USER']['UF_ROLE_LIST']?>
                            </td>
                            <td>
                                <?=$trainer['NAME']?>
                            </td>
                            <?php if(!$started){?>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            <?php } else { ?>
                                <td>
                                    <span class="fact_value"><?=$item['UF_WAS_ON_COURSE']==1?Loc::getMessage('YES'):Loc::getMessage('NO')?></span>
                                    <span class="for_admin form_value" style="text-align: center">
                                            <input type="checkbox" checked name="was_on_course">
                                    </span>
                                </td>
                                <td>
                                    <span class="fact_value"><?=$item['UF_IS_COMPLETE']==1?Loc::getMessage('YES'):Loc::getMessage('NO')?></span>
                                    <span class="for_admin form_value" style="text-align: center">
                                        <input type="checkbox" checked name="is_complete">
                                    </span>
                                </td>
                                <td>
                                    <span class="fact_value"><?=$item['UF_POINTS']??'-'?></span>
                                    <span class="for_admin form_value" style="text-align: center">
                                          <input type="number" name="points" value="<?=$item['UF_POINTS']??''?>" style="width: 70px; padding: 5px;">
                                    </span>
                                </td>
                            <?php } ?>
                            <td>
                                <span class="fact_value"><?=$item['UF_COMMENT']?></span>
                                <span class="for_admin form_value">
                                      <textarea name="coment" style="width: 150px; height: 75px; padding: 5px;"></textarea>
                                </span>
                            </td>
                            <td class="fact_value">

                                    <a href="#" class="btn comment_modal " data-id="<?=$item['ID']?>" style="padding: 5px; height: 30px"><?= Loc::getMessage('INFO_BUTTON') ?></a>

                            </td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
                <?php if($started){?>
                    <div class="for_admin save_all_records_block hidden"><a href="#" class="btn save_all_records " style="padding: 5px; height: 30px">Сохранить все записи</a></div>
                <?php }?>

                <?php } else {?>
                    <p><?= Loc::getMessage('NO_PARTICIPAINTS') ?></p>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<?php /*if($USER->GetID()==2){*/?>
    <style>
        .for_admin{
            display: flex;
            justify-content: space-between;
            padding: 15px 0px;
        }
        .form_value{
            display: none;
        }
    </style>

<?php /*} else {*/?><!--
    <style>
        .for_admin{
            display: none!important;
        }
    </style>
--><?php /*}*/?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


