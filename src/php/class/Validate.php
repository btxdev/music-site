<?php

    include_once __DIR__.'/Status.php';

    class Validate {


        function __construct() {

        }


        function std($str) {
            $str = isset($str) ? htmlspecialchars($str) : '';
            return $str;
        }


        function login($str) {
            $str = $this->std($str);
            if(!preg_match('/^[a-z\d_]{4,20}$/i', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }


        function password($str) {
            $str = $this->std($str);
            if(!preg_match('/^([a-zA-Z0-9-.,_!а-яА-ЯёЁ]){6,32}$/', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }


        function email($str) {

            $str = $this->std($str);

            // can be NULL
            if($str == '') return new Status('OK');

            if(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }


        function phone($str) {

            $str = $this->std($str);

            // can be NULL
            if($str == '') return new Status('OK');

            if(!preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }


        function name($str) {

            $str = $this->std($str);

            // can be NULL
            if($str == '') return new Status('OK');

            if(!preg_match('/^([A-Za-zА-ЯЁа-яё]){2,32}$/u', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }


        function text($str) {

            $str = $this->std($str);

            // can be NULL
            if($str == '') return new Status('OK');

            if(!preg_match('/^([A-Za-zА-ЯЁа-яё ,.]){2,32}$/u', $str))
                return new Status('WRONG_FORMAT');
            else
                return new Status('OK', ['returnValue' => $str]);
        }

    }

?>