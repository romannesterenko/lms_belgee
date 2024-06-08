<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
<div class="course-information">
    <?php foreach ($arResult['rows'] as $row){
        //$string = html_entity_decode($row['UF_TEXT']);
        $string = strip_tags(html_entity_decode($row['UF_TEXT']));
        //$string = strip_tags($string);
        $string = str_ireplace("&amp;quot;", "\"", $string);
        $pattern = "/(<br>.+?)/i";
        $replacement = "";
        $string = preg_replace($pattern, $replacement, $string);
        ?>
        <div class="course-info-item">
              <span class="icon">
                <img src="<?=SITE_TEMPLATE_PATH?>/images/education-icon.svg" alt="">
              </span>
            <p>
                <a href="<?=$row['UF_LINK']??'#'?>"><?=$string?></a>
            </p>
            <a class="course-info-item__delete icon-delete delete_notification" data-id="<?=$row['ID']?>"></a>
        </div>
    <?php }?>
</div>