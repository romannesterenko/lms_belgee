<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$accounts = \Settings\ZoomAccount::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
$roles = Models\Role::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
$dealers = Models\Dealer::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
$accounts_array = [];
$roles_array = [];
$dealers_array = [];
foreach ($accounts as $account)
    $accounts_array[$account['ID']] = $account['NAME'];
foreach ($roles as $role)
    $roles_array[$role['ID']] = $role['NAME'];
foreach ($dealers as $dealer)
    $dealers_array[$dealer['ID']] = $dealer['NAME'];

$meeting_types = ['meeting' => 'meeting', 'webinar' => 'webinar'];
Loader::includeModule($module_id);
$aTabs = array(
    array(
        "DIV" => "common_settings",
        "TAB" => "Общие настройки",
        "TITLE" => "Общие настройки",
        "OPTIONS" => array(
            "Направления обучения",
            array(
                "sale_admin_has_marketing_rights",
                "Администратор ОП имеет все права на просмотр и управление направлением Маркетинг",
                "",
                array("checkbox")
            ),
            "Настройки дилеров",
            array(
                "main_dealer",
                "Ключевой дилер (дистрибьютор)",
                "",
                array("selectbox", $dealers_array)
            ),
            "Лого и изображения",
            array(
                "header_logo",
                "Логотип в шапке сайта (путь к файлу от корня)",
                "",
                array("text", 30)
            ),
            array(
                "common_logo",
                "Логотип",
                "",
                array("text", 30)
            ),
            array(
                "logo_404",
                "Изображение на странице 404 (путь к файлу от корня)",
                "",
                array("text", 30)
            ),
            array(
                "logo_auth",
                "Изображение на странице авторизации (путь к файлу от корня)",
                "",
                array("text", 30)
            )
        )
    ),
    array(
        "DIV" => "balance_settings",
        "TAB" => "Баланс",
        "TITLE" => "Настройки баланса",
        "OPTIONS" => array(
            array(
                "is_allow_to_enroll_minus_balance",
                "Разрешать записывать при отрицательном балансе",
                "",
                array("checkbox")
            ),
            "Тестирование",
            array(
                "balance_testing_mode",
                "Режим тестирования",
                "",
                array("checkbox")
            ),
            array(
                "balance_testing_dealers",
                "Дилер для теста",
                "",
                array("selectbox", $dealers_array)
            ),
        )
    ),
    array(
        "DIV" => "course_card_settings",
        "TAB" => "Карточка курса",
        "TITLE" => "Карточка курса",
        "OPTIONS" => array(
            "Отображение элементов",
            array(
                "show_cost_in_op_courses",
                "Показывать блок стоимости в ОП курсах",
                "",
                array("checkbox")
            )
        )
    ),
    array(
        "DIV" => "events_settings",
        "TAB" => "Выездная платформа",
        "TITLE" => "Выездная платформа",
        "OPTIONS" => array(
            Loc::getMessage('EVENTS_ASK_QUESTION_TITLE'),
            array(
                "question_emails",
                "Адреса Email для отправки вопросов (через запятую)",
                "",
                array("text", 30)
            ),
        )
    ),
    array(
        "DIV" => "enrollments",
        "TAB" => Loc::getMessage('ENROLLS_TITLE'),
        "TITLE" => Loc::getMessage('ENROLLS_TITLE'),
        "OPTIONS" => array(
            Loc::getMessage('APPROVE_ENROLLS'),
            array(
                "enroll_life",
                Loc::getMessage('ENROLL_LIFE'),
                "48",
                array("text", 30)
            ),
            Loc::getMessage('EVENTS_STAFF_ABOUT_TITLE'),
            array(
                "approve_event_text",
                Loc::getMessage('APPROVE_EVENT_TEXT'),
                "",
                array("text", 50)
            ),
            array(
                "decline_event_text",
                Loc::getMessage('DECLINE_EVENT_TEXT'),
                "",
                array("text", 50)
            ),
            array(
                "delete_expire_event_text",
                Loc::getMessage('DELETE_EXPIRE_EVENT_TEXT'),
                "",
                array("text", 50)
            )
        )
    ),

    array(
        "DIV" => "reminds",
        "TAB" => Loc::getMessage('REMIND_TITLE'),
        "TITLE" => Loc::getMessage('REMIND_TITLE_TEXT'),
        "OPTIONS" => array(
            "Напоминания о ре-тестах",
            array(
                "how_long_to_retest_remind",
                Loc::getMessage('HOW_LONG_TO_RETEST_REMIND'),
                "10",
                array("text", 30)
            ),
            Loc::getMessage('REMIND_TITLE'),
            array(
                "how_long_to_remind",
                Loc::getMessage('HOW_LONG_TO_REMIND'),
                "2",
                array("text", 30)
            ),
            Loc::getMessage('TIME_START_SCRIPTS_TITLE'),
            array(
                "sender_today_n_days",
                Loc::getMessage('SENDER_TODAY_N_DAYS'),
                "09:00",
                array("text", 30)
            ),
            array(
                "sender_today_time",
                Loc::getMessage('SENDER_TODAY_TIME'),
                "09:00",
                array("text", 30)
            ),
            "Напоминание об освобожденном месте на курс",
            /*array(
                "emails_to_remain_free_places",
                "Список Email, на которые высылать уведомления (через запятую)",
                "",
                array("text", 60)
            ),*/
            array(
                "emails_to_remain_free_places_op",
                "Список адресов Email отдела продаж, на которые высылать уведомления (через запятую)",
                "",
                array("text", 60)
            ),
            array(
                "emails_to_remain_free_places_ppo",
                "Список адресов Email отдела послепродажного обслуживания, на которые высылать уведомления (через запятую)",
                "",
                array("text", 60)
            ),
            array(
                "emails_to_remain_free_places_marketing",
                "Список адресов Email отдела маркетинга, на которые высылать уведомления (через запятую)",
                "",
                array("text", 60)
            ),
            array(
                "hours_to_remain_free_places",
                "Через сколько часов напомнить об освобожденном месте на курс",
                "",
                array("selectbox", range(1,24))
            ),
            "SMS уведомления",
            array(
                "sms_links_enabled",
                "Рассылка включена",
                "",
                array("checkbox")
            ),
            array(
                "sms_courses_directions",
                "Направления курсов для рассылки",
                "",
                array("selectbox", [
                    'all' => 'Все направления',
                    'op' => 'Отдел продаж',
                    'ppo' => 'Послепродажное обслуживание',
                    'marketing' => 'Отдел маркетинга',
                ])
            ),
            array(
                "sms_courses_types",
                "Тип курсов для рассылки",
                "",
                array("selectbox", [
                    'all' => 'Все курсы',
                    'online' => 'Онлайн',
                    'offline' => 'Оффлайн',
                ])
            ),
            array(
                "sms_smsc_login",
                "Логин провайдера SMSC",
                "",
                array("text", 60)
            ),
            array(
                "sms_smsc_password",
                "Пароль провайдера SMSC",
                "",
                array("text", 60)
            ),
            array(
                "sms_sender_text",
                "Шаблон сообщения",
                "",
                array("text", 100)
            ),
        )
    ),
    array(
        "DIV" => "subscriptions",
        "TAB" => "Подписки",
        "TITLE" => "Подписки",
        "OPTIONS" => array(
            "Общие настройки",
            array(
                "enable_subscription_mode",
                "Включить механизм подписок",
                "",
                array("checkbox")
            ),
            "Сроки напоминаний",
            array(
                "how_long_to_remind_subscription",
                "За сколько дней напомнить о необходимости прохождения курса",
                "7",
                array("selectbox", range(1,31))
            ),
            array(
                "how_long_to_remind_add_teaching_plan",
                "За сколько дней напомнить о необходимости добавления плана обучения",
                "7",
                array("selectbox", range(1,24))
            ),
        )
    ),
    array(
        "DIV" => "zoom_integration",
        "TAB" => Loc::getMessage('ZOOM_INTEGRATION_TITLE'),
        "TITLE" => Loc::getMessage('ZOOM_INTEGRATION_TEXT'),
        "OPTIONS" => array(
            'Дилерский клуб "Geely Motors Russia"',
            array(
                "dealer_club_meeting_id",
                'ID встречи в ZOOM (есть в url адресе)',
                "",
                array("text", 60)
            ),
            array(
                "start_cron",
                'Включить автоматическую модерацию регистраций(обработка cron)',
                "",
                array("checkbox")
            ),
            array(
                'zoom_account_for_cron',
                "ZOOM аккаунт для мероприятия",
                "",
                array("selectbox", $accounts_array)
            ),
            array(
                'zoom_event_type',
                "Тип проводимого мероприятия",
                "",
                array("selectbox", $meeting_types)
            ),
            Loc::getMessage('ZOOM_TITLE'),
            array(
                "zoom_client_id",
                'Client ID',
                "",
                array("text", 60)
            ),
            array(
                "zoom_client_secret",
                'Client secret',
                "",
                array("text", 60)
            ),
            array(
                "zoom_redirect_url_for_auth",
                'Redirect URL for OAuth',
                "",
                array("text", 60)
            ),
            array(
                "zoom_access_token",
                Loc::getMessage('ZOOM_ACCESS_TOKEN'),
                "",
                array("text", 60)
            ),
            array(
                "zoom_refresh_token",
                Loc::getMessage('ZOOM_REFRESH_TOKEN'),
                "",
                array("text", 60)
            ),
        )
    ),
    array(
        "DIV" => "telegram_integration",
        "TAB" => Loc::getMessage('TELEGRAM_INTEGRATION_TITLE'),
        "TITLE" => Loc::getMessage('TELEGRAM_INTEGRATION_TEXT'),
        "OPTIONS" => array(
            Loc::getMessage('TELEGRAM_DATA_TITLE'),
            array(
                "telegram_session_path",
                Loc::getMessage('TELEGRAM_SESSION_PATH'),
                "/local/php_interface/lib/Telegram/sessions/common_session/session.madeline",
                array("text", 60)
            ),
            [
                "telegram_admin_login",
                Loc::getMessage('TELEGRAM_ADMIN_LOGIN'),
                "",
                ["text", 60]
            ],
            "Лимиты",
            array(
                "limit_invites",
                'Лимит приглашений в сутки (избежание бана)',
                "",
                array("text", 60)
            ),
            "Тестирование",
            array(
                "use_test_mode",
                'Включить тестовый режим (отключить для полноценной работы, при включенном режиме используется тестовая группа и роль)',
                "",
                array("checkbox")
            ),
            array(
                "role_for_test",
                'Тестовая роль с группой Telegram',
                "",
                array("selectbox", $roles_array)
            ),
            array(
                "channel_for_test",
                Loc::getMessage('CHANNEL_FOR_TEST'),
                "",
                array("text", 60)
            ),
            "Антиспам Бот",
            array(
                "telegram_antispam_bot_token",
                "Токен Антиспам бота",
                "",
                array("text", 60)
            ),
            array(
                "telegram_antispam_bot_handler_path",
                "Путь к обработчику от корня",
                "",
                array("text", 60)
            ),
            /*
            [
                "telegram_antispam_stop_words",
                "Стоп слова (Через запятую)",
                "",
                ["text", 60]
            ],*/
        )
    ),
);
if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {

        foreach ($aTab["OPTIONS"] as $arOption) {

            if (!is_array($arOption)) {

                continue;
            }

            if ($arOption["note"]) {

                continue;
            }

            if ($request["apply"]) {

                $optionValue = $request->getPost($arOption[0]);

                if ($arOption[0] == "switch_on") {

                    if ($optionValue == "") {

                        $optionValue = "N";
                    }
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            } elseif ($request["default"]) {

                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
}
$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin(); ?>

<form action="<?php echo($APPLICATION->GetCurPage()); ?>?mid=<?php echo($module_id); ?>&lang=<?php echo(LANG); ?>" method="post">

    <?php foreach ($aTabs as $aTab) {

        if ($aTab["OPTIONS"]) {

            $tabControl->BeginNextTab();

            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }

    $tabControl->Buttons();
    ?>

    <input type="submit" name="apply" value="<?php echo(Loc::GetMessage("FALBAR_TOTOP_OPTIONS_INPUT_APPLY")); ?>"
           class="adm-btn-save"/>
    <input type="submit" name="default" value="<?php echo(Loc::GetMessage("FALBAR_TOTOP_OPTIONS_INPUT_DEFAULT")); ?>"/>
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            let sender_today_time = document.querySelector('input[name="sender_today_time"]');
            let sender_today_n_days = document.querySelector('input[name="sender_today_n_days"]');
            sender_today_time.setAttribute('type', 'time');
            sender_today_n_days.setAttribute('type', 'time');
        });
    </script>
    <style>
        .adm-workarea input[type="time"]{
            font-size: 13px;
            height: 25px;
            padding: 0 5px;
            margin: 0;
            background: #fff;
            border: 1px solid;
            border-color: #87919c #959ea9 #9ea7b1 #959ea9;
            border-radius: 4px;
            color: #000;
            box-shadow: 0 1px 0 0 rgb(255 255 255 / 30 %), inset 0 2px 2px -1px rgb(180 188 191 / 70 %);
            display: inline-block;
            outline: none;
            vertical-align: middle;
            -webkit-font-smoothing: antialiased;
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
        }
    </style>
    <?php echo(bitrix_sessid_post()); ?>

</form>
<?php $tabControl->End(); ?>
