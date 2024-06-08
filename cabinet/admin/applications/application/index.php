<?php

use Bitrix\Main\UserTable;
use Helpers\PageHelper;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Helpers\StringHelpers;
use Models\Application;
use Models\Dealer;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$_REQUEST['report_id'] = 99999;
$app_id = explode('?', $_REQUEST['app_id'])[0];
function GetEntityDataClass($HlBlockId) {
    if (empty($HlBlockId) || $HlBlockId < 1)
    {
        return false;
    }
    $hlblock = HLBT::getById($HlBlockId)->fetch();
    $entity = HLBT::compileEntity($hlblock);
    $entity_data_class = $entity->getDataClass();
    return $entity_data_class;
}
if($app_id > 0) {
$exists_apps = Application::getAllAdded($app_id);

/*
foreach ($exists_apps as $exists_app) {
    dump($exists_app['ID']." ".$exists_app['UF_USER_ID']);
    $user = UserTable::getById($exists_app['UF_USER_ID'])->fetch();
    if(!empty($user['WORK_POSITION'])){
        Application::updateRecord($app_id, $exists_app['ID'], ["UF_POSITION" => $user['WORK_POSITION']]);
    }
    dump($user);
}*/


$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
$arFilter = Array("ID" => $app_id, "IBLOCK_ID"=>35, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    $arFields['PROPERTIES'] = $arProps;
}
$tr_fields = [];
foreach($arFields['PROPERTIES']['FIELDS']['VALUE'] as $one_field){
    $one_field = StringHelpers::unserialize($one_field);
    $tr_fields["UF_".$one_field["FIELD_CODE"]] = $one_field["NAME"];
}
$tr_fields["UF_CREATED_AT"] = "Добавлена";
$tr_fields["UF_APPROVED"] = "Одобрена";
$tr_fields["UF_ACTIONS"] = "Действия";


$APPLICATION->SetTitle('Заявка '.$arFields['NAME']); ?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2 center lowercase">Отчет по заявке<br/><?=$arFields['NAME']?></h2>
            <div class="content-block">
                <div class="form-group" style="display: flex; padding-top: 1rem;">
                    <div class="btn-center">
                        <button class="btn" id="gen8"><span>Excel</span></button>
                    </div>
                </div>
                <div class="table-block">
                    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                        <thead class="thead-dark">
                            <tr>
                                <?php foreach ($tr_fields as $code => $tr_field){?>
                                    <th style="white-space: nowrap"><?=$tr_field?></th>
                                <?php }?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exists_apps as $item){ ?>
                                <tr>
                                    <?php foreach ($tr_fields as $code => $tr_field){
                                        $text = $item[$code];
                                        if($code=='UF_ROLE')
                                            $text = \Teaching\Roles::parseFromStringIds($item[$code]);
                                        if($code=='UF_APPROVED'){
                                            if($item[$code]==1)
                                                $text = 'Одобрена';
                                            else {
                                                if ($item['UF_DECLINED'] == 1) {
                                                    $text = 'Отклонена';
                                                } else {
                                                    $text = 'Не одобрена';
                                                }
                                            }
                                        }

                                        if($code=='UF_ACTIONS') {
                                            if($item['UF_APPROVED'] == 0 && $item['UF_DECLINED'] == 0) {
                                                $text = "<button style='cursor:pointer' class='approve_application' data-app-id='".$app_id."' data-id='".$item['ID']."'>Одобрить</button>";
                                                $text .= "<button style='cursor:pointer; margin-top: 5px' class='decline_application' data-app-id='".$app_id."' data-id='".$item['ID']."'>Отклонить</button>";
                                            }
                                        } ?>
                                        <td><?=$text?></td>
                                    <?php }?>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php }?>
    <script>
        $(function(){
            $(document).on('click', '.approve_application', function (e){
                e.preventDefault();
                let id = $(this).data('id')
                let app_id = $(this).data('app-id')
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/approve_application.php',
                    data: {
                        app_id: app_id,
                        record_id: id
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        document.location.reload()
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            })
            $(document).on('click', '.decline_application', function (e){
                e.preventDefault();
                let id = $(this).data('id')
                let app_id = $(this).data('app-id')
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/decline_application.php',
                    data: {
                        app_id: app_id,
                        record_id: id
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        document.location.reload()
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            })
        });
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>