<?php
    $breadcrumbs = [
        ['title' => 'ЦКС', 'link' => '/']
    ];

    switch ($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/cabinet':
            $breadcrumbs[] = ['title' => 'Особистий кабінет', 'link' => '/cabinet/'];

            switch ($__route_result['values']['subpage']) {

                case 'registration':
                    $breadcrumbs[] = ['title' => 'Реєстрація'];
                    break;

                case 'verify-email':
                    $breadcrumbs[] = ['title' => 'Підтвердження електронної пошти'];
                    break;

                case 'login':
                    $breadcrumbs[] = ['title' => 'Вхід'];
                    break;

                case 'restore':
                    $breadcrumbs[] = ['title' => 'Відновлення доступу'];
                    break;

                case 'objects':
                    $breadcrumbs[] = ['title' => 'Об’єкти', 'link' => '/cabinet/objects/'];

                    if (Authorization::isLogin() && isset($__route_result['values']['id'])) {

                        $object = Flat::getUserFlatById($__route_result['values']['id']);
                        
                        if (($object !== null) && ($object['user_id'] == Authorization::getLoggedUserId())) {
                            
                            $object_title = ($object['title']) ? $object['title'] : Flat::getAddressString($object['flat_id']);
                           
                            $breadcrumbs[] = [
                                'title' => trim(htmlspecialchars($object_title)),
                                'link' => '/cabinet/objects/'. $object['id'] .'/'
                            ];

                            if (isset($__route_result['values']['section'])) {
                                $sections = [
                                    'bill'        => 'Рахунок до сплати',
                                    'detailbill'  => 'Історія нарахувань',
                                    'historybill' => 'Довідка про платежі',
                                    'edit'        => 'Редагувати об’єкт',
                                    'paybill'     => 'Спосіб сплати',
                                    'checkout'    => 'Перенаправлення',
                                    'processing'  => 'Сплата',
                                ];
                                $breadcrumbs[] = ['title' => $sections[$__route_result['values']['section']]];
                            }
                        }
                    }
                    break;


                case 'instant-payments':
                    $breadcrumbs[] = ['title' => 'Миттєві платежі', 'link' => '/cabinet/instant-payments/'];

                    if (isset($__route_result['values']['section'])) {
                        $sections = [
                            'dai'          => 'Штрафи за порушення ПДР',
                            'kindergarten' => 'Дитячий садок',
                            'cards'        => 'Перекази з карти на карту',
                            'phone'        => 'Поповнення рахунку на мобільному',
                            'cks'          => 'Сплата послуг ЦКС',
                            'budget'       => 'Платежі до бюджету',
                        ];
                        $breadcrumbs[] = ['title' => $sections[$__route_result['values']['section']]];
                    }
                    break;

                case 'payments':
                    $breadcrumbs[] = ['title' => 'Мої платежі', 'link' => '/cabinet/payments/'];

                    if (isset($__route_result['values']['section'])) {
                        $sections = [
                            'history' => 'Історія платежів',
                            'komdebt' => 'ЖКГ платежі',
                            'instant' => 'Миттєві платежі',
                            'details' => 'Деталі платежу № ',
                        ];

                        if ($__route_result['values']['section'] == 'details') {
                            $sections['details'] .= $__route_result['values']['id'];
                        }

                        $breadcrumbs[] = ['title' => $sections[$__route_result['values']['section']]];
                    }
                    break;

                case 'settings':
                    $breadcrumbs[] = ['title' => 'Налаштування профілю', 'link' => '/cabinet/settings/'];
                    $sections = [
                        'info' => 'Персональні дані',
                        'notifications' => 'Налаштування повідомлень',
                        'rule' => 'Управління профілем',
                    ];
                    $breadcrumbs[] = ['title' => $sections[$__route_result['values']['section']]];
                    break;
            }

            break;

        case 'page/payment-status':
            $breadcrumbs[] = ['title' => 'Статус транзакції'];
            break;

        case 'error/404':
            $breadcrumbs[] = ['title' => 'Помилка 404'];
            break;
    }
?>
<breadcrumbs itemscope itemtype="http://schema.org/BreadcrumbList">
    <?php
        for ($i=0; $i < count($breadcrumbs); $i++) {
            if ($i < count($breadcrumbs) - 1) {
                ?>
                <span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <a itemprop="item" href="<?= EXT_BASE_URL . $breadcrumbs[$i]['link']; ?>" target="_top"><span itemprop="name"><?= $breadcrumbs[$i]['title']; ?></span></a>
                    <meta itemprop="position" content="<?= $i + 1; ?>" />
                </span>&nbsp;&rarr;&nbsp;
                <?php
            } else {
                ?><span class="current"><?= $breadcrumbs[$i]['title']; ?></span><?php
            }
        }
    ?>
</breadcrumbs>
