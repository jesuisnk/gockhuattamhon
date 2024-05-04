<?php

class LoginLibrary
{ 
    public function validateAccount($account)
    {
        if (empty($account)) {
            return 'Tên tài khoản không được để trống';
        }

        $len = mb_strlen($account);

        if ($len < 3 || $len > 32) {
            return 'Độ dài tên tài khoản phải từ 3 đến 32 ký tự';
        }

        if (preg_match('/[^0-9a-z.]/i', $account)) {
            return 'Tên tài khoản chỉ được sử dụng chữ cái, số và dấu chấm';
        }

        if (preg_match('/^\./', $account)) {
            return 'Tên tài khoản phải bắt đầu bằng chữ cái hoặc số';
        }

        if (preg_match('/\.\.+/', $account)) {
            return 'Tên tài khoản không được chứa hai dấu chấm liên tiếp';
        }

        if (preg_match('/\.$/', $account)) {
            return 'Tên tài khoản phải kết thúc bằng chữ cái hoặc số';
        }

        return false;
    }


    public function validatePassword($password)
    {
        if (empty($password)) {
            return 'Mật khẩu không được để trống';
        }

        $len = mb_strlen($password);

        if ($len < 3 || $len > 32) {
            return 'Độ dài mật khẩu phải từ 3 đến 32 ký tự';
        }

        return false;
    }
}