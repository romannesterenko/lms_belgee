<?php

class Database
{
    private string $host = 'u520251.mysql.masterhost.ru';
    private string $dbname = 'u520251_lms';
    private string $username = 'u520251';
    private string $password = '6_dEl2rIANte.';
    private PDO $pdo;
    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
        } catch (PDOException $pe) {
            die("Could not connect to the database $this->dbname :" . $pe->getMessage());
        }
    }
    public function showAllTables(){
        return $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    }
    public function query($query){
        return $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUncompletedTasks($limit = 0){
        $limit_string = '';
        if(is_int($limit)&&$limit>0)
            $limit_string = " LIMIT ".$limit;
        return $this->query("SELECT * FROM task_manager WHERE UF_COMPLETED=24".$limit_string);
    }
    public function getUncompletedAddTasks($user_id){
        return $this->query("SELECT * FROM task_manager WHERE UF_USER_ID=$user_id");
    }

    /*

    public function getChatByID($chat_id){
        return $this->query("SELECT * FROM tg_chat_links WHERE UF_COMPLETED=24".$limit_string);
    }*/

    public function getAllChats(){
        return $this->query("SELECT * FROM tg_chat_links");
    }

    public function deleteAllChats(){
        $this->query("DELETE FROM tg_chat_links");
    }

    public function deleteAllUsers(){
        $this->query("DELETE FROM tg_group_users");
    }
    public function getUserLogin($user_id){
        return current($this->query("SELECT UF_USER_LOGIN, ID FROM tg_group_users where UF_USER_ID = ".$user_id));
    }
    public function addChat($chat_id, $link, $type, $name, $coment=''){
        $this->query("INSERT INTO tg_chat_links (UF_CHAT_ID, UF_LINK, UF_TYPE, UF_NAME, UF_COMENT) VALUES ('$chat_id', '$link', '$type', '$name', '$coment')");
    }
    public function addTelegramUser($user_name, $user_login, $chat_id, $user_id, $role, $created_at)
    {
        $this->query("INSERT INTO tg_group_users (UF_USER_NAME, UF_USER_LOGIN, UF_CHAT_ID, UF_USER_ID, UF_ROLE, UF_CREATED_AT) VALUES ('$user_name', '$user_login', '$chat_id', '$user_id', '$role', $created_at)");
    }
    public function getTgIds(){
        $ids = [];
        foreach ($this->getUncompletedTasks() as $task){
            $ids[] = $task['UF_USER_ID'];
        }
        return $ids;
    }

    public function getRolesWithGroups(){
        //$test = $this->getTestChannel();
        if(!empty($test)) {
            $array[] = [
                'PROPERTY_65' => $test,
                'IBLOCK_ELEMENT_ID' => 385,
            ];
            return $array;
        } else {
            return $this->query("SELECT IBLOCK_ELEMENT_ID, PROPERTY_65 FROM b_iblock_element_prop_s5 where PROPERTY_65 IS NOT NULL");
        }
    }
    public function getTestChannel():string{
        $setting = current($this->query("SELECT NAME,VALUE FROM b_option where MODULE_ID='common.settings' and NAME='channel_for_test'"));
        return (string)$setting['VALUE'];
    }
    public function getLimitInvites():int{
        $setting = current($this->query("SELECT NAME, VALUE FROM b_option where MODULE_ID='common.settings' and NAME='limit_invites'"));
        return (int)$setting['VALUE'];
    }
    public function getTestRole(){
        $setting = current($this->query("SELECT NAME,VALUE FROM b_option where MODULE_ID='common.settings' and NAME='role_for_test'"));
        return (int)$setting['VALUE'];
    }
    public function isTestingTGMode(){
        $setting = current($this->query("SELECT NAME,VALUE FROM b_option where MODULE_ID='common.settings' and NAME='use_test_mode'"));
        return (string)$setting['VALUE']=='Y';
    }

    public function getTgUsersByGroup($group_id){
        return $this->query("SELECT VALUE_ID, UF_TELEGRAM FROM b_uts_user where UF_TELEGRAM is not null and LOCATE('".$group_id."', UF_ROLE)");
    }

    public function getTgUsersWithoutDealer()
    {
        return $this->query("SELECT VALUE_ID, UF_TELEGRAM FROM b_uts_user where UF_TELEGRAM is not null and UF_DEALER is null");
    }

    public function setCompleteTask(mixed $ID, $uncompleted_coment='')
    {
        $date = date('Y-m-d H:i:s');
        $sql = 'UPDATE task_manager SET UF_COMPLETED = 23, UF_COMENT = "'.$uncompleted_coment.'", UF_COMPLETED_AT = CAST("'.$date.'" AS DATETIME) WHERE ID = '.$ID;
        $this->query($sql);
    }

    public function setSendedTGRequestForUser($user_id)
    {
        $sql = "UPDATE b_uts_user SET UF_SENDED_TG_REQUEST = 1 WHERE VALUE_ID = ".$user_id;
        $this->query($sql);
    }
    public function removeSendedTGRequestForUser($user_id)
    {
        $sql = "UPDATE b_uts_user SET UF_SENDED_TG_REQUEST = 0 WHERE VALUE_ID = ".$user_id;
        $this->query($sql);
    }

    public function getUserIdByTGLogin($string = ''):int
    {
        if($string=='')
            return 0;
        $rows = $this->query("SELECT VALUE_ID FROM b_uts_user where UF_TELEGRAM LIKE '$string' limit 1");
        if(!is_array($rows)||count($rows)==0)
            return 0;
        return (int)$rows[0]['VALUE_ID'];
    }

    public function wasSentRequest($user_id):bool
    {
        $rows = $this->query("SELECT VALUE_ID FROM b_uts_user where VALUE_ID = $user_id and UF_SENDED_TG_REQUEST = 1");
        if(!is_array($rows)||count($rows)==0)
            return false;
        return $rows[0]['VALUE_ID']>0;
    }

    public function isSendedTGRequestForUser(int $user_id)
    {

    }

    public function getChatByID($chat_id)
    {
        return current($this->query("SELECT * FROM tg_chat_links WHERE UF_CHAT_ID=$chat_id"));
    }

    public function getTodayCompletedTasks()
    {
        return $this->query("SELECT * FROM task_manager WHERE UF_COMPLETED=23 and UF_COMPLETED_AT >= CURDATE()");

    }


}