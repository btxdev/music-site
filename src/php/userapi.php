<?php

require_once __DIR__.'/main.php';
require_once __DIR__.'/include_db.php';

$decoded = [];

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

function sendAsJson($data) {
  header("Content-type: application/json; charset=utf-8");
  exit($data->json());
}

function clamp($num, $min, $max) {
  return min(max($num, $min), $max);
}

function get_songs_from_category($category_id) {
  global $db;
  return $db->fetchAll('SELECT
    `songs_categories`.`song_id`,
    `songs`.`artist`,
    `songs`.`title`,
    `songs`.`album`
      FROM `songs_categories`
      JOIN `songs` ON `songs`.`song_id` = `songs_categories`.`song_id`
      WHERE `songs_categories`.`cat_id` = :cat_id',
  [
    ':cat_id' => $category_id
  ]
  );
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

        $songs = get_songs_from_category($id);
        // output
        $res = new Status('OK', ['msg' => [
          'cat_id' => $id,
          'title' => $title,
          'songs' => $songs
        ]]);
        sendAsJson($res);
    }

    if($decoded['op'] == 'search') {
        requireFields(['query']);
        $user_query = htmlspecialchars($decoded['query']);

        $count = 10;
        if(isset($decoded['count'])) {
          $count = clamp(intval($decoded['count']), 10, 50);
        }

        $page = 1;
        if(isset($decoded['page'])) {
          $page = clamp(intval($decoded['page']), 1, 65535);
        }

        $offset = ($page - 1) * $count;

        $query_1 = " SOUNDEX(`artist`) LIKE SOUNDEX('$user_query') OR SOUNDEX(`title`) LIKE SOUNDEX('$user_query') ";
        $query_2 = " `artist` LIKE '%$user_query%' OR `title` LIKE '%$user_query%' ";
        $query_3 = " CONCAT(`artist`, ' ', `title`) LIKE '%$user_query%' OR CONCAT(`artist`, ' - ', `title`) LIKE '%$user_query%' ";
        $query_4 = " `lyrics` LIKE '%$user_query%' ";
        $query = " WHERE $query_1 OR $query_2 OR $query_3 OR $query_4 ";
        $sql = "SELECT `song_id`, `artist`, `title`, `album`, SUBSTRING(`lyrics`, 1, 256) AS `lyrics` FROM `songs` $query LIMIT :offset, :count";

        $rows = $db->fetchAll($sql, [
          ':offset' => $offset,
          ':count' => $count
        ]);

        // output
        $res = new Status('OK', ['msg' => $rows]);
        sendAsJson($res);
    }

    if($decoded['op'] == 'get_song_data') {
        requireFields(['url']);
        $url = htmlspecialchars($decoded['url']);
        $args = explode(' - ', $url, 2);
        if(count($args) != 2) {
          sendAsJson(new Status('WRONG_FORMAT'));
          exit();
        }
        $artist = $args[0];
        $title = $args[1];
        // search song
        $data = $db->fetch('SELECT `song_id`, `artist`, `title`, `album`, `lyrics`, `song_link` FROM `songs` WHERE `artist` = :artist AND `title` = :title LIMIT 1',
        [
          ':artist' => $artist,
          ':title' => $title
        ]
        );
        // empty
        if(empty($data)) {
          sendAsJson(new Status('EMPTY'));
          exit();
        }
        $song_id = intval($data['song_id']);
        // get category of this song
        $category_id_rows = $db->fetchAll('SELECT `cat_id` FROM `songs_categories` WHERE `song_id` = :song_id',
          [
            ':song_id' => $song_id
          ]
        );
        // get other songs from this category
        $prev_song = '';
        $next_song = '';
        $categories = [];
        if(!empty($category_id_rows)) {
          // get previous and next categories
          $category_id = intval($category_id_rows[0]['cat_id']);
          $songs = get_songs_from_category($category_id);
          $prev_idx = -1;
          $next_idx = -1;
          $songs_count = count($songs);
          for($i = 0; $i < $songs_count; $i++) {
            $song = $songs[$i];
            if($song['song_id'] == $song_id) {
              $prev_idx = $i - 1;
              $next_idx = $i + 1;
              break;
            }
          }
          if($prev_idx >= 0) {
            $prev_song = $songs[$prev_idx]['artist'].' - '.$songs[$prev_idx]['title'];
          }
          if($next_idx < $songs_count) {
            $next_song = $songs[$next_idx]['artist'].' - '.$songs[$next_idx]['title'];
          }
          // get info about categories
          foreach($category_id_rows as $record) {
            $cat_id = $record['cat_id'];
            $cat_info = $db->fetch('SELECT `link` FROM `categories` WHERE `cat_id` = :cat_id LIMIT 1',
              [
                ':cat_id' => $cat_id
              ]
            );
            if(!empty($cat_info)) {
              array_push($categories, $cat_info['link']);
            }
          }
        }
        // output
        $res = new Status('OK', ['msg' => [
          'song_id' => $song_id,
          'artist' => $artist,
          'title' => $title,
          'album' => $data['album'],
          'lyrics' => $data['lyrics'],
          'href' => $data['song_link'],
          'categories' => $categories,
          'prev_song' => $prev_song,
          'next_song' => $next_song
        ]]);
        sendAsJson($res);
    }

    if($decoded['op'] == 'get_artist_data') {
        requireFields(['artist']);
        $artist = htmlspecialchars($decoded['artist']);
        // get category id and title
        $data = $db->fetchAll('SELECT `song_id`, `artist`, `title` FROM `songs` WHERE `artist` = :artist',
        [
          ':artist' => $artist
        ]
        );
        // empty
        if(empty($data)) {
          sendAsJson(new Status('EMPTY'));
          exit();
        }
        // output
        $res = new Status('OK', ['msg' => $data]);
        sendAsJson($res);
    }

}

$result = new Status('EMPTY');
sendAsJson($result);

?>
