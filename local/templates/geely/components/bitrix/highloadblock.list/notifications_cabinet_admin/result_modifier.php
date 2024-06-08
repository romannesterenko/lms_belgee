<?php
foreach ($arResult['rows'] as &$row){
    $date_arr = (explode(' ', $row['UF_DATE']));
    $row['UF_DATE'] = $date_arr[0].', '.$date_arr[1];
    switch($row['UF_TYPE']){
        case 'уведомление':
            $row['ICON'] = 'notice';
            break;
        case 'важно':
            $row['ICON'] = 'important';
            break;
        case 'критичное':
            $row['ICON'] = 'critical';
            break;
    }
}
