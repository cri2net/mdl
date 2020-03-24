<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>p2p</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto|Source+Sans+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        .ui-autocomplete {
            z-index: 9999999999999;
        }
        main, header, footer {
            opacity: 0.5;
        }
        .preloader-area {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin-top: -100px;
            z-index: 19999;
        }
        .preloader-gif {
            content: "";
            position: fixed;
            display: block;
            width: 200px;
            height: 150px;
            z-index: 10000;
            top: calc(50% - 100px);
            left: calc(50% - 100px);
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", stopSpinner);
        function stopSpinner() {
            $('header').css('opacity', '1');
            $('main').css('opacity', '1');
            $('footer').css('opacity', '1');
            $('.preloader-gif').css('display', 'none');
            $('.preloader-area').css('display', 'none');
        }
    </script>
</head>
<body>
<div class="preloader-gif">
    <img src="<?= BASE_URL; ?>/assets/images/loader.gif" alt="">
</div>
<div class="modal fade" id="modal-error" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 style="color: red" class="modal-title">Помилка</h5>
            </div>
            <div class="modal-body">
                <p id="modal-error-text" class="text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-custom" data-dismiss="modal">ОК</button>
            </div>
        </div>
    </div>
</div>
<header id="headerP2P">
    <div class="container d-flex align-items-center">
        <div class="main-logo col-12">
            <a href="https://my.kyivcity.gov.ua/" style="text-decoration: none;" class="d-flex align-items-center p-md-0">
                <img src="<?= BASE_URL; ?>/assets/images/Logo.svg"
                    alt="logo"><span>ВЕБ-ПОРТАЛ<br>НАДАННЯ<br>ЕЛЕКТРОННИХ<br>ПОСЛУГ</span>
            </a>
        </div>
    </div>
</header>
<main>
    <div class="container d-flex flex-column align-items-center">
        <div class="row d-flex flex-column col-12 p-0">
            <div class="breadcrumbs">
                <ul class="d-flex">
                    <li><a href="https://my.kyivcity.gov.ua/">Головна</a></li>
                    <span>/</span>
                    <li><a href="https://my.kyivcity.gov.ua/catalog">Каталог послуг</a></li>
                    <span>/</span>
                    <li>Переказ коштів з картки на карту</li>
                </ul>
            </div>
            <div class="main-title pt-3 pb-4">
                <h1>Переказ з карти на карту</h1>
            </div>
            <form action="" method="post" id="paymentFormCard"
                  class="p2p-container d-flex flex-column align-items-center col-12 p-0">
                <div class="p2p-form col-12 col-lg-6">
                    <div class="card-owner col-12 p-md-4 p-0">
                        <h3>Карта відправника</h3>
                        <div class="card-owner__card mt-3">
                            <input maxlength="19" type="text" name="card_no"
                                   onkeypress=" return isNumber(event)" value="<?= @$form_data['card_no'] ?>"
                                   placeholder="XXXX-XXXX-XXXX-XXXX"
                                   data-custom-validate="card-number1"
                                   class="my-4 mx-md-3 number-validate white-space">
                            <div class="d-flex justify-content-between mx-md-3 mt-5">
                                <div class="expiry-date d-flex flex-column align-items-center col-6 col-md-4">
                                    <label for="expiryYear">Термін дії</label>
                                    <div class="expiry-date__group d-flex">
                                        <input maxlength="2" id="month" type="text" name="month" data-custom-validate="expiry-month"
                                               onkeypress=" return isNumber(event)" value="<?= @$form_data['month'] ?>"
                                               class="number-validate">
                                        <span>/</span>
                                        <input maxlength="2" id="year" type="text" name="year" data-custom-validate="expiry-year"
                                               onkeypress=" return isNumber(event)" value="<?= @$year ?>"
                                               class="number-validate">
                                    </div>
                                </div>
                                <div class="cvc2-cvv2 d-flex flex-column align-items-center col-6 col-md-3 mb-5">
                                    <label for="cardCVC">CVV2/CVC2</label>
                                    <input maxlength="3" id="cardCVC" type="password"
                                           data-custom-validate="cvc"
                                           onkeypress="return isNumber(event)" name="cvv" value=""
                                           class="number-validate">
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center col-12 mb-1 error-msg-content text-danger"></div>
                        </div>
                    </div>
                    <div class="card-owner col-12 p-md-4 px-0 py-4">
                        <h3>Карта отримувача</h3>
                        <div class="card-owner__card mt-3">
                            <input maxlength="19" type="text" name="card_no_dest"
                                   data-custom-validate="card-number2"
                                   value="<?= @$form_data['card_no_dest'] ?>"
                                   onkeypress=" return isNumber(event)" placeholder="XXXX-XXXX-XXXX-XXXX"
                                   class="my-4 mx-md-3 number-validate white-space">
                            <div class="error-for-card text-center"></div>
                        </div>
                    </div>
                    <div class="col-12 p-md-4 px-0 py-4 d-flex flex-column">
                        <div class="d-flex p-0 justify-content-between">
                            <h3>Сума переказу</h3>
                            <div class="user-amount d-flex flex-column">
                                <div>
                                    <input maxlength="8" type="text" name="sum" value=""
                                           id="totalAmount"
                                           onkeypress=" return isNumber(event)"
                                           class="number-validate text-center">
                                    <input type="hidden" name="step_2" value="1">
                                    <span>грн</span>
                                </div>
                                <span>Комісія: 1% + 5 грн</span>
                            </div>
                        </div>
                        <div class="amountError text-center"></div>
                    </div>
                </div>
                <div class="col-12 user-terms agreement terms-box py-3">
                    <div class="text-center">
                        Натискаючи «Переказати» Ви приймаєте умови <a target="_blank" href="https://alfabank.ua/upload/Dogovor_p2p_2014.pdf">«Публічної пропозиції»</a>
                    </div>
                </div>
                <div class="col-12 col-lg-6 submit-btn d-flex justify-content-center mb-3">
                    <button type="submit" onclick="return startValidate(this)" name="btnSubm" class="btn-submit-form">Переказати</button>
                </div>
                <div class="row info-row col-12 col-lg-6 pb-4">
                    <div class="col-12 p2p-info-box d-flex px-4 flex-column">
                        <h3 class="title">Інструкція користувача</h3>
                        <div class="info-box py-2 px-3">
                            <div class="text-box">
                                <p>
                                    Ви можете здійснити переказ між картками Visa, MasterCard або Maestro будь-якого
                                    українського банку у гривні...
                                </p>
                                <div id="hiddenText" class="d-none">
                                    <ol>
                                        <li><b>Щоб скористатися послугою «Переказ з картки на картку» (далі –
                                                Послуга
                                                Банку), необхідно:</b></li>
                                        <li>
                                            На цій сторінці заповнити усі обов'язкові поля, необхідні для переказу:
                                            <ul>
                                                <li>Номер картки Відправника;</li>
                                                <li>Термін дії картки Відправника;</li>
                                                <li>CVC2/CVV2.</li>
                                                <li>Суму переказу у гривнях;</li>
                                                <li>Номер картки Отримувача.</li>
                                            </ul>
                                        </li>
                                        <li>Ознайомитись із умовами надання Послуги Банку (Публічної пропозиції на
                                            укладання Договору про надання послуги «Переказ з картки на картку»).
                                        </li>
                                        <li>Перевірити усі введені параметри, і, якщо вони правильні, натиснути
                                            кнопку «Переказати».
                                        </li>
                                        <li>Пройти процедуру Аутентифікації для підтвердження операції за однією з
                                            технологій:
                                            <ul>
                                                <li>за технологією 3DSecure (Verified by Visa/MasterCard Secure
                                                    Code):
                                                </li>
                                                Якщо картка Відправника підключена до 3DSecure в банку, який
                                                випустив
                                                дану картку – для підтвердження операції надійде запит для введення
                                                Одноразового паролю, який буде надісланий Вам на номер мобільного
                                                телефону банком, який випустив картку.
                                                <li>за технологією look-up:</li>
                                                Якщо картка Відправника не підключена до технології 3DSecure, для
                                                підтвердження операції надійде запит для введення Одноразового
                                                цифрового
                                                паролю, який надається Вам при авторизаційному запиті за карткою
                                                Відправника на суму 1 грн. у полі MERCHANT NAME. Одноразовий
                                                цифровий
                                                пароль Ви можете дізнатись, звернувшись до контакт-центру банка,
                                                який випустив картку Відправника, або отримавши SMS-повідомлення, у
                                                випадку, якщо картка Відправника підключена до послуги
                                                SMS-інформування.Рекомендуємо при введенні параметрів переказу
                                                вказати
                                                номер Вашого мобільного телефону для проведення реєстрації – у
                                                такому
                                                випадку усі наступні перекази протягом 3-х діб з моменту реєстрації
                                                Ви зможете проводити за технологією Одноразового SMS-паролю.
                                                <li>за технологією Одноразового SMS-паролю:</li>
                                                Якщо картка Відправника не підключена до технології 3DSecure, але Ви
                                                успішно скористалися Послугою Банку з реєстрацією номеру Вашого
                                                мобільного телефону протягом попередніх 3-х календарних днів - для
                                                підтверждення операції надійде запит на введення Одноразового
                                                цифрового
                                                паролю, що буде надісланий Вам в SMS-повідомленні на номер
                                                мобільного телефону, який був вказанний при реєстрації.
                                            </ul>
                                        </li>
                                        <li>Система перевірить усі Ваші дані та виведе на екран інформацію щодо
                                            успішного або неуспішного здійснення операції після декількох секунд,
                                            після натискання кнопки «Підтвердити операцію»/«Підтвердити».
                                        </li>
                                        <li>При успішному виконанні переказу термін доступності коштів визначається
                                            правилами банку, який випустив картку Отримувача.<br>Як правило, кошти
                                            доступні протягом 30 хвилин з моменту завершення переказу, у деяких
                                            випадках
                                            кошти можуть бути доступні через декілька днів: максимально до 5.
                                        </li>
                                    </ol>

                                    <h2 class="title">Обмеження:</h2>
                                    <p>
                                        <b>25000 грн</b> – максимальна сума одного переказу; <br>
                                        <b>150000 грн</b> – максимальна сума усіх переказів за календарний місяць за
                                        однією карткою;<br>
                                        <b>25</b> – максимальна кількість всіх переказів на добу за однією
                                        карткою<br>
                                        <b>100</b> – максимальна кількість всіх переказів за календарний місяць по
                                        одній картці. <br>
                                    </p>
                                </div>
                            </div>
                            <div id="btnOpenText" class="btn-more text-center">
                                <span id="toggleBtnMore">Читати далі</span>
                                <span class="iconify" data-icon="simple-line-icons:arrow-down"
                                      data-inline="false"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="logo-container py-3 col-12 col-lg-6 d-flex justify-content-between">
                    <a target="_blank">
                        <img src="<?= BASE_URL; ?>/assets/images/payment/visa-new.png" alt="Payment logo">
                    </a>
                    <a target="_blank">
                        <img src="<?= BASE_URL; ?>/assets/images/payment/mastercard-new.png" alt="Payment logo">
                    </a>
                    <a target="_blank">
                        <img src="<?= BASE_URL; ?>/assets/images/payment/pci-dss.png" alt="Payment logo">
                    </a>
                    <a target="_blank">
                        <img src="<?= BASE_URL; ?>/assets/images/payment/visa-old.png" alt="Payment logo">
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>
<div class="modal fade" id="modal-2ds" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">2DS {* $__DICT.pages.p2p.login *}</h5>
            </div>
            <div class="modal-body">
                <p class="text text-hint-2ds text-hint-2ds-smsCode">
                    {* $__DICT.pages.p2p.sms *}
                </p>
                <p class="text text-hint-2ds text-hint-2ds-blockCode">
                    {* $__DICT.pages.p2p.code *}
                </p>
                <div class="form-group">
                    <label for="userSurname">Код</label>
                    <input type="text" id="p2p_code" class="form-input" required>
                </div>
                <div class="form-group form-phone">
                    <label for="userPhone">Телефон</label>
                    <input type="text" name="phone_num" value="{*$phone|escape*}" id="userPhone" class="form-input input-phone-mask" data-validation="required length" data-validation-length="min19">
                    <small id="phoneHelper" class="form-text text-muted">
                        Формат +38 (0XX) ХХХ-ХХ-ХХ
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button id="2ds-submit" class="btn-custom">{* $__DICT.pages.p2p.next *}</button>
            </div>
        </div>
    </div>
</div>
<footer>
    <div class="footer-p2p d-flex align-items-center justify-content-center">
        <div class="logo-gerc p-1 p-md-3">
            <a href="https://www.gerc.ua/">
                <img src="<?= BASE_URL; ?>/assets/images/logoGERC.png" alt="GERC logo">
            </a>
        </div>
        <div class="text-center footer-text">
            <p>В рамках проекту KYIV<span id="smart">SMART</span>CITY</p>
            <p class="d-flex flex-column flex-md-row">Технічна підтримка: <span>E-mail: websupport@gerc.ua,</span>
                <span>Тел.: +38 (0482) 30 00 32</span></p>
        </div>
    </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<script src="<?= BASE_URL; ?>/assets/js/cardValidation.js"></script>
<script type="text/javascript">
    document.querySelector('#btnOpenText').addEventListener('click', function() {
        document.querySelector('#toggleBtnMore').innerText = document.querySelector('#hiddenText').classList.contains('d-none') ? 'Згорнути' : 'Читати далі';
        document.querySelector('#hiddenText').classList.toggle('d-none')
    });

    document.querySelectorAll('.white-space').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            var val = e.target.value;
            var whiteSpace = val.replace(/ /ig, '').match(/.{1,4}/g);

            if(e.target.value !== '') {
                e.target.value = whiteSpace.join(' ');
            }
        })
    })

    document.querySelector('#totalAmount').addEventListener('change', (e) => maxAmount(e))

    const maxAmount = (e) => e.target.value > 25000 ? e.target.value = 25000 : true;


    function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode;
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
            return false;

        return true;
    }

