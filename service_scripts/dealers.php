<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Teaching\CourseCompletion;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$list = \Models\Dealer::getList(['ACTIVE' => 'Y']);?>
<h4 class="text-center">Быстрая авторизация по дилерам</h4>
<form class="report_generator" action="" method="get" style="">
    <input type="hidden" name="dealer_set"value="Y">
    <div class="form-group selectable">
        <label for="">Дилер</label>
        <select class="js-example-basic-multiple" name="dealer" style="width: 100%;">
            <?php foreach ($list as $id => $dealer){?>
                <option value="<?=$id?>"<?=$_REQUEST['dealer'] == $id?' selected':''?>><?=$dealer['NAME']?></option>
            <?php }?>
        </select>
    </div>
    <div class="form-group" style="display: flex; padding-top: 1rem;">
        <div class="btn-center">
            <button class="btn">Генерировать</button>
            <a href="/service_scripts/dealers.php" class="btn">Сбросить</a>
        </div>
    </div>
</form>
<?php if($_REQUEST['dealer_set'] == 'Y' && $_REQUEST['dealer'] > 0) {
    $dealer = \Models\Dealer::find($_REQUEST['dealer']);
    $replace = [
        16 => 'Администратор ППО',
        15 => 'Администратор ОП',
    ];
    $users = \Models\User::getArray(['filter' => ['UF_DEALER' => $_REQUEST['dealer'], 'UF_LOCAL_ADMIN' => true], 'select' => ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_TEACHING_ADMIN_TYPE']]);
    if(check_full_array($users)){?>
        <h4 class="text-center">Администраторы дилера "<?=$dealer['NAME']?>"</h4>
        <table class="table table-bordered table-striped table--white" style="padding-top: 25px">
            <thead class="thead-dark">
                <tr>
                    <th class="text-left">ID</th>
                    <th class="text-left">Имя</th>
                    <th class="text-left">Направления</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user){
                    $dir = [];
                    foreach ($user['UF_TEACHING_ADMIN_TYPE'] as $dir_id)
                        $dir[] = $replace[$dir_id];?>
                    <tr>
                        <td class="text-left"><?=$user['ID']?></td>
                        <td class="text-left"><?=$user['NAME']?> <?=$user['LAST_NAME']?></td>
                        <td class="text-left"><?=implode('<br />', $dir)?></td>
                        <td class="text-right pr-30" ><a href="#" class="btn authorize_btn" data-id="<?=$user['ID']?>">Авторизация</a></td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    <?php }?>
<?php }?>
    <script>
        $(function (){
            $(document).on('click', '.authorize_btn', function (e){
                e.preventDefault()
                let user = $(this).data('id')
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/geely/ajax/auth.php',
                    data: {
                        uid: user
                    },
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function(response){
                        document.location.reload()
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                    },
                });
            })
        })
    </script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");