<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Сотрудники");
$id = \Helpers\UrlParamsHelper::getParam('id');
$employee = \Helpers\UserHelper::getByID($id);

?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2">Сотрудники</h2>

            <div class="content-block">
                <div class="content-nav content-nav--top">
                    <span class="active">
                        <a href="#">Профиль сотрудника</a>
                    </span>
                    <span><a href="/cabinet/dealer/employees/passing/<?=$id?>/">Проходит курсы</a></span>
                    <span><a href="/cabinet/dealer/employees/passed/<?=$id?>/">Пройденные курсы</a></span>
                    <span><a href="/cabinet/dealer/employees/setted_courses/<?=$id?>/">Назначенные курсы</a></span>
                </div>
                <div class="profile-main">
                    <div class="profile-main__avatar"><a href=""><img src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt=""></a></div>
                    <div class="profile-main__content">
                        <?$name = $employee['LAST_NAME'].' '.$employee['NAME'].' '.$employee['SECOND_NAME'];?>
                        <span class="profile-main__name"><?=$name?></span>
                        <span class="profile-main__name-en"><?=\Helpers\StringHelpers::translit($name)?></span>
                        <span class="profile-main__id">ID: <?=$employee['ID']?></span>
                        <span class="profile-main__status">
                          <span class="status status--passed">
                              <span class="icon">
                                  <img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt="">
                              </span> Профиль активен!
                          </span>
                          <?/*<a href="" class="btn">Назначить курс</a>*/?>
                        </span>
                    </div>
                </div>

                <div class="profile-information">

                    <div class="profile-information__section">
                        <?if(!empty($employee['PERSONAL_BIRTHDAY'])){?>
                            <div class="profile-information__item">
                                <strong>Дата рождения</strong>
                                <span><?=$employee['PERSONAL_BIRTHDAY']?></span>
                            </div>
                        <?}?>

                        <div class="profile-information__item">
                            <strong>Должность</strong>
                            <span><?=$employee['PERSONAL_PROFESSION']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>E-mail</strong>
                            <span><a href="mailto:<?=$employee['EMAIL']?>"><?=$employee['EMAIL']?></a></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Телефон</strong>
                            <span><a href="tel:"><?=$employee['PERSONAL_MOBILE']?></a></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Zoom</strong>
                            <span><?=$employee['UF_ZOOM_LOGIN']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Telegram</strong>
                            <span><?=$employee['UF_TELEGRAM']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Дата начала работы</strong>
                            <span><?=$employee['UF_WORK_START_DATE']?></span>
                        </div>

                        <!--<div class="profile-information__item">
                            <strong>Уровень сертификации</strong>
                            <span>Специалист высокого уровня продаж</span>
                        </div>-->
                    </div>

                    <?$dealer = \Helpers\DealerHelper::getByUser($employee['ID']);?>
                    <div class="profile-information__section">
                        <h3 class="h3 lowercase">Место работы</h3>
                        <div class="profile-information__item">
                            <strong>Код дилера</strong>
                            <span><?=$dealer['CODE']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Наименование </strong>
                            <span><?=$dealer['PROPERTY_COMM_NAME_VALUE']?> <br><?=$dealer['PROPERTY_ENG_NAME_VALUE']?></span>
                        </div>
                        <div class="profile-information__item">
                            <strong>Роль </strong>
                            <span><?=implode(', ', \Teaching\Roles::getRolesList(['ID' => $employee['UF_ROLE']]))?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Город</strong>
                            <span><?=$dealer['PROPERTY_CITY_VALUE']?> <br><?=\Helpers\StringHelpers::translit($dealer['PROPERTY_CITY_VALUE'])?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Адрес</strong>
                            <span><?=$dealer['PROPERTY_ORG_ADDRESS_VALUE']?></span>
                        </div>


                    </div>
                </div>
                <div class="btn-center margin">
                    <a href="/cabinet/dealer/employees/edit/<?=$employee['ID']?>/" class="btn ">Редактировать</a>
                    <a href="/cabinet/dealer/employees/delete/<?=$employee['ID']?>/" class="btn ">Удалить</a>
                </div>
            </div>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>