<?php

namespace Settings;

use \Notifications\Methods;

class Notifications
{
    private array $methods_send_notifications;
    private int $user_method_send_notifications;
    private string $notificationsHlDataClass;
    private $methods;
    private $user_id;
    private $notify_methods;

    public function __construct()
    {
        $this->notificationsHlDataClass = \Helpers\HLBlockHelper::initialize('notifications');
        $this->notify_methods = new Methods();
    }

    public function getMethodsForUser($user_id = 0)
    {
        $this->setUserId($user_id);
        $this->methods = $this->notify_methods->getAllNotificationsMethods()->getArray();
        return $this;
    }

    public function getSettedMethodsForUser($user_id = 0)
    {
        return \Helpers\UserHelper::getUserValue($user_id, 'UF_SEND_NOTIFIC_METHOD');
    }

    public function getArray()
    {
        return $this->methods;
    }

    public function getWithValues()
    {
        $current_method = $this->notify_methods->getNotificationsMethodForUser($this->user_id)->getId();
        foreach ($this->methods as &$method) {
            $method['CHECKED'] = $method['ID'] == $current_method ? 'checked' : '';
            switch ($method['VALUE']) {
                case 'email':
                    $method['DATA'] = \Helpers\UserHelper::getUserValue($this->user_id, 'EMAIL');
                    $method['ICON_URL'] = SITE_TEMPLATE_PATH . '/images/notice-mail.svg';
                    break;
                case 'phone':
                    $method['DATA'] = \Helpers\UserHelper::getUserValue($this->user_id, 'PERSONAL_MOBILE');
                    $method['ICON_URL'] = SITE_TEMPLATE_PATH . '/images/notice-message.svg';
                    break;
                case 'telegram':
                    $method['DATA'] = \Helpers\UserHelper::getUserValue($this->user_id, 'UF_TELEGRAM') ?? 'Telegram';
                    $method['ICON_URL'] = SITE_TEMPLATE_PATH . '/images/notice-telegram.svg';
                    break;
            }
        }
        return $this->methods;
    }

    public function isSendMeNotifications($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return \Helpers\UserHelper::getUserValue($user_id, 'UF_SEND_ME_NOTIFICATIONS') ? true : false;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = \Helpers\UserHelper::prepareUserId($user_id);
    }

    public function changeSendNotifications(array $response)
    {
        \Helpers\UserHelper::setUserValue('UF_SEND_ME_NOTIFICATIONS', $response['send'], $response['user']);
    }

    public function sendNotifications($id, string $text)
    {

    }
}