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
$sdk_key = '0o6B9X4OCkRfZihGKobouS8aG8o4q01D8T86';
$sdk_secret = 'ZeM2uKhwdx8i1G4IgmTUmgQ7KMH7jchSjV94';
$client_id = 'FiDSXzwpSpqjyQsHKZ3uJg';
$client_secret = '8R08aG6AiEph2WoFnOVKp2Unc0CmZJ7f';
    if(!empty($client_id)&&!empty($client_secret)){
        try {
            $client = new Client(['base_uri' => 'https://zoom.us']);

            $response = $client->request('POST', '/oauth/token', [
                "headers" => [
                    "Authorization" => "Basic " . base64_encode($client_id . ':' . $client_secret)
                ],
                'form_params' => [
                    "grant_type" => "authorization_code",
                    "code" => $_GET['code'],
                    "redirect_uri" => 'https://lms.geely-motors.com/zoom/check_sdk.php'
                ],
            ]);

            $array_response = json_decode($response->getBody()->getContents(), true);
            if($array_response['access_token']&&$array_response['refresh_token']){
                dump($array_response);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
/*Array
(
    [access_token] => eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiI4ZDQxYTYxNy1kNzFhLTQ4OTUtOWZjYS1jYzE3YjIwYjU3ZTQifQ.eyJ2ZXIiOjgsImF1aWQiOiI0NzcxMmNhYzY3NzYyNDkxOGMwNjRkNGU4MzRkMjZlMCIsImNvZGUiOiI1bGU4MlJmYUZ3ckd6bWJhdTFrUXJXYXVIejNacGl3UnciLCJpc3MiOiJ6bTpjaWQ6RmlEU1h6d3BTcHFqeVFzSEtaM3VKZyIsImdubyI6MCwidHlwZSI6MCwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiJlU3Bpdy1XaVItMmNtX2doSzhya2NnIiwibmJmIjoxNjc1MTgyODA2LCJleHAiOjE2NzUxODY0MDYsImlhdCI6MTY3NTE4MjgwNiwiYWlkIjoibGlBUzBZdUVUZUtvdGdWZ3BGODZrUSIsImp0aSI6IjI1OTUzZWE2LTk3NGMtNDljMy05ODc4LTBmZDUwZmQzZjkzZCJ9.9tVlaA5va_99VycEyw-XTEG2EAf5Q95iKiyJ1GeaJJgMemqXRT3JKlKXdswxqSzWJZ9iZPqFfaxgKv5B3Z8g6Q
    [token_type] => bearer
    [refresh_token] => eyJhbGciOiJIUzUxMiIsInYiOiIyLjAiLCJraWQiOiJkZDY2YTYwNC0zNDYzLTRiNmMtOWI0YS00ODZmZmJmNmY4ZDYifQ.eyJ2ZXIiOjgsImF1aWQiOiI0NzcxMmNhYzY3NzYyNDkxOGMwNjRkNGU4MzRkMjZlMCIsImNvZGUiOiI1bGU4MlJmYUZ3ckd6bWJhdTFrUXJXYXVIejNacGl3UnciLCJpc3MiOiJ6bTpjaWQ6RmlEU1h6d3BTcHFqeVFzSEtaM3VKZyIsImdubyI6MCwidHlwZSI6MSwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiJlU3Bpdy1XaVItMmNtX2doSzhya2NnIiwibmJmIjoxNjc1MTgyODA2LCJleHAiOjE2ODI5NTg4MDYsImlhdCI6MTY3NTE4MjgwNiwiYWlkIjoibGlBUzBZdUVUZUtvdGdWZ3BGODZrUSIsImp0aSI6IjFkY2ZmZDYxLTNmMTktNDNmOC1iOWNlLTczMGNkMzk0YzA2OCJ9.2htu8ELtwNKJU6G6zbn0TQchh0WYf6eDQO0bQ2TEtMbE7b9rr0AvKHEXqP9LD3g5pZVVeZt107O37gGJGyQKPA
    [expires_in] => 3599
    [scope] => meeting:write meeting_token:read:live_streaming webinar_token:read:local_recording meeting:read:sip_dialing meeting:read webinar:write user_zak:read webinar:read webinar_token:read:live_streaming meeting_token:read:local_recording
)*/



