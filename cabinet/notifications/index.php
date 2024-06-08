<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Settings\Notifications;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
global $USER;
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <div class="content-block content-block--margin">
                <h4 class="h4"><?= Loc::getMessage('METHOD') ?></h4>

                <div class="notifications">
                    <?php $notifications = new Notifications();
                    foreach($notifications->getMethodsForUser()->getWithValues() as $key => $method){?>
                        <div class="item">
                            <span class="item__icon"><img src="<?=$method['ICON_URL']?>" alt=""></span>
                            <div class="radio-item">
                                <input type="radio" data-user-id ="<?=$USER->GetID()?>" id="notice-<?=$method['ID']?>" name="method" value="<?=$method['ID']?>" <?=$method['CHECKED']?> />
                                <label for="notice-<?=$method['ID']?>"><?=$method['DATA']?></label>
                            </div>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php $notifications_main_filter['UF_USER_ID']=$USER->GetID();
                $APPLICATION->IncludeComponent("bitrix:highloadblock.list","notifications_cabinet",Array(
                        "BLOCK_ID" => "3",
                        "CHECK_PERMISSIONS" => "Y",
                        "DETAIL_URL" => "",
                        "FILTER_NAME" => "notifications_main_filter",
                        "PAGEN_ID" => "page",
                        "ROWS_PER_PAGE" => "10"
                    )
                );?>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>