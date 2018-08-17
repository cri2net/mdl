<?php

// выводит ссылки на подстраницы объекта

$sections = [
    'history' => 'Історія платежів',
    'komdebt' => 'ЖКГ платежі',
    'instant' => 'Миттєві платежі',
];

$subsections = [
    'history' => [],
    'komdebt' => [],
    'instant' => [],
];

$iconnums = [
    'history' => 2,
    'komdebt' => 5,
    'instant' => 9,

];

$i = 0;
foreach ($sections as $key => $value) {
    
    $current = (($current_section == $key) || in_array($current_section, $subsections[$key]));

    if ($current) {
        ?>
        <li class="item-<?= $iconnums[$key] ?> active">
            <a><?= $value; ?></a>
        </li>
        <?php
    } else {
        ?>
        <li class="item-<?= $iconnums[$key] ?>">
            <a href="<?= BASE_URL; ?>/cabinet/payments/<?= $key; ?>/"><?= $value; ?></a>
        </li>
        <?php
    }
}
