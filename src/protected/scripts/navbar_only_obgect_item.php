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

    ?>
    <li class="item-<?= ++$i; ?> inner-nav__item <?= ($current) ? 'inner-nav__item--active' : ''; ?>">
        <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/<?= $key; ?>/" class="inner-nav__link <?= ($current) ? 'inner-nav__link--active' : ''; ?>"><?= $value; ?></a>
    </li>
    <?php
}
