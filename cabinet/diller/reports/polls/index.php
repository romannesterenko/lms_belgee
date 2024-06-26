<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
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
            <?/*
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
                    <span><a href="/cabinet/diller/reports/employess/">Сотрудники</a></span>
                    <span><a href="/cabinet/diller/reports/dilers/">Дилеры</a></span>
                    <span class="active"><a href="">Опросы</a></span>
                </div>
                <h3 class="h3 center">Опросы</h3>
                <?php
                    $questions = new \Polls\PollQuestions();
                    $poll_processed = new \Polls\ProcessPoll();
                    $polls = new \Polls\Polls();
                    //получаем список всех активных опросов
                    $all_polls = $polls->getPolls(['ACTIVE'=>'Y'], ['UF_*']);
                    foreach ($all_polls as &$poll) {
                        //роли для опроса
                        $poll['FOR_ROLES'] = \Teaching\Roles::getById($poll['UF_ROLES']);
                        //завершенные прохождения опросв
                        $poll['COMPLETIONS'] = $poll_processed->getCompletePolls($poll['ID']);
                        //кол-во пройденных по опросу
                        $poll['ALL_COMPLETED'] = count($poll['COMPLETIONS']);
                        //вопросы опроса
                        $poll['QUESTIONS'] = $questions->getByPoll($poll['ID']);
                        foreach ($poll['QUESTIONS'] as $key => $question) {
                            //варианты ответов
                            $variants = $questions->getVariants($question['ID']);
                            foreach ($variants as $key_v => $variant){
                                $variant['count'] = 0;
                                //получаем ответы в прохождениях
                                foreach ($poll['COMPLETIONS'] as $completion){
                                    foreach($completion['UF_ANSWERS'] as $answer){
                                        $array = explode('_', $answer);
                                        //если вариант ответа в прохождении такой же, увеличиваем на 1 кол-во
                                        if($variant['id']==$array[1])
                                            $variant['count']++;
                                    }
                                }
                                //считаем процент
                                $variant['percent'] = $poll['ALL_COMPLETED']>0?floor($variant['count']/$poll['ALL_COMPLETED']*100):0;
                                $variants[$key_v] = $variant;
                            }
                            $poll['QUESTIONS'][$key]['VARIANTS'] = $variants;
                        }
                    }
                ?>

                <div class="reports">
                    <div class="table-block">
                        <?php foreach ($all_polls as $poll){?>
                            <table class="table table-bordered table-striped table--white" id="table-11" style="padding-top: 25px">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Наименование</th>
                                        <th>Для каких ролей</th>
                                        <th>Всего прошло, чел</th>
                                        <?php
                                        //получаем названия опросов в столбцы
                                        foreach ($poll['QUESTIONS'] as $question){?>
                                            <th><?=$question['NAME']?></th>
                                        <?php }?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><b><?=$poll['NAME']?></b></td>
                                        <td><?=implode(', ', $poll['FOR_ROLES'])?></td>
                                        <td><?=$poll['ALL_COMPLETED']?></td>
                                        <?php foreach ($poll['QUESTIONS'] as $question){?>
                                            <td class="text-left">
                                                <?php foreach ($question['VARIANTS'] as $variant){

                                                    echo $variant['text'].' - '.$variant['count'].' ('.$variant['percent'].'%)<br/>'?>
                                                <?php }?>
                                            </td>
                                        <?php }?>
                                    </tr>
                                </tbody>
                            </table>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>