<?php

use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;
use Twig\TwigFilter;

class TextMarkupLibrary implements ExtensionInterface
{
    private static $code_id;
    private static $code_index;
    private static $code_parts;

    public function getFunctions()
    {
        return [
            new TwigFunction('bbcode', [$this, 'bbcode']),
            new TwigFunction('description', [$this, 'description']),
            new TwigFunction('alert', [$this, 'alert']),
        ];
    }
    public function getFilters()
    {
        return [
            new TwigFunction('alert', [$this, 'alert'])
        ];
    }
    public function getTokenParsers()
    {
        return [];
    }
    public function getNodeVisitors()
    {
        return [];
    }
    public function getTests()
    {
        return [];
    }
    public function getOperators()
    {
        return [];
    }

    public function alert($string = null, $title = 'warning')
    {
        $output = null;
        if (isset($string) && in_array($title, ['highlight', 'highlight-title', 'note', 'note-title', 'important', 'important-title', 'new', 'new-title', 'warning', 'warning-title'])) {
            $output = "<p class='$title'>$string</p>";
        }
        return $output;
    }

    public function description($string)
    {
        $string = $this->bbcode($string);
        $string = strip_tags($string);
        $len = mb_strlen($string);
        $string = mb_substr($string, 0, 150);
        $string = trim($string);
        return ($len > 150 ? $string . '...' : $string);
    }

    public static function smile($string)
    {
        $arr_emo_name = ['ami', 'anya', 'aru', 'aka', 'dauhanh', 'dora', 'le', 'menhera', 'moew', 'nam', 'pepe', 'qoobee', 'qoopepe', 'thobaymau', 'troll', 'dui', 'firefox', 'conan'];
        foreach ($arr_emo_name as $emo_name) {
            if (strpos($string, ':' . $emo_name) !== false) {
                $parttern = '/[:]' . $emo_name . '([0-9]*):/';
                $replacement = '<img loading="lazy" src="https://dorew-site.github.io/assets/smileys/' . $emo_name . '/' . $emo_name . '$1.png" alt="$1"/>';
                $string = preg_replace($parttern, $replacement, $string);
            }
        }
        return $string;
    }

    // Processing of tags and links
    public function bbcode($var)
    {
        $var = self::process_code($var); // Highlighting code
        $var = nl2br($var);
        $var = self::smile($var);
        $var = self::highlight_bb($var);
        $var = self::highlight_url($var);
        $var = self::bb_url($var);
        $var = self::bb_vid($var);
        $var = self::bb_youtube($var);
        $var = self::bb_simple($var);
        $var = self::bb_align($var);
        $var = self::bb_color1($var);
        $var = self::bb_color2($var);
        $var = self::bb_color3($var);
        $var = self::bb_d($var);
        $var = self::highlight_code($var);
        return $var;
    }

    /**
     * Парсинг ссылок
     * За основу взята доработанная функция от форума phpBB 3.x.x
     *
     * @param $text
     * @return mixed
     */
    public static function highlight_url($text)
    {
        if (!function_exists('url_callback')) {
            function url_callback($type, $whitespace, $url, $relative_url)
            {
                $orig_url = $url;
                $orig_relative = $relative_url;
                $url = htmlspecialchars_decode($url);
                $relative_url = htmlspecialchars_decode($relative_url);
                $text = '';
                $chars = array('<', '>', '"');
                $split = false;
                foreach ($chars as $char) {
                    $next_split = strpos($url, $char);
                    if ($next_split !== false) {
                        $split = ($split !== false) ? min($split, $next_split) : $next_split;
                    }
                }
                if ($split !== false) {
                    $url = substr($url, 0, $split);
                    $relative_url = '';
                } else {
                    if ($relative_url) {
                        $split = false;
                        foreach ($chars as $char) {
                            $next_split = strpos($relative_url, $char);
                            if ($next_split !== false) {
                                $split = ($split !== false) ? min($split, $next_split) : $next_split;
                            }
                        }
                        if ($split !== false) {
                            $relative_url = substr($relative_url, 0, $split);
                        }
                    }
                }
                $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];
                switch ($last_char) {
                    case '.':
                    case '?':
                    case '!':
                    case ':':
                    case ',':
                        $append = $last_char;
                        if ($relative_url) {
                            $relative_url = substr($relative_url, 0, -1);
                        } else {
                            $url = substr($url, 0, -1);
                        }
                        break;

                    default:
                        $append = '';
                        break;
                }
                $short_url = (mb_strlen($url) > 40) ? mb_substr($url, 0, 30) . ' ... ' . mb_substr($url, -5) : $url;
                switch ($type) {
                    case 1:
                        $relative_url = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
                        $url = $url . '/' . $relative_url;
                        $text = $relative_url;
                        if (!$relative_url) {
                            return $whitespace . $orig_url . '/' . $orig_relative;
                        }
                        break;

                    case 2:
                        $text = $short_url;
                        $url = SITE_PATH . '/misc/go?url=' . rawurlencode($url);
                        break;

                    case 4:
                        $text = $short_url;
                        $url = 'mailto:' . $url;
                        break;
                }
                $url = htmlspecialchars($url);
                $text = htmlspecialchars($text);
                $append = htmlspecialchars($append);

                return $whitespace . '<a href="' . $url . '" target="_blank">' . $text . '</a>' . $append;
            }
        }

