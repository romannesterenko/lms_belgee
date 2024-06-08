<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("общие данные Администратора LMS ДЦ");
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

            <div class="content-block">
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
                        </span>
                    </div>
                </div>

                <div class="profile-information">

                    <div class="profile-information__section">


                        <div class="profile-information__item">
                            <strong>E-mail</strong>
                            <span><a href="mailto:<?=$employee['EMAIL']?>"><?=$employee['EMAIL']?></a></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Телефон</strong>
                            <span><a href="tel:<?=$employee['PERSONAL_MOBILE']?>"><?=$employee['PERSONAL_MOBILE']?></a></span>
                        </div>



                        <div class="profile-information__item">
                            <strong>Код дилера</strong>
                            <span><?=$dealer['CODE']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>Наименование </strong>
                            <span><?=$dealer['PROPERTY_COMM_NAME_VALUE']?> <br><?=$dealer['PROPERTY_ENG_NAME_VALUE']?></span>
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
                    <a href="/cabinet/diller/profile/edit/" class="btn ">Редактировать</a>
                </div>
            </div>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>