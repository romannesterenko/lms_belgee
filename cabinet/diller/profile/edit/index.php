<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Редактирование профиля");

if($_REQUEST['user_id']>0)
    \Helpers\UserHelper::updateUserFields($_REQUEST, $_FILES);

$employee = \Helpers\UserHelper::getByID();
$dealer = \Helpers\DealerHelper::getByUser($employee['ID']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2">общие данные Администратора LMS ДЦ</h2>

            <form action="" method="post" enctype="multipart/form-data" class="content-block">
                <input type="hidden" name="user_id" value="<?=$employee['ID']?>">

                <div class="profile-edit profile-edit--top-border">

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


                    <?$name = $employee['LAST_NAME'].' '.$employee['NAME'].' '.$employee['SECOND_NAME'];?>
                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">ФИО (русский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" name="NAME" value="<?=$name?>">
                        </div>
                    </div>

                    <div class="profile-edit-item">
                        <div class="profile-edit-item__label">ФИО (английский):</div>
                        <div class="profile-edit-item__content">
                            <input type="text" value="<?=\Helpers\StringHelpers::translit($name)?>">
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