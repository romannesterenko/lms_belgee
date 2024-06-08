<?php
use Bitrix\Main\Localization\Loc;
use Helpers\HLBlockHelper;
use Models\Dealer;
use Models\GAdsAccess;
use Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Teaching\Roles;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER, $notifications_main_filter, $my_courses_filter, $needed_courses_filter, $courses_for_role_filter;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
$user = User::getCurrent();
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "CODE", "DATE_ACTIVE_FROM");//IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
$arFilter = Array('ID'=>User::getDealerByUser(), "IBLOCK_ID"=>10, "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
if($ob = $res->GetNextElement()){
    $dealer = $ob->GetFields();
    $dealer['PROPERTIES'] = $ob->GetProperties();
}
$application_id = "123965";
$role_ids = Roles::getByCurrentUser();
$roles = Roles::getById($role_ids);
$result = false;
$allow_add = true;
$apps_count = 2;
foreach($dealer['PROPERTIES']['APP_COUNT']['VALUE'] as $key => $value){
    $array = \Helpers\StringHelpers::unserialize($value);
    if($array['APP'] == $application_id && $array['COUNT'] <= $apps_count){
        $allow_add = false;
    }
}
if($_REQUEST['sended'] == 'Y') {
    if (!empty($_REQUEST['fio_lat']) && !empty($_REQUEST['vci_serial_number'])) {
        $fields = [
            'UF_APPLICATION_ID' => $_REQUEST['application_id'],
            'UF_USER_ID' => $_REQUEST['user_id'],
            'UF_CREATED_AT' => date("d.m.Y H:i:s"),
            'UF_APPROVED' => false,
            'UF_VCI_SERIAL_NUMBER' => $_REQUEST['vci_serial_number'],
            'UF_ROLES' => explode(',', $_REQUEST['role_ids']),
            'UF_NAME_LAT' => $_REQUEST['fio_lat'],
            'UF_DEALER_ID' => $_REQUEST['dealer_id'],
        ];
        $result = GAdsAccess::create($fields);
    }
}?>
<h2 class="h2 center lowercase">
    Получение доступа к диагностической системе G-ADS.
</h2>
<?php if($allow_add) {?>
<form action="" method="post" style="max-width: 500px; margin: 0 auto">
    <input type="hidden" name="application_id" value="<?=$application_id?>">
    <input type="hidden" name="user_id" value="<?=$user['ID']?>">
    <input type="hidden" name="sended" value="Y">
    <div class="form-group">
        <label for="">Наименование Дилера:</label>
        <input type="text" name="dealer_name" required disabled value="<?=$dealer['NAME']?>">
    </div>
    <input type="hidden" name="dealer_name" value="<?=$dealer['NAME']?>">
    <input type="hidden" name="dealer_id" value="<?=$dealer['ID']?>">
    <div class="form-group">
        <label for="">Dealer Name IDCS (служебное):</label>
        <input type="text" name="dealer_name_idcs" disabled value="">
    </div>
    <div class="form-group">
        <label for="">Код дилера:</label>
        <input type="text" name="dealer_code" required disabled value="<?=$dealer['CODE']?>">
    </div>
    <input type="hidden" name="dealer_code" value="<?=$dealer['CODE']?>">
    <div class="form-group">
        <label for="">Dealer Code IDCS (служебное):</label>
        <input type="text" name="dealer_code_idcs" disabled value="">
    </div>
    <div class="form-group">
        <label for="">Имя и Фамилия:</label>
        <input type="text" name="fio_ru" required disabled value="<?=$user['NAME']?> <?=$user['LAST_NAME']?>">
    </div>
    <input type="hidden" name="fio_ru" value="<?=$user['NAME']?> <?=$user['LAST_NAME']?>">
    <div class="form-group">
        <label for="">Name & Surname:</label>
        <input type="text" name="fio_lat" required value="">
    </div>
    <div class="form-group">
        <label for="">Должность:</label>
        <input type="text" name="role" required disabled value="<?=implode(', ', $roles)?>">
    </div>
    <input type="hidden" name="role" value="<?=implode(', ', $roles)?>">
    <input type="hidden" name="role_ids" value="<?=implode(',', array_keys($roles))?>">
    <div class="form-group">
        <label for="">E-mail:</label>
        <input type="text" name="email" required disabled value="<?=$user['EMAIL']?>">
    </div>
    <input type="hidden" name="email" value="<?=$user['EMAIL']?>">
    <div class="form-group">
        <label for="">Телефон:</label>
        <input type="text" name="phone" required disabled value="<?=$user['PERSONAL_MOBILE']?>">
    </div>
    <input type="hidden" name="phone" value="<?=$user['PERSONAL_MOBILE']?>">
    <div class="form-group">
        <label for="">VCI serial number:</label>
        <input type="text" name="vci_serial_number" required value="">
    </div>

    <div class="btn-center">
        <button class="btn"<?=$result?' disabled':''?>>Отправить</button>
    </div>
</form>
<?php } else {?>
    <p style="text-align: center; color: red">Количество поданных заявок от вашего дилера превысило установленный максимум</p>
<?php }?>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");