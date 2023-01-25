<?php

include_once __DIR__.'/main.php';
include_once __DIR__.'/include_db.php';

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
        if(!isset($decoded[$field])) {
            var_dump($decoded);
            exit(emptyJson());
        }
    }
}

// проверка доступа
$session_name = $settings->get('session_name');
$session_hash = $access->getSessionCookie($session_name);
$current_uuid = $access->getUserIdBySessionHash($session_hash);
if($current_uuid == false) {
    $result = new Status('NOT_AUTHORIZED');
    exit($result->json());
}

if(isset($decoded['op'])) {

    // === базовая информация ===
    if($decoded['op'] == 'basic_info') {

        // получить всю информацию о текущем пользователе
        $rows = $db->fetch(
            'SELECT * FROM `users` WHERE `uuid` = :uuid',
            [
                ':uuid' => $current_uuid
            ]
        );
        $data = [
            'status' => 'OK',
            'uuid' => $current_uuid,
            'username' => $rows['username'],
            'first_name' => $rows['first_name'],
            'second_name' => $rows['second_name'],
            'patronymic' => $rows['patronymic'],
            'phone' => $rows['phone'],
            'email' => $rows['email']
        ];
        exit(json_encode($data));
    }

    // === категории ===

    if($decoded['op'] == 'add_category') {

        requireFields(['title', 'link']);
        
        // $titleStatus = $validate->name($decoded['title']);
        // if($titleStatus->ok()) {
        //     $title = $titleStatus->returnValue;
        // }
        // else {
        //     $err = new Status('WRONG_FORMAT');
        //     exit($err->json());
        // }

        $title = $decoded['title'];
        $link = $decoded['link'];

        $data = $db->fetch(
            'INSERT INTO `categories` (title, icon) 
            VALUES (:title, :icon)',
            [
                ':title' => $title,
                ':icon' => $link
            ]
        );

        $res = new Status('OK', $title);
        exit($res->json());

    }

    if($decoded['op'] == 'get_categories') {
        $rows = $db->fetchAll('SELECT * FROM `categories`');
        $cats = [];
        foreach ($rows as $row) {
            array_push($cats, [
               'id' => $row['category_id'],
               'title' => $row['title'] 
            ]);
        }
        $data = [
            'status' => 'OK',
            'categories' => $cats
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'get_category_data') {

        requireFields(['id']);

        $row = $db->fetch(
            'SELECT * FROM `categories` 
            WHERE `category_id` = :id',
            [
                ':id' => $decoded['id']
            ]
        );
        $data = [
            'status' => 'OK',
            'data' => [
                'category_id' => $row['category_id'],
                'title' => $row['title'],
                'icon' => $row['icon']
            ]
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'remove_category') {

        requireFields(['id']);

        $rows = $db->fetchAll(
            'DELETE FROM `categories` 
            WHERE `category_id` = :id',
            [
                ':id' => $decoded['id']
            ]
        );

        $result = new Status('OK');
        exit(json_encode($result));
    }

    if($decoded['op'] == 'edit_category') {

        requireFields(['id', 'title', 'link']);

        $rows = $db->fetchAll(
            'UPDATE `categories` 
            SET `title` = :title, `icon` = :icon
            WHERE `category_id` = :id',
            [
                ':id' => $decoded['id'],
                ':title' => $decoded['title'],
                ':icon' => $decoded['link']
            ]
        );

        $result = new Status('OK');
        exit(json_encode($result));
    }

    // === товары ===
    
    if($decoded['op'] == 'add_item') {

        requireFields(['title', 'description', 'link', 'price', 'count']);

        $title = $validate->std($decoded['title']);
        $description = mb_substr($validate->std($decoded['description']), 0, 100);
        $link = $validate->std($decoded['link']);
        $price = $validate->std($decoded['price']);
        $count = $validate->std($decoded['count']);

        $data = $db->fetch(
            'INSERT INTO `items` (`title`, `description`, `image`, `count`, `price`) 
            VALUES (:title, :description, :image, :count, :price)',
            [
                ':title' => $title,
                ':description' => $description,
                ':image' => $link,
                ':count' => $count,
                ':price' => $price
            ]
        );

        $res = new Status('OK', $title);
        exit($res->json());

    }

    if($decoded['op'] == 'get_items') {
        $rows = $db->fetchAll('SELECT * FROM `items`');
        $items = [];
        foreach ($rows as $row) {
            array_push($items, [
               'id' => $row['item_id'],
               'title' => $row['title'],
               'description' => $row['description'],
               'link' => $row['image'],
               'count' => $row['count'],
               'price' => $row['price']
            ]);
        }
        $data = [
            'status' => 'OK',
            'items' => $items
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'get_item_data') {

        requireFields(['id']);

        $row = $db->fetch(
            'SELECT * FROM `items` 
            WHERE `item_id` = :id',
            [
                ':id' => $decoded['id']
            ]
        );
        $data = [
            'status' => 'OK',
            'data' => [
                'id' => $row['item_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'link' => $row['image'],
                'count' => $row['count'],
                'price' => $row['price']
            ]
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'remove_item') {

        requireFields(['id']);

        $rows = $db->fetchAll(
            'DELETE FROM `items` 
            WHERE `item_id` = :id',
            [
                ':id' => $decoded['id']
            ]
        );

        $result = new Status('OK');
        exit(json_encode($result));
    }

    if($decoded['op'] == 'edit_item') {

        requireFields(['id', 'title', 'description', 'link', 'price', 'count']);

        $title = $validate->std($decoded['title']);
        $description = mb_substr($validate->std($decoded['description']), 0, 100);
        $link = $validate->std($decoded['link']);
        $price = $validate->std($decoded['price']);
        $count = $validate->std($decoded['count']);

        $rows = $db->fetchAll(
            'UPDATE `items` 
            SET `title` = :title, 
            `description` = :description,
            `image` = :image,
            `price` = :price,
            `count` = :count
            WHERE `item_id` = :id',
            [
                ':id' => $decoded['id'],
                ':title' => $title,
                ':description' => $description,
                ':image' => $link,
                ':price' => $price,
                ':count' => $count
            ]
        );

        $result = new Status('OK');
        exit(json_encode($result));
    }

    // === заказы ===

    if($decoded['op'] == 'get_orders') {
        $rows = $db->fetchAll("SELECT * FROM `orders` WHERE `status` = 'wait'");
        $orders = [];
        foreach ($rows as $row) {
            array_push($orders, [
               'id' => $row['order_id'],
               'status' => $row['status'],
               'order_date' => $row['order_date']
            ]);
        }
        $data = [
            'status' => 'OK',
            'orders' => $orders
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'get_item_data') {

        requireFields(['id']);

        $row = $db->fetch(
            'SELECT * FROM `items` 
            WHERE `item_id` = :id',
            [
                ':id' => $decoded['id']
            ]
        );
        $data = [
            'status' => 'OK',
            'data' => [
                'id' => $row['item_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'link' => $row['image'],
                'count' => $row['count'],
                'price' => $row['price']
            ]
        ];
        exit(json_encode($data));
    }

    if($decoded['op'] == 'remove_order') {

        requireFields(['id']);

        $rows = $db->fetchAll(
            "UPDATE `orders` SET `status` = 'complete' WHERE `order_id` = :id", 
            [
                ':id' => $decoded['id']
            ]
        );

        $result = new Status('OK');
        exit($result->json());
    }

    // === пользователи ===

    if($decoded['op'] == 'get_users') {
        $rows = $db->fetchAll("SELECT * FROM `users`");
        $users = [];
        foreach ($rows as $row) {
            array_push($users, [
                'id' => $row['uuid'],
                'username' => $row['username'],
                'first_name' => $row['first_name'],
                'second_name' => $row['second_name'],
                'patronymic' => $row['patronymic'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'reg_date' => $row['reg_date']
            ]);
        }
        $result = new Status('OK', ['msg' => $users]);
        exit($result->json());
    }

    // === отправка писем ===
    if($decoded['op'] == 'send_mail') {
        $id = $decoded['id'];
        $email = $decoded['email'];

        $name = $db->fetch("SELECT first_name FROM users WHERE uuid = :uuid", ['uuid' => $id])['first_name'];

        $subject = $name.', Ваш заказ готов к выдаче!';
        //$msg_text = $name.', мы готовы выдать Ваш заказ. Заказ доступен по адресу: ул. Колтушкина, офис 42, 614000. Спасибо, что покупаете у нас!';
        $msg_text = $name.', мы готовы выдать Ваш заказ. Спасибо, что покупаете у нас!<br><br><br>';

        include_once('mail.php');
        $message = generate_mail_html($msg_text);
        $mailer_result = send_mail($email, $subject, $message);

        if($mailer_result['status']) {
            $result = new Status('OK');
            exit($result->json());
        }
        else {
            $result = json_encode([
                'status' => 'ERROR',
                'msg' => $mailer_result['msg']
            ]);
            exit($result);
        }
        
    }

    // // === заказы ===

    // if($decoded['op'] == 'get_orders') {
    //     $rows = $db->fetchAll("SELECT * FROM `orders` WHERE `status` = 'wait'");
    //     $orders = [];
    //     foreach ($rows as $row) {
    //         array_push($orders, [
    //             'id' => $row['order_id'],
    //             'date' => $row['order_date']
    //         ]);
    //     }
    //     $result = new Status('OK', ['msg' => $orders]);
    //     exit($result->json());
    // }

    // if($decoded['op'] == 'remove_order') {

    //     requireFields(['id']);

    //     $rows = $db->fetchAll(
    //         "UPDATE `orders` SET `status` = 'complete' WHERE `order_id` = :id", 
    //         [
    //             ':id' => $id
    //         ]
    //     );

    //     $result = new Status('OK');
    //     exit($result->json());
    // }

}





?>