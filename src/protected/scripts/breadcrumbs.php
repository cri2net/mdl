<?php
    $breadcrumbs = array(
        array('title' => 'ГіОЦ', 'link' => '/')
    );

    switch($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/index':
            $breadcrumbs[] = array('title' => 'Головна');
            break;

        case 'page/about':
            $breadcrumbs[] = array('title' => 'Про ГіOЦ');
            break;
        case 'page/media':
            $breadcrumbs[] = array('title' => 'Про ГіОЦ', 'link' => '/about/');
            $breadcrumbs[] = array('title' => 'Медiа', 'link' => '/about/media/');
            $breadcrumbs[] = array('title' => 'Відеоматеріали');
            break;

        case 'page/foruser':
            $breadcrumbs[] = array('title' => 'Споживачу');
            break;

        case 'page/chief':
            $breadcrumbs[] = array('title' => 'Керівництво');
            break;

        case 'page/calc-devices':
            $breadcrumbs[] = array('title' => 'Розрахунок за показаннями квартирних приладів обліку');
            break;
        case 'page/calc-subsidies':
            $breadcrumbs[] = array('title' => 'Орієнтовний On-line розрахунок субсидій');
            break;


        case 'page/cabinet':
            $breadcrumbs[] = array('title' => 'Особистий кабiнет', 'link' => '/cabinet/');

            switch ($__route_result['values']['subpage']) {

                case 'registration':
                    $breadcrumbs[] = array('title' => 'Реєстрація');
                    break;

                case 'login':
                    $breadcrumbs[] = array('title' => 'Вхід');
                    break;

                case 'objects':
                    $breadcrumbs[] = array('title' => 'Об\'єкти', 'link' => '/cabinet/objects/');

                    if (Authorization::isLogin() && isset($__route_result['values']['id'])) {

                        $object = Flat::getUserFlatById($__route_result['values']['id']);
                        
                        if (($object !== null) && ($object['user_id'] == Authorization::getLoggedUserId())) {
                            
                            $object_title = ($object['title'])
                                ? $object['title']
                                : Flat::getAddressString($object['flat_id'], $object['city_id']);
                           
                            $breadcrumbs[] = array(
                                'title' => htmlspecialchars($object_title),
                                'link' => '/cabinet/objects/'. $object['id'] .'/'
                            );

                            if (isset($__route_result['values']['section'])) {
                                $sections = array(
                                    'bill' => 'Рахунок до сплати',
                                    'detailbill' => 'Історія нарахувань',
                                    'historybill' => 'Довідка про платежі',
                                    'edit' => 'Редагувати об\'єкт',
                                );
                                $breadcrumbs[] = array('title' => $sections[$__route_result['values']['section']]);
                            }
                        }
                    }
                    break;

                case 'settings':
                    $breadcrumbs[] = array('title' => 'Налаштування профілю', 'link' => '/cabinet/settings/');
                    $sections = array(
                        'info' => 'Персональні дані',
                        'notifications' => 'Налаштування повідомлень',
                        'rule' => 'Управління профілем',
                    );
                    $breadcrumbs[] = array('title' => $sections[$__route_result['values']['section']]);
                    break;
            }

            break;


        case 'page/contacts':
            $breadcrumbs[] = array('title' => 'Контакти');
            break;

        case 'page/news':
            $breadcrumbs[] = array('title' => 'Новини');
            break;
        case 'page/news-item':
            $breadcrumbs[] = array('title' => 'Новини', 'link' => '/news/');
            $breadcrumbs[] = array(
                'title' => date('d ', $__news_item['created_at'])
                           . $MONTHS[date('n', $__news_item['created_at'])]['ua']
                           . date(' Y', $__news_item['created_at'])
            );
            break;

        case 'static_page/index':
            $link = '/';
            for ($i=0; $i < count($__static_pages_array); $i++) {
                $link .= $__static_pages_array[$i]['key'] . '/';
                $breadcrumbs[] = array('title' => $__static_pages_array[$i]['breadcrumb'], 'link' => $link);
            }
            break;

        case 'error/404':
            $breadcrumbs[] = array('title' => 'Помилка 404');
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