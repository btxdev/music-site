<?php

require_once __DIR__.'/main.php';
require_once __DIR__.'/include_db.php';

$partner_tmp_image_dir = __dir__.'/../img/tmp/';
$partner_tmp_image_fname = 'partner.png';
$partners_images_dir = __dir__.'/../img/partners/';

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
    if($status->ok()) {
      return $status->returnValue;
    }
    else {
      sendAsJson($status);
    }
}

function requireFields($fields) {
    global $decoded;
    foreach($fields as $field) {
        if(!isset($decoded[$field])) sendAsJson([]);
    }
}

function create_dir($path) {
  if(!file_exists($path)) {
    mkdir($path, 0777, true);
  }
}

function remove_file($path) {
  if(file_exists($path)) {
    unlink($path);
  }
}

// check authorization
$session_name = $settings->get('session_name');
$session_hash = $access->getSessionCookie($session_name);
$current_uuid = $access->getUserIdBySessionHash($session_hash);
if(!$current_uuid) {
  $result = new Status('NOT_AUTHORIZED');
  sendAsJson($result);
}

if(isset($_POST['upload_partner_icon'])) {
  if(!isset($_FILES['image'])) {
    sendAsJson(new Status('ERROR'));
  }
  $dir = $partner_tmp_image_dir;
  create_dir($dir);
  $file = $dir.$partner_tmp_image_fname;
  if(move_uploaded_file($_FILES['image']['tmp_name'], $file)) {
    $result = new Status('OK');
  }
  else {
    $result = new Status('ERROR', ['debug' => $_FILES]);
  }
  sendAsJson($result);
}

if(isset($decoded['op'])) {

  if($decoded['op'] == 'add_partner') {
    requireFields(['text']);
    $text = processStatus($validate->text($decoded['text']));
    // add record to db
    $db->run(
        'INSERT INTO `partners` (`text`) VALUES (:the_text)',
        [':the_text' => $text]
    );
    // // get id of partner
    $data = $db->fetch('SELECT `partner_id` FROM `partners` ORDER BY `partner_id` DESC LIMIT 1');
    $id = intval($data['partner_id']);
    // // move file
    $dir = $partners_images_dir;
    $from = $partner_tmp_image_dir.$partner_tmp_image_fname;
    create_dir($dir);
    $to = $dir.'partner_'.strval($id).'.png';
    if(file_exists($from)) {
      rename($from, $to);
    }

    $result = new Status('OK');
    sendAsJson($result);
  }

  if($decoded['op'] == 'remove_partner') {
    requireFields(['partner_id']);
    $id = intval($decoded['partner_id']);
    // remove record from db
    $db->run(
        'DELETE FROM `partners` WHERE `partner_id` = :id',
        [':id' => $id]
    );
    // // remove file
    $dir = $partners_images_dir;
    create_dir($dir);
    remove_file($dir.'partner_'.strval($id).'.png');

    $result = new Status('OK');
    sendAsJson($result);
  }

}

?>