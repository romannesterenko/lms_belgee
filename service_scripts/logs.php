<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

if($_REQUEST['action'] == 'detail' && $_REQUEST['ID'] > 0){
    $log = current(\Helpers\Log::getById($_REQUEST['ID']));
    $author = \Models\User::getById($log['UF_USER_ID']);
    $before_fields = json_decode($log['UF_BEFORE_FIELDS'], true);
    $after_fields = json_decode($log['UF_AFTER_FIELDS'], true);
    ?>
    <table class="table table-bordered table-striped table-responsive-stack table--white" id="table-1">
        <thead  class="thead-dark">
            <tr>
                <th>Название поля</th>
                <th>Новое значение</th>
                <th>Старое значение</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                Дата
            </td>
            <td><?=$log['UF_CREATED_AT']?></td>
            <td></td>
        </tr>
        <tr>
            <td>
                Кто изменил
            </td>
            <td><?=$author['NAME']?> <?=$author['LAST_NAME']?> (ID: <?=$author['ID']?>)</td>
            <td></td>
        </tr>
            <?php
            if($log['UF_ENTITY'] == 29){
                foreach ($before_fields as $field => $value){
                    if($field=='PASSWORD'||$field=='CHECKWORD') continue;?>
                    <tr>
                        <td>
                            <?=$field?>
                        </td>
                        <td><?=is_array($value)?print_r($value):$value?></td>
                        <?php $aaa = is_array($after_fields[$field])?print_r($after_fields[$field], true):$after_fields[$field]?>
                        <td><?=$after_fields[$field]?($after_fields[$field]==$value?$aaa:"<span style=\"color:red;\">".$aaa."</span>"):' - '?></td>
                    </tr>
                <?php }
            } else {
                foreach ($after_fields as $field => $value){?>
                    <tr>
                        <td>
                            <?=$field?>
                        </td>
                        <td><?=is_array($value)?print_r($value):$value?></td>
                        <?php $aaa = is_array($before_fields[$field])?print_r($before_fields[$field], true):$before_fields[$field]?>
                        <td><?=$before_fields[$field]?($before_fields[$field]==$value?$aaa:"<span style=\"color:red;\">".$aaa."</span>"):' - '?></td>
                    </tr>
                <?php }
            }?>
        </tbody>

    </table>

    <?php
}else {

    /*$APPLICATION->IncludeComponent("bitrix:highloadblock.list", "logs", array(
            "BLOCK_ID" => "14",
            "CHECK_PERMISSIONS" => "Y",
            "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
            "FILTER_NAME" => "",
            "PAGEN_ID" => "page",
            "ROWS_PER_PAGE" => "15"
        )
    );*/
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");