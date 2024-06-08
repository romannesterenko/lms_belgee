<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сотрудники");
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2">Точки продаж и сервисного обслуживания</h2>
            <div class="content-block  content-block--margin">

                <div class="location-item">
              <span class="location-item__logotype">
                <img src="<?=SITE_TEMPLATE_PATH?>/images/logo.svg" alt="">
                <span>K-Motors</span>
              </span>
                    <div class="location-item__address">г. Петрозаводск, Лесной проспект, д.57, стр.1</div>
                    <div class="location-item__contacts">
                <span class="location-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/phone-icon.svg" alt=""></span>
                  <a href="tel:">+7 (8142) 59 33 77</a>
                </span>
                        <span class="location-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/site-icon.svg" alt=""></span>
                  <a href="mailto:km-geely.ru">km-geely.ru</a>
                </span>
                    </div>
                </div>

                <div class="address-item">
              <span class="address-item__icon">
                <svg width="18px" height="18px">
                  <use xlink:href="#location"></use>
                </svg>
              </span>
                    <div class="address-item__title">К-Моторс</div>
                    <div class="address-item__address">г. Петрозаводск, Лесной проспект, д.57, стр.1</div>
                    <div class="address-item__contacts">
                <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/phone-icon.svg" alt=""></span>
                  <a href="tel:">+7 (8142) 59 33 77</a>
                </span>
                        <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/site-icon.svg" alt=""></span>
                  <a href="mailto:km-geely.ru">km-geely.ru</a>
                </span>
                    </div>

                    <div class="address-item__info">
                        <span class="address-item__info-item">Сотрудников: <span>59</span></span>
                        <span class="address-item__info-item">Для обучения:<span>32</span></span>
                    </div>
                </div>

                <div class="address-item">
              <span class="address-item__icon">
                <svg width="18px" height="18px">
                  <use xlink:href="#location"></use>
                </svg>
              </span>
                    <div class="address-item__title">К-Моторс</div>
                    <div class="address-item__address">г. Петрозаводск, Лесной проспект, д.57, стр.1</div>
                    <div class="address-item__contacts">
                <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/phone-icon.svg" alt=""></span>
                  <a href="tel:">+7 (8142) 59 33 77</a>
                </span>
                        <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/site-icon.svg" alt=""></span>
                  <a href="mailto:km-geely.ru">km-geely.ru</a>
                </span>
                    </div>

                    <div class="address-item__info">
                        <span class="address-item__info-item">Сотрудников: <span>59</span></span>
                        <span class="address-item__info-item">Для обучения:<span>32</span></span>
                    </div>
                </div>

                <div class="address-item">
              <span class="address-item__icon">
                <svg width="18px" height="18px">
                  <use xlink:href="#location"></use>
                </svg>
              </span>
                    <div class="address-item__title">К-Моторс</div>
                    <div class="address-item__address">г. Петрозаводск, Лесной проспект, д.57, стр.1</div>
                    <div class="address-item__contacts">
                <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/phone-icon.svg" alt=""></span>
                  <a href="tel:">+7 (8142) 59 33 77</a>
                </span>
                        <span class="address-item__contact-item">
                  <span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/site-icon.svg" alt=""></span>
                  <a href="mailto:km-geely.ru">km-geely.ru</a>
                </span>
                    </div>

                    <div class="address-item__info">
                        <span class="address-item__info-item">Сотрудников: <span>59</span></span>
                        <span class="address-item__info-item">Для обучения:<span>32</span></span>
                    </div>
                </div>

            </div>

            <div class="pagination">
                <div class="pagination__nav">
                    <a href="" class="prev"><span class="icon icon-arrow-link"></span>предыдущая</a>
                    <a href="" class="next">следующая<span class="icon icon-arrow-link"></span></a>
                </div>
                <div class="pagination__pages">
                    <span><a href="" class="active">1</a></span>
                    <span><a href="">2</a></span>
                    <span><a href="">3</a></span>
                    <span><a href="">4</a></span>
                    <span><a href="">5</a></span>
                    <span><a href="">6</a></span>
                    <span><a href="">7</a></span>
                    <span><a href="">8</a></span>
                </div>
            </div>

        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>