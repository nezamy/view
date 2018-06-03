# View
View - Template engine for PHP


## Usage
```php
require 'system/views/view.php';
$view = new System\Views\View;
$view->view('home');
```

## Default Options
```php
$view = new System\Views\View([
    'path'      => 'views/',
    'theme'     => 'default/',
    'layout'    => 'layout/default.php',
    'render'    => 'default/templates/',
    'cache'     => 'storage/cache',
    'compiler'  => true,
    //===========  echo Compiler
    //escape only
    'contentTags'        => ['{{ ', ' }}'],
    // escape and strip html tags
    'escapedContentTags' => ['{( ', ' )}'],
    //without escape
    'rawTags'            => ['{! ', ' !}']
]);
$view->view('home');
// pass data
$view->view('home', [
    'news' => [
        [
            'title' => 'Hello World',
            'content' => 'Here is some content',
        ],
        [
            'title' => 'Second page',
            'content' => 'Here is some content for second page',
        ]
    ]    
]);
```

## layout
```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@section('pageTitle')</title>
</head>
<body>
@section('content')

@section('scripts')
</body>
</html>
```

## view
- View file (views/default/home.php)
```php
@{
    $pageName = 'home page';
}@

@section('pageTitle'){{ $pageName }}@end

@section('content')
<h2>Hello it`s come from view file</h2>
@end

// scripts for this page only 
@section('scripts')
<script>

</script>
@end
```

## Views with render
```php
// news variable for test, you can pass variables to view
@{
    $news = [
        [
            'title' => 'Hello World',
            'content' => '<p>Here is some content</p>',
        ],
        [
            'title' => 'Second page',
            'content' => '<p>Here is some content for second page</p>',
        ]
    ];
}@

@section('content')
    <h2>Hello it's come from view file</h2>
    <?php foreach($news as $item);?>
        @render('block', ['item' => $item])
    <?php endforeach;?>
@end
```
- Render file (views/default/templates/block.php)
```php
<div>
    <h2>{{ $item['title'] }}</h2>
    {! $item['content'] !}
</div>
```


## Disable Layout
```php
$view = new System\Views\View;
$view->layout(false)->view('home');
// OR in view file i.e home.php
@layout('false')
```

## Change Layout
```php
$view = new System\Views\View;
$view->layout('layout/dashboard.php')->view('home');
// OR in view file i.e home.php
@layout('layout/dashboard.php')
```
