<?php

class CategoryController extends Controller
{
    private dbSelectModel $dbSelect;
    private CategoryModel $CategoryModel;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->CategoryModel = $this->load->model('Category');
    }

    public function breadcrumb($data, $mod = null)
    {
        $output = '<nav aria-label="Breadcrumb" class="breadcrumb-nav" itemscope itemtype="https://schema.org/BreadcrumbList">';
        $output .= '<ol class="breadcrumb-nav-list">';
        $output .= '<li class="breadcrumb-nav-list-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        if ($mod == 'home') {
            $output .= '<a itemprop="item" href="/">';
            $output .= '<span itemprop="name">Blog</span>';
            $output .= '</a>';
        } else {
            $output .= '<a itemprop="item" href="/manager">';
            $output .= '<span itemprop="name">Bảng quản trị</span>';
            $output .= '</a>';
        }
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

    public function view($slug)
    {
        $arr = [];
        $check = $this->dbSelect->RowCount('category', ['slug' => $slug, 'operator' => '=']) > 0 ? true : false;
        $arr['check'] = $check;
        if ($check > 0) {
            $cat = $this->dbSelect->RowData('category', 'slug', $slug);
            $arr['category'] = $cat;
            $arr['breadcrumb'] = $this->breadcrumb([
                'url' => '/category/' . $cat['slug'] . '.html',
                'title' => $cat['title']
            ], 'home');
            $arr['title'] = $cat['title'];
            $arr['totalBlog'] = $this->dbSelect->RowCount('blog', ['category' => $cat['id'], 'operator' => '=']);
        }
        return $this->view->render('view/category', $arr);
    }

    public function creator()
    {
        #stats
        $total = $this->dbSelect->TableCount('category');
        $list = ($total > 0) ? $this->dbSelect->FetchAll("SELECT * FROM `category` ORDER BY `id` DESC") : [];

        # lưu dữ liệu
        $error = null;
        $title = $this->request->postVar('title', '');
        $title = $this->dbSelect->RealEscape($title);
        $titleLen = (mb_strlen($title) < 5 || mb_strlen($title) > 50) ? true : false;
        $token = $this->request->postVar('csrf_token', '');
        $check = isCSRFTokenValid($token);
        if ($this->request->getMethod() === 'POST') {
            if (empty($title)) {
                $error = 'Vui lòng nhập tên chuyên mục';
            } else {
                if ($titleLen) {
                    $error = 'Tên chuyên mục có độ dài từ 5 đến 50 ký tự';
                }
                if ($check) {
                    $error = $check;
                }
                if (!$error) {
                    $row = [
                        'title' => $title,
                        'slug' => slug($title)
                    ];
                    $this->CategoryModel->creator($row);
                    unset($_SESSION['csrf_token']);
                    redirect($_SERVER['REQUEST_URI']);
                }
            }
        }

        # view
        return $this->view->render('manager/category/creator', [
            'title' => 'Quản lý chuyên mục',

            'total' => $total,
            'list' => $list,

            'error' => $error
        ]);
    }

    public function edit($id)
    {
        $cat = null;
        $total = $this->dbSelect->RowCount('category', ['id' => $id, 'operator' => '=']);
        if ($total > 0) {
            $cat = $this->dbSelect->RowData('category', 'id', $id);
            $oldtitle = $cat['title'];
            $title = $this->request->postVar('title', '');
            $title = $this->dbSelect->RealEscape($title);
            $titleLen = (mb_strlen($title) < 5 || mb_strlen($title) > 50) ? true : false;
            if ($this->request->getMethod() === 'POST') {
                if ($oldtitle != $title) {
                    if (empty($title)) {
                        $error = 'Vui lòng nhập tên chuyên mục';
                    } else {
                        if ($titleLen) {
                            $error = 'Tên chuyên mục có độ dài từ 5 đến 50 ký tự';
                        }
                        $check = $this->dbSelect->RowCount('category', ['slug' => slug($title), 'operator' => '=']) > 0 ? true : false;
                        if ($check) {
                            $error = 'Tên chuyên mục đã tồn tại. Vui lòng chọn tên khác';
                        }
                        if (!$error) {
                            $row = [
                                'title' => $title,
                                'slug' => slug($title)
                            ];
                            $this->CategoryModel->edit($row, $id);
                            redirect(url('/manager/category/creator'));
                        }
                    }
                } else {
                    redirect(url('/manager/category/creator'));
                }
            }
        }

        return $this->view->render('manager/category/edit', [
            'title' => 'Chỉnh sửa chuyên mục',

            'total' => $total,
            'cat' => $cat,
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
        $total = $this->dbSelect->RowCount('category', ['id' => $id, 'operator' => '=']);
        if ($total > 0) {
            $cat = $this->dbSelect->RowData('category', 'id', $id);
            $blog = $this->dbSelect->RowCount('blog', ['category' => $cat['id'], 'operator' => '=']);
            if ($this->request->getMethod() === 'POST') {
                $this->CategoryModel->delete($id);
                redirect(url('/manager/category/creator'));
            }
        }

        return $this->view->render('manager/category/delete', [
            'title' => 'Xóa chuyên mục',

            'total' => $total,
            'cat' => $cat,
            'blog' => $blog,
            'breadcrumb' => $this->breadcrumb([
                'url' => $cat ? '/category/' . $cat['slug'] . '.html' : '',
                'title' => $cat ? $cat['title'] : ''
            ])
        ]);
    }
}