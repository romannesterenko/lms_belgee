<?php

namespace Models;
use Helpers\HLBlockHelper as HLBlock;
use Teaching\CourseCompletion;
use Teaching\Courses;

class Reserve
{
    private string  $dataClass;
    public function __construct() {
        $this->dataClass = HLBlock::initialize('reserves');
    }

    public static function get($filter, $select = ['*'], $order = ['ID' => "DESC"])
    {
        return HLBlock::get(
            HLBlock::initialize('reserves'),
            $filter,
            $select,
            $order
        );
    }

    public static function reservesByDealerId($dealer_id)
    {
        //$dealer = Dealer::find($dealer_id, ["ID", "CODE"]);
        $reserve_filter = [
            'UF_DEALER_ID' => $dealer_id,
            "UF_ID_DEBIT" => false
        ];
        $directions = User::getDirectionsByDealerAdmin();
        if(check_full_array($directions))
            $reserve_filter["UF_DIRECTION"] = $directions;
        return Reserve::get($reserve_filter);
    }

    public static function debitsByDealerId($dealer_id)
    {
        //$dealer = Dealer::find($dealer_id, ["ID", "CODE"]);
        $reserve_filter = [
            'UF_DEALER_ID' => $dealer_id,
            "UF_ID_DEBIT" => true
        ];
        $directions = User::getDirectionsByDealerAdmin();
        if(check_full_array($directions))
            $reserve_filter["UF_DIRECTION"] = $directions;
        return Reserve::get($reserve_filter);
    }

    public static function makeDebitFile($fields):void
    {
        $array = [
            "id" => time().$fields['UF_INVOICE_ID'],
            "type" => "money_debited",
            "course_category" => $fields['UF_ID_TRENING'],
            "dealer_id" => $fields['UF_ID_DEALER'],
            "total" => $fields['UF_TOTAL'],
        ];
        // Преобразование массива в JSON
        $jsonData = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filename = $fields['UF_INVOICE_ID']."_".date("Y-m-d-H-i-s");
        // Путь для сохранения файла
        $filePath = $_SERVER["DOCUMENT_ROOT"] . "/ftp1c/in/$filename.json";

        // Сохранение JSON в файл
        file_put_contents($filePath, $jsonData);
    }

    public static function createDebitFromInvoice($invoice):void
    {
        $fields = [
            'UF_DIRECTION' => $invoice['UF_ID_TRENING'],
            'UF_DEALER' => $invoice['UF_ID_DEALER'],
            'UF_CREATED_AT' => date('d.m.Y')."T".date('H:i:s'),
            'UF_PRICE' => $invoice['UF_TOTAL'],
            'UF_ID_DEBIT' => true
        ];
        self::makeDebitFile($invoice);
        HLBlock::add($fields, HLBlock::initialize('reserves'));
    }

    public static function deleteByCompletion($id):void {
        $reserves = \Models\Reserve::get(['UF_COMPLETION_ID' => $id]);
        foreach ($reserves as $reserve){
            if(check_full_array($reserve) && $reserve['ID']>0) {
                self::delete($reserve['ID']);
            }
        }
    }

    public static function delete(mixed $ID):void
    {
        HLBlock::delete($ID, HLBlock::initialize('reserves'));
    }

    public static function update($ID, $fields)
    {
        HLBlock::update($ID, $fields, HLBlock::initialize('reserves'));
    }

    public static function close(mixed $reserve_id): void
    {
        $fields = [
            "UF_CLOSED_AT" => date("d.m.Y H:i:s"),
            "UF_UPDATED_AT" => date("d.m.Y H:i:s"),
            'UF_IS_CLOSED' => true
        ];
        self::update($reserve_id, $fields);
    }

    public static function setClosedSum(mixed $reserve_id, $sum): void
    {
        $fields = [
            "UF_UPDATED_AT" => date("d.m.Y H:i:s"),
            "UF_CLOSED_SUM" => $sum
        ];
        self::update($reserve_id, $fields);
    }

}