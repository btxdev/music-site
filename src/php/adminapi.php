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

  if($decoded['op'] == 'add_category') {
    requireFields(['title', 'link']);
    $title = $decoded['title'];
    $link = $decoded['link'];
    // add record to db
    $db->run(
        'INSERT INTO `categories` (`title`, `link`) VALUES (:title, :link)',
        [
          ':title' => $title,
          ':link' => $link
        ]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

  if($decoded['op'] == 'remove_category') {
    requireFields(['cat_id']);
    $id = intval($decoded['cat_id']);
    // remove record from db
    $db->run(
        'DELETE FROM `categories` WHERE `cat_id` = :id',
        [':id' => $id]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

  if($decoded['op'] == 'edit_category') {
    requireFields(['cat_id', 'title', 'link']);
    $id = intval($decoded['cat_id']);
    $title = $decoded['title'];
    $link = $decoded['link'];
    // update record in db
    $db->run(
        'UPDATE `categories` SET `title` = :title, `link` = :link WHERE `cat_id` = :id',
        [
          ':id' => $id,
          ':title' => $title,
          ':link' => $link
        ]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

  if($decoded['op'] == 'add_song_to_category') {
    requireFields(['cat_id', 'song_id']);
    $cat_id = intval($decoded['cat_id']);
    $song_id = intval($decoded['song_id']);
    // add record to db
    $db->run(
        'INSERT INTO `songs_categories` (`song_id`, `cat_id`) VALUES (:song_id, :cat_id)',
        [
          ':song_id' => $song_id,
          ':cat_id' => $cat_id
        ]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

  if($decoded['op'] == 'remove_song_from_category') {
    requireFields(['cat_id', 'song_id']);
    $cat_id = intval($decoded['cat_id']);
    $song_id = intval($decoded['song_id']);
    // add record to db
    $db->run(
        'DELETE FROM `songs_categories` WHERE `song_id` LIKE :song_id AND `cat_id` LIKE :cat_id',
        [
          ':song_id' => $song_id,
          ':cat_id' => $cat_id
        ]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

  // remove song permanently
  if($decoded['op'] == 'remove_song') {
    requireFields(['song_id']);
    $song_id = intval($decoded['song_id']);
    // add record to db
    $db->run(
        'DELETE FROM `songs` WHERE `song_id` LIKE :song_id',
        [
          ':song_id' => $song_id
        ]
    );

    $result = new Status('OK');
    sendAsJson($result);
  }

}

?>
