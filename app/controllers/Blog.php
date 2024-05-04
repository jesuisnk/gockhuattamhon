<?php

class BlogController extends Controller
{
    private dbSelectModel $dbSelect;
    private dbUpdateModel $dbUpdate;
    private BlogModel $BlogModel;
    private BlogLibrary $BlogLibrary;
    private TextMarkupLibrary $TextMarkup;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->dbUpdate = $this->load->model('dbUpdate');
        $this->BlogModel = $this->load->model('Blog');
        $this->BlogLibrary = $this->load->library('Blog');
        $this->TextMarkup = $this->load->library('TextMarkup');
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

    public function view($slug, $id)
    {
        $blog = [];
        $category = [];
        $check = $this->dbSelect->RowCount('blog', ['id' => $id, 'operator' => '=']) > 0 ? true : false;
        if ($check) {
            $blog = $this->dbSelect->RowData('blog', 'id', $id);
            $category = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $description = $this->TextMarkup->description($blog['content']);

            if(isset($_SESSION['blog_views'][$id])) {
                $view = $_SESSION['blog_views'][$id];
            } else {
                $view = 0;
            }
            if ($view < 1) {
                $this->dbUpdate->RowData('blog', ['view' => $blog['view'] + 1], ['id' => $id]);
                $_SESSION['blog_views'][$id] = 1;
            }

            $SameCategory = [
                'total' => 0,
                'list' => []
            ];
            $SameCategory['total'] = $this->dbSelect->RowCount('blog', ['id' => $id, 'operator' => '!=']);
            if ($SameCategory['total'] > 3) {
                $SameCategory['list'] = $this->dbSelect->FetchAll("SELECT * FROM `blog` WHERE `id` != '$id' ORDER BY RAND() LIMIT 3");
            }

            $Chapter = [
                'total' => $this->dbSelect->RowCount('chapter', ['blog' => $id, 'operator' => '==']),
                'first' => $this->dbSelect->RowDataFirst('chapter', "WHERE `blog` = '$id'"),
                'last' => $this->dbSelect->RowDataLast('chapter', "WHERE `blog` = '$id'")
            ];
        }

        return $this->view->render('view/blog', [
            'title' => $blog['title'],
            'description' => $description,

            'check' => $check,
            'blog' => $blog,
            'category' => $category,
            'SameCategory' => $SameCategory,
            'Chapter' => $Chapter,

            'breadcrumb' => $this->breadcrumb([
                'title' => $category['title'],
                'url' => '/category/' . $category['slug'] . '.html'
            ])
        ]);
    }

    public function creator()
    {
        #stats
        $total = $this->dbSelect->TableCount('blog');

        # lưu dữ liệu
        $error = [];
        $title = $this->request->postVar('title', '');
        $title = $this->dbSelect->RealEscape($title);

        $category = $this->request->postVar('category', '');
        $content = $this->request->postVar('msg', '');
        $content = $this->dbSelect->RealEscape($content);
        $token = $this->request->postVar('csrf_token', '');
        if ($this->request->getMethod() === 'POST') {
            $check = $this->BlogLibrary->validateTitle($title);
            if ($check) {
                $error[] = $check;
            }
            $check = $this->BlogLibrary->validateContent($content);
            if ($check) {
                $error[] = $check;
            }
            $slug = slug($title);
            $check = $this->dbSelect->RowCount('blog', ['slug' => $slug, 'operator' => '=']) > 0 ? true : false;
            if ($check) {
                $error[] = 'Bài viết đã tồn tại. Vui lòng đặt tiêu đề khác cho bài viết nếu bài viết này có nội dung khác';
            }
            $check = $this->dbSelect->RowCount('category', ['id' => $category, 'operator' => '=']) > 0 ? false : true;
            if ($check) {
                $error[] = 'Chuyên mục được chọn không tồn tại';
            }
            $check = isCSRFTokenValid($token);
            if ($check) {
                $error[] = 'Token không hợp lệ';
            }

            if (!$error) {
                $this->BlogModel->creator([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $content,
                    'category' => $category
                ]);
                $LastBlog = $this->dbSelect->RowDataLast('blog');
                redirect('/blog/' . $LastBlog['slug'] . '-' . $LastBlog['id'] . '.html');
            } else {
                $error = display_error($error);
            }
        }

        # view
        return $this->view->render('manager/blog/creator', [
            'title' => 'Viết bài mới',

            'total' => $total,
            'input' => [
                'title' => $title,
                'content' => $content
            ],

            'error' => $error
        ]);
    }

