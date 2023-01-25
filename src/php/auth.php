<?php

include_once __DIR__.'/main.php';
include_once __DIR__.'/include_db.php';

$decoded = [];

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($contentType === "application/json") {
    // получение данных POST формы
    $content = trim(file_get_contents("php://input"));

    $decoded = json_decode($content, true);

    // ошибка обработки JSON
    if(! is_array($decoded)) {
        exit(emptyJson());
    }
}

function emptyJson() {
    return json_encode (json_decode ("{}"));
}
function processStatus($status) {
    if($status->ok())
        return $status->returnValue;
    else
        exit($status->json());
}
function requireFields($fields) {
    global $decoded;
    foreach($fields as $field) {
        if(!isset($decoded[$field])) exit(emptyJson());
    }
}

if(isset($decoded['op'])) {

    if($decoded['op'] == 'login') {

        requireFields(['login', 'password']);

        $login = processStatus($validate->login($decoded['login']));
        $password = processStatus($validate->password($decoded['password']));
        
        $loginResult = $access->login($login, $password);
        if($loginResult->ok()) {
            $hash = $loginResult->session;
            $result = $access->setSessionCookie($settings->get('session_name'), $hash);
            exit($result->json());
        }
        else {
            exit($loginResult->json());
        }
    }

    if($decoded['op'] == 'register') {
        
    }

    if($decoded['op'] == 'logout') {
        $session = $access->getSessionCookie($settings->get('session_name'));
        if($session != 'none') {
            $id = $access->getUserIdBySessionHash($session);
            if($id != false) {
                // авторизован
                try {
                    $access->removeAccessFrom($session);
                    $status = new Status('OK');
                }
                catch(Exception $e) {
                    $status = new Status('ERROR');
                }
                exit($status->json());
            }
        }
        exit(emptyJson());
    }

}

?>