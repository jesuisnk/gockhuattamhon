<?php

/** @var Router */
$router = Container::get(Router::class);

# trang chủ
$router->add('/', 'HomeController@index');
$router->add('/index.php', 'HomeController@index');
$router->add('/index.html', 'HomeController@index');
$router->add('/api/{plugin:[\w-]+}', 'HomeController@api', 'GET');
$router->add('/search', 'HomeController@search', 'GET');
$router->add('/blog', function() {
    return view('index');
});
$router->add('/category', function() {
    return view('home/category');
});

# robots
$router->add('/sitemap.xml', function() {
    return view('home/sitemap.xml');
});
$router->add('/robots.txt', function() {
    return view('home/robots.txt');
});

# đăng nhập
$router->add('/login', 'LoginController@login', 'GET|POST');
$router->add('/logout', 'LoginController@logout', 'GET|POST');

# view
$router->add('/manager', 'HomeController@manager', 'GET|POST');
$router->add('/category/{slug:[\w-]+}.html', 'CategoryController@view', 'GET|POST');
$router->add('/blog/{slug:[\w-]+}-{id}.html', 'BlogController@view', 'GET|POST');
$router->add('/blog/{slug:[\w-]+}-{id}/{chapter_id:[\d-]+}.html', 'ChapterController@view', 'GET|POST');

# creator
$router->add('/manager/category/creator', 'CategoryController@creator', 'GET|POST');
$router->add('/manager/blog/creator', 'BlogController@creator', 'GET|POST');
$router->add('/manager/blog.{id}/creator', 'ChapterController@creator', 'GET|POST');

# edit
$router->add('/manager/category.{id}/edit', 'CategoryController@edit', 'GET|POST');
$router->add('/manager/blog.{id}/edit', 'BlogController@edit', 'GET|POST');
$router->add('/manager/chapter.{id}/edit', 'ChapterController@edit', 'GET|POST');

# delete
$router->add('/manager/category.{id}/delete', 'CategoryController@delete', 'GET|POST');
$router->add('/manager/blog.{id}/delete', 'BlogController@delete', 'GET|POST');
$router->add('/manager/chapter.{id}/delete', 'ChapterController@delete', 'GET|POST');