<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');
global $USER, $APPLICATION;
CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("404 Страница не найдена");
$log = date('Y-m-d H:i:s') . ' ' . print_r($_SERVER, true);
file_put_contents(__DIR__ . '/404LogError.log', $log . PHP_EOL, FILE_APPEND);

if($USER->GetID()>0&&$USER->GetID()!=2) {
    $text = 'Пользователь <a href="https://lms.geely-motors.com/bitrix/admin/user_edit.php?lang=ru&ID=' . $USER->GetID() . '">' . $USER->GetFormattedName() . '</a> получил 404 страницу при переходе по ссылке <a href="https://lms.geely-motors.com' . $APPLICATION->GetCurPage() . '">' . $APPLICATION->GetCurPage() . '</a>';
    \Notifications\EmailNotifications::sentCommonEmail(
        'yuri@timofeev.su,romannesterenko87@gmail.com',
        '404 страница',
        $text,
    );
}
/*$APPLICATION->IncludeComponent("bitrix:main.map", ".default", Array(
	"LEVEL"	=>	"3",
	"COL_NUM"	=>	"2",
	"SHOW_DESCRIPTION"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"CACHE_TIME"	=>	"36000000"
	)
);*/
?>
    <span class="logo-title" style="text-align: center"><img src="<?=\Bitrix\Main\Config\Option::get('common.settings', 'logo_404')?>" alt=""></span>
    <h2 class="h2">Страница не найдена</h2>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>