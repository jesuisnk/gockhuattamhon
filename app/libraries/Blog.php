<?php

class BlogLibrary
{
    public function validateTitle($title)
    {
        $error = null;
        if (empty($title)) {
            $error = 'Tiêu đề bài viết không được bỏ trống';
        } else {
            if (mb_strlen($title) < 5 || mb_strlen($title) > 250) {
                $error = 'Tiêu đề bài viết phải có độ dài từ 5 đến 250 ký tự';
            }
        }
        return $error;
    }

    public function validateContent($content)
    {
        $error = null;
        if (empty($content)) {
            $error = 'Nội dung bài viết không được bỏ trống';
        } else {
            $minLength = 200;
            $maxLength = 32000;
            if (strlen($content) < $minLength || strlen($content) > $maxLength) {
                $error = "Nội dung bài viết phải có độ dài từ $minLength đến $maxLength ký tự";
            }
        }
        return $error;
    }
}