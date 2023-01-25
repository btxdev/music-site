<?php

    $development = true;

    // ошибки
    if($development) {
        error_reporting(E_ALL);
    }
    else {
        error_reporting(0);
    }
    
    // зависимости
    include_once __DIR__.'/class/LoadSettings.php';

    // настройки из файла
    $settings = new LoadSettings();
    if(!$settings->load()) {
        throw Exception('Невозможно загрузить файл с настройками');
    }

?>