<?php

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;
global $USER, $APPLICATION;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
	die();
}
if($USER->IsAuthorized()){?>
</div>
</main>
<?php }?>

<footer>
    <div class="container">
        <div class="footer-content">
        <span class="footer-logo">
          <a href="/" class="logo"><img src="<?=\Bitrix\Main\Config\Option::get('common.settings', 'header_logo')?>" alt=""></a>
        </span>

            <ul class="footer-menu">
                <li><a href="/rules.pdf" target="_blank"><?= Loc::getMessage('LEGAL_INFO') ?></a></li>
                <li><a href="/pers.pdf" target="_blank"><?= Loc::getMessage('PERSONAL_DATA_POLICY') ?></a></li>
            </ul>
            <div class="footer-contacts">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    Array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => SITE_TEMPLATE_PATH."/include/footer_contacts.php",
                        "EDIT_TEMPLATE" => ""
                    )
                );?>
            </div>
        </div>
    </div>
    <div class="copy"><?=\Settings\Common::get('footer_name')?> © <?=date('Y')?> <?= Loc::getMessage('ALL_RIGHTS') ?></div>
</footer>
<script src="https://unpkg.com/imask"></script>
<?php
Asset::getInstance()->addString('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>');
Asset::getInstance()->addString('<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>');
Asset::getInstance()->addString('<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>');
Asset::getInstance()->addString('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>');
if(CSite::InDir('/table/')||CSite::InDir('/cabinet/admin/polls/')||$_REQUEST['report_id']>0){
Asset::getInstance()->addString('<link rel="stylesheet" href="//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">');
Asset::getInstance()->addString('<link rel="stylesheet" href="//cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">');
Asset::getInstance()->addString('<link rel="stylesheet" href="//cdn.datatables.net/rowreorder/1.2.8/css/rowReorder.dataTables.min.css">');
Asset::getInstance()->addString('<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css">');

Asset::getInstance()->addString('<script src="//cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>');
Asset::getInstance()->addString('<script src="//cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdn.datatables.net/rowreorder/1.2.8/js/dataTables.rowReorder.min.js"></script>');
Asset::getInstance()->addString('<script src="//cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>');
Asset::getInstance()->addString('<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>');
Asset::getInstance()->addString('<script src="https://cdn.datatables.net/plug-ins/1.11.3/sorting/datetime-moment.js"></script>');
?>
    <script>
        $(document).ready( function () {
            $.fn.dataTable.moment('DD.MM.YYYY');
            $.fn.dataTable.moment('DD.MM.YYYY HH:mm:ss');
            var table1 = $('#table-1').DataTable({
                "order": [],
                "pageLength" : 25,
                rowReorder: {
                    selector: 'td:nth-child(2)'
                },
                responsive: false,
                dom: 'Bfrtip',
                buttons: [
                    'excel', 'pdf', 'print'
                ],
                language: {
                    "decimal":        "",
                    "emptyTable":     "Нет данных",
                    "info":           "Показано с _START_ по _END_ записи из _TOTAL_ ",
                    "infoEmpty":      "Показано с 0 по 0 записи из 0",
                    "infoFiltered":   "(Выбрано из _MAX_ записей)",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "Показать _MENU_ записей",
                    "loadingRecords": "Загрузка ...",
                    "processing":     "",
                    "search":         "Найти:",
                    "zeroRecords":    "Записей не найдено",
                    "paginate": {
                        "first":      "Первая",
                        "last":       "Последняя",
                        "next":       "Следующая",
                        "previous":   "Предыдущая"
                    },
                    "aria": {
                        "sortAscending":  ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                },
            });
            var cnt = -1;
            if(parseInt($('#count').val())>0)
                cnt = parseInt($('#count').val());
            var table = $('#table-report').DataTable({
                "order": [],
                aLengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "Все записи"]
                ],
                "pageLength" : cnt,
                /*rowReorder: {
                    selector: 'td:nth-child(2)'
                },*/

                responsive: $('#is_adaptive').val()==='Да',
                //responsive: false,
                dom: 'lfrtiBp',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'A2'
                    }
                ],
                language: {
                    "decimal":        "",
                    "emptyTable":     "Нет данных",
                    "info":           "Показано с _START_ по _END_ записи из _TOTAL_ ",
                    "infoEmpty":      "Показано с 0 по 0 записи из 0",
                    "infoFiltered":   "(Выбрано из _MAX_ записей)",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "Показать _MENU_ записей",
                    "loadingRecords": "Загрузка ...",
                    "processing":     "",
                    "search":         "Найти:",
                    "zeroRecords":    "Записей не найдено",
                    "paginate": {
                        "first":      "Первая",
                        "last":       "Последняя",
                        "next":       "Следующая",
                        "previous":   "Предыдущая"
                    },
                    "aria": {
                        "sortAscending":  ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                },
            });
        } );
    </script>
    <style>
        .table-bordered td, .table-bordered th{
            border: 1px solid #9b9b9b;
        }
        .table.table-bordered{
            margin-bottom: 15px;
        }
        .dt-buttons{
            margin-left: 15px;
        }
    </style>
    <?php


}?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/js/datepicker.js"></script>
<?php
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/air-datepicker.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/owl.carousel.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/scripts.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/custom.js');
?>
<a href="#ex2" style="display: none" rel="modal:open">course card choose date</a>
<a href="#ex1" style="display: none" rel="modal:open">course card choose date</a>
<a href="#ex3" style="display: none" rel="modal:open"></a>
<a href="#ex4" style="display: none" rel="modal:open"></a>
<a href="#ex5" style="display: none" rel="modal:open"></a>

