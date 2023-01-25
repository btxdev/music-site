<?php

// класс расширяет функционал класса Database для работы с БД
// класс предоставляет методы для управления сайтом

include_once __DIR__.'/Database.php';

class Admin extends Database {

    function __construct($instance) {
        $this->instance = $instance;
    }

    function relation_alter($new_table, $table1, $table2, $key1, $key2) {
        // установка связи между таблицами table1 и table2_table1 по индексу key1
        $this->run(
            "ALTER TABLE `{$new_table}` ADD FOREIGN KEY (`{$key1}`) 
            REFERENCES `{$table1}`(`{$key1}`) ON DELETE CASCADE ON UPDATE CASCADE;"
        );
        // установка связи между таблицами table2 и table2_table1 по индексу key2
        $this->run(
            "ALTER TABLE `{$new_table}` ADD FOREIGN KEY (`{$key2}`) 
            REFERENCES `{$table2}`(`{$key2}`) ON DELETE CASCADE ON UPDATE CASCADE;"
        );
    }

    // связь один к одному
    function relation_11($table1, $table2, $key1, $key2) {

        $new_table  = $table1.'_'.$table2;
        $this->dropTable($new_table);

        // создание таблицы
        $this->run(
            "CREATE TABLE `{$new_table}` ( 
                `{$key1}` INT UNSIGNED NOT NULL , 
                `{$key2}` INT UNSIGNED NOT NULL , 
                PRIMARY KEY (`{$key1}`) , 
                INDEX `{$key2}`(`{$key2}`)
            )
            ENGINE = InnoDB;",
        );

        // установка связей
        $this->relation_alter($new_table, $table1, $table2, $key1, $key2);

    }

    // связь один ко многим
    function relation_1N($table1, $table2, $key1, $key2) {

        $new_table  = $table1.'_'.$table2;
        $this->dropTable($new_table);

        // создание таблицы
        $this->run(
            "CREATE TABLE `{$new_table}` ( 
                `{$key1}` INT UNSIGNED NOT NULL , 
                `{$key2}` INT UNSIGNED NOT NULL , 
                PRIMARY KEY (`{$key2}`)
            ) 
            ENGINE = InnoDB;",
        );

        // установка связей
        $this->relation_alter($new_table, $table1, $table2, $key1, $key2);
        
    }

    // связь многие ко многим
    function relation_NN($table1, $table2, $key1, $key2) {

        $new_table  = $table1.'_'.$table2;
        $index_name = $key1.'_'.$key2;
        $this->dropTable($new_table);

        // создание таблицы
        $this->run(
            "CREATE TABLE `{$new_table}` ( 
                `{$key1}` INT UNSIGNED NOT NULL , 
                `{$key2}` INT UNSIGNED NOT NULL , 
                INDEX `{$index_name}` (`{$key1}`, `{$key2}`)
            ) 
            ENGINE = InnoDB;",
        );

        // установка связей
        $this->relation_alter($new_table, $table1, $table2, $key1, $key2);

        // // установка связи между таблицами table1 и table2_table1 по индексу key1
        // $this->run(
        //     "ALTER TABLE `{$new_table}` ADD CONSTRAINT `{$key1}` FOREIGN KEY (`{$key1}`) 
        //     REFERENCES `{$table1}`(`{$key1}`) ON DELETE CASCADE ON UPDATE CASCADE;"
        // );
        // // установка связи между таблицами table2 и table2_table1 по индексу key2
        // $this->run(
        //     "ALTER TABLE `{$new_table}` ADD CONSTRAINT `{$key2}` FOREIGN KEY (`{$key2}`) 
        //     REFERENCES `{$table2}`(`{$key2}`) ON DELETE CASCADE ON UPDATE CASCADE;"
        // );

    }

    // получить id пользователя по его username
    function getUserId($username) {
        $rows = $this->fetch(
            'SELECT `uuid` FROM users WHERE username=?',
            [$username]
        );
        if($rows == false) return false;
        else {
            return intval($rows['uuid']);
        }
    }

    // создать пользователя (с паролем)
    function createUser($username, $password) {
        // игнор, если пользователь уже существует
        if($this->getUserId($username) != false) {
            return false;
        }
        $salt = $this->salt();
        // создание записи в БД
        $this->run(
            'INSERT INTO users 
            (username, password, salt, reg_date)
            VALUES (:username, :password, :salt, :reg_date)',
            [
                ':username' => $username,
                ':password' => $this->hashPassword($password, $salt),
                ':salt' => $salt,
                ':reg_date' => date('Y-m-d')
            ]
        );
        // id созданного пользователя
        $id = $this->getUserId($username);
        return $id;
    }

    // посолить пароль
    function saltPassword($password, $salt) {
        return hash_hmac('sha256', $password, $salt);
    }

    // поперчить пароль
    function pepperPassword($password) {
        return hash_hmac('sha256', $password, $this->pepper());
    }

    // захешировать пароль с солью и перцем
    function hashPassword($password, $salt) {
        $salted = $this->saltPassword($password, $salt);
        $peppered = $this->pepperPassword($salted);
        return $peppered;
    }

    // сверить пароль с хешем
    function verifyPassword($password, $salt, $hash) {
        $salted = $this->saltPassword($password, $salt);
        $peppered = $this->pepperPassword($salted);
        return ($peppered == $hash);
    }

    // удалить пользователя
    function removeUser($uuid) {
        // удаление записи
        $this->run('DELETE IGNORE FROM users WHERE uuid=?', [$uuid]);
    }

    // создать роль
    function createRole($role) {
        // создание записи в БД
        $this->run('INSERT IGNORE INTO roles (role) VALUES (?)', [$role]);
    }

    // удалить роль
    function removeRole($role) {
        // удаление записи
        $this->run('DELETE IGNORE FROM roles WHERE role=?', [$role]);
    }

    // установить роль пользователю
    function setRoleToUser($role, $uuid) {
        // получение id роли
        $rows = $this->fetch('SELECT `role_id` FROM roles WHERE role=?', [$role]);
        if($rows == false) {
            throw new Exception("Указанная роль '$role' не существует");
        }
        $role_id = intval($rows['role_id']);
        // создание записи в БД
        $this->run(
            'INSERT IGNORE INTO roles_users
            (uuid, role_id)
            VALUES (:uuid, :role_id)',
            [
                ':uuid' => $uuid,
                ':role_id' => $role_id
            ]
        );
    }

}

?>