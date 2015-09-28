<?php
    $MONTHS_NAME = [
         1 => ['en' => 'January',   'ua' => 'січень',   'ru' => 'январь'],
         2 => ['en' => 'February',  'ua' => 'лютий',    'ru' => 'февраль'],
         3 => ['en' => 'March',     'ua' => 'березень', 'ru' => 'март'],
         4 => ['en' => 'April',     'ua' => 'квiтень',  'ru' => 'апрель'],
         5 => ['en' => 'May',       'ua' => 'травень',  'ru' => 'май'],
         6 => ['en' => 'June',      'ua' => 'червень',  'ru' => 'июнь'],
         7 => ['en' => 'July',      'ua' => 'липень',   'ru' => 'июль'],
         8 => ['en' => 'August',    'ua' => 'серпень',  'ru' => 'август'],
         9 => ['en' => 'September', 'ua' => 'вересень', 'ru' => 'сентябрь'],
        10 => ['en' => 'October',   'ua' => 'жовтень',  'ru' => 'октябрь'],
        11 => ['en' => 'November',  'ua' => 'листопад', 'ru' => 'ноябрь'],
        12 => ['en' => 'December',  'ua' => 'грудень',  'ru' => 'декабрь'],
    ];

    $MONTHS = [
         1 => ['ru' => 'января',   'en' => 'January',   'ua' => 'січня'],
         2 => ['ru' => 'февраля',  'en' => 'February',  'ua' => 'лютого'],
         3 => ['ru' => 'марта',    'en' => 'March',     'ua' => 'березня'],
         4 => ['ru' => 'апреля',   'en' => 'April',     'ua' => 'квiтня'],
         5 => ['ru' => 'мая',      'en' => 'May',       'ua' => 'травня'],
         6 => ['ru' => 'июня',     'en' => 'June',      'ua' => 'червня'],
         7 => ['ru' => 'июля',     'en' => 'July',      'ua' => 'липня'],
         8 => ['ru' => 'августа',  'en' => 'August',    'ua' => 'серпня'],
         9 => ['ru' => 'сентября', 'en' => 'September', 'ua' => 'вересня'],
        10 => ['ru' => 'октября',  'en' => 'October',   'ua' => 'жовтня'],
        11 => ['ru' => 'ноября',   'en' => 'November',  'ua' => 'листопада'],
        12 => ['ru' => 'декабря',  'en' => 'December',  'ua' => 'грудня'],
    ];

    $MONTHS_WHEN = [
         1 => ['ru' => 'января',   'en' => 'January',   'ua' => 'січні'],
         2 => ['ru' => 'февраля',  'en' => 'February',  'ua' => 'лютому'],
         3 => ['ru' => 'марта',    'en' => 'March',     'ua' => 'березні'],
         4 => ['ru' => 'апреля',   'en' => 'April',     'ua' => 'квiтні'],
         5 => ['ru' => 'мая',      'en' => 'May',       'ua' => 'травні'],
         6 => ['ru' => 'июня',     'en' => 'June',      'ua' => 'червні'],
         7 => ['ru' => 'июля',     'en' => 'July',      'ua' => 'липні'],
         8 => ['ru' => 'августа',  'en' => 'August',    'ua' => 'серпні'],
         9 => ['ru' => 'сентября', 'en' => 'September', 'ua' => 'вересні'],
        10 => ['ru' => 'октября',  'en' => 'October',   'ua' => 'жовтні'],
        11 => ['ru' => 'ноября',   'en' => 'November',  'ua' => 'листопаді'],
        12 => ['ru' => 'декабря',  'en' => 'December',  'ua' => 'грудні'],
    ];

    $DAYS_OF_WEEK = [
        'ua' => [
            0 => ['short' => 'нд', 'full' => 'Неділя',    'is_holiday' => true],
            1 => ['short' => 'пн', 'full' => 'Понеділок', 'is_holiday' => false],
            2 => ['short' => 'вт', 'full' => 'Вівторок',  'is_holiday' => false],
            3 => ['short' => 'ср', 'full' => 'Середа',    'is_holiday' => false],
            4 => ['short' => 'чт', 'full' => 'Четвер',    'is_holiday' => false],
            5 => ['short' => 'пт', 'full' => 'П\'ятниця', 'is_holiday' => false],
            6 => ['short' => 'сб', 'full' => 'Субота',    'is_holiday' => true],
        ],
        'ru' => [
            0 => ['short' => 'вс', 'full' => 'Воскресенье', 'is_holiday' => true],
            1 => ['short' => 'пн', 'full' => 'Понедельник', 'is_holiday' => false],
            2 => ['short' => 'вт', 'full' => 'Вторник',     'is_holiday' => false],
            3 => ['short' => 'ср', 'full' => 'Среда',       'is_holiday' => false],
            4 => ['short' => 'чт', 'full' => 'Четверг',     'is_holiday' => false],
            5 => ['short' => 'пт', 'full' => 'Пятниця',     'is_holiday' => false],
            6 => ['short' => 'сб', 'full' => 'Суббота',     'is_holiday' => true],
        ],
        'en' => [
            0 => ['short' => 'su', 'full' => 'Sunday',    'is_holiday' => true],
            1 => ['short' => 'mo', 'full' => 'Monday',    'is_holiday' => false],
            2 => ['short' => 'tu', 'full' => 'Tuesday',   'is_holiday' => false],
            3 => ['short' => 'we', 'full' => 'Wednesday', 'is_holiday' => false],
            4 => ['short' => 'th', 'full' => 'Thursday',  'is_holiday' => false],
            5 => ['short' => 'fr', 'full' => 'Friday',    'is_holiday' => false],
            6 => ['short' => 'sa', 'full' => 'Saturday',  'is_holiday' => true],
        ]
    ];
