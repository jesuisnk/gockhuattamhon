<?php

class ChapterController extends Controller
{
    private dbSelectModel $dbSelect;
    private ChapterModel $ChapterModel;
    private BlogLibrary $BlogLibrary;
    private TextMarkupLibrary $TextMarkup;
    private dbUpdateModel $dbUpdate;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->ChapterModel = $this->load->model('Chapter');
        $this->BlogLibrary = $this->load->library('Blog');
        $this->TextMarkup = $this->load->library('TextMarkup');
        $this->dbUpdate = $this->load->model('dbUpdate');
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
        $output .= '<a itemprop="item" href="' . $data['category']['url'] . '">';
        $output .= '<span itemprop="name">' . $data['category']['title'] . '</span>';
        $output .= '</a>';
        $output .= '<meta itemprop="position" content="2" />';
        $output .= '</li>';
        $output .= '<li class="breadcrumb-nav-list-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        $output .= '<a itemprop="item" href="' . $data['blog']['url'] . '">';
        $output .= '<span itemprop="name">' . $data['blog']['title'] . '</span>';
        $output .= '</a>';
        $output .= '<meta itemprop="position" content="3" />';
        $output .= '</li>';
        if ($data['chapter']) {
            $output .= '<li class="breadcrumb-nav-list-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            $output .= '<a itemprop="item" href="' . $data['chapter']['url'] . '">';
            $output .= '<span itemprop="name">' . $data['chapter']['title'] . '</span>';
            $output .= '</a>';
            $output .= '<meta itemprop="position" content="4" />';
            $output .= '</li>';
        }
        $output .= '</ol>';
        $output .= '</nav>';
        return $output;
    }

    public function view($blog_slug, $blog_id, $chapter_id)
    {
        $arr = [];
        $blog = [];
        $chapter = [];
        $chapter_id = $chapter_id > 0 ? $chapter_id : 1;
        $check = $this->dbSelect->RowCount('blog', ['id' => $blog_id, 'operator' => '=']) > 0 ? true : false;
        $check = $check ? ($this->dbSelect->RowCount('chapter', ['blog' => $blog_id, 'chapter_id' => $chapter_id, 'operator' => '=']) > 0 ? true : false) : false;
        if ($check) {
            $blog = $this->dbSelect->RowData('blog', 'id', $blog_id);
            $category = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $chapter = $this->dbSelect->RowData('chapter', 'chapter_id', $chapter_id);

            $sessionKey = 'blog_' . $blog_id . '_' . $chapter_id;
            if (!isset($_SESSION[$sessionKey])) {
                $this->dbUpdate->RowData('blog', ['view' => $blog['view'] + 1], ['id' => $blog_id]);
                $_SESSION[$sessionKey] = 1;
            }

            $arr['title'] = $chapter['title'];
            $description = $this->TextMarkup->description($chapter['content']);
            $arr['description'] = $description;
            $arr['blog'] = $blog;
            $arr['chapter'] = $chapter;
            $arr['breadcrumb'] = $this->breadcrumb([
                'category' => [
                    'url' => '/category/' . $category['slug'] . '.html',
                    'title' => $category['title']
                ],
                'blog' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html',
                    'title' => $blog['title']
                ],
                'chapter' => null
            ]);
            $chapters = $this->dbSelect->FetchAll("SELECT * FROM `chapter` WHERE `blog` = '$blog_id' ORDER BY id ASC");
            $chapterCount = $this->dbSelect->RowCount('chapter', ['blog' => $blog_id, 'operator' => '=']);
            $currentChapterIndex = array_search($chapter_id, array_column($chapters, 'chapter_id'));
            $previousChapter = ($currentChapterIndex > 0) ? $chapters[$currentChapterIndex - 1] : null;
            $nextChapter = ($currentChapterIndex < count($chapters) - 1) ? $chapters[$currentChapterIndex + 1] : null;
            $paging = null;
            if ($chapterCount > 1) {
                $paging = '<div class="topmenu">';
                $paging .= $previousChapter ? '<a href="/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $previousChapter['chapter_id'] . '.html" class="btn btn-default pull-left">Trang trước</a>' : null;
                $paging .= $nextChapter ? '<a href="/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $nextChapter['chapter_id'] . '.html" class="btn btn-default pull-right">Trang sau</a>' : null;
                $paging .= '<div style="clear:both"></div>';
                $paging .= '</div>';
            }
            $arr['paging'] = $paging;
            $arr['ChapList'] = [
                'get' => $chapters,
                'total' => $chapterCount
            ];
        }
        $arr['check'] = $check;

        return $this->view->render('view/chapter', $arr);
    }

    public function creator($blog_id)
    {
        $arr = [];
        #stats
        $check_blog = $this->dbSelect->RowCount('blog', ['id' => $blog_id, 'operator' => '=']) > 0 ? true : false;
        if ($check_blog) {
            $blog = $this->dbSelect->RowData('blog', 'id', $blog_id);
            $category = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $arr['title'] = 'Thêm chapter';
            $arr['breadcrumb'] = $this->breadcrumb([
                'category' => [
                    'url' => '/category/' . $category['slug'] . '.html',
                    'title' => $category['title']
                ],
                'blog' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html',
                    'title' => $blog['title']
                ],
                'chapter' => null
            ]);
            # lưu dữ liệu
            $error = [];
            $title = $this->request->postVar('title', '');
            $title = $this->dbSelect->RealEscape($title);
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
                $check = isCSRFTokenValid($token);
                if ($check) {
                    $error[] = 'Token không hợp lệ';
                }

                if (!$error) {
                    $this->ChapterModel->creator([
                        'title' => $title,
                        'content' => $content
                    ], $blog);
                    $LastChapter = $this->dbSelect->RowDataLast('chapter');
                    redirect('/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $LastChapter['chapter_id'] . '.html');
                } else {
                    $error = display_error($error);
                }
            }
            $arr['input'] = [
                'title' => $title,
                'content' => $content
            ];
            $arr['error'] = $error;
        }
        $arr['check'] = $check_blog;

        # view
        return $this->view->render('manager/chapter/creator', $arr);
    }

    public function edit($id)
    {
        $cat = null;
        $input = [];
        $total = $this->dbSelect->RowCount('chapter', ['id' => $id, 'operator' => '=']);

        if ($total > 0) {
            $chapter = $this->dbSelect->RowData('chapter', 'id', $id);
            $blog = $this->dbSelect->RowData('blog', 'id', $chapter['blog']);
            $category = $this->dbSelect->RowData('category', 'id', $blog['category']);
            $input = [
                'title' => $chapter['title'],
                'content' => $chapter['content']
            ];

            $error = [];
            $title = $this->request->postVar('title', '');
            $title = $this->dbSelect->RealEscape($title);
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
                $check = isCSRFTokenValid($token);
                if ($check) {
                    $error[] = 'Token không hợp lệ';
                }

                if (!$error) {
                    $this->ChapterModel->edit($input, $id);
                    redirect('/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $chapter['chapter_id'] . '.html');
                } else {
                    $error = display_error($error);
                }
            }
        }

        return $this->view->render('manager/chapter/edit', [
            'title' => 'Chỉnh sửa nội dung chapter',

            'total' => $total,
            'input' => $input,

            'breadcrumb' => $this->breadcrumb([
                'category' => [
                    'url' => '/category/' . $category['slug'] . '.html',
                    'title' => $category['title']
                ],
                'blog' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html',
                    'title' => $blog['title']
                ],
                'chapter' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $chapter['chapter_id'] . '.html',
                    'title' => $chapter['title']
                ]
            ])
        ]);
    }

    public function delete($id)
    {
        /**
         * Xóa theo trường `id`
         * Trường này dùng để đếm tăng dần số chapter của blog, để phân biệt với trường `chapter_id`
         */
        $cat = null;
        $blog = null;
        $total = $this->dbSelect->RowCount('chapter', ['id' => $id, 'operator' => '=']);
        if ($total > 0) {
            $chapter = $this->dbSelect->RowData('chapter', 'id', $id);
            $blog = $this->dbSelect->RowData('blog', 'id', $chapter['blog']);
            $category = $this->dbSelect->RowData('category', 'id', $blog['category']);
            if ($this->request->getMethod() === 'POST') {
                $this->ChapterModel->delete($id);
                redirect(url('/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html'));
            }
        }

        return $this->view->render('manager/chapter/delete', [
            'title' => 'Dọn dẹp danh sách chương',

            'total' => $total,
            'blog' => $blog,
            'chapter' => $chapter,

            'breadcrumb' => $this->breadcrumb([
                'category' => [
                    'url' => '/category/' . $category['slug'] . '.html',
                    'title' => $category['title']
                ],
                'blog' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '.html',
                    'title' => $blog['title']
                ],
                'chapter' => [
                    'url' => '/blog/' . $blog['slug'] . '-' . $blog['id'] . '/' . $chapter['chapter_id'] . '.html',
                    'title' => $chapter['title']
                ]
            ])
        ]);
    }
}