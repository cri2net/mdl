<?php
// ещё раз берём последний элемент (текущий) из массива, чтоб не привязываться к временной переменной из другого файла
$static_page = $__static_pages_array[count($__static_pages_array) - 1];
$children = StaticPage::getChildren($static_page['id']);
$see_also = StaticPage::getSeeAlso($static_page['id']);
$link = StaticPage::getPath($static_page['id']);

// увеличиваем число просмотров страницы
StaticPage::incrementViews($static_page['id']);

require_once(PROTECTED_DIR . '/layouts/_header.php');
require_once(PROTECTED_DIR . '/layouts/static_page.php');
require_once(PROTECTED_DIR . '/layouts/_footer.php');
