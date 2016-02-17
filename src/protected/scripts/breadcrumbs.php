<?php
    $breadcrumbs = [
        ['title' => 'ГіОЦ', 'link' => '/']
    ];

    switch ($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/index':
            $breadcrumbs[] = ['title' => 'Головна'];
            break;

        case 'page/search':
            $breadcrumbs[] = ['title' => 'Результати пошуку'];
            break;
        case 'page/tender':
            $breadcrumbs[] = ['title' => 'Тендерні закупівлі'];
            break;
        case 'page/about':
            $breadcrumbs[] = ['title' => 'Про ГіOЦ'];
            break;
        case 'page/media':
            $breadcrumbs[] = ['title' => 'Про ГіОЦ', 'link' => '/about/'];
            $breadcrumbs[] = ['title' => 'Медіа', 'link' => '/about/media/'];
            $breadcrumbs[] = ['title' => 'Відеоматеріали'];
            break;

        case 'page/foruser':
            $breadcrumbs[] = ['title' => 'Споживачу'];
            break;

        case 'page/chief':
            $breadcrumbs[] = ['title' => 'Керівництво'];
            break;

        case 'page/calc-devices':
            $breadcrumbs[] = ['title' => 'Розрахунок за показаннями квартирних приладів обліку'];
            break;
        case 'page/calc-subsidies':
            $breadcrumbs[] = ['title' => 'Орієнтовний онлайн розрахунок субсидій'];
            break;


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
                            
                            $object_title = ($object['title'])
                                ? $object['title']
                                : Flat::getAddressString($object['flat_id'], $object['city_id']);
                           
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
                            'dai'          => 'Штрафи ДАІ',
                            'kindergarten' => 'Дитячий садок',
                            'cards'        => 'Перекази з карти на карту',
                            'phone'        => 'Поповнення рахунку на мобільному'
                        ];
                        $breadcrumbs[] = ['title' => $sections[$__route_result['values']['section']]];
                    }
                    break;

                case 'payments':
                    $breadcrumbs[] = ['title' => 'Мої платежі', 'link' => '/cabinet/payments/'];

                    if (isset($__route_result['values']['section'])) {
                        $sections = [
                            'history' => 'Історія платежів',
                            'komdebt' => 'ЖКХ платежі',
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


        case 'page/contacts':
            $breadcrumbs[] = ['title' => 'Контакти'];
            break;

        case 'page/news':
            $breadcrumbs[] = ['title' => 'Новини'];
            break;
        case 'page/payment-status':
            $breadcrumbs[] = ['title' => 'Статус транзакції'];
            break;
        case 'page/news-item':
            $breadcrumbs[] = ['title' => 'Новини', 'link' => '/news/'];
            $breadcrumbs[] = [
                'title' => date('d ', $__news_item['created_at'])
                           . $MONTHS[date('n', $__news_item['created_at'])]['ua']
                           . date(' Y', $__news_item['created_at'])
            ];
            break;

        case 'static_page/index':
            $link = '/';
            for ($i=0; $i < count($__static_pages_array); $i++) {
                $link .= $__static_pages_array[$i]['key'] . '/';
                $breadcrumbs[] = ['title' => $__static_pages_array[$i]['breadcrumb'], 'link' => $link];
            }
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
                    <a itemprop="item" href="<?= BASE_URL . $breadcrumbs[$i]['link']; ?>"><span itemprop="name"><?= $breadcrumbs[$i]['title']; ?></span></a>
                    <meta itemprop="position" content="<?= $i + 1; ?>" />
                </span>&nbsp;/&nbsp;
                <?php
            } else {
                ?><span class="current"><?= $breadcrumbs[$i]['title']; ?></span><?php
            }
        }
    ?>
</breadcrumbs>