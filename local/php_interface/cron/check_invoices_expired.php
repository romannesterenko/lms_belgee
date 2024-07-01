<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../..');
$_SERVER["REMOTE_ADDR"] = $_SERVER["DOCUMENT_ROOT"];
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$need_date = date('d.m.Y', strtotime('-1 year'));
dump($need_date);
$invoices = \Models\Invoice::get(['UF_STATUS' => 51, '<=UF_PAYMENT_DATE' => $need_date]);
foreach ($invoices as $invoice) {
    \Models\Invoice::setExpired($invoice['ID']);
    \Models\Reserve::createDebitFromInvoice($invoice);
    //dump($invoices);
}
