<?php

class ChapterModel extends Model
{
    private dbSelectModel $dbSelect;
    private dbUpdateModel $dbUpdate;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
        $this->dbUpdate = $this->load->model('dbUpdate');
    }

    public function creator($row, $blog)
    {
        $chapter_id = $blog['chapter'] + 1;

        $this->dbSelect->GetResult("INSERT INTO `chapter` SET
        `blog` = '" . $blog['id'] . "',
        `chapter_id` = '$chapter_id',
        `title` = '" . $row['title'] . "',
        `content` = '" . $row['content'] . "',
        `publish` = '" . TIME . "'
        ");
        $this->dbUpdate->RowData('blog', ['chapter' => $chapter_id, 'update' => TIME], ['id' => $blog['id']]);
    }

    public function edit($row, $id)
    {
        $this->dbUpdate->RowData('chapter', $row, ['id' => $id]);
    }

    public function delete($id)
    {
        $this->dbSelect->GetResult("DELETE FROM `chapter` WHERE `id` = '$id'");
    }
}