    public function edit($id)
    {
        $cat = null;
        $input = [];
        $total = $this->dbSelect->RowCount('blog', ['id' => $id, 'operator' => '=']);

        if ($total > 0) {
            $blog = $this->dbSelect->RowData('blog', 'id', $id);
            $cat = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $input = [
                'title' => $blog['title'],
                'slug' => $blog['slug'],
                'category' => $blog['category'],
                'content' => $blog['content']
            ];

            $error = [];
            $title = $this->request->postVar('title', '');
            $title = $this->dbSelect->RealEscape($title);

            $category = $this->request->postVar('category', '');
            $content = $this->request->postVar('msg', '');
            $content = $this->dbSelect->RealEscape($content);
            $token = $this->request->postVar('csrf_token', '');
            if ($this->request->getMethod() === 'POST') {
                $check = $this->BlogLibrary->validateTitle($title);
                if ($check) {
                    $error[] = $check;
                } else {
                    $input['title'] = $title;
                }
                $check = $this->BlogLibrary->validateContent($content);
                if ($check) {
                    $error[] = $check;
                } else {
                    $input['content'] = $content;
                }
                $slug = slug($title);
                if ($slug != $input['slug']) {
                    $check = $this->dbSelect->RowCount('blog', ['slug' => $slug, 'operator' => '=']) > 0 ? true : false;
                    if ($check) {
                        $error[] = 'Bài viết đã tồn tại. Vui lòng đặt tiêu đề khác cho bài viết nếu bài viết này có nội dung khác';
                    } else {
                        $input['slug'] = $slug;
                    }
                }
                $check = $this->dbSelect->RowCount('category', ['id' => $category, 'operator' => '=']) > 0 ? false : true;
                if ($check) {
                    $error[] = 'Chuyên mục được chọn không tồn tại';
                } else {
                    $input['category'] = $category;
                }
                $check = isCSRFTokenValid($token);
                if ($check) {
                    $error[] = 'Token không hợp lệ';
                }

                if (!$error) {
                    $this->BlogModel->edit($input, $id);
                    $blog = $this->dbSelect->RowData('blog', 'id', $id);
                    redirect('/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html');
                } else {
                    $error = display_error($error);
                }
            }
        }

        return $this->view->render('manager/blog/edit', [
            'title' => 'Chỉnh sửa bài viết',

            'total' => $total,
            'input' => $input,

            'breadcrumb' => $this->breadcrumb([
                'url' => $cat ? '/category/' . $cat['slug'] . '.html' : '',
                'title' => $cat ? $cat['title'] : ''
            ])
        ]);
    }

    public function delete($id)
    {
        $cat = null;
        $blog = null;
        $total = $this->dbSelect->RowCount('blog', ['id' => $id, 'operator' => '=']);
        if ($total > 0) {
            $blog = $this->dbSelect->RowData('blog', 'id', $id);
            $cat = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $chapter = $this->dbSelect->RowCount('chapter', ['blog' => $id, 'operator' => '=']);
            if ($this->request->getMethod() === 'POST') {
                $this->BlogModel->delete($id);
                redirect(url('/manager'));
            }
        }

        return $this->view->render('manager/blog/delete', [
            'title' => 'Xóa bài viết',

            'total' => $total,
            'cat' => $cat,
            'chapter' => $chapter,

            'breadcrumb' => $this->breadcrumb([
                'url' => $cat ? '/category/' . $cat['slug'] . '.html' : '',
                'title' => $cat ? $cat['title'] : ''
            ])
        ]);
    }
}