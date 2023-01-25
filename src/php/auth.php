<?php

include_once __DIR__.'/main.php';
include_once __DIR__.'/include_db.php';

$decoded = [];

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

function sendAsJson($data) {
  header("Content-type: application/json; charset=utf-8");
  exit($data->json());
}

if ($contentType === "application/json") {
    // получение данных POST формы
    $content = trim(file_get_contents("php://input"));

    $decoded = json_decode($content, true);

    // ошибка обработки JSON
    if(! is_array($decoded)) {
        sendAsJson([]);
    }
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

  // проверка доступа
    if($decoded['op'] == 'check') {
      $session_name = $settings->get('session_name');
      $session_hash = $access->getSessionCookie($session_name);
      $current_uuid = $access->getUserIdBySessionHash($session_hash);
      if($current_uuid) {
          $result = new Status('AUTHORIZED');
          sendAsJson($result);
      }
      else {
          $result = new Status('NOT_AUTHORIZED');
          sendAsJson($result);
      }

    }

    if($decoded['op'] == 'login') {

        requireFields(['login', 'password']);

        $login = processStatus($validate->login($decoded['login']));
        $password = processStatus($validate->password($decoded['password']));

        $loginResult = $access->login($login, $password);
        if($loginResult->ok()) {
            $hash = $loginResult->session;
            $result = $access->setSessionCookie($settings->get('session_name'), $hash);
            sendAsJson($result);
        }
        else {
            sendAsJson($loginResult);
        }
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
                sendAsJson($status);
            }
        }
        sendAsJson([]);
    }

}

?>