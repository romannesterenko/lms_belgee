<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle("Создание сотрудника");
$errors = '';
if($_REQUEST['create']=='Y') {
    $id = \Helpers\UserHelper::createUser($_REQUEST, $_FILES);
    if($id>0)
        LocalRedirect('/cabinet/dealer/employees/'.$id.'/');
    else
        $errors =  $id;
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
            <h2 class="h2">Сотрудник - создание</h2>
            <?php if(!empty($errors)){?>
                <div style="color: #dc4343"><?=$errors?></div>
            <?php }?>
            <form action="" method="post" enctype="multipart/form-data"  class="content-block">
                <input type="hidden" name="create" value="Y">
                <input type="hidden" name="UF_DEALER" value="<?=$dealer['ID']?>">
                <div class="profile-edit">

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фотография:</div>
                        <div class="profile-edit-item__content">

                            <div class="input-avatar">
                                <div class="imageWrapper">
                                    <img class="image" src="<?=SITE_TEMPLATE_PATH?>/images/No-photo-m.png">
                                </div>
                                <button class="file-upload">
                                    <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/download.svg" alt=""></span>
                                    <input type="file" name="PERSONAL_PHOTO" class="file-input">Загрузить
                                </button>
                                <p>Фотография высокого качества, размер не более 15 мб</p>

                            </div>


                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Имя (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Имя (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фамилия (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="LAST_NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фамилия (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>
                    <?php //отчество?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Отчество (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="SECOND_NAME" value="">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Отчество (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="">
                        </div>
                    </div>


                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Статус профиля:</div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" name="ACTIVE">
                                    <option value="Y" selected>Активен</option>
                                    <option value="N">Не активен</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="profile-edit-item with_explain">
                        <div class="profile-edit-item__label">Дата рождения: <div class="alert_popup">
                                <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                <span>Дата рождения пользователя. Если не выбрано все три значения (день, месяц, год), дата не запишется</span>
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
                        <div class="profile-edit-item__label">Роли: <div class="alert_popup">
                                                                        <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                                                        <span>Можно выбрать несколько ролей</span>
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
                        <div class="profile-edit-item__label">Должность:</div>
                        <div class="profile-edit-item__content">
                            <textarea name="PERSONAL_PROFESSION"></textarea>
                        </div>
                    </div>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Филиал:</div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" disabled>
                                    <option><?=$dealer['PROPERTY_COMM_NAME_VALUE']?></option>
                                    <option>очень длинный длинный текст</option>
                                    <option>Option 3</option>
                                    <option>Option 4</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">E-mail:</div>
                        <div class="profile-edit-item__content">
                            <input type="email" name="EMAIL" value="" required>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Телефон:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="PERSONAL_MOBILE" value="" required>
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
                        <div class="profile-edit-item__label">Дата начала работы:</div>
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
                    <h3 class="h3 lowercase">Место работы</h3>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Код дилера:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['CODE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Наименование (русский): </div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_COMM_NAME_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Наименование (английский): </div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_ENG_NAME_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Город (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=$dealer['PROPERTY_CITY_VALUE']?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Город (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($dealer['PROPERTY_CITY_VALUE'])?>" disabled>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Адрес:</div>
                        <div class="profile-edit-item__content">
                            <textarea disabled><?=$dealer['PROPERTY_ORG_ADDRESS_VALUE']?></textarea>
                        </div>
                    </div>
                </div>
                <div class="btn-center margin">
                    <button class="btn " type="submit">Сохранить</button>
                </div>

            </form>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>