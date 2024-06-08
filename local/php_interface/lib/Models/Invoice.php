<?php

namespace Models;
use Helpers\DateHelper;
use Helpers\HLBlockHelper as HLBlock;
use Settings\Common;
use Teaching\CourseCompletion;
use Teaching\Courses;
use Teaching\SheduleCourses;

class Invoice
{
    private string $dataClass;
    public function __construct() {
        $this->dataClass = HLBlock::initialize('invoices');
    }

    public static function get($filter, $select = ['*'], $order = ["ID" => "DESC"])
    {
        return HLBlock::get(
            HLBlock::initialize('invoices'),
            $filter,
            $select,
            $order
        );
    }

    public static function addFromFileData($data) {
        $number = trim($data['invoice_number']);
        $data['invoice_number'] = $number!=""?$number:$data['id'];
        $dealer_id = Dealer::getActiveIdByCode($data['id_dealer']);
        $fields = [
            'UF_INVOICE_ID' => $data['id'],
            'UF_ID_TRENING' => $data['id_trening'],
            'UF_ID_DEALER' => $data['id_dealer'],
            'UF_INVOICE_NUMBER' => $data['invoice_number'],
            'UF_INVOICE_DATE' => date('d.m.Y H:i:s', strtotime($data['invoice_date'])),
            'UF_PRICE' => $data['price'],
            'UF_QUANTITY' => $data['quantity'],
            'UF_TOTAL' => $data['total'],
            'UF_TYPE' => 47,
            'UF_STATUS' => 49,
            'UF_CREATED_AT' => date('d.m.Y H:i:s'),
            'UF_DEALER_ID' => $dealer_id,
        ];
        if(!empty($data['invoice_number'])){
            $old_orders = HLBlock::get(
                HLBlock::initialize('invoices'),
                [
                    'UF_INVOICE_NUMBER' => $data['invoice_number'],
                ]
            );
            if (check_full_array($old_orders)) {
                if (!empty($data['payment_date']) && $data['payment_date'] != '0001-01-01T00:00:00' ) {
                    $fields['UF_PAYMENT_DATE'] = date('d.m.Y H:i:s', strtotime($data['payment_date']));
                    $fields['UF_STATUS'] = 51;
                    $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
                    foreach ($old_orders as $old_order) {
                        HLBlock::update($old_order['ID'], $fields, HLBlock::initialize('invoices'));
                    }
                    return true;
                } else {
                    $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
                    foreach ($old_orders as $old_order) {
                        HLBlock::update($old_order['ID'], $fields, HLBlock::initialize('invoices'));
                    }
                    return true;
                }
            } else {
                return HLBlock::add($fields, HLBlock::initialize('invoices'));
            }
        }
    }

    public static function createRelation($incoming_invoice_id, $outgoing_invoice_id, $sum){
        $fields = [
            'UF_INCOMING_INVOICE' => $incoming_invoice_id,
            'UF_OUTGOING_INVOICE' => $outgoing_invoice_id,
            'UF_SUM' => $sum,
        ];
        return HLBlock::add($fields, HLBlock::initialize('in_out_invoice_relations'));
    }

    public static function createFromCompletion($completion_id)
    {

        $completion = (new CourseCompletion())->find($completion_id);
        $start_date = date('Y-m-d H:i:s', strtotime('01.01.2024 00:00:00'));
        if(!empty($completion['UF_DATE']) && $completion['UF_DATE']->format('Y-m-d H:i:s') < $start_date)
            return;
        $course = Course::find($completion['UF_COURSE_ID'], ['PROPERTY_COST']);

        $user = User::find($completion['UF_USER_ID'], ['UF_DEALER']);
        $dealer = Dealer::find($user['UF_DEALER'], ['ID', 'CODE']);

        if (Courses::isPaid($completion['UF_COURSE_ID']) && $completion['UF_PAYMENT_FROM_BALANCE'] == 1 && Course::hasBalancePayment($completion['UF_COURSE_ID'])) {
            $fields = [
                'UF_DIRECTION' => Course::getCourseCategory($completion['UF_COURSE_ID']),
                'UF_DEALER' => $dealer['CODE'],
                'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                'UF_PRICE' => $course['PROPERTY_COST_VALUE'],
                'UF_USER_ID' => $completion['UF_USER_ID'],
                'UF_COURSE_ID' => $completion['UF_COURSE_ID'],
                'UF_COMPLETION_ID' => $completion_id,
                'UF_DEALER_ID' => $dealer['ID']
            ];
            HLBlock::add($fields, HLBlock::initialize('reserves'));
            //self::calculateBalance($fields["UF_DIRECTION"], $fields["UF_DEALER"]);
        }

    }

