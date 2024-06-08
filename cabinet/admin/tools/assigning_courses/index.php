<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Helpers\PageHelper;

$courses = Models\Course::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'PROPERTY_SCORM']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Назначение курсов</h2>
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
                        <div class="form-group">
                            <label for="">Файл шаблона вопросов к тесту</label>
                            <input type="file" id="fileInput" value="" name="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        </div>
                        <p>Файл с расширением .xlsx, заполненный согласно шаблона. <a href="/upload/tests/assigned_courses_template.xlsx" download="example">Скачать пример файла</a></p>
                        <div class="form-group" style="display: flex; padding-top: 1rem; justify-content: center">
                            <div class="btn-center">
                                <button class="btn load_data">Назначить курсы</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="results_block hidden">

                </div>
                <div class="loader hidden" style="text-align: center;">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/spinner.gif" style="width: 100px" alt="">
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function (){
            $(document).on('click', '.load_data', function (e){
                e.preventDefault();
                let formData = new FormData($('.report_form')[0]);
                formData.append('file', $('#fileInput')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/assigning_courses.php',
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