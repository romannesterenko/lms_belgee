<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Сотрудники");
$id = \Helpers\UrlParamsHelper::getParam('id');
$errors = '';
if($_REQUEST['user_id']>0) {
    $result = \Helpers\UserHelper::updateUserFields($_REQUEST, $_FILES);
    if($result===true)
        LocalRedirect('/cabinet/dealer/employees/'.$_REQUEST['user_id'].'/');
    else
        $errors = $result;
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
            <h2 class="h2">Сотрудник - редактирование</h2>
            <?php if(!empty($errors)){?>
                <div style="color: #dc4343"><?=$errors?></div>
            <?php }?>
            <form action="" method="post" enctype="multipart/form-data"  class="content-block">
                <input type="hidden" name="user_id" value="<?=$employee['ID']?>">
                <div class="profile-edit">

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фотография:</div>
                        <div class="profile-edit-item__content">

                            <div class="input-avatar">
                                <div class="imageWrapper">
                                    <img class="image" src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>">
                                </div>
                                <button class="file-upload">
                                    <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/download.svg" alt=""></span>
                                    <input type="file" name="PERSONAL_PHOTO" class="file-input">Загрузить
                                </button>
                                <p>Фотография высокого качества, размер не более 15 мб</p>

                            </div>


                        </div>
                    </div>

                    <?php //$name = $employee['LAST_NAME'].' '.$employee['NAME'].' '.$employee['SECOND_NAME'];?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Имя (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="NAME" value="<?=$employee['NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Имя (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($employee['NAME'])?>">
                        </div>
                    </div>
                    <?php //last_name?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фамилия (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="LAST_NAME" value="<?=$employee['LAST_NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Фамилия (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($employee['LAST_NAME'])?>">
                        </div>
                    </div>
                    <?php //отчество?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Отчество (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="SECOND_NAME" value="<?=$employee['SECOND_NAME']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Отчество (английский):</div>
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
                        <div class="profile-edit-item__label">Cтатус профиля:</div>
                        <div class="profile-edit-item__content">
                            <div class="select select--custom">
                                <select class="select2" name="ACTIVE">
                                    <option value="Y"<?=$employee['ACTIVE']=='Y'?' selected':''?>>Активен</option>
                                    <option value="N"<?=$employee['ACTIVE']=='Y'?'':' selected'?>>Не активен</option>
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
                        <div class="profile-edit-item__label">Дата рождения: <div class="alert_popup">
                                                                                <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                                                                <span>Дата рождения пользователя. Если не выбрано все три значения (день, месяц, год), дата не запишется</span>
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
                        <div class="profile-edit-item__label">Роли: <div class="alert_popup">
                                                                        <img src="<?=SITE_TEMPLATE_PATH?>/images/question.svg" alt="">
                                                                        <span>Можно выбрать несколько ролей</span>
                                                                    </div>
                        </div>
                        <div class="profile-edit-item__content">
                            <?php foreach (\Teaching\Roles::getRolesListByAdminDc() as $id => $role){?>
                                <div class="checkbox-item" style="margin-bottom: 10px">
                                    <input type="checkbox" id="role_<?=$id?>" name="UF_ROLE[]" value="<?=$id?>"<?=in_array($id, $employee['UF_ROLE'])?' checked':''?>>
                                    <label for="role_<?=$id?>"><?=$role?></label>
                                </div>
                            <?php }?>
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Должность:</div>
                        <div class="profile-edit-item__content">
                            <textarea name="PERSONAL_PROFESSION"><?=$employee['PERSONAL_PROFESSION']?></textarea>
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
                            <input type="email" name="EMAIL" value="<?=$employee['EMAIL']?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">Телефон:</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="PERSONAL_MOBILE" value="<?=$employee['PERSONAL_MOBILE']?>">
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