    public static function getByDealerId($dealer_id)
    {
        //$dealer = Dealer::find($dealer_id, ["ID", "CODE"]);
        $invoice_filter = ['UF_DEALER_ID' => $dealer_id];
        $directions = User::getDirectionsByDealerAdmin();
        if(check_full_array($directions))
            $invoice_filter["UF_ID_TRENING"] = $directions;
        return Invoice::get($invoice_filter);
    }

    public static function setExpired($id)
    {
        $fields["UF_STATUS"] = 52;
        self:self::update($id, $fields);
    }

    private static function generateNumber()
    {
        $num = (int)Common::get('invoice_number');
        $num = ++$num;
        Common::set('invoice_number', $num);
        return date('y')."-".date('m').str_pad($num, 8, 0, STR_PAD_LEFT);
    }

    public static function setPaid($completion_id)
    {
        $item = HLBlock::get(HLBlock::initialize('reserves'), ["UF_COMPLETION_ID" => $completion_id, "UF_IS_COMPLETE" => false]);
        if (check_full_array($item)) {
            $fields["UF_IS_COMPLETE"] = true;
            $fields["UF_COMPLETED_AT"] = date('d.m.Y H:i:s');
            HLBlock::update($item[0]['ID'], $fields, HLBlock::initialize('reserves'));
            self::makeFileFromCompletionId($completion_id);
            self::calculateBalance($item[0]["UF_DIRECTION"], $item[0]["UF_DEALER_ID"]);
        }
    }

    public static function closeInvoice($account_id)
    {
        $fields["UF_STATUS"] = 50;
        self:self::update($account_id, $fields);
    }

