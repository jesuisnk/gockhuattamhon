<?php

class HomeController extends Controller
{
    private dbSelectModel $dbSelect;
    private TextMarkupLibrary $TextMarkup;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->TextMarkup = $this->load->library('TextMarkup');
    }

    public function index($uri = 'index')
    {
        $arr = [];
        if (!file_exists(TPL . $uri . '.twig')) {
            $uri = 'error';
            $arr['title'] = '404 Not Found';
        }
        return $this->view->render($uri, $arr);
    }

    public function api($plugin = null)
    {
        if (file_exists(TPL . '/api/' . $plugin . '.twig')) {
            return $this->view->render('api/' . $plugin);
        } else {
            return null;
        }
    }

    public function manager()
    {
        #stats
        $totalCategory = $this->dbSelect->TableCount('category');
        $totalBlog = $this->dbSelect->TableCount('blog');
        $totalChapter = $this->dbSelect->TableCount('chapter');
        # view
        return $this->view->render('home/manager', [
            'title' => 'Quản lý blog',

            'category' => $totalCategory,
            'blog' => $totalBlog,
            'chapter' => $totalChapter
        ]);
    }

    public function breadcrumb($data)
    {
        $output = '<nav aria-label="Breadcrumb" class="breadcrumb-nav" itemscope itemtype="https://schema.org/BreadcrumbList">';
        $output .= '<ol class="breadcrumb-nav-list">';
        $output .= '<li class="breadcrumb-nav-list-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        $output .= '<a itemprop="item" href="/">';
        $output .= '<span itemprop="name">Blog</span>';
        $output .= '</a>';
        $output .= '<meta itemprop="position" content="1" />';
        $output .= '</li>';
        $output .= '<li class="breadcrumb-nav-list-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        $output .= '<a itemprop="item" href="' . $data['url'] . '">';
        $output .= '<span itemprop="name">' . $data['title'] . '</span>';
        $output .= '</a>';
        $output .= '<meta itemprop="position" content="2" />';
        $output .= '</li>';
        $output .= '</ol>';
        $output .= '</nav>';
        return $output;
    }

    public function search()
    {
        $arr = [];
        $query = get_get('query');
        $query = urldecode($query);
        $query = $this->dbSelect->RealEscape($query);
        if (empty($query) || in_array($query, [null, ''])) {
            $arr['error'] = 'empty';
        } else {
            $e_query = urlencode($query);
            $sqlFromBlog = "FROM blog WHERE title LIKE '%$query%' OR content LIKE '%$query%'";
            $Count = "SELECT COUNT(*) " . $sqlFromBlog;
            $Select = "SELECT DISTINCT * " . $sqlFromBlog;
            $countBlog = $this->dbSelect->Count($Count);
            $pagingConfig = pagingConfig($countBlog, 'page', 10);
            $start = $pagingConfig['start'];
            $end = $pagingConfig['end'];
            $page = $pagingConfig['page'];
            $page_max = $pagingConfig['page_max'];
            $arr['result'] = "<p style='text-align:center'>Có <b>$countBlog</b> bài viết phù hợp với nhãn <span style='color:red;font-weight:700'>$query</span></p>";
            $arr['total'] = $countBlog;
            $arr['list'] = $this->dbSelect->FetchAll($Select . " LIMIT $start, $end");
            $arr['paging'] = paging("?query=$e_query&page=", $page, $page_max);
            $arr['error'] = $countBlog > 0 ? null : $this->TextMarkup->alert('Không thể tìm thấy bài viết nào phù hợp với nhãn <b>' . $query . '</b>!', 'warning-title');
        }
        $arr['title'] = 'Tìm kiếm';
        $arr['breadcrumb'] = $this->breadcrumb([
            'url' => '/search',
            'title' => 'Tìm kiếm'
        ]);

        return $this->view->render('home/search', $arr);
    }
}