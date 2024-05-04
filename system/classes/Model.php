<?php

class Model
{
    protected Loader $load;
    protected mysqli $db;
    protected Config $config;
    protected Auth $auth;

    function __construct()
    {
        $this->db = Container::get(DB::class);
        $this->config = Container::get(Config::class);
        $this->load = Container::get(Loader::class);
        $this->auth = Container::get(Auth::class);
    }
}
