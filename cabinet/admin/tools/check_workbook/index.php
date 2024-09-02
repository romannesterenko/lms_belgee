<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;

use Helpers\PageHelper;

?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Проверка файла</h2>
                <div class="form-div">
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group">
                            <label for="">Файл (добавьте для проверки)</label>
                            <input type="file" id="fileInput" name="file" accept="application/pdf">
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem; justify-content: center">
                            <div class="btn-center">
                                <button class="btn load_data">Проверить</button>
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
            $(document).on('click', '.load_data', function (e){
                e.preventDefault();
                let formData = new FormData($('.report_form')[0]);
                formData.append('file', $('#fileInput')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/check_workbook.php',
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

    </style>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>