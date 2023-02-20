<?php

    require_once __DIR__.'/main.php';
    require_once __DIR__.'/include_db.php';

    //

    $db->run('SET FOREIGN_KEY_CHECKS = 0;');
    $db->run('SET UNIQUE_CHECKS = 0;');

    // создание сущностей

    // создание таблицы users
    $db->run('DROP TABLE IF EXISTS `users`;');
    $db->run(
        'CREATE TABLE `users` (
            `uuid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
            `username` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
            `password` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
            `salt` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
            `first_name` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
            `second_name` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
            `email` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
            `reg_date` DATE NOT NULL ,
            PRIMARY KEY (`uuid`) ,
            UNIQUE `username_index` (`username`(32))
        )
        ENGINE = InnoDB'
    );

    // сущности необходимые для авторизации

    // создание таблицы roles
    $db->run('DROP TABLE IF EXISTS `roles`;');
    $db->run(
        'CREATE TABLE `roles` (
            `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
            `role` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
            PRIMARY KEY (`role_id`)
        )
        ENGINE = InnoDB;'
    );

    // создание таблицы sessions
    $db->run('DROP TABLE IF EXISTS `sessions`;');
    $db->run(
        'CREATE TABLE `sessions` (
            `sesshash` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
            `uuid` INT UNSIGNED NOT NULL ,
            `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            PRIMARY KEY (`sesshash`(32)),
            INDEX `uuid` (`uuid`)
        )
        ENGINE = InnoDB;'
    );

    // создание таблицы songs
    $db->run('DROP TABLE IF EXISTS `songs`;');
    $db->run(
        'CREATE TABLE `songs` (
          `song_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
          `artist` TINYTEXT NOT NULL ,
          `title` TINYTEXT NOT NULL ,
          `album` TINYTEXT NULL DEFAULT NULL ,
          `lyrics` TEXT NULL DEFAULT NULL ,
          `song_link` TINYTEXT NULL DEFAULT NULL ,
          `submitted_by` TINYTEXT NOT NULL ,
          `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
          PRIMARY KEY (`song_id`)
        )
        ENGINE = InnoDB;'
    );

    // создание таблицы yt_songs
    $db->run('DROP TABLE IF EXISTS `yt_songs`;');
    $db->run(
        'CREATE TABLE `yt_songs` (
          `song_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
          `artist` TINYTEXT NOT NULL ,
          `link` TINYTEXT NOT NULL ,
          `about` TINYTEXT NULL DEFAULT NULL ,
          `email` TINYTEXT NULL NOT NULL ,
          `submitted_by` TINYTEXT NOT NULL ,
          `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
          PRIMARY KEY (`song_id`)
        )
        ENGINE = InnoDB;'
    );

    // создание таблицы categories
    $db->run('DROP TABLE IF EXISTS `categories`;');
    $db->run(
        'CREATE TABLE `categories` (
          `cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
          `title` TINYTEXT NOT NULL ,
          `link` TINYTEXT NOT NULL ,
          PRIMARY KEY (`cat_id`)
        )
        ENGINE = InnoDB;'
    );

    // создание таблицы partners
    $db->run('DROP TABLE IF EXISTS `partners`;');
    $db->run(
        'CREATE TABLE `partners` (
          `partner_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
          `text` TINYTEXT NOT NULL ,
          PRIMARY KEY (`partner_id`)
        )
        ENGINE = InnoDB;'
    );

    // связь users и sessions
    $db->run(
        'ALTER TABLE `sessions` ADD FOREIGN KEY (`uuid`)
        REFERENCES `users`(`uuid`) ON DELETE CASCADE ON UPDATE CASCADE;'
    );

    // организация связей
    $admin->relation_1N('roles', 'users', 'role_id', 'uuid');
    $admin->relation_NN('songs', 'categories', 'song_id', 'cat_id');
    //

    $db->run('SET FOREIGN_KEY_CHECKS = 1;');
    $db->run('SET UNIQUE_CHECKS = 1;');

    // создание ролей
    $admin->createRole('admin');
    $admin->createRole('default');

    // создание пользователей
    $admin_uuid = $admin->createUser('admin', 'vB2kyaM2L5LJtXMz');

    // присвоение роли admin пользователю admin
    $admin->setRoleToUser('admin', $admin_uuid);

    echo('ok');

?>