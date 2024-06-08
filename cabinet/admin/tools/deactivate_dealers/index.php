<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION, $USER;
$APPLICATION->SetTitle(Loc::getMessage('MAIN_TITLE'));
use Models\User;
if (!$USER->isAdmin()){
    LocalRedirect('/cabinet/common/');
} else {
$dealers = Models\Dealer::getList(['ACTIVE' => 'Y'], ['ID', 'NAME', 'CODE']);
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <div class="content-block">
                <h2 class="h2 center">Деактивация дилеров</h2>
                <div class="form-div">
                    <div class="error-text" style="color: red; padding: 15px 0px"></div>
                    <form class="report_form" action="" method="post" style="">
                        <div class="form-group selectable">
                            <label for="">Дилер</label>
                            <select class="js-example-basic-multiple" name="dealer_id" style="width: 100%;" required>
                                <option value="">Выберите дилера</option>
                                <?php foreach ($dealers as $id => $dealer){?>
                                    <option value="<?=$dealer['ID']?>"><?=$dealer['NAME']?> (<?=$dealer['CODE']?>)</option>
                                <?php }?>
                            </select>
                        </div>
                    </form>
                    <div id="participants_table">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function loadParticipantsTable(){
            $("#participants_table").empty()
            if(!isNaN(parseInt($("[name='dealer_id']").val()))){
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/loadParticipantsByDealer.php',
                    data: {
                        'dealer_id': $("[name='dealer_id']").val(),
                    },
                    dataType: 'html',
                    beforeSend: function () {
                    },
                    success: function (response) {
                        $("#participants_table").empty().html(response)
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            }

        }
        function deactivateDealer(dealer_id) {
            $.ajax({
                type: 'POST',
                url: '/local/templates/geely/ajax/deactivate_dealer.php',
                data: {
                    'id': dealer_id,
                },
                dataType: 'html',
                beforeSend: function () {
                },
                success: function (response) {
                    $("#participants_table").empty()
                    $("option[value='"+dealer_id+"']").remove()
                    alert('Дилер успешно деактивирован')
                },
                error: function (xhr, ajaxOptions, thrownError) {
                },
            });
        }
        $(function (){
            $(document).on('change', '[name="dealer_id"]', function (){
                loadParticipantsTable()
            });
            $(document).on('click', '.deactivate_dealer', function (){
                if(confirm('При деактивации дилера все сотрудники привязанные к этому дилеру будут уволены и отписаны с записанных курсов. Эта операция необратима! Вы уверены и хотите продолжить?')){
                    deactivateDealer($(this).data('dealer'))
                }
            });
        })
    </script>

<?php }
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>