<div id="ex3" class="modal modal--md">
    <div class="modal-content">

    </div>
    <a href="#" rel="modal:close"></a>
</div>
<div id="ex5" class="modal modal--md">
    <div class="modal-content">
        <input type="hidden" class="hdn_ex5" value="">
    </div>
    <a href="#" rel="modal:close"></a>
</div>
<div id="ex2" class="modal modal--md">
    <div class="modal-content">

    </div>
    <a href="#" rel="modal:close"></a>
</div>
<div id="ex1" class="modal modal--xs">

    <div class="modal-content send_request_form">
        <div class="modal-icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/education-icon.svg" alt=""></div>
        <h3 class="h3 center"><?=GetMessage('MODAL_REGISTER_TITLE')?></h3>
        <p><?=GetMessage('YOU_ARE_REGISTERING')?><span id="beg_date"></span>
            в <span id="beg_time"></span>. <?=GetMessage('APPROVE_YOUR_REGISTRATION')?></p>
        <div class="btn-center">
            <a href="#" data-template="list" data-user-id="<?=$USER->GetID();?>" data-id="<?=$arResult['ITEM']['ID']?>" class="btn send_request_to_course"><?=GetMessage('APPROVE_REGISTRATION_BUTTON')?></a>
        </div>
    </div>


    <a href="#" rel="modal:close"></a>
</div>
<div id="ex4" class="modal modal--xs">
    <div class="modal-content">
        <div class="modal-icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/like-gray.svg" width="67px" alt=""></div>
        <h3 class="h3 center"></h3>
        <p></p>

    </div>


    <a href="#" rel="modal:close"></a>
</div>


