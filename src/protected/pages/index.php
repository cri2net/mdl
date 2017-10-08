<?php

    use cri2net\php_pdo_db\PDO_DB;

    define('INDEX_FOOTER', true);
?>
<body>

<nav class="navbar navbar-fixed-top">
    <div class="container">
        <a class="logo" href="<?= BASE_URL; ?>"><img src="<?= BASE_URL; ?>/assets/images/logo.png" alt="ЦКС"></a>
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar top-bar"></span>
                <span class="icon-bar middle-bar"></span>
                <span class="icon-bar bottom-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <div id="search">
                <form>
                    <div class="input-div"><input type="text" name="search" value="" placeholder="Пошук"></div>
                    <a href="#" class="search-icon fa fa-search"></a>
                </form>
            </div>
            <ul class="nav navbar-nav">
                <li><a href="<?= BASE_URL; ?>/about_cks/">Про нас</a></li>
                <li><a href="<?= BASE_URL; ?>/services_list_and_docs/">Перелік послуг</a></li>
                <li><a href="<?= BASE_URL; ?>/service-centers/">Сервісні центри</a></li>
                <li><a href="<?= BASE_URL; ?>/cabinet/instant-payments/">Сплатити за послуги</a></li>
                <li><a href="<?= BASE_URL; ?>/cabinet/" class="bordered">Особистий кабінет</a></li>
            </ul>
        </div>
    </div>
</nav>
<section id="home-1" class="parallax">
    <div class="container">
        <h1>Центр<br>комунального<br>сервісу</h1>
        <p class="descr">Ваш експерт в сфері житлово-комунальних послуг</p>
        <div class="social pull-right">
            <a href="https://www.facebook.com/cks.kiev.ua/" target="_blank" class="fa fa-facebook"></a>
            <a href="https://www.youtube.com/channel/UCBZgKIDjq4AOOpYYKIK40kQ" target="_blank" class="fa fa-youtube"></a>
            <p>Ми поруч</p>
        </div>
    </div>
</section>
<section id="home-account">
    <div class="block-gray">
        <div class="container"> 
            <div class="row">
                <div class="col-md-6 hidden-sm hidden-ms hidden-xs">
                    <img src="<?= BASE_URL; ?>/assets/images/notebook.png" class="notebook" alt="Notebook">
                </div>
                <div class="col-md-6">
                    <div class="text">Швидко та зручно<br> сплачуйте послуги ЖКГ<br> онлайн</div>
                </div>
            </div>
        </div>
    </div>
    <div class="block-white grey">
        <div class="container">
            <h2>Перейти до особистого кабінету ?</h2>
            <a href="<?= BASE_URL; ?>/cabinet/" class="btn btn-yellow btn-lg">Увійти<span class="arrow-right"></span></a>
            <br><span class="gray">або</span> <a href="<?= BASE_URL ?>/main_help/cabinet_faq/" class="underline">Переглянути довідку</a>
        </div>
    </div>
</section>

