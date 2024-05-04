<?php

function _e(string $text)
{
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    return trim($text);
}

/**
 * Get autoload config
 *
 * @param string|null $path
 * @param mixed $default
 * @return Config|mixed
 */
function config(string $path = null, $default = null)
{
    $config = Container::get(Config::class);

    if (is_null($path)) {
        return $config;
    }

    return $config->get($path, $default);
}

function display_error($error)
{
    if (is_array($error)) {
        if (sizeof($error) === 1) {
            $error = array_pop($error);
        } else {
            $error = '- ' . implode('<br />- ', $error);
        }
    }

    return $error;
}

function redirect(string $uri = '/')
{
    header('Location: ' . SITE_PATH . $uri);
    exit;
}

/**
 * Get request instance
 *
 * @return Request
 */
function request()
{
    return Container::get(Request::class);
}

function url(string $path = '', $absulute = true)
{
    if ($absulute) {
        return SITE_URL . '/' . ltrim($path, '/');
    }

    return (SITE_PATH ? '/' . ltrim(SITE_PATH, '/') : '')
        . '/' . ltrim($path, '/');
}

/**
 * Get template instance or render a view
 *
 * @param string|null $template
 * @param array $data
 * @return Template|string
 */
function view(string $template = null, array $data = [])
{
    /** @var Template */
    $view = Container::get(Template::class);

    if (is_null($template)) {
        return $view;
    }

    return $view->render($template, $data);
}

function set_cookie($name, $value, $time = null)
{
    if (empty($time)) {
        $time = 3600 * 24 * 365;
    }
    setcookie($name, $value, TIME + $time, '/');
    return;
}

function delete_cookie($name)
{
    setcookie($name, '', -1, '/');
    unset($_COOKIE[$name]);
    return;
}

function get_cookie($name)
{
    if (!$_COOKIE[$name]) return false;
    return $_COOKIE[$name];
}

function is_integer_string($string)
{
    $integer = (int) $string;
    return strval($integer) === $string;
}

function checkExtension($one)
{
    $extension = pathinfo($one, PATHINFO_EXTENSION);

    if (in_array($extension, ['jpg', 'png', 'webp', 'psd', 'heic'])) {
        return 'file-image-o';
    } elseif (in_array($extension, ['mp4', 'mkv', 'webm', 'flv', '3gp'])) {
        return 'file-video-o';
    } elseif (in_array($extension, ['mp3', 'mkv', 'm4a', 'flac', 'wav'])) {
        return 'file-audio-o';
    } elseif (in_array($extension, ['docx', 'doc', 'txt', 'md', 'odt'])) {
        return 'file-text-o';
    } elseif (in_array($extension, ['txt', 'md'])) {
        return 'file-text-o';
    } elseif (in_array($extension, ['docx', 'doc', 'odt'])) {
        return 'file-word-o';
    } elseif (in_array($extension, ['xls', 'xlsx'])) {
        return 'file-excel-o';
    } elseif (in_array($extension, ['ppt', 'pptx'])) {
        return 'file-powerpoint-o';
    } elseif ($extension === 'pdf') {
        return 'file-pdf-o';
    } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar'])) {
        return 'file-archive-o';
    } elseif (in_array($extension, ['cpp', 'cs', 'php', 'html', 'js', 'py'])) {
        return 'file-code-o';
    } elseif ($extension === 'sql') {
        return 'database';
    } else {
        return 'file-o';
    }
}

function get_post($string)
{
    return ($_POST[$string]) ? htmlspecialchars(addslashes($_POST[$string])) : null;
}

function get_get($string)
{
    return isset($_GET[$string]) ? htmlspecialchars(addslashes($_GET[$string])) : null;
}

function get_youtube_id($url)
{
    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/", $url, $matches);
    return $matches[1];
}

function get_youtube_title($url)
{
    $youtube_id = get_youtube_id($url);
    $youtube_title = file_get_contents("https://www.youtube.com/watch?v=$youtube_id");
    preg_match("/<title>(.*)<\/title>/", $youtube_title, $matches);
    return str_replace(' - YouTube', '', $matches[1]);
}

function ago($time_ago)
{
    $timeht = date('U');
    $time = $time_ago;
    $time_giay = $timeht - $time;
    $time_phut = floor($time_giay / 60);
    $time_day = date('z', $timeht) - date('z', $time);
    $fulltime = date('d.m.Y - H:i', $time_ago);
    $minitime = date('H:i', $time_ago);

    if ($time_day == 0) {
        if ($time_giay <= 60) {
            return $time_giay . ' giây trước';
        } elseif ($time_phut <= 60) {
            return $time_phut . ' phút trước';
        } else {
            return 'Hôm nay, ' . $minitime;
        }
    } elseif ($time_day == 1) {
        return 'Hôm qua, ' . $minitime;
    } else {
        return $fulltime;
    }
}

