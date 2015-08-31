<?php
    // ещё раз берём последний элемент (текущий) из массива, чтоб не привязываться к временной переменной из другого файла
    $static_page = $__static_pages_array[count($__static_pages_array) - 1];
    $children = StaticPage::getChildren($static_page['id']);
    $link = StaticPage::getPath($static_page['id']);

    // увеличиваем число просмотров страницы
    StaticPage::incrementViews($static_page['id']);
   
    require_once(ROOT . '/protected/layouts/_header.php');
    require_once(ROOT . '/protected/layouts/static_page.php');
    require_once(ROOT . '/protected/layouts/_footer.php');
