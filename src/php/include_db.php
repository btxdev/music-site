<?php

    require_once __DIR__.'/main.php';
    require_once __DIR__.'/class/Database.php';
    require_once __DIR__.'/class/Admin.php';
    require_once __DIR__.'/class/Access.php';
    require_once __DIR__.'/class/Validate.php';

    $db = new Database($settings->get('db_host'), $settings->get('db_dbname'),
        $settings->get('db_user'), $settings->get('db_password'), $settings->get('db_encoding'));

    $admin = new Admin($db->instance);

    $access = new Access($db->instance, $settings->get('session_name'));

    $validate = new Validate();

?>