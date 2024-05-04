<?php
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;

class Template
{
    private $twig;

    private $auth;
    private $load;
    private $db;
    private $dbSelect;

    function __construct()
    {
        $this->auth = Container::get(Auth::class);
        $this->load = Container::get(Loader::class);
        $this->db = Container::get(DB::class);

        $FilesystemLoader = new \Twig\Loader\FilesystemLoader(ROOT . 'templates');
        $twig = new \Twig\Environment($FilesystemLoader);

        $this->dbSelect = $this->load->model('dbSelect');
        $this->addGlobal($twig);
        $this->addFunction($twig);
        $this->addExtension($twig);

        // load extensions
        $this->twig = $twig;
    }

    public function addGlobal($twig)
    {
        // get uri segment
        $uri = preg_replace('#^/#', '', $_SERVER['REQUEST_URI']);
        $uri = explode('?', $uri)[0];
        $uri = explode('/', $uri);
        $twig->addGlobal('uri', $uri);

        $twig->addGlobal('_SERVER', $_SERVER);
        $twig->addGlobal('_SESSION', $_SESSION);
        $twig->addGlobal('_COOKIE', $_COOKIE);

        $twig->addGlobal('isLogin', $this->auth->isLogin);

        $totalCategory = $this->dbSelect->TableCount('category');
        $twig->addGlobal('totalCategory', $totalCategory);
        $listCategory = ($totalCategory) ? $this->dbSelect->FetchAll("SELECT * FROM `category` ORDER BY `id` ASC") : [];
        $twig->addGlobal('listCategory', $listCategory);

        $totalBlog = $this->dbSelect->TableCount('blog');
        $twig->addGlobal('totalBlog', $totalBlog);
    }

    public function addFunction($twig)
    {
        /**
         * Add function from system/functions.php
         */
        $config = new \Twig\TwigFunction('config', 'config');
        $twig->addFunction($config);

        $display_error = new \Twig\TwigFunction('display_error', 'display_error');
        $twig->addFunction($display_error);

        $redirect = new \Twig\TwigFunction('redirect', 'redirect');
        $twig->addFunction($redirect);

        $get_post = new \Twig\TwigFunction('get_post', 'get_post');
        $twig->addFunction($get_post);
        $get_get = new \Twig\TwigFunction('get_get', 'get_get');
        $twig->addFunction($get_get);
        $get_youtube_id = new \Twig\TwigFunction('get_youtube_id', 'get_youtube_id');
        $twig->addFunction($get_youtube_id);
        $get_youtube_title = new \Twig\TwigFunction('get_youtube_title', 'get_youtube_title');
        $twig->addFunction($get_youtube_title);

        $url = new \Twig\TwigFunction('url', 'url');
        $twig->addFunction($url);

        $checkExtension = new \Twig\TwigFunction('checkExtension', 'checkExtension');
        $twig->addFunction($checkExtension);

        $ago = new \Twig\TwigFunction('ago', 'ago');
        $twig->addFunction($ago);
        $paging = new \Twig\TwigFunction('paging', 'paging');
        $twig->addFunction($paging);
        $pagingConfig = new \Twig\TwigFunction('pagingConfig', 'pagingConfig');
        $twig->addFunction($pagingConfig);
        $toolbar = new \Twig\TwigFunction('toolbar', 'toolbar');
        $twig->addFunction($toolbar);

        $generateCSRFToken = new \Twig\TwigFunction('generateCSRFToken', 'generateCSRFToken');
        $twig->addFunction($generateCSRFToken);
        $isCSRFTokenValid = new \Twig\TwigFunction('isCSRFTokenValid', 'isCSRFTokenValid');
        $twig->addFunction($isCSRFTokenValid);
    }

    public function addExtension($twig)
    {
        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new StringExtension());

        $twig->addExtension($this->load->model('dbSelect'));

        $twig->addExtension($this->load->library('TextMarkup'));
    }

    public function render($file, $data = [])
    {
        try {
            // content type
            $ext_path = explode('.', $file);
            if (count($ext_path) < 2) {
                $check_ext = 'html';
            } else {
                $check_ext = array_pop($ext_path);
            }
            $check_ext = strtolower($check_ext);
            header('Content-Type: ' . self::get_format($check_ext));

            // view
            $result = $this->twig->render($file . '.twig', $data);
        } catch (\Twig\Error\RuntimeError $e) {
            $result = 'Syntax Error: ' . $e->getMessage();
        } catch (\Twig\Error\RuntimeError $e) {
            $result = 'Runtime Error: ' . $e->getMessage();
        } catch (\Twig\Error\LoaderError $e) {
            $result = 'Loader Error: ' . $e->getMessage();
        } catch (\Twig\Error\Error $e) {
            $result = 'Error: ' . $e->getMessage();
        }

        return $result;
    }

    public function error()
    {
        return $this->render('error', ['title' => '404 Not Found']);
    }

    private static function get_format($ext)
    {
        $mime = array(
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'txt' => 'text/plain',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'rss' => 'application/rss+xml',
            'svg' => 'image/svg+xml'
        );
        if ($mime[$ext]) {
            return $mime[$ext];
        } else {
            return 'text/html';
        }
    }
}