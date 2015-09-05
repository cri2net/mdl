<?php
    $MONTHS = array(
         1 => array('ru' => 'января',   'en' => 'January',   'ua' => 'січня'),
         2 => array('ru' => 'февраля',  'en' => 'February',  'ua' => 'лютого'),
         3 => array('ru' => 'марта',    'en' => 'March',     'ua' => 'березня'),
         4 => array('ru' => 'апреля',   'en' => 'April',     'ua' => 'квiтня'),
         5 => array('ru' => 'мая',      'en' => 'May',       'ua' => 'травня'),
         6 => array('ru' => 'июня',     'en' => 'June',      'ua' => 'червеня'),
         7 => array('ru' => 'июля',     'en' => 'July',      'ua' => 'липня'),
         8 => array('ru' => 'августа',  'en' => 'August',    'ua' => 'серпеня'),
         9 => array('ru' => 'сентября', 'en' => 'September', 'ua' => 'вересня'),
        10 => array('ru' => 'октября',  'en' => 'October',   'ua' => 'жовтня'),
        11 => array('ru' => 'ноября',   'en' => 'November',  'ua' => 'листопада'),
        12 => array('ru' => 'декабря',  'en' => 'December',  'ua' => 'грудня'),
    );

    $MONTHS_WHEN = array(
         1 => array('ru' => 'января',   'en' => 'January',   'ua' => 'січні'),
         2 => array('ru' => 'февраля',  'en' => 'February',  'ua' => 'лютому'),
         3 => array('ru' => 'марта',    'en' => 'March',     'ua' => 'березні'),
         4 => array('ru' => 'апреля',   'en' => 'April',     'ua' => 'квiтні'),
         5 => array('ru' => 'мая',      'en' => 'May',       'ua' => 'травні'),
         6 => array('ru' => 'июня',     'en' => 'June',      'ua' => 'червені'),
         7 => array('ru' => 'июля',     'en' => 'July',      'ua' => 'липні'),
         8 => array('ru' => 'августа',  'en' => 'August',    'ua' => 'серпені'),
         9 => array('ru' => 'сентября', 'en' => 'September', 'ua' => 'вересні'),
        10 => array('ru' => 'октября',  'en' => 'October',   'ua' => 'жовтні'),
        11 => array('ru' => 'ноября',   'en' => 'November',  'ua' => 'листопаді'),
        12 => array('ru' => 'декабря',  'en' => 'December',  'ua' => 'грудні'),
    );

    $DAYS_OF_WEEK = array(
        'ua' => array(
            0 => array('short' => 'нд', 'full' => 'Неділя',     'is_holiday' => true),
            1 => array('short' => 'пн', 'full' => 'Понеділок',  'is_holiday' => false),
            2 => array('short' => 'вт', 'full' => 'Вівторок',   'is_holiday' => false),
            3 => array('short' => 'ср', 'full' => 'Середа',     'is_holiday' => false),
            4 => array('short' => 'чт', 'full' => 'Четвер',     'is_holiday' => false),
            5 => array('short' => 'пт', 'full' => 'П\'ятниця',  'is_holiday' => false),
            6 => array('short' => 'сб', 'full' => 'Субота',     'is_holiday' => true)
        ),
        'en' => array(
            0 => array('short' => 'su', 'full' => 'Sunday',     'is_holiday' => true),
            1 => array('short' => 'mo', 'full' => 'Monday',     'is_holiday' => false),
            2 => array('short' => 'tu', 'full' => 'Tuesday',    'is_holiday' => false),
            3 => array('short' => 'we', 'full' => 'Wednesday',  'is_holiday' => false),
            4 => array('short' => 'th', 'full' => 'Thursday',   'is_holiday' => false),
            5 => array('short' => 'fr', 'full' => 'Friday',     'is_holiday' => false),
            6 => array('short' => 'sa', 'full' => 'Saturday',   'is_holiday' => true)
        )
    );
