<?php

require_once __DIR__.'/main.php';
require_once __DIR__.'/include_db.php';

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
function get_ip() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
      $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}

if(isset($decoded['op'])) {


    if($decoded['op'] == 'submit_lyrics') {

        requireFields(['artist', 'title', 'album', 'lyrics', 'href']);

        $artist = processStatus($validate->song($decoded['artist']));
        $title = processStatus($validate->song($decoded['title']));
        $album = processStatus($validate->song($decoded['album']));
        $lyrics = processStatus($validate->lyrics($decoded['lyrics']));
        $song_link = processStatus($validate->song($decoded['href']));
        $submitted_by = get_ip();

        $data = $db->fetch(
            'INSERT INTO `songs` (`artist`, `title`, `album`, `lyrics`, `song_link`, `submitted_by`)
            VALUES (:artist, :title, :album, :lyrics, :song_link, :submitted_by)',
            [
                ':artist' => $artist,
                ':title' => $title,
                ':album' => $album,
                ':lyrics' => $lyrics,
                ':song_link' => $song_link,
                ':submitted_by' => $submitted_by
            ]
        );

        $res = new Status('OK');
        sendAsJson($res);

    }

    if($decoded['op'] == 'submit_lyrics_yt') {

        requireFields(['artist', 'href', 'about', 'email']);

        $artist = processStatus($validate->song($decoded['artist']));
        $href = processStatus($validate->song($decoded['href']));
        $about = processStatus($validate->text($decoded['about']));
        $email = processStatus($validate->email($decoded['email']));
        $submitted_by = get_ip();

        // var_dump($artist);
        // var_dump($href);
        // var_dump($about);
        // var_dump($email);
        // var_dump($submitted_by);

        $data = $db->fetch(
            'INSERT INTO `yt_songs` (`artist`, `link`, `about`, `email`, `submitted_by`)
            VALUES (:artist, :link, :about, :email, :submitted_by)',
            [
                ':artist' => $artist,
                ':link' => $href,
                ':about' => $about,
                ':email' => $email,
                ':submitted_by' => $submitted_by
            ]
        );

        $res = new Status('OK');
        sendAsJson($res);

    }

    if($decoded['op'] == 'get_partners') {
        $data = $db->run('SELECT `partner_id`, `text` FROM `partners`');
        $rows = $data->fetchAll();
        $res = new Status('OK', ['msg' => $rows]);
        sendAsJson($res);
    }

    if($decoded['op'] == 'get_categories') {
        $data = $db->run('SELECT * FROM `categories`');
        $rows = $data->fetchAll();
        $res = new Status('OK', ['msg' => $rows]);
        sendAsJson($res);
    }

    if($decoded['op'] == 'get_category_data') {
        requireFields(['url']);
        $href = htmlspecialchars($decoded['url']);
        // get category id and title
        $data = $db->fetch('SELECT `cat_id`, `title` FROM `categories` WHERE `link` = :link LIMIT 1',
        [
          ':link' => $href
        ]
        );
        // empty
        if(!$data) {
          sendAsJson(new Status('EMPTY'));
          exit();
        }
        $id = intval($data['cat_id']);
        $title = $data['title'];
        // get songs from category
        $rows = $db->fetchAll('SELECT * FROM `songs_categories` WHERE `cat_id` = :cat_id',
        [
          ':cat_id' => $id
        ]
        );
        // output
        $res = new Status('OK', ['msg' => [
          'cat_id' => $id,
          'title' => $title,
          'songs' => $rows
        ]]);
        sendAsJson($res);
    }


}

$result = new Status('EMPTY');
sendAsJson($result);

?>