<svg width="0" height="0" class="hidden">
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 7 12" id="table-arrow">
        <path
                d="M6.69509 4.78871C5.06889 3.33675 3.44269 1.88479 1.75841 0.490903C0.829159 -0.380274 -0.622804 1.01361 0.364531 1.88479C1.75842 3.10444 3.1523 4.32408 4.4881 5.54373C3.09422 6.82146 1.70034 8.09919 0.306452 9.37691C-0.622804 10.2481 0.77108 11.642 1.70034 10.7708C3.38461 9.26076 5.01081 7.75072 6.69509 6.24067C7.10164 5.8922 7.10164 5.13718 6.69509 4.78871Z"
                fill="currentColor"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 24" id="document">
        <g clip-path="url(#clip0_432_27797)">
            <path
                    d="M21.7969 5.20644L16.2585 -0.137314C16.1292 -0.262002 15.9538 -0.333252 15.7692 -0.333252H6.07692C4.93231 -0.333252 4 0.566279 4 1.67065V20.4628C4 21.5672 4.93231 22.4667 6.07692 22.4667H19.9231C21.0677 22.4667 22 21.5672 22 20.4628V5.67847C22 5.49589 21.9215 5.32667 21.7969 5.20644ZM16.4615 1.94675L19.6369 5.0105H17.1538C16.7708 5.0105 16.4615 4.71214 16.4615 4.34253V1.94675ZM19.9231 21.1308H6.07692C5.69385 21.1308 5.38462 20.8325 5.38462 20.4628V1.67065C5.38462 1.30104 5.69385 1.00269 6.07692 1.00269H15.0769V4.34253C15.0769 5.4469 16.0092 6.34644 17.1538 6.34644H20.6154V20.4628C20.6154 20.8325 20.3062 21.1308 19.9231 21.1308Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 9.10742H8.84465C8.46157 9.10742 8.15234 9.40578 8.15234 9.77539C8.15234 10.145 8.46157 10.4434 8.84465 10.4434H17.1523C17.5354 10.4434 17.8447 10.145 17.8447 9.77539C17.8447 9.40578 17.5354 9.10742 17.1523 9.10742Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 11.7793H8.84465C8.46157 11.7793 8.15234 12.0777 8.15234 12.4473C8.15234 12.8169 8.46157 13.1152 8.84465 13.1152H17.1523C17.5354 13.1152 17.8447 12.8169 17.8447 12.4473C17.8447 12.0777 17.5354 11.7793 17.1523 11.7793Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 14.4512H8.84465C8.46157 14.4512 8.15234 14.7495 8.15234 15.1191C8.15234 15.4887 8.46157 15.7871 8.84465 15.7871H17.1523C17.5354 15.7871 17.8447 15.4887 17.8447 15.1191C17.8447 14.7495 17.5354 14.4512 17.1523 14.4512Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M14.3831 17.123H8.84465C8.46157 17.123 8.15234 17.4214 8.15234 17.791C8.15234 18.1606 8.46157 18.459 8.84465 18.459H14.3831C14.7662 18.459 15.0754 18.1606 15.0754 17.791C15.0754 17.4214 14.7662 17.123 14.3831 17.123Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_27797">
                <rect width="24" height="24" fill="white" transform="translate(0.75)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 24" id="document">
        <g clip-path="url(#clip0_432_27797)">
            <path
                    d="M21.7969 5.20644L16.2585 -0.137314C16.1292 -0.262002 15.9538 -0.333252 15.7692 -0.333252H6.07692C4.93231 -0.333252 4 0.566279 4 1.67065V20.4628C4 21.5672 4.93231 22.4667 6.07692 22.4667H19.9231C21.0677 22.4667 22 21.5672 22 20.4628V5.67847C22 5.49589 21.9215 5.32667 21.7969 5.20644ZM16.4615 1.94675L19.6369 5.0105H17.1538C16.7708 5.0105 16.4615 4.71214 16.4615 4.34253V1.94675ZM19.9231 21.1308H6.07692C5.69385 21.1308 5.38462 20.8325 5.38462 20.4628V1.67065C5.38462 1.30104 5.69385 1.00269 6.07692 1.00269H15.0769V4.34253C15.0769 5.4469 16.0092 6.34644 17.1538 6.34644H20.6154V20.4628C20.6154 20.8325 20.3062 21.1308 19.9231 21.1308Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 9.10742H8.84465C8.46157 9.10742 8.15234 9.40578 8.15234 9.77539C8.15234 10.145 8.46157 10.4434 8.84465 10.4434H17.1523C17.5354 10.4434 17.8447 10.145 17.8447 9.77539C17.8447 9.40578 17.5354 9.10742 17.1523 9.10742Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 11.7793H8.84465C8.46157 11.7793 8.15234 12.0777 8.15234 12.4473C8.15234 12.8169 8.46157 13.1152 8.84465 13.1152H17.1523C17.5354 13.1152 17.8447 12.8169 17.8447 12.4473C17.8447 12.0777 17.5354 11.7793 17.1523 11.7793Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M17.1523 14.4512H8.84465C8.46157 14.4512 8.15234 14.7495 8.15234 15.1191C8.15234 15.4887 8.46157 15.7871 8.84465 15.7871H17.1523C17.5354 15.7871 17.8447 15.4887 17.8447 15.1191C17.8447 14.7495 17.5354 14.4512 17.1523 14.4512Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path
                    d="M14.3831 17.123H8.84465C8.46157 17.123 8.15234 17.4214 8.15234 17.791C8.15234 18.1606 8.46157 18.459 8.84465 18.459H14.3831C14.7662 18.459 15.0754 18.1606 15.0754 17.791C15.0754 17.4214 14.7662 17.123 14.3831 17.123Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_27797">
                <rect width="24" height="24" fill="white" transform="translate(0.75)"></rect>
            </clipPath>
        </defs>
    </symbol>

    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="purse-icon">
        <g clip-path="url(#clip0_432_29265)">
            <path
                    d="M30.562 24.3571H27.4431V23.2181C27.4431 21.4824 26.0309 20.0703 24.2952 20.0703H21.5502V17.6842L22.9013 18.4128C23.0609 18.4985 23.2367 18.5414 23.4102 18.5414C23.6299 18.5414 23.8484 18.4739 24.0338 18.341C24.367 18.1021 24.5352 17.696 24.4677 17.2921L23.6149 12.1867L27.2245 8.576C27.5106 8.28886 27.6102 7.86672 27.4838 7.48207C27.3563 7.0985 27.0242 6.81779 26.6245 6.75779L21.6595 6.01743L19.4342 1.38993C19.2542 1.02243 18.8792 0.785645 18.4677 0.785645C18.0563 0.785645 17.6802 1.02243 17.5024 1.39207L15.277 6.01957L10.3109 6.761C9.91129 6.821 9.57915 7.10172 9.45165 7.48529C9.32522 7.86886 9.42594 8.29207 9.71094 8.57814L13.3206 12.1889L12.4677 17.2942C12.4002 17.6981 12.5684 18.1042 12.9017 18.3431C13.2349 18.5821 13.6752 18.6089 14.0331 18.4139L15.1217 17.8267V20.0714H12.3756C10.6409 20.0714 9.22879 21.4824 9.22879 23.2192V24.3571H6.10987C4.58094 24.3571 3.33594 25.601 3.33594 27.131V28.0117C3.33594 29.5417 4.58094 30.7856 6.10987 30.7856H30.5609C32.0909 30.7856 33.3359 29.5417 33.3359 28.0117V27.131C33.3359 25.601 32.0909 24.3571 30.562 24.3571ZM15.2267 11.0649L12.7302 8.56743L16.1545 8.05636C16.5059 8.00386 16.8081 7.781 16.9624 7.46064L18.4677 4.32886L19.9731 7.46064C20.1274 7.781 20.4295 8.00386 20.7809 8.05636L24.2042 8.56743L21.7077 11.0649C21.4634 11.3103 21.352 11.6585 21.4088 11.9992L21.9917 15.4889L18.9756 13.8614C18.6584 13.691 18.2759 13.6889 17.9577 13.8614L14.9406 15.4889L15.5234 11.9992C15.5824 11.6574 15.4709 11.3092 15.2267 11.0649ZM17.2302 16.6889L18.4667 16.0224L19.4663 16.5614C19.4384 16.6567 19.4074 16.7521 19.4074 16.8571V20.0714H17.2645V16.8571C17.2645 16.7971 17.2399 16.7456 17.2302 16.6889ZM11.3717 23.2192C11.3717 22.6653 11.8217 22.2142 12.3767 22.2142H16.1931H20.4788H24.2963C24.8502 22.2142 25.3013 22.6653 25.3013 23.2192V24.3571H11.3727V23.2192H11.3717ZM31.1931 28.0117C31.1931 28.3599 30.9102 28.6428 30.562 28.6428H6.10987C5.76165 28.6428 5.47879 28.3599 5.47879 28.0117V27.131C5.47879 26.7828 5.76165 26.4999 6.10987 26.4999H10.3002H26.3717H30.562C30.9102 26.4999 31.1931 26.7828 31.1931 27.131V28.0117Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29265">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="education-icon">
        <g clip-path="url(#clip0_432_29230)">
            <path
                    d="M29.1421 12.9129H27.2012V8.32069C27.2012 3.73537 23.4505 0.00488281 18.8359 0.00488281C14.2213 0.00488281 10.4706 3.73781 10.4706 8.32612V12.9129H8.55129C7.88624 12.9129 7.33594 13.4376 7.33594 14.0982V27.973C7.33594 29.6219 8.69757 30.962 10.3575 30.962H27.3359C28.9958 30.962 30.3359 29.6219 30.3359 27.973V14.0982C30.3359 13.4376 29.8071 12.9129 29.1421 12.9129ZM12.8763 8.32612C12.8763 5.05686 15.5493 2.39711 18.8359 2.39711C22.1226 2.39711 24.7955 5.0544 24.7955 8.32069V12.9129H12.8763V8.32612ZM27.9302 27.973C27.9302 28.3028 27.6679 28.5724 27.3359 28.5724H10.3575C10.0255 28.5724 9.74164 28.3028 9.74164 27.973V15.3026H27.9302V27.973Z"
                    fill="currentColor"></path>
            <path
                    d="M18.8357 25.5212C19.5007 25.5212 20.0385 24.9857 20.0385 24.3251V20.0191C20.0385 19.3585 19.5007 18.823 18.8357 18.823C18.1706 18.823 17.6328 19.3585 17.6328 20.0191V24.3251C17.6328 24.9857 18.1706 25.5212 18.8357 25.5212Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29230">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="question">
        <g clip-path="url(#clip0_432_29295)">
            <circle cx="18.3359" cy="15.7856" r="14" stroke="currentColor" stroke-width="2.2"></circle>
            <path
                    d="M22.9805 11.9536C22.9805 12.5851 22.8372 13.145 22.5508 13.6333C22.2708 14.1151 21.9388 14.5838 21.5547 15.0396C20.526 16.2505 19.9108 16.9927 19.709 17.2661C19.5137 17.5396 19.416 17.8228 19.416 18.1157V18.8774C19.416 18.9751 19.377 19.063 19.2988 19.1411C19.2207 19.2192 19.1263 19.2583 19.0156 19.2583H17.3555C17.1016 19.2583 16.9746 19.1313 16.9746 18.8774V18.1157C16.9746 17.4907 17.0527 16.9829 17.209 16.5923C17.3652 16.2017 17.681 15.7297 18.1562 15.1763C18.8659 14.356 19.3509 13.7798 19.6113 13.4478C19.8783 13.1157 20.0117 12.6177 20.0117 11.9536V11.6313C20.0117 11.1431 19.6276 10.8989 18.8594 10.8989C18.4232 10.8989 17.9349 10.9217 17.3945 10.9673L15.8516 11.1138H15.7734C15.5456 11.1138 15.4316 10.9673 15.4316 10.6743V9.43408C15.4316 9.21273 15.474 9.06299 15.5586 8.98486C15.6432 8.90023 16.0306 8.80257 16.7207 8.69189C17.4108 8.57471 18.1237 8.51611 18.8594 8.51611C20.3242 8.51611 21.3757 8.76676 22.0137 9.26807C22.6582 9.76937 22.9805 10.5571 22.9805 11.6313V11.9536ZM19.4941 23.1548C19.4941 23.2655 19.4551 23.3566 19.377 23.4282C19.2988 23.4998 19.2044 23.5356 19.0938 23.5356H17.2969C17.1862 23.5356 17.0918 23.4998 17.0137 23.4282C16.9355 23.3566 16.8965 23.2655 16.8965 23.1548V20.8989C16.8965 20.7882 16.9355 20.6938 17.0137 20.6157C17.0918 20.5376 17.1862 20.4985 17.2969 20.4985H19.0938C19.2044 20.4985 19.2988 20.5376 19.377 20.6157C19.4551 20.6938 19.4941 20.7882 19.4941 20.8989V23.1548Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29295">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="lock">
        <g clip-path="url(#clip0_432_29230)">
            <path
                    d="M29.1421 12.9129H27.2012V8.32069C27.2012 3.73537 23.4505 0.00488281 18.8359 0.00488281C14.2213 0.00488281 10.4706 3.73781 10.4706 8.32612V12.9129H8.55129C7.88624 12.9129 7.33594 13.4376 7.33594 14.0982V27.973C7.33594 29.6219 8.69757 30.962 10.3575 30.962H27.3359C28.9958 30.962 30.3359 29.6219 30.3359 27.973V14.0982C30.3359 13.4376 29.8071 12.9129 29.1421 12.9129ZM12.8763 8.32612C12.8763 5.05686 15.5493 2.39711 18.8359 2.39711C22.1226 2.39711 24.7955 5.0544 24.7955 8.32069V12.9129H12.8763V8.32612ZM27.9302 27.973C27.9302 28.3028 27.6679 28.5724 27.3359 28.5724H10.3575C10.0255 28.5724 9.74164 28.3028 9.74164 27.973V15.3026H27.9302V27.973Z"
                    fill="currentColor"></path>
            <path
                    d="M18.8357 25.5212C19.5007 25.5212 20.0385 24.9857 20.0385 24.3251V20.0191C20.0385 19.3585 19.5007 18.823 18.8357 18.823C18.1706 18.823 17.6328 19.3585 17.6328 20.0191V24.3251C17.6328 24.9857 18.1706 25.5212 18.8357 25.5212Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29230">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="awards">
        <g clip-path="url(#clip0_432_29265)">
            <path
                    d="M30.562 24.3571H27.4431V23.2181C27.4431 21.4824 26.0309 20.0703 24.2952 20.0703H21.5502V17.6842L22.9013 18.4128C23.0609 18.4985 23.2367 18.5414 23.4102 18.5414C23.6299 18.5414 23.8484 18.4739 24.0338 18.341C24.367 18.1021 24.5352 17.696 24.4677 17.2921L23.6149 12.1867L27.2245 8.576C27.5106 8.28886 27.6102 7.86672 27.4838 7.48207C27.3563 7.0985 27.0242 6.81779 26.6245 6.75779L21.6595 6.01743L19.4342 1.38993C19.2542 1.02243 18.8792 0.785645 18.4677 0.785645C18.0563 0.785645 17.6802 1.02243 17.5024 1.39207L15.277 6.01957L10.3109 6.761C9.91129 6.821 9.57915 7.10172 9.45165 7.48529C9.32522 7.86886 9.42594 8.29207 9.71094 8.57814L13.3206 12.1889L12.4677 17.2942C12.4002 17.6981 12.5684 18.1042 12.9017 18.3431C13.2349 18.5821 13.6752 18.6089 14.0331 18.4139L15.1217 17.8267V20.0714H12.3756C10.6409 20.0714 9.22879 21.4824 9.22879 23.2192V24.3571H6.10987C4.58094 24.3571 3.33594 25.601 3.33594 27.131V28.0117C3.33594 29.5417 4.58094 30.7856 6.10987 30.7856H30.5609C32.0909 30.7856 33.3359 29.5417 33.3359 28.0117V27.131C33.3359 25.601 32.0909 24.3571 30.562 24.3571ZM15.2267 11.0649L12.7302 8.56743L16.1545 8.05636C16.5059 8.00386 16.8081 7.781 16.9624 7.46064L18.4677 4.32886L19.9731 7.46064C20.1274 7.781 20.4295 8.00386 20.7809 8.05636L24.2042 8.56743L21.7077 11.0649C21.4634 11.3103 21.352 11.6585 21.4088 11.9992L21.9917 15.4889L18.9756 13.8614C18.6584 13.691 18.2759 13.6889 17.9577 13.8614L14.9406 15.4889L15.5234 11.9992C15.5824 11.6574 15.4709 11.3092 15.2267 11.0649ZM17.2302 16.6889L18.4667 16.0224L19.4663 16.5614C19.4384 16.6567 19.4074 16.7521 19.4074 16.8571V20.0714H17.2645V16.8571C17.2645 16.7971 17.2399 16.7456 17.2302 16.6889ZM11.3717 23.2192C11.3717 22.6653 11.8217 22.2142 12.3767 22.2142H16.1931H20.4788H24.2963C24.8502 22.2142 25.3013 22.6653 25.3013 23.2192V24.3571H11.3727V23.2192H11.3717ZM31.1931 28.0117C31.1931 28.3599 30.9102 28.6428 30.562 28.6428H6.10987C5.76165 28.6428 5.47879 28.3599 5.47879 28.0117V27.131C5.47879 26.7828 5.76165 26.4999 6.10987 26.4999H10.3002H26.3717H30.562C30.9102 26.4999 31.1931 26.7828 31.1931 27.131V28.0117Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29265">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 32" id="user2">
        <path
                d="M28.6176 22.9527C28.6242 22.9622 28.6321 22.9735 28.6408 22.9855C28.646 22.9928 28.6517 23.0006 28.6578 23.0089C28.662 23.0242 28.6668 23.041 28.6723 23.0589L28.6751 23.0693L28.6818 23.0935C28.6827 23.097 28.6835 23.1 28.6842 23.1026C28.6847 23.1083 28.6853 23.114 28.6859 23.1196V29.0773C28.6859 29.7713 28.1133 30.3439 27.4193 30.3439H3.2526C2.55856 30.3439 1.98594 29.7713 1.98594 29.0773V23.142C1.98778 23.1278 1.98941 23.1132 1.99077 23.0983C1.99109 23.0948 1.99139 23.0913 1.99168 23.0878L1.99673 23.0692L2.00254 23.0481C2.00498 23.0397 2.0073 23.0315 2.00948 23.0236C2.01679 23.0131 2.02446 23.0017 2.03235 22.9897L2.04051 22.978L2.05656 22.9551C2.06223 22.947 2.06634 22.9413 2.06934 22.9372C2.07334 22.933 2.0772 22.9289 2.08092 22.9248C9.78199 16.5326 20.8973 16.5329 28.5904 22.9243C28.599 22.9336 28.6081 22.9431 28.6176 22.9527ZM2.18547 22.7951C2.18554 22.795 2.18552 22.7951 2.18542 22.7952L2.18547 22.7951ZM1.92775 23.3875C1.92767 23.3877 1.92828 23.3858 1.9298 23.3812C1.92859 23.385 1.92783 23.3873 1.92775 23.3875Z"
                stroke="currentColor" stroke-width="2.3"></path>
        <path
                d="M21.4359 7.32764C21.4359 10.6966 18.7049 13.4276 15.3359 13.4276C11.967 13.4276 9.23594 10.6966 9.23594 7.32764C9.23594 3.9587 11.967 1.22764 15.3359 1.22764C18.7049 1.22764 21.4359 3.9587 21.4359 7.32764Z"
                stroke="currentColor" stroke-width="2.3"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 32" id="purse">
        <g clip-path="url(#clip0_432_29196)">
            <path
                    d="M32.5662 16.076V11.8558C32.5662 10.4235 31.4535 9.2598 30.0531 9.1511L26.1331 2.30938C25.7686 1.67636 25.1867 1.22238 24.4833 1.03695C23.7863 0.851519 23.0573 0.947431 22.4306 1.3119L9.00814 9.13192H6.6485C5.13935 9.13192 3.91797 10.3532 3.91797 11.8622V28.2312C3.91797 29.7338 5.13935 30.9615 6.6485 30.9615H29.842C31.3448 30.9615 32.5726 29.7402 32.5726 28.2312V24.0111C33.3655 23.7297 33.9346 22.9816 33.9346 22.0928V18.0006C33.9282 17.1118 33.3591 16.3637 32.5662 16.076ZM28.4672 9.13192H22.5201L26.9772 6.5359L28.4672 9.13192ZM26.3058 5.35299L19.8152 9.13192H17.123L25.6343 4.17647L26.3058 5.35299ZM23.1212 2.49481C23.4282 2.31578 23.7927 2.26462 24.1316 2.36053C24.4769 2.45645 24.771 2.68024 24.9501 2.99355L14.4053 9.13192H11.7131L23.1212 2.49481ZM31.2041 28.2312C31.2041 28.9857 30.5902 29.5932 29.842 29.5932H6.6485C5.89393 29.5932 5.28643 28.9793 5.28643 28.2312V11.8622C5.28643 11.1077 5.90032 10.5003 6.6485 10.5003H29.842C30.5966 10.5003 31.2041 11.1141 31.2041 11.8622V15.9545H27.1115C24.8542 15.9545 23.0189 17.7896 23.0189 20.0467C23.0189 22.3038 24.8542 24.139 27.1115 24.139H31.2041V28.2312ZM32.5662 22.0928C32.5662 22.4701 32.2592 22.777 31.8819 22.777H27.1051C25.6024 22.777 24.3746 21.5557 24.3746 20.0467C24.3746 18.5441 25.596 17.3164 27.1051 17.3164H31.8819C32.2592 17.3164 32.5662 17.6233 32.5662 18.0006V22.0928Z"
                    fill="currentColor" stroke="currentColor" stroke-width="0.6"></path>
            <path
                    d="M27.1121 18.6775C26.3575 18.6775 25.75 19.2913 25.75 20.0394C25.75 20.7876 26.3639 21.4014 27.1121 21.4014C27.8602 21.4014 28.4741 20.7876 28.4741 20.0394C28.4741 19.2913 27.8602 18.6775 27.1121 18.6775Z"
                    fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29196">
                <rect width="36" height="31" fill="white" transform="translate(0.335938 0.285645)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 25" id="notice-message">
        <g clip-path="url(#clip0_432_29313)">
            <path
                    d="M26.0161 0.0214844H5.62449C3.49245 0.0214844 1.75781 1.75578 1.75781 3.88815V16.3674C1.75781 18.495 3.4849 20.2265 5.61075 20.2341V25.897L13.749 20.2341H26.0161C28.1482 20.2341 29.8828 18.4994 29.8828 16.3674V3.88815C29.8828 1.75578 28.1482 0.0214844 26.0161 0.0214844ZM28.2349 16.3674C28.2349 17.5907 27.2396 18.5861 26.0161 18.5861H13.232L7.2587 22.7427V18.5861H5.62449C4.40105 18.5861 3.40576 17.5907 3.40576 16.3674V3.88815C3.40576 2.66455 4.40105 1.66943 5.62449 1.66943H26.0161C27.2396 1.66943 28.2349 2.66455 28.2349 3.88815V16.3674Z"
                    fill="currentColor"></path>
            <path d="M9.28516 5.84448H22.3551V7.49243H9.28516V5.84448Z" fill="currentColor"></path>
            <path d="M9.28516 9.36011H22.3551V11.0081H9.28516V9.36011Z" fill="currentColor"></path>
            <path d="M9.28516 12.8757H22.3551V14.5237H9.28516V12.8757Z" fill="currentColor"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_29313">
                <rect width="30" height="25" fill="white" transform="translate(0.835938)"></rect>
            </clipPath>
        </defs>
    </symbol>
