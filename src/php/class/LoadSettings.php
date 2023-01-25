<?php

class LoadSettings {

    private $path = '';
    private $loaded = false;
    private $settings = Array();

    // путь к файлу по умолчанию
    function __construct($path = null) {
        $parent_dir = dirname(__DIR__, 1);
        if(is_null($path)) {
            $path = realpath($parent_dir.'/.htsettings');
        }
        $this->path = $path;
    }

    // загрузка файла с настройками
    function load() {

        if(file_exists($this->path)) {
            $handle = fopen($this->path, 'r');
            while (($line = fgets($handle)) !== false) {
                $arguments = explode('=', $line);
                if(count($arguments) != 2) {
                    throw new Exception("Ошибка в файле настроек. Содержание файла не соответствует примеру:\nkey = value\nkey = value\nkey = value");
                    return false;
                }
                $key = trim($arguments[0]);
                $value = trim($arguments[1]);
                $this->settings[$key] = $value;
            }
            fclose($handle);
            $this->loaded = true;
            return true;
        }
        else {
            $abspath = realpath($this->path);
            throw new Exception("Невозможно загрузить файл настроек по заданному пути ($this->path) $abspath");
        }

        return false;
    }

    // получение значений настроек
    function get($key) {
        if(!$this->loaded) load();
        if(array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }
        else {
            throw new Exception("Параметр $key в файле настроек не найден");
            return NULL;
        }
    }

}

?>