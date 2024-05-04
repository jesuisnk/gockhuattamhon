<?php

class BlogModel extends Model
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
        $this->dbSelect->GetResult("INSERT INTO `blog` SET
        `category` = '" . $row['category'] . "',
        `title` = '" . $row['title'] . "',
        `slug` = '" . $row['slug'] . "',
        `content` = '" . $row['content'] . "',
        `publish` = '" . TIME . "',
        `update` = '" . TIME . "'
        ");
    }

    public function edit($row, $id)
    {
        $this->dbUpdate->RowData('blog', $row, ['id' => $id]);
    }

    public function delete($id)
    {
        $this->dbSelect->GetResult("DELETE FROM `blog` WHERE `id` = '$id'");
        $totalChapter = $this->dbSelect->RowCount('chapter', ['blog' => $id, 'operator' => '=']);
        if ($totalChapter > 0) {
            while ($chapter = mysqli_fetch_assoc($this->dbSelect->GetResult("SELECT * FROM `blog` WHERE `category` = '$id'"))) {
                $this->dbSelect->GetResult("DELETE FROM `chapter` WHERE `blog` = '$id'");
            }
        }
    }
}