<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div class="text">

            <p>Основною метою діяльності Концерну – є відкриття та організація роботи сервісних центрів для обслуговування споживачів з усіх питань надання житлово-комунальних послуг. Створення мережі сервісних центрів – це, в першу чергу, необхідність стандартизувати роботу усіх точок прийому споживачів з питань ЖКГ, які на сьогодні працюють. Організація роботи центрів - це потреба часу та змін, які відбулися в системі визначення нових виконавців послуг. Створення таких центрів комунального сервісу сприятиме підвищенню та покращенню сфери обслуговування споживачів у частині отримання консультацій та інформації з усіх питань ЖКГ. Основний акцент сервісних центрів робиться на наданні професійних консультацій споживачам з питань житлово-комунального господарства. Новий сервіс також передбачає підвищення якості стандартів обслуговування, а саме:</p>

            <p>
            > ввічливе ставлення до споживачів<br>
            > швидкість та якість надання консультацій<br>
            > створення комфортних умов очікування<br>
            > мінімальні черги<br>
            > Пріоритетним також буде створення нових форматів обслуговування:<br>
            можливість попереднього запису до спеціалістів за телефоном або через інтернет (економія часу й зусиль)
            > отримання інформації «не виходячи з дому» (по телефону, електронною поштою тощо)<br>
            > Заступник генерального директора з адміністративно-господарської діяльності</p>
        </div>

        <div class="about-form">
            <div class="row">
                <div class="col-md-8">
                    <div class="map-about matchHeight" id="map" data-lat="50.4359167" data-lng="30.5420269" data-zoom="17"></div>
                </div>
                <div class="col-md-4">
                    <div class="info matchHeight">
                        <div class="dropdown">
                          <button class="input-green no-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            Оберіть район
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu disabled">
                            <li><a href="#">Район 1</a></li>
                            <li><a href="#">Район 2</a></li>
                            <li><a href="#">Район 3</a></li>
                          </ul>
                        </div>  
                        <input type="text" class="input-green" placeholder="Ваша адреса"> 
                        <label class="checkbox black black-label">
                            <input checked="checked" value="inp_0" name="items[]" type="checkbox" class="">
                            <span>Веб камера</span>
                        </label>
                        <label class="checkbox black black-label">
                            <input checked="checked" value="inp_0" name="items[]" type="checkbox" class="">
                            <span>Термінал для сплати</span>
                        </label>
                        <input type="submit" class="btn btn-green btn-green-white" value="Пошук відділень">
                        <a href="#" class="all">перейти до списку всіх відділень</a>
                    </div>
                </div>
            </div>
        </div>
    </content>
</div>