<?php
    $time = time();
    $news = PDO_DB::table_list(News::TABLE, "is_actual=1 AND created_at<='$time'", "created_at DESC", 5);

    if (count($news) > 0) {
        ?>
        <section id="home-news">
            <div class="container">
                <h2 class="aligncenter">Наші новини</h2>
                <div class="row">
                <?php
                    for ($i=0; $i < count($news); $i++) {
                        
                        $date = date('d ', $news[$i]['created_at']) . $MONTHS[date('n', $news[$i]['created_at'])]['ua'];
                        if (date('Y') != date('Y', $news[$i]['created_at'])) {
                            $date .= date(' Y', $news[$i]['created_at']);
                        }

                        // if (mb_strlen($news[$i]['title'], 'UTF-8') > 50) {
                        //     $news[$i]['title'] = mb_substr($news[$i]['title'], 0, 50, 'UTF-8') . '...';
                        // }

                        $last = ($i == count($news) - 1);
                        $news[$i]['image'] = PDO_DB::first(News::IMAGES_TABLE, "news_id='{$news[$i]['id']}'", "is_main DESC, pos ASC");

                        ?>
                        <div class="<?= ($last) ? 'col-md-8 col-sm-12' : 'col-md-4 col-sm-6'; ?>">
                            <a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/" class="<?= $large ? 'item-large' : '' ?> item-<?= $news[$i]['color'] ?> matchHeight">
                                <?php
                                    if ($news[$i]['image']) {
                                        ?>
                                        <img src="<?= BASE_URL; ?>/photos/news/<?= ($last) ? '795x266fc' : '387x266fc'; ?>/<?= $news[$i]['image']['id']; ?>.jpg" class="full-width" alt="">
                                        <?php
                                    }
                                    else {
                                        ?>
                                        <img src="<?= BASE_URL ?>/assets/images/news/nophoto-<?= ($last) ? '795x266' : '387x266'; ?>.jpg" alt="cks" />
                                        <?php 
                                    }

                                ?>
                                <div class="descr">
                                    <h4><?= htmlspecialchars($news[$i]['title']); ?></h4>
                                    <p><?= ($news[$i]['announce']); ?></p>
                                    <div class="info">
                                        <span class="date"><?= $date; ?></span>
                                        <span class="views"><?= $news[$i]['views']; ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                ?>
                </div>
                <a href="<?= BASE_URL; ?>/news/" class="btn btn-yellow btn-lg aligncenter">Інші Новини</a>
            </div>
        </section>
        <?php
    }
?>

<section id="home-debt" class="parallax">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="header">Що робити якщо<br> ви стали боржником?</div>
            </div>
        </div>
        <div class="row list">
            <div class="col-md-1 col-md-offset-1 visible-md visible-lg"><span class="num">1</span></div>
            <div class="col-md-6 descr"><span class="hidden-md hidden-lg">1. </span>Питання та відповіді щодо заборгованості</div>
            <div class="col-md-4"><a href="<?= BASE_URL; ?>/informatsiya-dlya-borzhnikiv" class="btn btn-yellow-bordered btn-lg pull-right">Переглянути <span class="arrow-right"></span></a></div>
        </div>

        <div class="row list">
            <div class="col-md-1 col-md-offset-1 visible-md visible-lg"><span class="num">2</span></div>
            <div class="col-md-6 descr"><span class="hidden-md hidden-lg">2. </span>Заповніть форму для укладання договору про реструктуризацію боргу</div>
            <div class="col-md-4"><a href="https://docs.google.com/forms/d/1bJ1IMKQTp1Ssyj-u4yf9yW4dSpYcnb2Tqw5MUfJKhDA/viewform?edit_requested=true 
" target="_blank" class="btn btn-yellow-bordered btn-lg pull-right">Заповнити <span class="arrow-right"></span></a></div>
        </div>

        <div class="row list">
            <div class="col-md-1 col-md-offset-1 visible-md visible-lg"><span class="num">3</span></div>
            <div class="col-md-6 descr"><span class="hidden-md hidden-lg">3. </span>Cплатіть заборгованість через особистий кабінет ЦКС</div>
            <div class="col-md-4"><a href="<?= BASE_URL; ?>/cabinet/" class="btn btn-yellow btn-lg pull-right">Сплатити <span class="arrow-right"></span></a></div>
        </div>
    </div>
</section>
<section id="home-service" class="parallax">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="header">Особливий сервіс<br>допомоги користувачам</div>
            </div>
        </div>
        <div class="row">
            <div class="icons clearfix">
                <div class="col-md-4 col-sm-4">
                    <div class="image"><img src="<?= BASE_URL; ?>/assets/images/_service-1.png" alt="Сервіс"></div>
                    <p>Найбільша мережа зручних сервісних центрів</p>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="image"><img src="<?= BASE_URL; ?>/assets/images/_service-2.png" alt="Сервіс"></div>
                    <p>Команда професіоналів швидко допоможе з вирішенням комунальних питань</p>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="image"><img src="<?= BASE_URL; ?>/assets/images/_service-3.png" alt="Сервіс"></div>
                    <p>Мінімум очікування і черг- ми цінуємо ваш час</p>
                </div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/service-centers/" class="btn btn-yellow btn-lg aligncenter black">Знайти своЄ відділення</a>
    </div>
</section>
<section id="services-1" class="parallax">
    <div class="container">
        <h2>Додатковий сервіс</h2>
        <div class="row items">
            <div class="col-md-5ths item item-1">
                Сантенхнічні<br>послуги
            </div>
            <div class="col-md-5ths item item-2">
                Послуги з<br>електрики
            </div>
            <div class="col-md-5ths item item-3">
                Прибирання<br>приміщень
            </div>
            <div class="col-md-5ths item item-4">
                Ремонтно-підготовчі<br>послуги
            </div>
            <div class="col-md-5ths item item-5">
                Послуги для<br>ОСББ та ЖБК
            </div>
        </div>
        <h3>Найкращі спеціалісти, сертифіковані матеріали,<br>гарантія якості</h3>

        <div><a href="<?= BASE_URL ?>/services/" class="btn btn-yellow btn-lg">Детальніше</a></div>
    </div>
</section>  
<section id="home-comfort">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="header">Навчальний центр<br>“Платформа комфорту”</div>
                <p>Центр навчання «Платформа комфорту» створений навесні 2016 року при КК «Центр комунального сервісу» для проведення роз’яснювальної роботи направленої на підвищення правової обізнаності мешканців у частині діючого законодавства у вигляді лекцій, семінарів, практикумів, круглих столів.</p>
                <a href="http://edu.cks.kiev.ua/ " target="_blank" class="btn btn-yellow btn-lg">Детальніше <span class="arrow-right"></span></a>
            </div>
        </div>
    </div>
</section>
<section id="home-wiki">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="header">Соціальний проект<br>“Комунальна вікіпедія”</div>
                <p>Проект в якому кожен бажаючий має можливість задати питання стосовно сплати послуг, субсидій, ОСББ, тощо та отримати на нього кваліфіковану відповідь і все це абсолютно безкоштовно.</p>
                <a href="http://wiki.1551.gov.ua/pages/viewpage.action?pageId=3507952" target="_blank" class="btn btn-yellow btn-lg">Детальніше <span class="arrow-right"></span></a>
            </div>
        </div>
    </div>
</section>