</svg>
<svg width="0" height="0" class="hidden">
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 24" id="document">
        <g clip-path="url(#clip0_432_27797)">
            <path d="M21.7969 5.20644L16.2585 -0.137314C16.1292 -0.262002 15.9538 -0.333252 15.7692 -0.333252H6.07692C4.93231 -0.333252 4 0.566279 4 1.67065V20.4628C4 21.5672 4.93231 22.4667 6.07692 22.4667H19.9231C21.0677 22.4667 22 21.5672 22 20.4628V5.67847C22 5.49589 21.9215 5.32667 21.7969 5.20644ZM16.4615 1.94675L19.6369 5.0105H17.1538C16.7708 5.0105 16.4615 4.71214 16.4615 4.34253V1.94675ZM19.9231 21.1308H6.07692C5.69385 21.1308 5.38462 20.8325 5.38462 20.4628V1.67065C5.38462 1.30104 5.69385 1.00269 6.07692 1.00269H15.0769V4.34253C15.0769 5.4469 16.0092 6.34644 17.1538 6.34644H20.6154V20.4628C20.6154 20.8325 20.3062 21.1308 19.9231 21.1308Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path d="M17.1523 9.10742H8.84465C8.46157 9.10742 8.15234 9.40578 8.15234 9.77539C8.15234 10.145 8.46157 10.4434 8.84465 10.4434H17.1523C17.5354 10.4434 17.8447 10.145 17.8447 9.77539C17.8447 9.40578 17.5354 9.10742 17.1523 9.10742Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path d="M17.1523 11.7793H8.84465C8.46157 11.7793 8.15234 12.0777 8.15234 12.4473C8.15234 12.8169 8.46157 13.1152 8.84465 13.1152H17.1523C17.5354 13.1152 17.8447 12.8169 17.8447 12.4473C17.8447 12.0777 17.5354 11.7793 17.1523 11.7793Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path d="M17.1523 14.4512H8.84465C8.46157 14.4512 8.15234 14.7495 8.15234 15.1191C8.15234 15.4887 8.46157 15.7871 8.84465 15.7871H17.1523C17.5354 15.7871 17.8447 15.4887 17.8447 15.1191C17.8447 14.7495 17.5354 14.4512 17.1523 14.4512Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
            <path d="M14.3831 17.123H8.84465C8.46157 17.123 8.15234 17.4214 8.15234 17.791C8.15234 18.1606 8.46157 18.459 8.84465 18.459H14.3831C14.7662 18.459 15.0754 18.1606 15.0754 17.791C15.0754 17.4214 14.7662 17.123 14.3831 17.123Z" fill="currentColor" stroke="currentColor" stroke-width="0.3"></path>
        </g>
        <defs>
            <clipPath id="clip0_432_27797">
                <rect width="24" height="24" fill="white" transform="translate(0.75)"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 19" id="location">
        <path d="M6.50001 0.5C3.19167 0.5 0.5 3.19167 0.5 6.50001C0.5 7.49318 0.748308 8.4779 1.22035 9.35132L6.1719 18.3066C6.23782 18.426 6.36343 18.5 6.50001 18.5C6.63659 18.5 6.76221 18.426 6.82812 18.3066L11.7815 9.34837C12.2517 8.4779 12.5 7.49314 12.5 6.49998C12.5 3.19167 9.80835 0.5 6.50001 0.5ZM6.50001 9.5C4.84584 9.5 3.50002 8.15418 3.50002 6.50001C3.50002 4.84584 4.84584 3.50002 6.50001 3.50002C8.15418 3.50002 9.5 4.84584 9.5 6.50001C9.5 8.15418 8.15418 9.5 6.50001 9.5Z" fill="currentColor"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 18" id="user">
        <path d="M15.9958 12.9471C15.9917 12.8974 15.9792 12.856 15.9667 12.8105C15.9542 12.7732 15.95 12.7318 15.9333 12.6945C15.9167 12.6572 15.8917 12.6282 15.8667 12.5951C15.8375 12.5578 15.8167 12.5164 15.7792 12.4874C15.7708 12.4791 15.7667 12.4708 15.7625 12.4667C11.2667 8.71814 4.7375 8.71814 0.2375 12.4667C0.229167 12.4749 0.225 12.4832 0.220833 12.4874C0.1875 12.5205 0.1625 12.5578 0.133333 12.5992C0.1125 12.6323 0.0833333 12.6613 0.0666667 12.6945C0.05 12.7318 0.0458333 12.769 0.0333333 12.8105C0.0208333 12.856 0.00833333 12.8974 0.00416667 12.9471C0.00833333 12.9554 0 12.9637 0 12.9761V16.2897C0 17.0187 0.6 17.6152 1.33333 17.6152H14.6667C15.4 17.6152 16 17.0187 16 16.2897V12.9761C16 12.9637 15.9958 12.9596 15.9958 12.9471Z" fill="currentColor"></path>
        <path d="M8 8.33718C10.2091 8.33718 12 6.55692 12 4.36085C12 2.16479 10.2091 0.384521 8 0.384521C5.79086 0.384521 4 2.16479 4 4.36085C4 6.55692 5.79086 8.33718 8 8.33718Z" fill="currentColor"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" id="message">
        <path d="M16.7894 1.34766H3.19497C1.77361 1.34766 0.617188 2.50385 0.617188 3.92544V12.2449C0.617188 13.6633 1.76858 14.8177 3.18581 14.8227V18.598L8.61134 14.8227H16.7894C18.2108 14.8227 19.3672 13.6663 19.3672 12.2449V3.92544C19.3672 2.50385 18.2108 1.34766 16.7894 1.34766Z" fill="currentColor"></path>
        <path d="M5.63281 5.22974H14.3461V6.32837H5.63281V5.22974Z" fill="white"></path>
        <path d="M5.63281 7.57349H14.3461V8.67212H5.63281V7.57349Z" fill="white"></path>
        <path d="M5.63281 9.91724H14.3461V11.0159H5.63281V9.91724Z" fill="white"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 18" id="work-table">
        <rect x="0.75" width="7.13507" height="7.13507" fill="currentColor"></rect>
        <rect x="0.75" y="10.0183" width="7.13507" height="7.13507" fill="currentColor"></rect>
        <rect x="11.0547" width="7.13507" height="7.13507" fill="currentColor"></rect>
        <rect x="11.0547" y="10.0183" width="7.13507" height="7.13507" fill="currentColor"></rect>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 19" id="report">
        <g clip-path="url(#clip0_644_45209)">
            <path d="M12.8421 0H3.15789C1.41642 0 0 1.38442 0 3.08632V15.0189C0 16.7213 1.41642 18.1053 3.15789 18.1053H12.8421C14.5836 18.1053 16 16.7213 16 15.0189V3.08632C16 1.38442 14.5836 0 12.8421 0Z" fill="currentColor"></path>
            <path d="M11.7891 6.73682H4.21012C3.97769 6.73682 3.78906 6.92545 3.78906 7.15787V7.99997C3.78906 8.2324 3.97769 8.42103 4.21012 8.42103H11.7891C12.0219 8.42103 12.2101 8.2324 12.2101 7.99997V7.15787C12.2101 6.92545 12.0219 6.73682 11.7891 6.73682Z" fill="white"></path>
            <path d="M11.7891 9.68433H4.21012C3.97769 9.68433 3.78906 9.87254 3.78906 10.1054V10.9475C3.78906 11.1803 3.97769 11.3685 4.21012 11.3685H11.7891C12.0219 11.3685 12.2101 11.1803 12.2101 10.9475V10.1054C12.2101 9.87254 12.0219 9.68433 11.7891 9.68433Z" fill="white"></path>
            <path d="M11.7891 12.6316H4.21012C3.97769 12.6316 3.78906 12.8198 3.78906 13.0526V13.8947C3.78906 14.1276 3.97769 14.3158 4.21012 14.3158H11.7891C12.0219 14.3158 12.2101 14.1276 12.2101 13.8947V13.0526C12.2101 12.8198 12.0219 12.6316 11.7891 12.6316Z" fill="white"></path>
            <path d="M11.7891 3.78955H4.21012C3.97769 3.78955 3.78906 3.97818 3.78906 4.2106V5.05271C3.78906 5.28513 3.97769 5.47376 4.21012 5.47376H11.7891C12.0219 5.47376 12.2101 5.28513 12.2101 5.05271V4.2106C12.2101 3.97818 12.0219 3.78955 11.7891 3.78955Z" fill="white"></path>
        </g>
        <defs>
            <clipPath id="clip0_644_45209">
                <rect width="16" height="18.1053" fill="white"></rect>
            </clipPath>
        </defs>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 4" id="minus-btn">
        <path d="M7.2793 3.16821C7.2793 3.50545 7.10156 3.67407 6.74609 3.67407H1.26367C0.889974 3.67407 0.703125 3.50545 0.703125 3.16821V1.40454C0.703125 1.24959 0.757812 1.12199 0.867188 1.02173C0.976562 0.921468 1.10872 0.871338 1.26367 0.871338H6.74609C7.10156 0.871338 7.2793 1.04907 7.2793 1.40454V3.16821Z" fill="currentColor"></path>
    </symbol>
    <symbol fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 6" id="check2">
        <path d="M1.37969 2.99826C1.32378 2.91582 1.20129 2.88908 1.10278 2.93364C1.00426 2.98043 0.97231 3.08292 1.02556 3.16536L2.88673 4.93224L3.06247 5.05478L3.22755 4.95006L6.95788 1.21801C7.0271 1.14449 7.00847 1.03754 6.9206 0.979608C6.83007 0.921677 6.70493 0.939502 6.6357 1.01526L3.08643 4.53787L1.37969 2.99826Z" fill="currentColor" stroke="currentColor" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
    </symbol>
</svg>

<?php if( $_REQUEST['report_id'] > 0 ) {
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/ultimate-export/libs/FileSaver/FileSaver.min.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/ultimate-export/libs/js-xlsx/xlsx.core.min.js');
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH.'/js/ultimate-export/tableExport.min.js');
}?>
<script>
    $(function (){
        $('#gen').click(function (e){
            $('#table-report').tableExport({
                type:'excel'
            });

        })
        $('#gen1').click(function (e){
            $('#table-report1').tableExport({
                type:'excel'
            });

        })
        $('#gen8').click(function (e){
            $('#table-report').tableExport({
                type:'excel'
            });
        })
        $('#gen13').click(function (e){
            $('#table-report13').tableExport({
                type:'excel'
            });
        })
        $('#gen13_1').click(function (e){
            $('#table-report13_1').tableExport({
                type:'excel'
            });
        })
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });
    })
</script>

</body>

</html>
