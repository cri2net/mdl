<?php

    use cri2net\php_pdo_db\PDO_DB;

    define('INDEX_FOOTER', true);
?>
<body>

<nav class="navbar navbar-fixed-top">
    <div class="container">
        <a class="logo" href="index.php"><img src="<?= BASE_URL; ?>/assets/images/logo.png" alt="ЦКС"></a>
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
                <li><a href="<?= BASE_URL; ?>/about/">Про нас</a></li>
                <li><a href="#">Перелік послуг</a></li>
                <li><a href="#">Сервісні центри</a></li>
                <li><a href="#">Сплатити за послуги</a></li>
                <li><a href="#" class="bordered">Увійти в особистий кабінет</a></li>
            </ul>
        </div>
    </div>
</nav>
<section id="home-1" class="parallax">
    <div class="container">
        <h1>Центр<br>комунального<br>сервісу</h1>
        <p class="descr">Сплачуй  комуналку легко!</p>
        <div class="social pull-right">
            <a href="#" class="fa fa-facebook"></a>
            <a href="#" class="fa fa-youtube"></a>
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
                    <div class="text">Швидко та зручно<br> сплачуйте послуги жкх<br> онлайн</div>
                </div>
            </div>
        </div>
    </div>
    <div class="block-white">
        <div class="container">
            <h2>Перейти до особистого кабінету ?</h2>
            <a href="<?= BASE_URL; ?>/cabinet/" class="btn btn-yellow btn-lg">Увійти<span class="arrow-right"></span></a>
            <br><span class="gray">або</span> <a href="#" class="underline">Переглянути довідку</a>
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

                        if (mb_strlen($news[$i]['title'], 'UTF-8') > 50) {
                            $news[$i]['title'] = mb_substr($news[$i]['title'], 0, 50, 'UTF-8') . '...';
                        }

                        $last = ($i == count($news) - 1);
                        $news[$i]['image'] = PDO_DB::first(News::IMAGES_TABLE, "news_id='{$news[$i]['id']}'", "is_main DESC, pos ASC");

                        ?>
                        <div class="<?= ($last) ? 'col-md-8 col-sm-12' : 'col-md-4 col-sm-6'; ?>">
                            <a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/" class="matchHeight">
                                <?php
                                    if ($news[$i]['image']) {
                                        ?>
                                        <img src="<?= BASE_URL; ?>/photos/news/<?= ($last) ? '795x266' : '410x274'; ?>/<?= $news[$i]['image']['id']; ?>.jpg" class="full-width" alt="">
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
            <div class="col-md-6 descr"><span class="hidden-md hidden-lg">1. </span>Перевірити список абонентів, що мають заборгованість</div>
            <div class="col-md-4"><a href="#" class="btn btn-yellow-bordered btn-lg pull-right">Перевірити <span class="arrow-right"></span></a></div>
        </div>

        <div class="row list">
            <div class="col-md-1 col-md-offset-1 visible-md visible-lg"><span class="num">2</span></div>
            <div class="col-md-6 descr"><span class="hidden-md hidden-lg">2. </span>Заповніть форму для укладання договору про реструктуризацію боргу</div>
            <div class="col-md-4"><a href="#" class="btn btn-yellow-bordered btn-lg pull-right">Заповнити <span class="arrow-right"></span></a></div>
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
        <a href="#" class="btn btn-yellow btn-lg aligncenter black">Знайти своЄ відділення</a>
    </div>
</section>
<section id="home-comfort">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="header">Навчальний центр<br>“Платформа комфорту”</div>
                <p>Центр навчання «Платформа комфорту» створений навесні 2016 року при КК «Центр комунального сервісу» для проведення роз’яснювальної роботи направленої на підвищення правової обізнаності мешканців у частині діючого законодавства у вигляді лекцій, семінарів, практикумів, круглих столів.</p>
                <a href="#" class="btn btn-yellow btn-lg">Детальніше <span class="arrow-right"></span></a>
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
                <a href="#" class="btn btn-yellow btn-lg">Детальніше <span class="arrow-right"></span></a>
            </div>
        </div>
    </div>
</section>
