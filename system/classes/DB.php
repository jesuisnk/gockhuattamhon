<?php

class DB
{
    public function __invoke()
    {
        $db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $db_user = defined('DB_USER') ? DB_USER : '';
        $db_pass = defined('DB_PASS') ? DB_PASS : '';
        $db_name = defined('DB_NAME') ? DB_NAME : '';

        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die('<center><h2>Database connection failed</h2></center>');
        mysqli_query($conn, 'SET NAMES `utf8mb4`');

        return $conn;
    }
}