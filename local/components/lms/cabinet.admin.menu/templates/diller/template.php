<?php use Models\Employee;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION, $USER;?>
<h3 class="h3"><?=GetMessage('ADMIN_DILLER_MENU_TITLE')?></h3>

<div class="side-menu aside-block">
    <ul>

        <li<?=$APPLICATION->GetCurPage()=='/cabinet/dealer/'?" class='active'":""?>>
            <a href="/cabinet/dealer/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#work-table"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_DILLER_MENU_WORKSPACE')?>
            </a>
        </li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/dealer/balance/'?" class='active'":""?>>
            <a href="/cabinet/dealer/balance/">
                <span class="icon">
                  <svg width="17px" height="17px">
                    <use xlink:href="#work-table"></use>
                  </svg>
                </span>
                Баланс
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/dealer/employees/')&&!CSite::InDir('/cabinet/dealer/employees/enrolled/')?" class='active'":""?>>
            <a href="/cabinet/dealer/employees/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#user"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_DILLER_MENU_EMPLOYEES')?>
            </a>
            <span class="side-menu-number">
                <?=count(Employee::getActiveEmployeesIdsByAdmin())?>
            </span>
        </li>
        <li<?=CSite::InDir('/cabinet/dealer/employees/enrolled/')?" class='active'":""?>>
            <a href="/cabinet/dealer/employees/enrolled/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Записанные сотрудники
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/dealer/report/')?" class='active'":""?>>
            <a href="/cabinet/dealer/report/index.php">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Отчет о посещаемости
            </a>
        </li>
        <li<?=CSite::InDir('/cabinet/dealer/report_schedule/')?" class='active'":""?>>
            <a href="/cabinet/dealer/report_schedule/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Расписания
            </a>
        </li>
    </ul>
</div>


