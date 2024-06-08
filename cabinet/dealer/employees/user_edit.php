<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
$id = \Helpers\UrlParamsHelper::getParam('id');
$errors = '';
if($_REQUEST['user_id']>0) {
    $request_phone = $_REQUEST['PERSONAL_MOBILE'];
    $new_phone = str_replace(['+', '(', ')', '-', ' '], '', $_REQUEST['PERSONAL_MOBILE']);
    $_REQUEST['PERSONAL_MOBILE'] = $new_phone;
    $old_phone = \Helpers\UserHelper::getPhoneByID($_REQUEST['user_id']);
    if($new_phone!=$old_phone){
        if(\Helpers\UserHelper::getIdByPhone($new_phone)>0){
            $errors = 'Номер телефона '.$request_phone.' уже есть в базе';
        } else {
            $result = \Helpers\UserHelper::updateUserFields($_REQUEST, $_FILES);
            if($result===true)
                LocalRedirect('/cabinet/dealer/employees/'.$_REQUEST['user_id'].'/');
            else
                $errors = $result;
        }
    } else {
        $result = \Helpers\UserHelper::updateUserFields($_REQUEST, $_FILES);
        if($result===true)
            LocalRedirect('/cabinet/dealer/employees/'.$_REQUEST['user_id'].'/');
        else
            $errors = $result;
    }

}

