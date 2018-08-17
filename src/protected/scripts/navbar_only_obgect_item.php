<?php

// выводит ссылки на подстраницы объекта

$sections = [
    'bill'        => 'Рахунок до сплати',
    'detailbill'  => 'Історія нарахувань',
    'historybill' => 'Довідка про платежі',
    'edit'        => 'Редагувати об’єкт',
];

$subsections = [
    'bill'        => ['paybill', 'checkout', 'processing'],
    'detailbill'  => [],
    'historybill' => [],
    'edit'        => [],
];

$i = 0;

?>
<!-- <li class="item item-objects">
    <a href="<?= BASE_URL; ?>/cabinet/objects/">&larr; Об’єкти</a>
</li> -->
<?php

foreach ($sections as $key => $value) {
    
    $current = (($current_section == $key) || in_array($current_section, $subsections[$key]));

    if ($current) {
        ?>
        <li class="item-<?= ++$i; ?> active">
            <a><?= $value; ?></a>
        </li>
        <?php
    } else {
        ?>
        <li class="item-<?= ++$i; ?>">
            <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/<?= $key; ?>/"><?= $value; ?></a>
        </li>
        <?php
    }
}
