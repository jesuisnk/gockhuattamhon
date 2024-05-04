<?php

class Auth
{
    public $isLogin = false;
    public $settings;

    public function __construct()
    {
        $this->authorize();
    }

    private function authorize()
    {
        $tokenlogin = isset($_COOKIE['login']) ? $_COOKIE['login'] : '';
        if ($tokenlogin == config('system.tokenlogin')) {
            $this->isLogin = true;
        } else {
            $this->unset();
        }
    }

    private function unset()
    {
        delete_cookie('login');
    }
}