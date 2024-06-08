<?php
namespace Integrations;
use GuzzleHttp\Client;
use Helpers\DateHelper as Date;
use Models\User;
use Settings\Common as Settings;
use Teaching\Courses;
use Teaching\SheduleCourses as Schedule;

class Zoom
{
    public static function getClientID(){
        return Settings::get('zoom_client_id');
    }

    public static function getClientSecret(){
        return Settings::get('zoom_client_secret');
    }

    public static function getDealerClubMeeting(){
        return Settings::get('dealer_club_meeting_id');
    }

    public static function getAllowCronApprovement(){
        return Settings::get('start_cron');
    }

    public static function getDealerClubAccountId(){
        return Settings::get('zoom_account_for_cron');
    }

    public static function getRedirectUrl(){
        return Settings::get('zoom_redirect_url_for_auth');
    }

    public static function getAccessToken(){
        return Settings::get('zoom_access_token');
    }

    public static function getRefreshToken(){
        return Settings::get('zoom_refresh_token');
    }

    public static function setAccessToken($new_token){
        if($new_token)
            return Settings::set('zoom_access_token', $new_token);
        return false;
    }

    public static function setRefreshToken($refresh_token){
        if($refresh_token)
            return Settings::set('zoom_refresh_token', $refresh_token);
        return false;
    }

    public static function updateTokensFromResponse($zoom_acc_id, $response)
    {
        \Settings\ZoomAccount::setAccessToken($zoom_acc_id, $response['access_token']);
        \Settings\ZoomAccount::setRefreshToken($zoom_acc_id, $response['refresh_token']);
        return true;
    }

    public static function regenerateTokens($zoom_acc_id){
        $zoom_account = \Settings\ZoomAccount::find($zoom_acc_id);
        if(check_full_array($zoom_account)) {
            $client = new Client(['base_uri' => 'https://zoom.us']);
            $response = $client->request('POST', '/oauth/token', [
                "headers" => [
                    "Authorization" => "Basic " . base64_encode($zoom_account['PROPERTY_CLIENT_ID_VALUE'] . ':' . $zoom_account['PROPERTY_CLIENT_SECRET_VALUE'])
                ],
                'form_params' => [
                    "grant_type" => "refresh_token",
                    "refresh_token" => $zoom_account['PROPERTY_REFRESH_TOKEN_VALUE']
                ],
            ]);
            $array_response = json_decode($response->getBody()->getContents(), true);

            self::updateTokensFromResponse($zoom_acc_id, $array_response);
        }
    }

    public static function addEmployeeToMeetByShedule($user_id, $schedule_id){
        $schedules = Schedule::getById($schedule_id);
        $schedule = array_shift($schedules);
        if((int)$schedule['PROPERTIES']['ZOOM_ACCOUNT']>0) {
            $user = User::find($user_id, ['NAME', 'LAST_NAME', 'EMAIL', 'UF_ZOOM_LOGIN']);
            $login = $user['UF_ZOOM_LOGIN'] ?? $user['EMAIL'];
            if(!empty($schedule['PROPERTIES']['ZOOM_MEET_ID'])) {
                $fields = [
                    'first_name' => $user['NAME'],
                    'last_name' => $user['LAST_NAME'],
                    "email" => $login,
                    "auto_approve" => true,
                ];
                $response = self::addEmployeeToMeeting($schedule['PROPERTIES']['ZOOM_ACCOUNT'], $schedule['PROPERTIES']['ZOOM_MEET_ID'], $fields);
            }
        }
    }

