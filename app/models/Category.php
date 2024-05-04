<?php

class CategoryModel extends Model
{
    private dbSelectModel $dbSelect;
    private dbUpdateModel $dbUpdate;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->dbUpdate = $this->load->model('dbUpdate');
    }

    public function creator($row)
    {
        $this->dbSelect->GetResult("INSERT INTO `category` SET
        `title` = '" . $row['title'] . "',
        `slug` = '" . $row['slug'] . "'
        ");
    }

    public function edit($row, $id)
    {
        $this->dbUpdate->RowData('category', $row, ['id' => $id]);
    }

    public function delete($id)
    {
        $this->dbSelect->GetResult("DELETE FROM `category` WHERE `id` = '$id'");
        $totalBlog = $this->dbSelect->RowCount('blog', ['category' => $id, 'operator' => '=']);
        if ($totalBlog > 0) {
            while ($blog = mysqli_fetch_assoc($this->dbSelect->GetResult("SELECT * FROM `blog` WHERE `category` = '$id'"))) {
                $this->dbSelect->GetResult("DELETE FROM `blog` WHERE `category` = '$id'");
                $totalChapter = $this->dbSelect->RowCount('chapter', ['blog' => $blog['id'], 'operator' => '=']);
                if ($totalChapter > 0) {
                    while ($chapter = mysqli_fetch_assoc($this->dbSelect->GetResult("SELECT * FROM `blog` WHERE `category` = '$id'"))) {
                        $this->dbSelect->GetResult("DELETE FROM `chapter` WHERE `blog` = '" . $blog['id'] . "'");
                    }
                }
            }
        }
    }
}