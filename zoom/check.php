<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use \GuzzleHttp\Client;
if(!empty($_REQUEST['acc_code'])){
    if( str_contains($_REQUEST['acc_code'], '?code=') ) {
        $code_array = explode('?code=', $_REQUEST['acc_code']);
        $code = $code_array[0];
    }else{
        $code = $_REQUEST['acc_code'];
    }
}
$current_account = \Settings\ZoomAccount::getByCode($code);
if(check_full_array($current_account)){
    if(!empty($current_account['PROPERTY_CLIENT_ID_VALUE'])&&!empty($current_account['PROPERTY_CLIENT_SECRET_VALUE'])&&!empty($current_account['PROPERTY_REDIRECT_URL_VALUE'])){
        try {
            $client = new Client(['base_uri' => 'https://zoom.us']);

            $response = $client->request('POST', '/oauth/token', [
                "headers" => [
                    "Authorization" => "Basic " . base64_encode($current_account['PROPERTY_CLIENT_ID_VALUE'] . ':' . $current_account['PROPERTY_CLIENT_SECRET_VALUE'])
                ],
                'form_params' => [
                    "grant_type" => "authorization_code",
                    "code" => $_GET['code'],
                    "redirect_uri" => $current_account['PROPERTY_REDIRECT_URL_VALUE']
                ],
            ]);

            $array_response = json_decode($response->getBody()->getContents(), true);
            if($array_response['access_token']&&$array_response['refresh_token']){
                \Settings\ZoomAccount::setAccessToken($current_account['ID'], $array_response['access_token']);
                \Settings\ZoomAccount::setRefreshToken($current_account['ID'], $array_response['refresh_token']);
                $updated_account = \Settings\ZoomAccount::getByCode($code);
                echo "Аккаунт успешно зарегистрирован";
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}




