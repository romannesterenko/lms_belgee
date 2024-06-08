<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Helpers\PageHelper;

$courses = Models\Course::getList(['ACTIVE' => 'Y', 'PROPERTY_COURSE_TYPE' => [6, 125]], ['ID', 'NAME', 'PROPERTY_SCORM']);
$first_course = current($courses);
$test = current(\Teaching\Tests::getTestByCourse($first_course['ID'], ['ID', 'NAME', 'PROPERTY_POINTS']));
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Загрузка вопросов к тесту</h2>
                <div class="form-div">
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Курс</label>
                            <select class="js-example-basic-multiple" name="course_id" style="width: 100%;">
                                <?php foreach ($courses as $id => $course){?>
                                    <option value="<?=$course['ID']?>"><?=$course['NAME']?></option>
                                <?php }?>
                            </select>
                        </div>
                        <?php if($test['ID']>0){?>
                            <p class="test_info green" style="padding-bottom: 15px">Для курса <?=$first_course['NAME']?> найден тест. Вы можете изменить количество баллов для прохождения</p>
                        <?php } else {?>
                            <p class="test_info red" style="padding-bottom: 15px">Для курса <?=$first_course['NAME']?> еще не был создан тест. Укажите количество баллов для прохождения теста в поле ниже для корректного его автоматического создания</p>
                        <?php }?>
                        <div class="form-group">
                            <label for="">Количество баллов для прохождения теста</label>
                            <input type="text" value="<?=$test['PROPERTY_POINTS_VALUE']?>" name="max_points" placeholder="Введите максимальное количество баллов для прохождения теста">
                        </div>
                        <div class="form-group">
                            <label for="">Файл шаблона вопросов к тесту</label>
                            <input type="file" id="fileInput" value="" name="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" placeholder="Введите ID, имя или фамилию или номер телефона и выберите пользователя из списка">
                        </div>
                        <p>Файл с расширением .xlsx, заполненный согласно шаблона. <a href="/upload/tests/Шаблон_загрузки_вопросов_теста.xlsx" download="example">Скачать пример файла</a></p>
                        <div class="form-group" style="display: flex; padding-top: 1rem; justify-content: center">
                            <div class="btn-center">
                                <button class="btn load_data">Посмотреть предварительные результаты</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="results_block hidden">

                </div>

                <div class="add_results_block hidden">

                </div>
                <div class="loader hidden" style="text-align: center;">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" style="width: 100px" alt="">
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function (){
            $(document).on('change', '[name="course_id"]', function (e){
                e.preventDefault();
                $('.results_block').empty().addClass('hidden');
                $('.add_results_block').empty().addClass('hidden');
                $('[name="max_points"]').val('');
                var course_id = $(this).val()
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/get_test_info.php',
                    data: {
                        course_id: course_id
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        $('.loader').removeClass('hidden');
                    },
                    success: function(response){
                        $('.loader').addClass('hidden');
                        if(response.test===false){
                            $('.test_info').removeClass('green').addClass('red').empty().text(response.message);
                        } else {
                            $('.test_info').removeClass('red').addClass('green').empty().text(response.message);
                            $('[name="max_points"]').val(response.test.PROPERTY_POINTS_VALUE);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });
            $(document).on('click', '.load_data', function (e){
                e.preventDefault();
                let formData = new FormData($('.report_form')[0]);
                formData.append('file', $('#fileInput')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/load_test_questions_data.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'html',
                    beforeSend: function () {
                        $('.loader').removeClass('hidden');
                    },
                    success: function(response){
                        $('.loader').addClass('hidden');
                        $('.results_block').removeClass('hidden').empty().html(response);

                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });
            $(document).on('click', '.load_test_questions', function (e){
                e.preventDefault();
                var file_id = $(this).data('file-id');
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/load_test_questions.php',
                    data: {
                        course_id: $('[name="course_id"]').val(),
                        file_id: file_id,
                        max_points: $('[name="max_points"]').val()
                    },
                    dataType: 'html',
                    beforeSend: function () {
                        $('.loader').removeClass('hidden');
                    },
                    success: function(response){
                        $('.loader').addClass('hidden');
                        $('.add_results_block').removeClass('hidden').empty().html(response);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            });

        })
    </script>
    <style>
        .green{
            color: green;
        }
        .red{
            color: red;
        }
    </style>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>