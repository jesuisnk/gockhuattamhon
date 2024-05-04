<?php

class LoginController extends Controller
{
    private LoginLibrary $LoginLibrary;

    function __construct()
    {
        parent::__construct();
        $this->LoginLibrary = $this->load->library('Login');
    }

    public function logout()
    {
        delete_cookie('login');
        redirect('/');
        return $this->view->render('account/logout');
    }

    public function login()
    {
        if ($this->auth->isLogin) {
            redirect('/');
        }

        $error = false;
        $username = $this->request->postVar('username', '');
        $password = $this->request->postVar('password', '');
        $remember = $this->request->postVar('remember', 0);

        if ($this->request->getMethod() === 'POST') {
            if (empty($username) || empty($password)) {
                $error = 'Vui lòng nhập tên tài khoản và mật khẩu';
            } else {
                $error = $this->LoginLibrary->validateAccount($username);
                if (!$error) {
                    $error = $this->LoginLibrary->validatePassword($password);
                }

                if (!$error) {
                    $username = slug($username);

                    if ($username == ADMIN_LOGIN['account'] && $password == ADMIN_LOGIN['password']) {
                        if ($remember) {
                            $tokentime = 3600 * 24 * 365;
                        } else {
                            $tokentime = 3600 * 24;
                        }
                        set_cookie('login', config('system.tokenlogin'), $tokentime);
                        redirect('/');
                    } else {
                        $error = 'Tên tài khoản hoặc mật khẩu không chính xác';
                    }
                }
            }
        }

        return $this->view->render('account/login', [
            'title' => 'Đăng nhập',
            
            'error' => $error,
            'inputAccount' => $username,
            'inputRemember' => $remember
        ]);
    }
}