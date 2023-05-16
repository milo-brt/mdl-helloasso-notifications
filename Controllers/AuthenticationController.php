<?php

namespace Controllers {

    class AuthenticationController
    {
        public function __construct()
        {
        }


        public function getAuth()
        {

            if (isset($_COOKIE['token']) && $_COOKIE['token'] == $_ENV['TOKEN']) {
                header('Location: /');
                return [null, null];
            } else {
                return ["login", null];
            }

        }


        public function postAuth()
        {

            if (isset($_COOKIE['token']) && $_COOKIE['token'] == $_ENV['TOKEN']) {
                header('Location: /');
                return [null, null];
            } else if (isset($_POST['username']) && isset($_POST['password'])) {
                if ($_POST['username'] == $_ENV['USERNAME'] && $_POST['password'] == $_ENV['PASSWORD']) {
                    setcookie('token', $_ENV['TOKEN'], time() + 3600, '/');
                    header('Location: /');
                    return [null, null];
                } else {
                    return ["login", null];
                }
            } else {
                return ["login", null];
            }

        }
    }
}

?>