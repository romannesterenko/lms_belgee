<?php

use Bitrix\Main\Localization\Loc;
use Helpers\UserHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
$errors = '';
if($_REQUEST['create']=='Y') {
    $request_phone = $_REQUEST['PERSONAL_MOBILE'];
    $phone = str_replace(['+', '(', ')', '-', ' '], '', $_REQUEST['PERSONAL_MOBILE']);
    $_REQUEST['PERSONAL_MOBILE'] = $phone;
    if(empty($phone)){
        $errors = 'Номер телефона обязателен к заполнению';
    } else {
        if (\Helpers\UserHelper::getIdByPhone($phone) > 0) {
            $errors = 'Номер телефона ' . $request_phone . ' уже есть в базе';
        } else {
            $id = UserHelper::createUser($_REQUEST, $_FILES);
            if (is_int($id) && $id > 0) {
                /*$s = EmailNotifications::sendToEmployee($id);
                if( $s==false ) {

                } else {
                    UserHelper::setUserValue('UF_INVITE_MAILING', 21, $id);

                }*/
                LocalRedirect('/cabinet/dealer/employees/' . $id . '/');
            } else {
                $errors = $id;
            }
        }
    }
}

$dealer = \Helpers\DealerHelper::getByUser();
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?= Loc::getMessage('CREATE_EMPLOYEE') ?></h2>
            <?php if(!empty($errors)){?>
                <div style="color: #dc4343" class="pb-10"><?=$errors?></div>
            <?php }?>
            <form action="" method="post" enctype="multipart/form-data"  class="content-block">
                <input type="hidden" name="create" value="Y">
                <input type="hidden" name="UF_DEALER" value="<?=$dealer['ID']?>">
                <div class="profile-edit">

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PHOTO') ?></div>
                        <div class="profile-edit-item__content">

                            <div class="input-avatar">
                                <div class="imageWrapper">
                                    <img class="image" src="<?=SITE_TEMPLATE_PATH?>/images/No-photo-m.png">
                                </div>
                                <button class="file-upload">
                                    <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/download.svg" alt=""></span>
                                    <input type="file" name="PERSONAL_PHOTO" class="file-input"><?= Loc::getMessage('UPLOAD') ?></button>
                                <p><?= Loc::getMessage('PHOTO_SIZE') ?></p>

                            </div>


                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('LAST_NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="LAST_NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('LAST_NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>
                    <?php //отчество?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PARENT_NAME_RU') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="SECOND_NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PARENT_NAME_EN') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>


                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('STATUS') ?></div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" name="ACTIVE">
                                    <option value="Y" selected><?= Loc::getMessage('ACTIVE') ?></option>
                                    <option value="N"><?= Loc::getMessage('NO_ACTIVE') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="profile-edit-item with_explain">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DATE') ?><div class="alert_popup">
                                <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                <span><?= Loc::getMessage('DATE_LABEL') ?></span>
                            </div></div>
                        <div class="profile-edit-item__content">
                            <div class="row">
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_day">
                                            <option value="0">-</option>
                                            <?php for($i=1; $i<=31; $i++){?>
                                                <option value="<?=$i?>"><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_month">
                                            <option value="0">-</option>
                                            <?php for($i=1; $i<=12; $i++){?>
                                                <option value="<?=$i?>"><?=$i?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="select select--custom">
                                        <select class="select2" name="birthday_year">
                                            <option value="0">-</option>
                                            <?php for($i=((int)date('Y')-85); $i<=((int)date('Y')-18); $i++){?>
                                                <option value="<?=$i?>"><?=$i?></option>
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
                                                                        <span><?= Loc::getMessage('MANY_ROLES') ?></span>
                                                                    </div>
                        </div>
                        <div class="profile-edit-item__content">
                            <?php foreach (\Teaching\Roles::getRolesListByAdminDc() as $id => $role){?>
                                <div class="checkbox-item" style="margin-bottom: 10px">
                                    <input type="checkbox" id="role_<?=$id?>" name="UF_ROLE[]" value="<?=$id?>">
                                    <label for="role_<?=$id?>"><?=$role?></label>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DOL') ?></div>
                        <div class="profile-edit-item__content">
                            <textarea name="PERSONAL_PROFESSION"></textarea>
                        </div>
                    </div>
                    <?php /*<div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('FILIAL') ?></div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" disabled>
                                    <option><?=$dealer['PROPERTY_COMM_NAME_VALUE']?></option>
                                </select>
                            </div>
                        </div>
                    </div>*/?>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">E-mail:</div>
                        <div class="profile-edit-item__content">
                            <input type="email" name="EMAIL" value="<?=$_REQUEST['EMAIL']?>" required>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('PHONE') ?></div>
                        <div class="profile-edit-item__content">
                            <input type="text" class="phone_mask" name="PERSONAL_MOBILE" value="<?=$_REQUEST['PERSONAL_MOBILE']?>" placeholder="+7(___)___-____" required>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Zoom:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="UF_ZOOM_LOGIN" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Telegram:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="UF_TELEGRAM" value="">
                        </div>
                    </div>

                    <?php $employee['UF_WORK_START_DATE']=$employee['UF_WORK_START_DATE']??new \Bitrix\Main\Type\DateTime();?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label"><?= Loc::getMessage('START_DATE') ?></div>
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
                        <div class="profile-edit-item__label"><?= Loc::getMessage('DEALER_NAME') ?></div>
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
                        <div class="profile-edit-item__label"><?= Loc::getMessage('ADRESS') ?></div>
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