</script>

<script>
    $(document).ready(function() {
        $('#paymentFormCard').submit(function(event) {
            preloaderBeforeUnload();
            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/p2p/',
                type: "POST",
                data: $('#paymentFormCard').serialize(),
                dataType: "json",
                success: function(response) {

                    if (response.status) {

                        window.payment_id = response.result.payment_id;

                        if (response.result.type == '3ds') {
                            location.href = response.result.url;
                        } else if (response.result.type == '2ds') {

                            $('.text-hint-2ds').hide(0);
                            $('.text-hint-2ds-' + response.result.prompt_code).show(0);
                            $('#modal-2ds').modal('show');
                        }
                    } else {
                        stopSpinner();
                        $('#modal-error-text').html(response.text);
                        $('#modal-error').modal('show');
                    }
                }
            });

            return false;
        });

        $('#2ds-submit').click(function() {

            $('#modal-error').modal('hide');

            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/p2p-prov/',
                type: "POST",
                data: {
                    payment_id: window.payment_id,
                    code: $('#p2p_code').val(),
                    phone: $('#userPhone').val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        location.href = response.url;
                    } else {
                        $('#modal-error-text').html(response.text);
                        $('#modal-error').modal('show');
                    }
                }
            });
        });
    });

    function preloaderBeforeUnload() {
        $('header').css('opacity', '.5');
        $('main').css('opacity', '.5');
        $('footer').css('opacity', '.5');
        $('.preloader-area').css('display', 'block');
        $('.preloader-gif').css('display', 'block');
    }
</script>

</body>
</html>
