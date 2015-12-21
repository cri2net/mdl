<?php
define('ERROR_SERVICE_TEMPORARY_ERROR', 'Спробуйте повторити запит пізніше.');
define('ERROR_EMPTY_KOMDEBT_PAYMENT', 'Повинна оплачуватись хоча б одна послуга');
define('ERROR_FIELD_EMPTY_ERROR_MSG', 'Поле «{FIELD}» не повинно бути порожнім.');
define('ERROR_FIELD_SPESIAL_CHARS_ERROR_MSG', ' поле не може містити спеціальні символи.');
define('ERROR_INCORRECT_EMAIL_ERROR_MSG', 'Адреса електронної пошти невірна.');
define('ERROR_FIELD_MIN_ERROR_MSG', ' не може бути менше, ніж ');
define('ERROR_FIELD_MAX_ERROR_MSG', ' не може бути більше, ніж ');
define('ERROR_LOGIN_FIELDS_EMPTY', 'Дані для авторизації не вказано.');
define('ERROR_LOGIN_ERROR_MSG', 'Дані для авторизації невірні.');
define('ERROR_SECURITY_CODE', 'Невірний перевірочний код.');
define('ERROR_CURRENT_PASSWORD', 'Дійсний пароль невірний.');
define('ERROR_EMAIL_DOESNOT_EXIST', 'Користувач з такою адресою електронної пошти не існує.');
define('ERROR_EMAIL_ALREADY_EXIST', 'Користувач з такою адресою електронної пошти вже існує.');
define('ERROR_PHONE_ALREADY_EXIST', 'Користувач з таким номером мобільного телефону вже існує.');
define('ERROR_LOGIN_ALREADY_EXIST', 'Користувач з таким логіном вже існує.');
define('ERROR_SET_NEWPASSWORD', 'Неможливо задати новий пароль.');
define('ERROR_SENDING_MAIL', 'Неможливо відправити повідомлення.');
define('ERROR_DATE', 'Помилка дати.');
define('ERROR_EMPTY_BILL', 'Нарахування за даний місяць відсутні.');
define('ERROR_EMPTY_HISTORYBILL', 'Платежі за даний місяць відсутні.');
define('ERROR_OLD_REQUEST', 'Ваш сеанс застарів, будь ласка, повторіть ваш запит');
define('ERROR_GET_FLAT', 'Помилка при отриманні квартири.');
define('ERROR_FLAT_INVALID_AUTH_KEY', 'Ключ авторизації не є дійсний');
define('ERROR_GET_PAYMENT', 'Помилка при отриманні платежу.');
define('ERROR_GET_PAYMENT_PDF', 'Помилка при отриманні квитанції.');
define('ERROR_SHOW_PAYMENT', 'Неможливо показати деталі платежу');
define('ERROR_GETTING_DEBT', 'Немає платежів за вибраний місяць.');
define('ERROR_ADDRESS_ALREADY_EXIST', 'Квартира вже додана.');
define('ERROR_TRANSACTION_NOT_SUCCESS', 'Транзакція не оплачена.');
define('ERROR_TOO_MANY_FLATS', 'Занадто багато доданих об\'єктів');
define('ERROR_TRANSACTION_NEW', 'Транзакція має статус «нова» (ще не оплачена).');
define('ERROR_PASSWORD_NOT_CONCUR', ' Поля «пароль» і «повторити пароль» не збігаються.');
define('ERROR_PASSWORD_TOO_SHORT', 'Пароль повинен бути не менше 6 символів.');
define('ERROR_LOGIN_TOO_SHORT', 'Логін повинен бути не менше 3 символів.');
define('ERROR_LOGIN_NOT_VALID_FORMAT', 'Логін користувача повинен містити лише латинські літери, цифри та символи «-» і «_»');
define('ERROR_INVALID_ACCOUNT', 'Невірний номер свого особового рахунку.');
define('ERROR_TRANSACTION_FOUND', 'Транзакція не знайдена.');
define('ERROR_INCORRECT_PHONE_FORMAT_ERROR_MSG', 'Неправильний формат телефону.');
define('ERROR_USER_NOT_LOGGED_IN', 'Користувач повинен бути авторизований');
define('ERROR_NOT_FIND_FLAT', 'Запитуваний об\'єкт не знайдено');
define('ERROR_NOT_FIND_FLAT_FOR_REPAY', 'Неможливо повторити платіж: об\'єкт був видалений з Вашого аккаунту.');
define('ERROR_INVALID_ZERO_PAYMENT', 'Платіж не може бути порожнім');
define('ERROR_GER_RESTORE_CODE', 'Такий код доступу не знайдено');
define('ERROR_RESTORE_CODE_EXPIRE', 'Час дії цього коду закінчився');
define('ERROR_RESTORE_CODE_ACTIVE', 'Цей код вже використано або було сформовано новий');
define('ERROR_ADD_CARD_EMPTY_CARD_NUMBER', 'Номер картки не вказано');
define('ERROR_ADD_CARD_EMPTY_PASP_NUMBER', 'Номер паспорту не вказано');
define('ERROR_ADD_CARD_EMPTY_BIRTHDAY', 'Дату народження не вказано');
define('ERROR_ADD_CARD_BAD_CARD_STATE_ID', 'Ваша картка неактивна');
define('ERROR_ADD_CARD_CARD_NOT_FOUND', 'Картку не знайдено');
define('ERROR_CARD_NO_SELECT_CARD', 'Ви не вказали картку');
define('ERROR_GET_CARD', 'Помилка при отриманні картки');