    public static function me()
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $response = $client->get('/v2/me/meetings');
        dump(json_decode($response->getBody()));
    }

    public static function createMeetingFromShedule($schedule_fields){
        $schedules = Schedule::getById($schedule_fields['ID']);
        $schedule = current($schedules);
        if((int)$schedule['PROPERTIES']['ZOOM_ACCOUNT']>0) {
            $zoom_type = Courses::getZoomType($schedule['PROPERTIES']['COURSE']);
            $zoom_account = \Settings\ZoomAccount::find((int)$schedule['PROPERTIES']['ZOOM_ACCOUNT']);
            if(check_full_array($zoom_account)&&($zoom_type==16||$zoom_type==15)){
                if (!$schedule['ID'] > 0)
                    return false;
                $start = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
                $end = strtotime($schedule['PROPERTIES']['END_DATE'].' 23:59:59');
                $pass = \Helpers\StringHelpers::generatePassword(8);
                $approval_type = $zoom_type==16?1:2;
                $fields = [
                    'topic' => $schedule['NAME'],
                    "start_time" => Date::getFormatted($schedule['PROPERTIES']['BEGIN_DATE'], 'Y-m-d') . 'T' . Date::getFormatted($schedule['PROPERTIES']['BEGIN_DATE'], 'H:i:s').'Z',
                    "duration" => (int)(($end-$start)/60),
                    "password" => $pass,
                    "settings" => ["approval_type" => $approval_type],
                ];
                $response = self::createMeeting($zoom_account['ID'], $fields);
                self::updateSheduleZoomInfo($schedule_fields['ID'], $response);
            }
        }
    }

    public static function createMeeting($zoom_account_id, $fields){
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($zoom_account_id);
        if(check_full_array($zoom_account)&&!empty($zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'])) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('POST', '/v2/users/me/meetings', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token",
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens($zoom_account['ID']);
                    return self::createMeeting($zoom_account_id, $fields);
                } else {
                    echo $e->getCode();
                    echo $e->getMessage();
                }
            }
        }
    }

    private static function updateSheduleZoomInfo($schedule_id, $response)
    {
        \Teaching\SheduleCourses::setField($schedule_id, 'ZOOM_MEET_ID', $response->id);
        \Teaching\SheduleCourses::setField($schedule_id, 'ZOOM_LINK', $response->join_url);
        \Teaching\SheduleCourses::setField($schedule_id, 'ZOOM_PASSWORD', $response->password);
    }

    private static function addEmployeeToMeeting($zoom_account_id, $id, $fields)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($zoom_account_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('POST', '/v2/meetings/' . $id . '/registrants', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens($zoom_account_id);
                    return self::addEmployeeToMeeting($zoom_account_id, $id, $fields);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function getWebinar($id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9704);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/past_webinars/' . $id . '/participants', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                dump('/v2/webinar/' . $id . '/participants');
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9704);
                    dump('regenerate');
                    //return self::addEmployeeToMeeting(9704, $id, $fields);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function getMeeting($id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/meetings/' . $id, [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                dump('/v2/webinar/' . $id . '/participants');
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9704);
                    dump('regenerate');
                    //return self::addEmployeeToMeeting(9704, $id, $fields);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function getMeetingRegistrants($id, $account_id=0)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($account_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/meetings/' . $id . '/registrants?status=pending', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens($account_id);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function getWebinarRegistrants($id, $account_id=0)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($account_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/webinars/' . $id . '/registrants?status=pending', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens($account_id);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function approveRegistrant($meeting, $uid, $email){
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "approve",
                    'registrants' => [["email" => $email, "id" => $uid]],
                ];
                $response = $client->request('PUT', '/v2/meetings/' . $meeting . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function getWebinars()
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9704);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/me/webinars', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9704);
                    dump('regenerate');
                    //return self::addEmployeeToMeeting(9704, $id, $fields);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function denyRegistrant($meeting, $uid, $email)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "deny",
                    'registrants' => [["email" => $email, "id" => $uid]],
                ];
                $response = $client->request('PUT', '/v2/meetings/' . $meeting . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function approveRegistrantBatch($string, $users_to_approve)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "approve",
                    'registrants' => $users_to_approve,
                ];
                $response = $client->request('PUT', '/v2/meetings/' . $string . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function approveWebinarRegistrantBatch($string, $users_to_approve, $account_id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($account_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "approve",
                    'registrants' => $users_to_approve,
                ];
                $response = $client->request('PUT', '/v2/webinars/' . $string . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function denyRegistrantBatch($meeting, $users_to_deny)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "deny",
                    'registrants' => $users_to_deny,
                ];
                $response = $client->request('PUT', '/v2/meetings/' . $meeting . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function denyWebinarRegistrantBatch($meeting, $users_to_deny, $account_id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($account_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $fields = [
                    "action" => "deny",
                    'registrants' => $users_to_deny,
                ];
                $response = $client->request('PUT', '/v2/webinars/' . $meeting . '/registrants/status', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ],
                    'json' => $fields,
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    \Helpers\Log::write($e->getMessage());
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function getMeetingDenyRegistrants($id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find(9800);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/meetings/' . $id . '/registrants?status=denied&page_size=200', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                \Helpers\Log::write($e->getMessage());
                if (401 == $e->getCode()) {
                    self::regenerateTokens(9800);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }
    public static function getWebinarDenyRegistrants($id, $acc_id)
    {
        $client = new Client(['base_uri' => 'https://api.zoom.us']);
        $zoom_account = \Settings\ZoomAccount::find($acc_id);
        if (check_full_array($zoom_account)) {
            $access_token = $zoom_account['PROPERTY_ACCESS_TOKEN_VALUE'];
            try {
                $response = $client->request('GET', '/v2/webinars/' . $id . '/registrants?status=denied&page_size=200', [
                    "headers" => [
                        "Authorization" => "Bearer $access_token"
                    ]
                ]);
                return json_decode($response->getBody());
            } catch (\Exception $e) {
                \Helpers\Log::write($e->getMessage());
                if (401 == $e->getCode()) {
                    self::regenerateTokens($acc_id);
                } else {
                    echo $e->getMessage();
                }
            }
        }
    }

    public static function getEventType()
    {
        return Settings::get('zoom_event_type');
    }
}