$employee = \Helpers\UserHelper::getByID($id);
$dealer = \Helpers\DealerHelper::getByUser($employee['ID']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?= Loc::getMessage('H2_TITLE') ?></h2>
            <?php if(!empty($errors)){?>
                <div style="color: #dc4343; margin: 20px 0px;"><?=$errors?></div>
            <?php }?>
            <form action="" method="post" enctype="multipart/form-data"  class="content-block">
                <input type="hidden" name="user_id" value="<?=$employee['ID']?>">
                <div class="profile-edit">

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PHOTO') ?></div>
                        <div class="profile-edit-item__content">

                            <div class="input-avatar">
                                <div class="imageWrapper">
                                    <img class="image" src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>">
                                </div>
                                <button class="file-upload">
                                    <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/download.svg" alt=""></span>
                                    <input type="file" name="PERSONAL_PHOTO" class="file-input"><?= Loc::getMessage('UPLOAD') ?></button>
                                <p><?= Loc::getMessage('UPLOAD_INFO') ?></p>

                            </div>


                        </div>
                    </div>

                    <?php //$name = $employee['LAST_NAME'].' '.$employee['NAME'].' '.$employee['SECOND_NAME'];?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="NAME" value="<?=$employee['NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($employee['NAME'])?>">
                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('LAST_NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="LAST_NAME" value="<?=$employee['LAST_NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('LAST_NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($employee['LAST_NAME'])?>">
                        </div>
                    </div>
                    <?php //отчество?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PARENT_NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="SECOND_NAME" value="<?=$employee['SECOND_NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PARENT_NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($employee['SECOND_NAME'])?>">
                        </div>
                    </div>


                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">ID:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$employee['ID']?>" disabled>
                        </div>
                    </div>


                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PROFILE_STATUS') ?></div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" name="ACTIVE">
                                    <option value="Y"<?=$employee['ACTIVE']=='Y'?' selected':''?>><?= Loc::getMessage('ACTIVE') ?></option>
                                    <option value="N"<?=$employee['ACTIVE']=='Y'?'':' selected'?>><?= Loc::getMessage('NO_ACTIVE') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php
                        if($employee['PERSONAL_BIRTHDAY']){
                            $day = $employee['PERSONAL_BIRTHDAY']->format('d');
                            $month = $employee['PERSONAL_BIRTHDAY']->format('m');
                            $year = $employee['PERSONAL_BIRTHDAY']->format('Y');
                        }
                    ?>
                    <div class="profile-edit-item with_explain">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('BIRTHDAY_DATE') ?><div class="alert_popup">
                                                                                <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                                                                <span><?= Loc::getMessage('BIRTHDAY_INFO') ?></span>
                                                                            </div>
                        </div>
                        <div class="profile-edit-item__content">
                            <div class="row">
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_day">
                                            <option value="0">-</option>
                                            <?php for($i=1; $i<=31; $i++){?>
                                                <option value="<?=$i?>"<?=$day==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_month">
                                            <option value="0">-</option>
                                            <?php for($i=1; $i<=12; $i++){?>
                                                <option value="<?=$i?>"<?=$month==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_year">
                                            <option value="0">-</option>
                                            <?php for($i=((int)date('Y')-85); $i<=((int)date('Y')-18); $i++){?>
                                                <option value="<?=$i?>"<?=$year==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-edit-item with_explain">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('ROLES') ?><div class="alert_popup">
                                                                        <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                                                        <span><?= Loc::getMessage('ROLES_INFO') ?></span>
                                                                    </div>
                        </div>
                        <div class="profile-edit-item__content">
                            <?php foreach (\Teaching\Roles::getRolesListByAdminDc() as $id => $role){?>
                                <div class="checkbox-item" style="margin-bottom: 10px">
                                    <input type="checkbox" id="role_<?=$id?>" name="UF_ROLE[]" value="<?=$id?>"<?=check_full_array($employee['UF_ROLE'])&&in_array($id, $employee['UF_ROLE'])?' checked':''?>>
                                    <label for="role_<?=$id?>"><?=$role?></label>
                                </div>
                            <?php }?>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PROFESSION') ?></div>
                        <div class="profile-edit-item__content">
                            <textarea name="PERSONAL_PROFESSION"><?=$employee['PERSONAL_PROFESSION']?></textarea>
                        </div>
                    </div>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('FILIAL') ?></div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" disabled>
                                    <option><?=$dealer['PROPERTY_COMM_NAME_VALUE']?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">E-mail:</div>
                        <div class="profile-edit-item__content">
                            <input type="email" name="EMAIL" value="<?=$employee['EMAIL']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PHONE') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="PERSONAL_MOBILE" class="phone_mask" value="<?=$employee['PERSONAL_MOBILE']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Zoom:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="UF_ZOOM_LOGIN" value="<?=$employee['UF_ZOOM_LOGIN']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Telegram:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="UF_TELEGRAM" value="<?=$employee['UF_TELEGRAM']?>">
                        </div>
                    </div>

                    <?php $employee['UF_WORK_START_DATE']=$employee['UF_WORK_START_DATE']??new \Bitrix\Main\Type\DateTime();?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('START_WORK_DATE') ?></div>
                        <div class="profile-edit-item__content">
                            <div class="row">
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="work_day">
                                            <?php for($i=1; $i<=31; $i++){?>
                                                <option value="<?=$i?>"<?=$employee['UF_WORK_START_DATE']->format('d')==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="work_month">
                                            <?php for($i=1; $i<=12; $i++){?>
                                                <option value="<?=$i?>"<?=$employee['UF_WORK_START_DATE']->format('m')==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="work_year">
                                            <?php for($i=2000; $i<=(int)date('Y'); $i++){?>
                                                <option value="<?=$i?>"<?=$employee['UF_WORK_START_DATE']->format('Y')==$i?' selected':''?>><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--<div class="profile-edit-item">
                        <div class="profile-edit-item__label">Уровень сертификации:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="Специалист высокого уровня продаж">
                        </div>
                    </div>-->


                </div>

                <div class="profile-edit">
                    <h3 class="h3 lowercase"><?= Loc::getMessage('WORK_PLACE') ?></h3>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DEALER_CODE') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['CODE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DEALER_NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_COMM_NAME_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DEALER_NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_ENG_NAME_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('CITY_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_CITY_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('CITY_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($dealer['PROPERTY_CITY_VALUE'])?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('ADDRESS') ?></div>
                        <div class="profile-edit-item__content">
                            <textarea disabled><?=$dealer['PROPERTY_ORG_ADDRESS_VALUE']?></textarea>
                        </div>
                    </div>
                </div>
                <div class="btn-center margin">
                    <button class="btn " type="submit"><?= Loc::getMessage('SAVE') ?></button>
                </div>

            </form>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>