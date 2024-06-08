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
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <div class="content-block  content-block--margin">
                <div class="content-menu">
                    <ul>
                        <li class="active"><a href="">Финансово-экономический отдел</a></li>
                        <li><a href="">Отдел продаж</a></li>
                        <li><a href="">Сборочный цех</a></li>
                        <li><a href="">Юридический отдел</a></li>
                        <li><a href="">Отдел закупок</a></li>
                        <li><a href="">Ремонтная служба</a></li>
                        <li><a href="">Отдел бухгалтерии</a></li>
                        <li><a href="">Отдел маркетинга</a></li>
                        <li><a href="">Гарантийное обеспечение</a></li>
                        <li><a href="">Кадровый отдел</a></li>
                        <li><a href="">Отдел контроля качества</a></li>
                        <li><a href="">Логистика и транспорт</a></li>
                    </ul>
                </div>

                <h3 class="h3 center">Финансово-экономический отдел</h3>

                <div class="workers">
                    <div class="worker-item">
                        <div class="worker-item__content">
                            <span class="worker-item__avatar"><img src="images/avatar-1.jpg" alt=""></span>
                            <span class="worker-item__name">Ворошилов <br>
                    Александр Павлович</span>
                        </div>
                        <div class="worker-item__position">Специалист отдела продаж <br> «Авангард-Лахта»</div>
                        <ul>
                            <li>Стаж работы: <span> 5 лет</span></li>
                            <li>Пройдено курсов: <span>7</span></li>
                            <li>Назначено курсов: <span>3</span></li>
                        </ul>
                    </div>
                    <div class="worker-item">
                        <div class="worker-item__content">
                            <span class="worker-item__avatar"><img src="images/avatar-1.jpg" alt=""></span>
                            <span class="worker-item__name">Ворошилов <br>
                    Александр Павлович</span>
                        </div>
                        <div class="worker-item__position">Специалист отдела продаж <br> «Авангард-Лахта»</div>
                        <ul>
                            <li>Стаж работы: <span> 5 лет</span></li>
                            <li>Пройдено курсов: <span>7</span></li>
                            <li>Назначено курсов: <span>3</span></li>
                        </ul>
                    </div>
                    <div class="worker-item">
                        <div class="worker-item__content">
                            <span class="worker-item__avatar"><img src="images/avatar-1.jpg" alt=""></span>
                            <span class="worker-item__name">Ворошилов <br>
                    Александр Павлович</span>
                        </div>
                        <div class="worker-item__position">Специалист отдела продаж <br> «Авангард-Лахта»</div>
                        <ul>
                            <li>Стаж работы: <span> 5 лет</span></li>
                            <li>Пройдено курсов: <span>7</span></li>
                            <li>Назначено курсов: <span>3</span></li>
                        </ul>
                    </div>
                    <div class="worker-item">
                        <div class="worker-item__content">
                            <span class="worker-item__avatar"><img src="images/avatar-1.jpg" alt=""></span>
                            <span class="worker-item__name">Ворошилов <br>
                    Александр Павлович</span>
                        </div>
                        <div class="worker-item__position">Специалист отдела продаж <br> «Авангард-Лахта»</div>
                        <ul>
                            <li>Стаж работы: <span> 5 лет</span></li>
                            <li>Пройдено курсов: <span>7</span></li>
                            <li>Назначено курсов: <span>3</span></li>
                        </ul>
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