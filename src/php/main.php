<?php

  $development = true;

  // ошибки
  if($development) {
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __dir__.'/error.log');
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