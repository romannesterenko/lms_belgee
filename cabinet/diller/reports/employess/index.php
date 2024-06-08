<?php
const NEED_AUTH = true;
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
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <?php /*
            <div class="content-block content-block--margin">
                <h4 class="h4">Общая статистика </h4>
                <div class="stats">
                    <div class="stat-item">
                        О прохождении курсов
                        <span>62</span>
                    </div>
                    <div class="stat-item">
                        Об обучающихся
                        <span>97</span>
                    </div>
                    <div class="stat-item">
                        О записях
                        <span>115</span>
                    </div>
                    <div class="stat-item">
                        О пройденных опросах
                        <span>125</span>
                    </div>
                    <div class="stat-item">
                        Об успеваемости
                        <span>60</span>
                    </div>
                    <div class="stat-item">
                        О новостях
                        <span>48</span>
                    </div>
                </div>

            </div>
            */?>
            <div class="content-block  content-block--margin">
                <div class="content-nav content-nav--top">
                    <span><a href="/cabinet/diller/reports/">Курсы</a></span>
                    <span class="active"><a href="#">Сотрудники</a></span>
                    <span><a href="/cabinet/diller/reports/dilers/">Дилеры</a></span>
                </div>

                <h3 class="h3 center">Сотрудники</h3>
                <?
                $list = \Settings\Reports::getEmployees();
                $headers = [];
                foreach ($list[0] as $name => $value)
                    if (strpos($name, '.ID') === false) {
                        if (strpos($name, 'VALUE_ID') === false)
                            $headers[] = $name;
                    }


                ?>

                <div class="reports">
                    <div class="table-block">
                        <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                            <thead class="thead-dark">
                            <tr>
                                <?php foreach ($headers as $header){?>
                                    <th><?=str_replace('.', '_', $header)?></th>
                                <?php }?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list as $item){?>
                                <tr>
                                    <?php foreach ($headers as $header){?>
                                        <td><?=$item[$header]?></td>
                                    <?php }?>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
            <?/*
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
            */?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>