        // Обработка внутренних ссылок
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])(' . preg_quote(SITE_URL, '#') . ')/((?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return url_callback(1, $matches[1], $matches[2], $matches[3]);
            },
            $text
        );

        // Обработка обычных ссылок типа xxxx://aaaaa.bbb.cccc. ...
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return url_callback(2, $matches[1], $matches[2], '');
            },
            $text
        );

        return $text;
    }

    /*
    -----------------------------------------------------------------
    Подсветка кода
    -----------------------------------------------------------------
    */
    private static function process_code($var)
    {
        self::$code_id = uniqid();
        self::$code_index = 0;
        self::$code_parts = array();
        $var = preg_replace_callback('#\[code=(.+?)\](.+?)\[\/code]#is', 'self::codeCallback', $var);
        $var = preg_replace_callback('#\[php\](.+?)\[\/php\]#s', 'self::phpCodeCallback', $var);

        return $var;
    }

    private static $geshi;

    private static function phpCodeCallback($code)
    {
        return self::codeCallback(array(1 => 'php', 2 => $code[1]));
    }

    private static function codeCallback($code)
    {
        $parsers = array(
            'php' => 'php',
            'css' => 'css',
            'html' => 'html5',
            'js' => 'javascript',
            'sql' => 'sql',
            'twig' => 'twig',
            'c++' => 'cpp',
            'cpp' => 'cpp',
            'text' => 'text'
        );

        $parser = isset($code[1]) && isset($parsers[$code[1]]) ? $parsers[$code[1]] : 'php';

        if (null === self::$geshi) {
            self::$geshi = new \GeSHi;
            self::$geshi->set_link_styles(GESHI_LINK, 'text-decoration: none');
            self::$geshi->set_link_target('_blank');
            self::$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
            self::$geshi->set_line_style(null, null, false);
            self::$geshi->set_code_style('padding-left: 6px; white-space: pre-wrap');
            self::$geshi->enable_keyword_links(false);
        }

        self::$geshi->set_language($parser);
        $php = strtr($code[2], array('<br />' => '', '</p><p>' => ''));
        $php = html_entity_decode(trim($php), ENT_QUOTES, 'UTF-8');
        self::$geshi->set_source($php);
        self::$code_index++;
        self::$code_parts[self::$code_index] = array(
            'type' => $parser,
            'source' => self::$geshi->parse_code()
        );

        return '[code|' . self::$code_id . ']' . self::$code_index . '[/code]';
    }

    private static function highlight_code($var)
    {
        $var = preg_replace_callback(
            '#\[code\|' . self::$code_id . '\](\d+)\[\/code\]#s',
            function ($code) {
                $part = self::$code_parts[$code[1]];
                unset(self::$code_parts[$code[1]]);
                return '</p><div class="bbCodeBlock bbCodePHP"><div class="type">' . mb_strtoupper($part['type']) . '</div><div class="code" style="overflow-x: auto;">' . $part['source'] . '</div></div><p>';
            },
            $var
        );

        return $var;
    }

    private static function bb_url($nd)
    {
        $pattern = '/\[url=([^\]]+)](.*?)\[\/url\]/';
        $replacement = "<i class='fa fa-link fa-spin'></i><a rel='nofollow' href='$1'>$2</a>";

        return preg_replace($pattern, $replacement, $nd);
    }

    private static function bb_img($nd)
    {
        $pattern = '/\[img\](.*?)\[\/img\]/';
        $loaderror = "https://i.imgur.com/806SpRu.png";
        $replacement = '<div style="text-align:center"><a href="$1" class="swipebox"><img class="bb_img LoadImage" src="$1" border="2" onerror="this.onerror=null;this.src=' . "'" . $loaderror . "'" . '" style="max-width:50%"/></a></div>';

        $current_url = $_SERVER['REQUEST_URI'];
        $current_url_lower = strtolower($current_url);

        if (strpos($current_url_lower, 'dorew.ovh') === false) {
            $nd = preg_replace('/i.imgur.com/', 'imgur.dorew.ovh', $nd);
        }

        return preg_replace($pattern, $replacement, $nd);
    }

    private static function bb_vid($nd)
    {
        $pattern = '/\[vid\](.*?)\[\/vid\]/';
        $replacement = '<div class="video-wrapper" style="text-align: center;"><iframe loading="lazy" src="/api/video?link=$1" height="315" width="560" scrolling="no" allowfullscreen="" frameborder="0"></iframe></div>';

        return preg_replace($pattern, $replacement, $nd);
    }

    private static function bb_youtube($nd)
    {
        $pattern = '/\[youtube\](.*?)\[\/youtube\]/';
        $replacement = '<div class="video-wrapper" style="text-align: center;"><iframe loading="lazy" src="/api/video?link=$1" height="315" width="560" scrolling="no" allowfullscreen="" frameborder="0"></iframe></div>';

        return preg_replace($pattern, $replacement, $nd);
    }

    private static function bb_d($nd)
    {
        // tải xuống
        $pattern_d = '/\[d\](.*?)\[\/d\]/';
        $replacement_d = '<center><a href="$1"><button class="btn btn-primary"><i class="fa fa-download"></i> Download</button></a></center>';

        return preg_replace($pattern_d, $replacement_d, $nd);
    }

    private static function bb_simple($nd)
    {
        // định dạng văn bản
        $pattern = '/\[(b|i|u|s|h1|h2|h3|h4|h5|h6|strong|em)\](.*?)\[\/\\1\]/';
        $replacement = '<$1>$2</$1>';

        return preg_replace($pattern, $replacement, $nd);
    }

    private static function bb_align($nd)
    {
        // căn lề
        $pattern_align = '/\[(center|left|right)\](.*?)\[\/\\1\]/';
        $replacement_align = '<div style="text-align:$1;">$2</div>';

        return preg_replace($pattern_align, $replacement_align, $nd);
    }

    private static function bb_color1($nd)
    {
        // văn bản màu
        $pattern_color = '/\[(red|blue|purple|green|orange|darkorange)\](.*?)\[\/\\1\]/';
        $replacement_color = '<span style="color:$1;">$2</span>';

        return preg_replace($pattern_color, $replacement_color, $nd);
    }

    private static function bb_color2($nd)
    {
        $pattern_color = '/\[color=(.+?)\](.*?)\[\/color\]/';
        $replacement_color = '<span style="color:$1;">$2</span>';

        return preg_replace($pattern_color, $replacement_color, $nd);
    }

    private static function bb_color3($nd)
    {
        $pattern_color = '/\[bcolor=(.+?)\](.*?)\[\/bcolor\]/';
        $replacement_color = '<span style="color:$1;font-weight:700">$2</span>';

        return preg_replace($pattern_color, $replacement_color, $nd);
    }

    private static function highlight_bb($var)
    {
        $var = self::bb_img($var);

        // search list
        $search = array(
            '#(\r\n|[\r\n])#',
            // Strikethrough
            '#\[small](.+?)\[/small]#is',
            // Small Font
            '#\[big](.+?)\[/big]#is',
            // font color
            '#\[quote](.+?)\[/quote]#is',
            // quote
            '#\[quote=([\d]+?),([\d]+?),([\da-z.@_]+?)](.+?)\[/quote]#is',
            // quote
            '#\[\*](.+?)\[/\*]#is',
            // list
            '#\[spoiler=(.+?)](.+?)\[/spoiler]#is' // spoiler
        );
        // List of replacement
        $replace = array(
            '',
            // Зачеркнутый
            '<span style="font-size:x-small">$1</span>',
            // Маленький шрифт
            '<span style="font-size:large">$1</span>',
            // Цвет шрифта
            '</p><div class="quote"><blockquote>$1</blockquote></div><p>', // Цитата
            '</p><div class="bbCodeBlock bbCodeQuote"><div class="attribution type"><a href="' . SITE_URL . '/profile/$3.$2/">$3</a> đã viết</div><blockquote><p>$4</p></blockquote></div><p>',
            // Цитата
            '</p><div class="bblist">$1</div><p>',
            // Список
            '</p><div><div class="spoilerhead" onclick="var _n=this.parentNode.getElementsByTagName(\'div\')[1];if(_n.style.display==\'none\'){_n.style.display=\'\';}else{_n.style.display=\'none\';}">$1 (+/-)</div><div class="spoilerbody" style="display:none">$2</div></div><p>'
        );
        return preg_replace($search, $replace, $var);
    }
}