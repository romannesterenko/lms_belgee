<?php

use Helpers\PageHelper;
use Helpers\StringHelpers;
use Models\Course;
use Models\Dealer;
use Models\Direction;
use Models\Invoice;
use Models\Reserve;
use Models\User;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle('Баланс'); ?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <?php
            $balance = Dealer::getBalance(Dealer::getByEmployee());

            $statuses = Invoice::getStatusList();
            if(check_full_array($balance)) {
                foreach ($balance as $direction_code => $direction_info){?>
                    <h2 class="h2 text-left">Баланс по <?= Direction::getDirectionByCode($direction_code)['title']?></h2>
                    <div class="table-block">
                        <table class="table table-bordered table-striped table-responsive-stack">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Всего</th>
                                    <th>Резерв</th>
                                    <th>Свободно</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?=Helpers\StringHelpers::preparePrice((int)$direction_info["incoming"])?></td>
                                    <td><?=Helpers\StringHelpers::preparePrice((int)$direction_info["reserves"])?></td>
                                    <td><?=Helpers\StringHelpers::preparePrice((int)$direction_info["free"])?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php }
            }
            $invoices = Invoice::getByDealerId(Dealer::getByEmployee()) ?>
            <h2 class="h2 text-left">История пополнений</h2>
            <div class="table-block">
                <table class="table table-bordered table-striped table-responsive-stack">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>№ счета</th>
                            <th>Дата счета</th>
                            <th>Статус</th>
                            <th>Дата оплаты</th>
                            <th>Направление</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $key => $invoice){?>
                            <tr>
                                <td class="text-center"><?=($key+1)?></td>
                                <td><?=$invoice["UF_INVOICE_NUMBER"]?></td>
                                <td><?=$invoice["UF_INVOICE_DATE"]?></td>
                                <td><?=$statuses[$invoice["UF_STATUS"]]?></td>
                                <td><?=$invoice["UF_STATUS"]==49?"-":Helpers\DateHelper::getHumanDate((string)$invoice["UF_PAYMENT_DATE"], 'd.m.Y')?> </td>
                                <td><?= Direction::getDirectionByCode($invoice["UF_ID_TRENING"])['title']?></td>
                                <td><?= StringHelpers::preparePrice($invoice["UF_TOTAL"])?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
            <?php $debits = Reserve::debitsByDealerId(Dealer::getByEmployee()) ?>
            <h2 class="h2 text-left">История списаний</h2>
            <div class="table-block">
                <table class="table table-bordered table-striped table-responsive-stack">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Направление</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $course_ids = [];
                        $user_ids = [];
                        foreach ($debits as $key => $debit){
                            $course_ids[] = $debit['UF_COURSE_ID'];
                            $user_ids[] = $debit['UF_USER_ID'];
                        }
                        $courses = $users = [];
                        if(check_full_array($course_ids)){
                            $courses = Course::getList(['ID' => $course_ids], ['ID', 'NAME']);
                        }
                        if(check_full_array($user_ids)){
                            $users = User::get(['ID' => $user_ids], ['ID', 'NAME', 'LAST_NAME']);
                        }
                        foreach ($debits as $key => $debit){?>
                            <tr>
                                <td class="text-center"><?=($key+1)?></td>
                                <td><?= $debit["UF_CREATED_AT"] ?></td>
                                <td><?= Direction::getDirectionByCode($debit["UF_DIRECTION"])['title']?></td>
                                <td style="white-space: nowrap"><?=number_format($debit["UF_PRICE"], 0, '.', ' ')?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
            <?php unset($debits, $course_ids, $user_ids, $courses, $users);
            $debits = Reserve::reservesByDealerId(Dealer::getByEmployee()) ?>
            <h2 class="h2 text-left">История резервов</h2>
            <div class="table-block">
                <table class="table table-bordered table-striped table-responsive-stack">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Оплачен</th>
                            <th>Направление</th>
                            <th>Курс</th>
                            <th>Сотрудник</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $course_ids = [];
                        $user_ids = [];
                        foreach ($debits as $key => $debit){
                            $course_ids[] = $debit['UF_COURSE_ID'];
                            $user_ids[] = $debit['UF_USER_ID'];
                        }
                        $courses = $users = [];
                        if(check_full_array($course_ids)){
                            $courses = Course::getList(['ID' => $course_ids], ['ID', 'NAME']);
                        }
                        if(check_full_array($user_ids)){
                            $users = User::get(['ID' => $user_ids], ['ID', 'NAME', 'LAST_NAME']);
                        }
                        ?>
                        <?php foreach ($debits as $key => $debit){?>
                            <tr>
                                <td class="text-center"><?=($key+1)?></td>
                                <td><?= $debit["UF_CREATED_AT"] ?></td>
                                <td><?=$debit["UF_IS_COMPLETE"]?"Да":"Нет"?></td>
                                <td><?= Direction::getDirectionByCode($debit["UF_DIRECTION"])['title']?></td>
                                <td><?=$courses[$debit['UF_COURSE_ID']]['NAME']??"-"?></td>
                                <td><?=$debit["UF_ID_DEBIT"]==1?"-":$users[$debit['UF_USER_ID']]['NAME']." ".$users[$debit['UF_USER_ID']]['LAST_NAME']?></td>
                                <td style="white-space: nowrap"><?=number_format($debit["UF_PRICE"], 0, '.', ' ')?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>