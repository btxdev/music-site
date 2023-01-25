<?php

    class Status {

        function __construct($status, $args=[]) {

            $this->status = $status;
            $this->session = isset($args['session']) ? $args['session'] : '';
            $this->msg = isset($args['msg']) ? $args['msg'] : '';
            $this->returnValue = isset($args['returnValue']) ? $args['returnValue'] : false;

            switch($status) {
                case 'OK':
                    break;
                case 'USER_NOT_FOUND':
                case 'WRONG_PASSWORD':
                    $this->status = 'WRONG_PASSWORD';
                    $this->msg = 'Неверный логин или пароль, проверьте введенные данные.';
                    break;
                case 'WRONG_FORMAT':
                    $this->msg = 'Введенные данные не соответствуют шаблону.';
                    break;
                case 'NOT_AUTHORIZED':
                    $this->msg = 'Авторизируйтесь в системе.';
                    break;
            }
            
        }

        function ok() {
            return ($this->status == 'OK');
        }

        function json() {
            return json_encode([
                'status' => $this->status,
                'msg' => $this->msg
            ]);
        }

    }

?>