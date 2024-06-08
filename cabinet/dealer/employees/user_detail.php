<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
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
            <h2 class="h2"><?= Loc::getMessage('EMPLOYESS') ?></h2>

            <div class="content-block">
                <div class="content-nav content-nav--top">
                    <span class="active">
                        <a href="#"><?= Loc::getMessage('PROFILE') ?></a>
                    </span>
                    <span><a href="/cabinet/dealer/employees/passing/<?=$id?>/"><?= Loc::getMessage('IN_COURSES') ?></a></span>
                    <span><a href="/cabinet/dealer/employees/passed/<?=$id?>/"><?= Loc::getMessage('COMPLETE_COURSES') ?></a></span>
                    <span><a href="/cabinet/dealer/employees/setted_courses/<?=$id?>/"><?= Loc::getMessage('SETTED_COURSES') ?></a></span>
                </div>
                <div class="profile-main">
                    <div class="profile-main__avatar"><a href=""><img src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt=""></a></div>
                    <div class="profile-main__content">
                        <?php $name = $employee['LAST_NAME'].' '.$employee['NAME'].' '.$employee['SECOND_NAME'];?>
                        <span class="profile-main__name"><?=$name?></span>
                        <span class="profile-main__name-en"><?=\Helpers\StringHelpers::translit($name)?></span>
                        <span class="profile-main__id">ID: <?=$employee['ID']?></span>
                        <span class="profile-main__status">
                          <span class="status status--passed">
                              <span class="icon">
                                  <img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt="">
                              </span><?= Loc::getMessage('PROFILE_IS_ACTIVE') ?></span>
                          <?php /*<a href="" class="btn">Назначить курс</a>*/?>
                        </span>
                    </div>
                </div>

                <div class="profile-information">

                    <div class="profile-information__section">
                        <?php if(!empty($employee['PERSONAL_BIRTHDAY'])){?>
                            <div class="profile-information__item">
                                <strong><?= Loc::getMessage('BIRTHDAY_DATE') ?></strong>
                                <span><?=$employee['PERSONAL_BIRTHDAY']?></span>
                            </div>
                        <?php }?>

                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('PROFESSION') ?></strong>
                            <span><?=$employee['PERSONAL_PROFESSION']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong>E-mail</strong>
                            <span><a href="mailto:<?=$employee['EMAIL']?>"><?=$employee['EMAIL']?></a></span>
                        </div>

                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('PHONE') ?></strong>
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
                            <strong><?= Loc::getMessage('WORK_START_DATE') ?></strong>
                            <span><?=$employee['UF_WORK_START_DATE']?></span>
                        </div>

                        <!--<div class="profile-information__item">
                            <strong>Уровень сертификации</strong>
                            <span>Специалист высокого уровня продаж</span>
                        </div>-->
                    </div>

                    <?php $dealer = \Helpers\DealerHelper::getByUser($employee['ID']);?>
                    <div class="profile-information__section">
                        <h3 class="h3 lowercase"><?= Loc::getMessage('WORK_PLACE') ?></h3>
                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('DEALER_CODE') ?></strong>
                            <span><?=$dealer['CODE']?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('NAME') ?></strong>
                            <span><?=$dealer['PROPERTY_COMM_NAME_VALUE']?> <br><?=$dealer['PROPERTY_ENG_NAME_VALUE']?></span>
                        </div>
                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('ROLE') ?></strong>
                            <span><?=implode(', ', \Teaching\Roles::getRolesList(['ID' => $employee['UF_ROLE']]))?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('CITY') ?></strong>
                            <span><?=$dealer['PROPERTY_CITY_VALUE']?> <br><?=\Helpers\StringHelpers::translit($dealer['PROPERTY_CITY_VALUE'])?></span>
                        </div>

                        <div class="profile-information__item">
                            <strong><?= Loc::getMessage('ADDRESS') ?></strong>
                            <span><?=$dealer['PROPERTY_ORG_ADDRESS_VALUE']?></span>
                        </div>
                    </div>
                </div>
                <div class="btn-center margin">
                    <a href="/cabinet/dealer/employees/edit/<?=$employee['ID']?>/" class="btn "><?= Loc::getMessage('EDIT') ?></a>
                    <a href="/cabinet/dealer/employees/delete/<?=$employee['ID']?>/" class="btn "><?= Loc::getMessage('DELETE') ?></a>
                </div>
            </div>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>