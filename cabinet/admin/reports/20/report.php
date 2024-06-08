<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    die("Доступ по прямой ссылке запрещен");

$_REQUEST['report_id'] = 9999999;
$table_data = [];
if(check_full_array($_REQUEST['regional_op'])){
    if (!empty($_REQUEST['dealer']) && $_REQUEST['dealer'] != 'all'){
        $dealers = \Models\Dealer::getList(['ACTIVE' => 'Y', 'ID' => $_REQUEST['dealer']], ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);
    } else {
        switch ($_REQUEST['direction']){
            case 'S01':
                $dealers = \Models\Dealer::getByRegionalOP($_REQUEST['regional_op'], ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);
                break;
            case 'A01':
                $dealers = \Models\Dealer::getByRegionalPPO($_REQUEST['regional_op'], ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);
                break;
            case 'M01':
                $dealers = \Models\Dealer::getByRegionalMarketing($_REQUEST['regional_op'], ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);
                break;
        }
    }
} else {
    if (!empty($_REQUEST['dealer']) && $_REQUEST['dealer'] != 'all'){
        $dealers = \Models\Dealer::getList(['ACTIVE' => 'Y', 'ID' => $_REQUEST['dealer']], ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);

    } else {
        $dealers = \Models\Dealer::getAll(['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING', 'ACTIVE']);
    }
}
unset($dealers[360]);
$regional = [];
$directions = \Models\Direction::all();
if(check_full_array($_REQUEST['direction'])) {
    $directions = $_REQUEST['direction'];
}
$directions = $_REQUEST['direction'] == 'all' ? \Models\Direction::all() : [$_REQUEST['direction']];
foreach ($dealers as &$one_dealer) {
    foreach ($directions as $direction) {
        switch ($direction) {
            case 'S01':
                $one_dealer[$direction]['REGIONAL'] = (int)$one_dealer['PROPERTY_REGIONAL_VALUE'] > 1 ? \Models\User::getFullName($one_dealer['PROPERTY_REGIONAL_VALUE']) : '';
                break;
            case 'A01':
                $one_dealer[$direction]['REGIONAL'] = (int)$one_dealer['PROPERTY_REGIONAL_PPO_VALUE'] > 1?\Models\User::getFullName($one_dealer['PROPERTY_REGIONAL_PPO_VALUE']):'';
                break;
            case 'M01':
                $one_dealer[$direction]['REGIONAL'] = (int)$one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE'] > 1?\Models\User::getFullName($one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE']):'';
                break;
        }
        $one_dealer[$direction]['BALANCE'] = \Models\Dealer::getAllBalance($one_dealer['ID'])[$direction];
    }
} ?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Отчет по балансу дилеров</h2>
                <div class="text-content text-content--long">
                    <div class="table-block">
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <a href="." class="btn">К генератору</a>
                            </div>
                            <div class="btn-center">
                                <button class="btn" id="gen"><span>Excel</span></button>
                            </div>
                        </div>
                        <table class="table table-bordered" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="vertical-align: middle" rowspan="2" class="text-center ">№ п/п</th>
                                    <th style="vertical-align: middle" rowspan="2" class="text-center">Код дилера</th>
                                    <th style="vertical-align: middle" rowspan="2" class="text-center">Дилер</th>
                                    <?php foreach ($directions as $direction) {?>
                                        <th style="vertical-align: middle" colspan="4" class="text-center"><?=\Models\Direction::getDirectionByCode($direction)['title']?></th>
                                    <?php }?>
                                </tr>
                                <tr>
                                    <?php foreach ($directions as $direction) {?>
                                        <th style="vertical-align: middle" rowspan="2" class="text-center ">Регионал</th>
                                        <th style="vertical-align: middle" class="text-center">Всего</th>
                                        <th style="vertical-align: middle" class="text-center">Резерв</th>
                                        <th style="vertical-align: middle" class="text-center">Свободно</th>
                                    <?php }?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $key = 1;
                            foreach ($dealers as $dealer) { ?>
                                <tr>
                                    <td><?=$key?></td>
                                    <td><?= $dealer['CODE'] ?></td>
                                    <td class="text-left"><?= $dealer['NAME'] ?></td>
                                    <?php foreach ($directions as $direction) {?>
                                        <td><?= $dealer[$direction]['REGIONAL'] ?></td>
                                        <td><?= (int)$dealer[$direction]['BALANCE']['incoming'] ?></td>
                                        <td><?= (int)$dealer[$direction]['BALANCE']['reserves'] ?></td>
                                        <td><?= (int)$dealer[$direction]['BALANCE']['free'] ?></td>
                                    <?php }?>

                                </tr>
                            <?php $key++; }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>