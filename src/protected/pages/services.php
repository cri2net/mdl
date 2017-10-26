<?php

    use cri2net\php_pdo_db\PDO_DB;

    define('INDEX_FOOTER', true);
?>

<body>

    <nav class="navbar navbar-fixed-top dark">
        <div class="container">
            <a class="logo" href="<?= BASE_URL ?>"><img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ЦКС"></a>
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
                    <li><a href="<?= BASE_URL ?>/about_cks/">Про нас</a></li>
                    <li><a href="<?= BASE_URL ?>/services_list_and_docs/">Перелік послуг</a></li>
                    <li><a href="#how">Алгоритм надання послуг</a></li>
                    <li><a href="<?= BASE_URL ?>/service-centers/">Розклад роботи</a></li>
                    <li><a class="bordered submit-request">Оформити заявку</a></li>
                </ul>
            </div>      
        </div>
    </nav>
    <section id="complex-1" class="parallax">
        <div class="container">
            <h1>Комплекс<br>побутових послуг<br>від професіоналів</h1>
            <div class="row items">
                <div class="col-md-4 item item-1">
                    Кваліфіковані<br>спеціалісти
                </div>
                <div class="col-md-4 item item-2">
                    Гарантія<br>та якість
                </div>
            </div>
        </div>
    </section>
    <section id="services-2" class="parallax">
        <div class="container">
            <h2>Наші<br>послуги</h2>
            <div class="alignCenter">
                <div class="row items">
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="electro">Електромонтажні<br>роботи</a>
                    </div>
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="montage">Монтажні<br>роботи</a>
                    </div>
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="santeh">Сантехнічні<br>роботи</a>
                    </div>          
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="remont">Ремонтні<br>роботи</a>
                    </div>
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="cleaning">Прибирання</a>
                    </div>
                    <div class="col-md-4">
                        <a class="matchHeight item-service" data-service="other">Інші послуги</a>
                    </div>                  
                </div>
                <form action="<?= BASE_URL ?>/services/request/" method="POST" id="request-form" >
                    <input type="hidden" name="service[electro]" value="0" />
                    <input type="hidden" name="service[montage]" value="0" />
                    <input type="hidden" name="service[santeh]" value="0" />
                    <input type="hidden" name="service[remont]" value="0" />
                    <input type="hidden" name="service[cleaning]" value="0" />
                    <input type="hidden" name="service[other]" value="0" />
                </form>
                <div><a class="btn btn-yellow btn-lg submit-request">Оформити заявку</a></div>

                <a href="<?= BASE_URL ?>/feedback/" class="italic"><u>Наші менеджери проконсультують вас</u></a>            
            </div>
        </div>
    </section>      
    <section id="how" class="parallax">
        <div class="container">
            <h2>Як це працює ?</h2>

            <div class="row items">
                <div class="col-md-5ths">
                    <div class=" item item-yellow matchHeight">
                        <h4>1</h4>
                        <h5>Обробка вашої заяви</h5>
                        <blockquote>все починається тут, наші менеджери швидко зв’яжуться з вами</blockquote>
                    </div>
                </div>
                <div class="col-md-5ths">
                    <div class=" item item-blue matchHeight">
                        <h4>2</h4>
                        <h5>Узгодження<br>часу</h5>
                        <blockquote>ми приїдемо саме тоді, коли вам зручно</blockquote>
                    </div>
                </div>      
                <div class="col-md-5ths">
                    <div class=" item item-blue matchHeight">
                        <h4>3</h4>
                        <h5>Виконання<br>робіт</h5>
                        <blockquote>гарантуємо, що роботу буде виконано швидко та якісно </blockquote>
                    </div>
                </div>      
                <div class="col-md-5ths">
                    <div class=" item item-blue matchHeight">
                        <h4>4</h4>
                        <h5>Сплата за послуги</h5>
                        <blockquote>сплачуйте будь-яким зручним для вас способом</blockquote>
                    </div>
                </div>      
                <div class="col-md-5ths">
                    <div class=" item item-green matchHeight">
                        <h4>5</h4>
                        <h5>Акт виконаних робіт та гарантія</h5>
                        <blockquote>отримайте акт та гарантії</blockquote>
                    </div>
                </div>
            </div>

            <div class="alignCenter">
                <!-- <div><a href="" class="btn btn-yellow btn-lg submit-request">Оформити заявку</a></div> -->

                <a href="<?= BASE_URL ?>/feedback/" class="italic"><u>Наші менеджери проконсультують вас</u></a>            
            </div>          
        </div>
    </section>      
    <section id="contacts" class="parallax">
        <div class="container">

            <h2>Розклад роботи</h2>

            <div class="text">
                <p><strong>Майстер:</strong>  з 07:00 до 21:00 (Без вихідних).</p>

                <p><strong>Відділ продажу:</strong>  з 09:00 до 18:00 <br>
                (Субота, Неділя - вихідні).</p>

                <p><strong>E-mail:</strong>  <a href="#">savchuk.s@src.kiev.ua</a></p>

                <p><strong>Менеджер з продажу:</strong>   (044) 591-56-82</p>
            </div>

            <div class="form">
                <div class="container">
                    <form>
                        <h4>Залиште свій телефон, ми допоможемо</h4>

                        <div class="form-group">
                            <input type="text" name="phone" class="phone" placeholder="(xxx) xx - xx - xxx">
                            <input type="submit" class="btn btn-yellow hidden-xs" value="зателефонуйте мені">
                            <input type="submit" class="btn btn-yellow visible-xs" value="зателефонуйте">
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>  
    <script>
    $(function(){
        $('.item-service').click(function(){
            var service = $(this).attr('data-service');
            var val = parseInt($('input[name="service[' + service + ']"]').val());
            $('input[name="service[' + service + ']"]').val(val == 0 ? 1 : 0);
            $(this).toggleClass('checked');
        });

        $('.submit-request').click(function(){ $('#request-form').submit() })
    });
    </script>