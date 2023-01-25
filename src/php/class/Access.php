<?php

// класс предоставляет методы для проверки и выдачи доступа

include_once __DIR__.'/Admin.php';
include_once __DIR__.'/Status.php';

class Access extends Admin {

    function __construct($instance, $session_name, $hash_hardness = 32) {
        $this->instance = $instance;
        $this->session_name = $session_name;
        $this->hash_hardness = $hash_hardness;
        $this->uuid = -1;
        $this->username = -1;
    }

    function generateSessionHash() {
        return $this->strgen('0123456789abcdef', $this->hash_hardness);
    }

    function getUserIdBySessionHash($hash) {
        $result = $this->fetch(
            'SELECT `uuid` 
            FROM `sessions` 
            WHERE `sesshash` LIKE :hash',
            [
                ':hash' => $hash
            ]
        );
        if($result == false) return false;
        return $result['uuid'];
    }

    function isAccessGranted($hash) {
        $result = $this->getUserIdBySessionHash($hash);
        return ($result != false);
    }

    function grantAccessToUserId($uuid) {
        // проверка существования пользователя
        if ($uuid == false) return false;
        // создание записи в БД
        $hash = $this->generateSessionHash();
        $this->run(
            'INSERT INTO sessions 
            (sesshash, uuid, created)
            VALUES (:sesshash, :uuid, :created)',
            [
                ':sesshash' => $hash,
                ':uuid' => $uuid,
                ':created' => date('Y-m-d')
            ]
        );
        return $hash;
    }

    function grantAccessToUserName($username) {
        $uuid = $this->getUserId($username);
        return $this->grantAccessToUserId($uuid);
    }

    function removeAccessFrom($hash) {
        $this->run(
            'DELETE FROM `sessions` WHERE `sesshash` LIKE :sesshash',
            [
                ':sesshash' => $hash
            ]
        );
    }

    function removeAccessFromUserId($uuid) {
        $this->run(
            'DELETE FROM `sessions` WHERE `uuid` LIKE :uuid',
            [
                ':uuid' => $uuid
            ]
        );
    }

    function removeAccessFromUserName($username) {
        $uuid = $this->getUserId($username);
        $this->removeAccessFromUserId($uuid);
    }

    function login($username, $password_raw) {

        $uuid = $this->getUserId($username);
        if($uuid == false) 
            return new Status('USER_NOT_FOUND');

        $data = $this->fetch(
            'SELECT password, salt FROM users WHERE uuid LIKE :uuid',
            [
                ':uuid' => $uuid
            ]
        );
        if($data == false) 
            return new Status('USER_NOT_FOUND');

        $hash = $data['password'];
        $salt = $data['salt'];

        if(!$this->verifyPassword($password_raw, $salt, $hash))
            return new Status('WRONG_PASSWORD');

        return new Status('OK', ['session' => $this->grantAccessToUserId($uuid)]);

    }

    function setSessionCookie($session_name, $hash) {
        setcookie($session_name, $hash, [
            'expires' => time() + 60 * 60 * 24,
            'path' => '/',
            'secure' => false,
            'samesite' => 'Strict'
        ]);
        return new Status('OK');
    }

    function getSessionCookie($session_name) {
        $hash = isset($_COOKIE[$session_name]) ? $_COOKIE[$session_name] : 'none';
        return $hash;
    }

    function checkSessionCookie($session_name) {
        $hash = $this->getSessionCookie($session_name);
        return $this->isAccessGranted($hash);
    }

}

?>