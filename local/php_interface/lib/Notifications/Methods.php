<?php

namespace Notifications;
class Methods
{
    private array $methods_send_notifications;
    private $user_method_send_notifications;

    public function __construct()
    {
        $this->getAllNotificationsMethods();
    }

    public function getAllNotificationsMethods()
    {
        $rsRes = \CUserFieldEnum::GetList(array(), array(
            "USER_FIELD_NAME" => 'UF_SEND_NOTIFIC_METHOD',
        ));
        while ($arGender = $rsRes->GetNext()) {
            $this->methods_send_notifications[$arGender['ID']] = $arGender;
        }
        return $this;
    }

    public function getNotificationsMethodForUser($user_id = 0)
    {
        $this->user_method_send_notifications = \Helpers\UserHelper::getUserValue(\Helpers\UserHelper::prepareUserId($user_id), 'UF_SEND_NOTIFIC_METHOD');
        if (empty($this->user_method_send_notifications))
            $this->user_method_send_notifications = $this->getAllNotificationsMethods()->getDefault();
        return $this;
    }

    public function getId()
    {
        return $this->methods_send_notifications[$this->user_method_send_notifications]['ID'];
    }

    public function getValue()
    {
        return $this->methods_send_notifications[$this->user_method_send_notifications]['VALUE'];
    }

    public function getArray()
    {
        return $this->methods_send_notifications;
    }

    public function getDefault()
    {
        foreach ($this->methods_send_notifications as $method) {
            if ($method['DEF'] == 'Y') {
                return $method['ID'];
                break;
            }
        }
    }
}