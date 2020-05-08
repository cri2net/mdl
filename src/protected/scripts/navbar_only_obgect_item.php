<?php

// выводит ссылки на подстраницы объекта

$sections = [
    'bill'        => 'Рахунок до сплати',
    'detailbill'  => 'Історія нарахувань',
    'historybill' => 'Довідка про платежі',
    'edit'        => 'Редагувати об’єкт',
];

$subsections = [
    'bill'        => ['paybill'],
    'detailbill'  => [],
    'historybill' => [],
    'edit'        => [],
];

$i = 0;

foreach ($sections as $key => $value) {
    
    $current = (($current_section == $key) || in_array($current_section, $subsections[$key]));

    if ($current) {
        ?>
        <li class="item-<?= ++$i; ?> active inner-nav__item inner-nav__item--active">
            <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/<?= $key; ?>/" class="inner-nav__link inner-nav__link--active"><?= $value; ?></a>
        </li>
        <?php
    } else {
        ?>
        <li class="item-<?= ++$i; ?> inner-nav__item">
            <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/<?= $key; ?>/" class="inner-nav__link"><?= $value; ?></a>
        </li>
        <?php
    }
}
