<?php

namespace Settings;
class ShowMaterials
{
    private $values;
    private $user_id;

    public function __construct()
    {
        //$this->notify_methods = new Methods();
    }

    public function getAllValues()
    {
        $rsRes = \CUserFieldEnum::GetList(array(), array(
            "USER_FIELD_NAME" => 'UF_SHOW_MATERIALS',
        ));
        while ($arGender = $rsRes->GetNext()) {
            $this->values[] = $arGender;
        }
        return $this;
    }

    public function updateShowMaterialsSettingsByUser($data)
    {
        if (\Helpers\UserHelper::setUserValue('UF_SHOW_MATERIALS', $data['values'], $data['user']))
            return true;
        return false;
    }

    public function getArray()
    {
        return $this->values;
    }

    public function getExistSetting($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return \Helpers\UserHelper::getShowMaterialsValue($user_id);
    }

    private function getDefault()
    {
        foreach ($this->getAllValues()->getArray() as $setting) {
            //dd($setting);
        }
    }
}