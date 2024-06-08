<?php


namespace Helpers;


class PageHelper
{

    public static function getPageForCabinet()
    {
        $page = \Helpers\UserHelper::isTeachingAdmin() ? 'teaching_admin' : 'user';
        if($page=='user'&&\Models\Employee::isTrainer())
            $page = 'trainer';
        return $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . '/pages/cabinet/' . $page . '.php';
    }

    public static function getSideBarForCabinet()
    {
        return $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . '/pages/cabinet/sidebars/user.php';
    }

    public static function set404($message="Страница не найдена")
    {
        \Bitrix\Iblock\Component\Tools::process404(
            $message, //Сообщение
            true, // Нужно ли определять 404-ю константу    true, // Устанавливать ли статус
            true, // Показывать ли 404-ю страницу
            false // Ссылка на отличную от стандартной 404-ю
        );

        die();
    }
}