    public static function getStatusList():array
    {
        $return_array = [];
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => 235));
        while($arEnum = $rsEnum->GetNext()) {
            $return_array[$arEnum['ID']] = $arEnum['VALUE'];
        }
        return $return_array;
    }

    public static function update($id, $fields)
    {
        $fields['UF_UPDATED_AT'] = date('d.m.Y H:i:s');
        HLBlock::update($id, $fields, HLBlock::initialize('invoices'));
    }

    public static function prepareDate($dateString) {
        return date( 'Y-m-d', strtotime((string)$dateString))."T".date('H:i:s', strtotime((string)$dateString));
    }

    public static function makeFileFromCompletionId($completion_id)
    {
        $completion = (new CourseCompletion())->find($completion_id);
        if(!check_full_array($completion))
            return;
        $course = Course::find($completion['UF_COURSE_ID'], ["ID", "NAME", "PROPERTY_COST"]);
        $user = User::find($completion['UF_USER_ID'], ["ID", "UF_DEALER", "NAME", "LAST_NAME"]);
        $dealer = Dealer::find($user["UF_DEALER"], ['ID', "CODE"]);
        if($completion['UF_SHEDULE_ID']>0)
            $shedule = current(SheduleCourses::getById($completion['UF_SHEDULE_ID']));
        $array = [
            "id" => time().$completion_id,
            "type" => "training_payment",
            "user_id" => (int)$completion["UF_USER_ID"],
            "user_name" => $user["NAME"]." ".$user["LAST_NAME"],
            "course_id" => (int)$completion["UF_COURSE_ID"],
            "course_name" => $course["NAME"],
            "schedule_name" => $shedule["NAME"]??$course["NAME"],
            "registration_date" => self::prepareDate((string)$completion["UF_DATE_CREATE"]),
            "begin_date" => $shedule["PROPERTY_BEGIN_DATE_VALUE"]?self::prepareDate((string)$shedule["PROPERTY_BEGIN_DATE_VALUE"]):self::prepareDate((string)$completion["UF_DATE_CREATE"]),
            "end_date" => $shedule["PROPERTY_END_DATE_VALUE"]?self::prepareDate((string)$shedule["PROPERTY_END_DATE_VALUE"]):self::prepareDate((string)$completion["UF_DATE_CREATE"]),
            "duration" => $shedule["ID"]>0?SheduleCourses::getDuration($shedule["ID"]):1,
            "dealer_id" => $dealer["CODE"],
            "course_category" => Course::getCourseCategory($completion["UF_COURSE_ID"]),
            "total" => (int)$course["PROPERTY_COST_VALUE"],
        ];
        // Преобразование массива в JSON
        $jsonData = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filename = $completion_id."_".date("Y-m-d-H-i-s");
        // Путь для сохранения файла
        $filePath = $_SERVER["DOCUMENT_ROOT"] . "/ftp1c/in/$filename.json";

        // Сохранение JSON в файл
        file_put_contents($filePath, $jsonData);
    }

    public static function getOutgoingInvoicesList($incoming_invoice_id)
    {
        $return_array = [];
        $res = HLBlock::get(HLBlock::initialize('in_out_invoice_relations'), ["UF_INCOMING_INVOICE" => $incoming_invoice_id], ['*'], ['UF_CREATED_DATE' => "ASC"]);
        if(check_full_array($res)){
            $ids = [];
            foreach ($res as $item){
                $ids[] = $item["UF_OUTGOING_INVOICE"];
            }
            if(check_full_array($ids)){
                $return_array = HLBlock::get(HLBlock::initialize('invoices'), ["ID" => $ids], ['*'], ['UF_PAYMENT_DATE' => "ASC"]);
            }
            foreach ($return_array as &$one_item){
                foreach ($res as $res_item){
                    if($one_item['ID']==$res_item["UF_OUTGOING_INVOICE"]){
                        $one_item["UF_PRICE"] = $res_item["UF_SUM"];
                    }
                }
            }
        }
        return $return_array;
    }

    private static function getFreeIncomingInvoice($dealer, $category, $new_price, $invoice_id)
    {
        $filter = [
            "UF_TYPE" => 47,
            "UF_STATUS" => 51,
            "UF_ID_TRENING" => $category,
            "UF_ID_DEALER" => $dealer,
        ];
        $new_price = 15000;
        $current_incoming_invoice = current(HLBlock::get(HLBlock::initialize('invoices'), $filter, ['*'], ['UF_PAYMENT_DATE' => "ASC"]));
        $outgoing_invoices = self::getOutgoingInvoicesList($current_incoming_invoice["ID"]);
        $total_price = $current_incoming_invoice["UF_PRICE"]-$new_price;
        if(check_full_array($outgoing_invoices)){
            foreach ($outgoing_invoices as $outgoing_invoice){
                if(($total_price-$outgoing_invoice["UF_PRICE"])>0) {
                    $total_price = $total_price - $outgoing_invoice["UF_PRICE"];
                    $current_incoming_invoice["INVOICE_ID"] = $current_incoming_invoice["ID"];
                    $current_incoming_invoice["ADD_PRICE"] = $new_price;
                } else {
                    if(($total_price-$outgoing_invoice["UF_PRICE"])==0){
                        dump("Гасим счет");
                        self::closeInvoice($current_incoming_invoice["ID"]);
                        $current_incoming_invoice["INVOICE_ID"] = $current_incoming_invoice["ID"];
                        $current_incoming_invoice["ADD_PRICE"] = $new_price;
                    } else {
                        dump("Гасим остаток и часть нового счета");
                        self::closeInvoice($current_incoming_invoice["ID"]);
                        $current_incoming_invoice["INVOICE_ID"] = $current_incoming_invoice["ID"];
                        $current_incoming_invoice["ADD_PRICE"] = $new_price-abs($total_price-$outgoing_invoice["UF_PRICE"]);
                        $current_incoming_invoice["NEED_DIVISION"] = true;
                        $current_incoming_invoice["PART_OF_SUM"] = $new_price-$current_incoming_invoice["ADD_PRICE"];
                    }
                }
            }
        } else {
            $current_incoming_invoice["INVOICE_ID"] = $current_incoming_invoice["ID"];
            $current_incoming_invoice["ADD_PRICE"] = $new_price;
        }
        return $current_incoming_invoice;
    }

    private static function getByCompletionId($completion_id)
    {
        $filter = [
            "UF_COMPLETION_ID" => $completion_id
        ];
        return current(HLBlock::get(HLBlock::initialize('invoices'), $filter));
    }

    public static function calculateBalanceOld($direction, $dealer)
    {

        $incoming_invoices = \Models\Invoice::get(["UF_DEALER_ID" => $dealer, "UF_STATUS" => 51, "UF_ID_TRENING" => $direction], ['*'], ['UF_PAYMENT_DATE' => 'ASC']);

        $free_reserves = \Models\Reserve::get(["UF_DEALER_ID" => $dealer, "UF_IS_CLOSED" => false, "UF_IS_COMPLETE" => true, "UF_DIRECTION" => $direction, 'UF_ID_DEBIT' => false]);



        $current_key = 0;
        $reserves_for_close = [];
        if(!check_full_array($incoming_invoices[$current_key]))
            return;

        foreach ($free_reserves as $free_reserve) {
            $sum = ((int)$free_reserve["UF_CLOSED_SUM"]>0)?((int)$free_reserve["UF_PRICE"]-(int)$free_reserve["UF_CLOSED_SUM"]):$free_reserve["UF_PRICE"];

            if(($incoming_invoices[$current_key]["UF_TOTAL"] - $sum) >= 0) {
                $incoming_invoices[$current_key]["UF_TOTAL"]-=$sum;
                $reserves_for_close[$free_reserve["ID"]] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => $sum,
                    "part_close" => false
                ];

            } else {
                $reserves_for_close[$free_reserve["ID"]] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => $incoming_invoices[$current_key]["UF_TOTAL"],
                    "part_close" => true,
                ];
                foreach ($reserves_for_close as $res) {
                    $relation_fields = [
                        "UF_INCOMING_INVOICE_ID" => $res['incoming_invoice_id'],
                        "UF_RESERVE_ID" => $res['reserve_id'],
                        "UF_SUM" => $res['close_price'],
                        "UF_CREATED_DATE" => date('d.m.Y H:i:s'),
                    ];
                    $update_fields = [
                        'UF_IS_CLOSED' => true,
                        "UF_CLOSED_SUM" => $res['close_price']
                    ];
                    if($res["part_close"]) {
                        $update_fields["UF_IS_CLOSED"] = false;
                    }
                    HLBlock::update($res['reserve_id'], $update_fields, HLBlock::initialize('reserves'));
                    HLBlock::add($relation_fields, HLBlock::initialize('in_out_invoice_relations'));
                }
                Invoice::closeInvoice($res['incoming_invoice_id']);
                $current_key = $current_key+1;
            }
        }
    }
    public static function calculateBalanceOldV2($direction, $dealer)
    {

        $incoming_invoices = \Models\Invoice::get(["UF_DEALER_ID" => $dealer, "UF_STATUS" => 51, "UF_ID_TRENING" => $direction], ['*'], ['UF_PAYMENT_DATE' => 'ASC']);

        $free_reserves = \Models\Reserve::get(["UF_DEALER_ID" => $dealer, "UF_IS_CLOSED" => false, "UF_IS_COMPLETE" => true, "UF_DIRECTION" => $direction, 'UF_ID_DEBIT' => false]);



        $current_key = 0;
        $reserves_for_close = [];
        if(!check_full_array($incoming_invoices[$current_key]))
            return;

        foreach ($free_reserves as $free_reserve) {
            $sum = ((int)$free_reserve["UF_CLOSED_SUM"]>0)?((int)$free_reserve["UF_PRICE"]-(int)$free_reserve["UF_CLOSED_SUM"]):$free_reserve["UF_PRICE"];

            if(($incoming_invoices[$current_key]["UF_TOTAL"] - $sum) > 0) {
                $incoming_invoices[$current_key]["UF_TOTAL"]-=$sum;
                $reserves_for_close[$free_reserve["ID"]] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => $sum,
                    "part_close" => false
                ];

            } else {
                $reserves_for_close[$free_reserve["ID"]] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => $incoming_invoices[$current_key]["UF_TOTAL"],
                    "part_close" => true,
                ];
                foreach ($reserves_for_close as $res) {
                    $relation_fields = [
                        "UF_INCOMING_INVOICE_ID" => $res['incoming_invoice_id'],
                        "UF_RESERVE_ID" => $res['reserve_id'],
                        "UF_SUM" => $res['close_price'],
                        "UF_CREATED_DATE" => date('d.m.Y H:i:s'),
                    ];
                    $update_fields = [
                        'UF_IS_CLOSED' => true,
                        "UF_CLOSED_SUM" => $res['close_price']
                    ];
                    if($res["part_close"]) {
                        $update_fields["UF_IS_CLOSED"] = false;
                    }
                    dump($relation_fields);
                    dump($update_fields);
                    //HLBlock::update($res['reserve_id'], $update_fields, HLBlock::initialize('reserves'));
                    //HLBlock::add($relation_fields, HLBlock::initialize('in_out_invoice_relations'));
                }
                dump("Закрываем счет №".$res['incoming_invoice_id']);
                //Invoice::closeInvoice($res['incoming_invoice_id']);
                $current_key = $current_key+1;
            }
        }
    }
    public static function calculateBalance($direction, $dealer): void
    {
        $incoming_invoices = \Models\Invoice::get(
            ["UF_DEALER_ID" => $dealer, "UF_STATUS" => 51, "UF_ID_TRENING" => $direction],
            ['*'],
            ['UF_PAYMENT_DATE' => 'ASC']
        );
        $free_reserves = \Models\Reserve::get(
            ["UF_DEALER_ID" => $dealer, "UF_IS_CLOSED" => false, "UF_IS_COMPLETE" => true, "UF_DIRECTION" => $direction, 'UF_ID_DEBIT' => false],
            ["*"],
            ["UF_COMPLETED_AT" => "ASC"]
        );
        $current_key = 0;
        $reserves_for_close = [];
        foreach ($free_reserves as $free_reserve) {
            $sum = ((int)$free_reserve["UF_CLOSED_SUM"]>0)?((int)$free_reserve["UF_PRICE"]-(int)$free_reserve["UF_CLOSED_SUM"]):$free_reserve["UF_PRICE"];
            self::process($current_key, $sum, $incoming_invoices, $free_reserve, $reserves_for_close);
        }
        foreach ($reserves_for_close as $invoice_id => $reserve_for_close){
            if($reserve_for_close['need_close']){
                foreach ($reserve_for_close['reserves'] as $res){
                    $relation_fields = [
                        "UF_INCOMING_INVOICE_ID" => $res['incoming_invoice_id'],
                        "UF_RESERVE_ID" => $res['reserve_id'],
                        "UF_SUM" => $res['close_price'],
                        "UF_CREATED_DATE" => date('d.m.Y H:i:s'),
                    ];
                    $update_fields = [
                        'UF_IS_CLOSED' => true,
                        "UF_CLOSED_SUM" => $res['close_price']
                    ];
                    if($res["part_close"]) {
                        $update_fields["UF_IS_CLOSED"] = false;
                    }
                    HLBlock::update($res['reserve_id'], $update_fields, HLBlock::initialize('reserves'));
                    HLBlock::add($relation_fields, HLBlock::initialize('in_out_invoice_relations'));
                }
                Invoice::closeInvoice($invoice_id);
            }

        }
    }

    public static function process(&$current_key, $sum,  &$incoming_invoices, $free_reserve, &$reserves_for_close): void
    {
        if(!check_full_array($incoming_invoices[$current_key])) {
            return;
        }
        dump("-----------------------------");
        dump("Обработка резерва №".$free_reserve['ID'].", сумма - ".$sum);
        dump("--Счет №".$incoming_invoices[$current_key]["ID"].", сумма - ".$incoming_invoices[$current_key]["UF_TOTAL"]);
        if(($incoming_invoices[$current_key]["UF_TOTAL"] - $sum) > 0) {
            dump("Сумма резерва меньше остатка");
            dump("Счет не закрываем");
            $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['need_close'] = false;
            $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['reserves'][] = [
                "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                "reserve_id" => $free_reserve["ID"],
                "close_price" => $sum,
                "part_close" => false,
            ];
            $incoming_invoices[$current_key]["UF_TOTAL"] = $incoming_invoices[$current_key]["UF_TOTAL"] - $sum;
        } else {
            if($incoming_invoices[$current_key]["UF_TOTAL"] - $sum == 0){
                dump("Разница - 0");
                dump("Закрытие счета №".$incoming_invoices[$current_key]["ID"]);
                dump("Закрытие резерва №".$free_reserve["ID"]);
                $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['need_close'] = true;
                $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['reserves'][] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => $sum,
                    "part_close" => false,
                ];
                $current_key++;
            } else {
                dump("Остаток меньше суммы");
                dump("Закрытие счета №".$incoming_invoices[$current_key]["ID"]);
                $remaining = abs($incoming_invoices[$current_key]["UF_TOTAL"] - $sum);
                $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['need_close'] = true;
                $reserves_for_close[$incoming_invoices[$current_key]["ID"]]['reserves'][] = [
                    "incoming_invoice_id" => $incoming_invoices[$current_key]["ID"],
                    "reserve_id" => $free_reserve["ID"],
                    "close_price" => ($sum - $remaining),
                    "part_close" => true,
                ];
                $current_key++;
                $free_reserve["UF_CLOSED_SUM"] = $sum - $remaining;

                self::process($current_key, $remaining, $incoming_invoices, $free_reserve, $reserves_for_close);
            }

        }
    }
}