function pagingConfig($total = 0, $get = 'page', $per = 10)
{
    $page_max = round($total / $per);
    $page = (isset($_GET[$get]) && $_GET[$get] > 0) ? $_GET[$get] : 1;
    if ($page >= $page_max) {
        $page = $page_max;
    }
    if ($page < 1) {
        $page = 1;
    }
    $start = ($page - 1) * $per;
    return [
        'start' => $start,
        'end' => $per,
        'page' => $page,
        'page_max' => $page_max
    ];
}

function paging($url, $p, $max)
{
    $p = (int) $p;
    $max = (int) $max;
    $b = '';
    if ($max > 1) {
        $a = ' <a class="pagenav" href="' . $url;
        if ($p > $max) {
            $p = $max;
            $b .= 'a';
        }
        if ($p > 1) {
            $b .= $a . ($p - 1) . '">&laquo;</a> ';
        }
        if ($p > 3) {
            $b .= $a . '1">1</a>';
        }
        if ($p > 4) {
            $b .= ' <a class="disabled">...</a> ';
        }
        if ($p > 2) {
            $b .= $a . ($p - 2) . '">' . ($p - 2) . '</a>';
        }
        if ($p > 1) {
            $b .= $a . ($p - 1) . '">' . ($p - 1) . '</a>';
        }
        $b .= ' <a class="current"><b>' . $p . '</b></a> ';
        if ($p < ($max - 1)) {
            $b .= $a . ($p + 1) . '">' . ($p + 1) . '</a>';
        }
        if ($p < ($max - 2)) {
            $b .= $a . ($p + 2) . '">' . ($p + 2) . '</a>';
        }
        if ($p < ($max - 3)) {
            $b .= ' <a class="disabled">...</a> ';
        }
        if ($p < $max) {
            $b .= $a . $max . '">' . $max . '</a> ';
        }
        if ($p < $max) {
            $b .= $a . ($p + 1) . '">&raquo;</a> ';
        }
        return '<div class="pagination">' . $b . '</div>';
    }
}

function toolbar($textareaID = 'postText', $row = 3, $msg = null)
{
    $output = '<script src="/assets/app/tag.js"></script>';
    $output .= '<div class="redactor_box" style="border-bottom: 1px solid #D7EDFC;margin-bottom:2px;">';
    $output .= file_get_contents(ROOT . DS . '/assets/toolbarStart.html');

    $code = ['php', 'css', 'js', 'html', 'sql', 'twig'];
    $output .= '<div id="codeShow" style="display:none"><div style="padding:2px">';
    foreach ($code as $val) {
        $codetag = '<a href="javascript:tag(';
        $codetag .= "'[code=" . $val . "]', '[/code]', ''";
        $codetag .= ');';
        $codetag .= '" tabindex="-1" class="btn btn-default">' . strtoupper($val) . '</a>';
        $output .= $codetag;
    }
    $output .= '</div></div>';

    $color = ['bcbcbc', '708090', '6c6c6c', '454545', 'fcc9c9', 'fe8c8c', 'fe5e5e', 'fd5b36', 'f82e00', 'ffe1c6', 'ffc998', 'fcad66', 'ff9331', 'ff810f', 'd8ffe0', '92f9a7', '34ff5d', 'b2fb82', '89f641', 'b7e9ec', '56e5ed', '21cad3', '03939b', '039b80', 'cac8e9', '9690ea', '6a60ec', '4866e7', '173bd3', 'f3cafb', 'e287f4', 'c238dd', 'a476af', 'b53dd2'];
    $output .= '<div id="colorShow" style="display:none"><div style="padding:2px">Màu chữ: ';
    foreach ($color as $val) {
        $codetag = '<a href="javascript:tag(';
        $codetag .= "'[color=#" . $val . "]', '[/color]', ''";
        $codetag .= ');';
        $codetag .= '" tabindex="-1" class="btn btn-default" style="background-color:#' . $val . ';color:#fff">' . $val . '</a>';
        $output .= $codetag;
    }
    $output .= '</div></div>';

    $vid = ['vid' => 'video', 'youtube' => 'youtube'];
    $output .= '<div id="vidShow" style="display:none"><div style="padding:2px">';
    foreach ($vid as $k => $v) {
        $codetag = '<a href="javascript:tag(';
        $codetag .= "'[" . $k . "]', '[/" . $k . "]', ''";
        $codetag .= ');';
        $codetag .= '" tabindex="-1" class="btn btn-default">' . strtoupper($v) . '</a>';
        $output .= $codetag;
    }
    $output .= '</div></div>';

    $output .= '<textarea class="form-control" type= "text" id="' . $textareaID . '" name="msg" rows="' . $row . '">' . $msg . '</textarea>';
    $output .= '</div>';
    return $output;
}

function generateCSRFToken()
{
    $encrypt = bin2hex(random_bytes(32));
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = $encrypt;
    }
    return true;
}

function isCSRFTokenValid($token)
{
    $output = null;
    $isCSRFTokenValid = isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
    if (!$isCSRFTokenValid) {
        $output = 'Thao tác không hợp lệ';
    }
    return $output;
}

function slug($string)
{
    $slugger = new \Symfony\Component\String\Slugger\AsciiSlugger();
    $slug = $slugger->slug($string)->lower();
    return $slug;
}