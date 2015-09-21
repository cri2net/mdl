

-- удалённые колонки будут заменены другой таблицей
ALTER TABLE `gioc_users`
  DROP COLUMN `rem_sms`, 
  DROP COLUMN `rem_email`, 
  DROP COLUMN `rem_invoice`, 
  ADD COLUMN `deleted_timestamp` DOUBLE NULL AFTER `deleted_message`, 
  ADD INDEX (`email`, `deleted`);



CREATE TABLE IF NOT EXISTS `gioc_feedback` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `fathername` varchar(255) NOT NULL,
  `timestamp` double NOT NULL,
  `ip` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `status` enum('new','spam','deleted','processed') NOT NULL DEFAULT 'new',
  `answer` text,
  `answer_time` double DEFAULT NULL,
  `answerer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


ALTER TABLE `gioc_feedback`
 ADD PRIMARY KEY (`id`);
 
 ALTER TABLE `gioc_feedback`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;



CREATE TABLE IF NOT EXISTS `gioc_text_errors` (
`id` int(11) NOT NULL,
  `status` enum('new','processed','spam','deleted') NOT NULL DEFAULT 'new',
  `url` varchar(256) NOT NULL,
  `timestamp` double NOT NULL,
  `raw_data` mediumtext
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `gioc_text_errors`
 ADD PRIMARY KEY (`id`), ADD KEY `status` (`status`);
 ALTER TABLE `gioc_text_errors`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `gioc_text_errors`   
  CHANGE `url` `url` VARCHAR(2500) CHARSET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `gioc_payment`
    ADD INDEX (`type`, `status`);



-- 2015.08.20

ALTER TABLE `gioc_payment`
  ADD COLUMN `send_first_pdf` TINYINT(1) DEFAULT 0  NOT NULL AFTER `send_payment_status_to_reports`;



-- 2015.08.26

CREATE TABLE `gioc_hot_news` (
  `id` int(11) NOT NULL,
  `img_filename` varchar(500) NOT NULL,
  `link` varchar(2500) NOT NULL,
  `type` enum('link') NOT NULL DEFAULT 'link',
  `pos` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `gioc_hot_news` ADD PRIMARY KEY (`id`);
ALTER TABLE `gioc_hot_news` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


-- 2015.08.29
CREATE TABLE `gioc_pages`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(250) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `h1` VARCHAR(500) NOT NULL,
  `breadcrumb` VARCHAR(500),
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `views` INT(11) NOT NULL DEFAULT 0,
  `announce` TEXT,
  `text` MEDIUMTEXT,
  `seo_title` VARCHAR(500),
  `seo_description` VARCHAR(500),
  `seo_keywords` VARCHAR(500),
  PRIMARY KEY (`id`),
  UNIQUE INDEX (`path`),
  INDEX (`is_active`),
  INDEX (`created_at`),
  INDEX (`updated_at`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `gioc_pages`
  ADD COLUMN `idp` INT(11) DEFAULT 0  NOT NULL AFTER `id`,
  ADD COLUMN `pos` INT(11) DEFAULT 0  NOT NULL AFTER `idp`, 
  DROP INDEX `is_active`,
  ADD  INDEX `is_active` (`is_active`, `idp`),
  ADD INDEX (`idp`),
  ADD INDEX (`pos`);


ALTER TABLE `gioc_pages`
  CHANGE `path` `key` VARCHAR(100) CHARSET utf8 COLLATE utf8_general_ci NOT NULL, 
  DROP INDEX `path`,
  ADD  UNIQUE INDEX `path` (`idp`, `key`);



-- 2015.08.31
CREATE TABLE `gioc_page_views`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `timestamp` DOUBLE NOT NULL,
  `page_type` ENUM('static_page','news','article','other') NOT NULL,
  `page_id` VARCHAR(200) NOT NULL COMMENT 'id or path',
  `user_id` INT(11) NOT NULL DEFAULT 0,
  `ip` VARCHAR(100),
  `user_agent_string` VARCHAR(350),
  PRIMARY KEY (`id`),
  INDEX (`timestamp`),
  INDEX (`user_id`),
  INDEX (`page_type`, `page_id`),
  INDEX (`page_type`, `page_id`, `timestamp`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `gioc_pages`
  ADD COLUMN `icon` VARCHAR(200) NULL AFTER `is_active`;



ALTER TABLE `gioc_news_links`
  CHANGE `news_id` `page_type` ENUM('static_page','news','article') NOT NULL,
  ADD COLUMN `page_id` INT(11) NOT NULL AFTER `page_type`,
  CHANGE `type` `type` ENUM('see_also','source') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'see_also' NOT NULL,
  ADD COLUMN `icon` VARCHAR(200) NULL AFTER `is_active`, 
  DROP INDEX `news_id`,
  DROP INDEX `news_id_2`,
  DROP INDEX `news_id_3`,
  DROP INDEX `pos`,
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`id`),
  ADD INDEX (`page_type`, `page_id`, `is_active`),
  ADD INDEX (`pos`),
  ADD INDEX (`page_type`, `page_id`, `type`, `is_active`),
  DROP FOREIGN KEY `gioc_news_links_ibfk_1`;

RENAME TABLE `gioc_news_links` TO `gioc_pages_links`;


ALTER TABLE `gioc_pages_links`
  DROP COLUMN `icon`;



ALTER TABLE `gioc_pages_links`
  ADD COLUMN `idp` INT(11) NOT NULL AFTER `id`,
  ADD INDEX (`idp`, `type`);



-- 2015.09.04

ALTER TABLE `gioc_pages_links`
  ADD COLUMN `idp_type` ENUM('static_page','news','article') DEFAULT 'static_page' NOT NULL AFTER `idp`, 
  DROP INDEX `idp`,
  ADD  INDEX `idp` (`idp`, `idp_type`, `type`);

-- alien будет использоваться для, например, ссылки "источник", ведущей на другой сайт
ALTER TABLE `gioc_pages_links`
  CHANGE `page_type` `page_type` ENUM('static_page','news','alien') CHARSET utf8 COLLATE utf8_general_ci NOT NULL;



CREATE TABLE `gioc_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(500) NOT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `gioc_text` (`id`, `variable`, `text`) VALUES
(1, 'CONTACTS_BLOCK_CLOCK', '<h3 id="page-map-clock" class="page-subtitle border-top">Графiк роботи</h3>\r\n\r\n<div class="work-content">\r\n    <div class="line green">\r\n        <div class="col">Понеділок—Четвер</div>\r\n        <div class="col-r">8<sup>30</sup>—17<sup>30</sup></div>\r\n    </div>\r\n    <div class="line green">\r\n        <div class="col">П''ятниця</div>\r\n        <div class="col-r">8<sup>30</sup>—16<sup>15</sup></div>\r\n    </div>\r\n    <div class="line yellow">\r\n        <div class="col">Обідня перерва</div>\r\n        <div class="col-r">12<sup>30</sup>—13<sup>15</sup></div>\r\n    </div>\r\n    \r\n    <h4 class="title">Прийом "Відділом Звернень"</h4>\r\n    <div class="line green">\r\n        <div class="col">Понеділок—Четвер</div>\r\n        <div class="col-r">9<sup>00</sup>—17<sup>00</sup></div>\r\n    </div>\r\n    <div class="line green">\r\n        <div class="col">П''ятниця</div>\r\n        <div class="col-r">9<sup>00</sup>—16<sup>00</sup></div>\r\n    </div>\r\n   \r\n    <h4 class="title">Прийом керівництвом</h4>\r\n    <div class="line green">\r\n        <div class="col">Вівторок</div>\r\n        <div class="col-r">14<sup>00</sup>—17<sup>00</sup></div>\r\n    </div>\r\n    <div class="line comment">\r\n        за попереднім записом в секретаріаті: тел. +380 (44) 238-80-05\r\n    </div>\r\n</div>'),
(2, 'CONTACTS_BLOCK_PHONE', '<h3 id="page-map-phone" class="page-subtitle border-top">Телефони та електрона пошта</h3>\r\n<h4 class="title" style="margin-top:43px;">Контакт-центр</h4>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>\r\n    +38 (044) 238 80 25,\r\n    +38 (044) 238 80 27\r\n</div>\r\n<h4 class="title">Приймальня</h4>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>\r\n    +38 (044) 513-52-52,\r\n    +38 (044) 238-80-55,\r\n    +38 (044) 238-80-05\r\n</div>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">факс:</span>\r\n    +38 (044) 238-80-38,\r\n    +38 (044) 238-80-50\r\n</div>\r\n<h4 class="title">Електронна пошта</h4>\r\n<div style="phone-line">\r\n    <a class="no-decoration" href="mailto:secretary@gioc-kmda.kiev.ua" target="_blank">secretary@gioc-kmda.kiev.ua</a>\r\n</div>'),
(3, 'CONTACTS_BLOCK_MARKER', '<h3 id="page-map-marker" class="page-subtitle border-top">Адреса</h3>\r\n<h4 class="title">Поштова адреса</h4>\r\n02192, Україна, м. Київ, вул. Космічна, 12-а\r\n<div class="map-block" style="height:274px; width: 100%;">\r\n    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d634.9232534968156!2d30.622652!3d50.465441!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0000000000000000%3A0x37f7d3eaa6d2faa4!2z0JrQvtC80YPQvdCw0LvRjNC90LUg0L_RltC00L_RgNC40ZTQvNGB0YLQstC-ICLQk9C-0LvQvtCy0L3QuNC5INGW0L3RhNC-0YDQvNCw0YbRltC50L3QviAtINC-0LHRh9C40YHQu9GO0LLQsNC70YzQvdC40Lkg0YbQtdC90YLRgCI!5e0!3m2!1sru!2sua!4v1440763298928" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>\r\n</div>'),
(4, 'HEADER_PHONE', '<a class="tel" href="tel:+380442388025">(044) 238-80-25</a>'),
(5, 'HEADER_PHONE_SECOND', '<a class="tel" href="tel:+380442388027">(044) 238-80-27</a>'),
(6, 'HEADER_WORK', '<div class="line green">\r\n  <div class="col">пн—чт</div>\r\n  <div class="col-r">8:30—17:30</div>\r\n</div>\r\n<div class="line green">\r\n <div class="col">пт</div>\r\n <div class="col-r">8:30—16:15</div>\r\n</div>\r\n<div class="line yellow">\r\n  <div class="col">перерва</div>\r\n  <div class="col-r">12:30—13:15</div>\r\n</div>');


-- 2015.09.05
ALTER TABLE `gioc_pages`
  ADD COLUMN `show_as_child` TINYINT(1) DEFAULT 1  NOT NULL AFTER `is_active`, 
  DROP INDEX `is_active`,
  ADD  INDEX `is_active` (`is_active`, `show_as_child`, `idp`);


ALTER TABLE `gioc_user_flats`
  ADD COLUMN `notify` TINYINT(1) DEFAULT 1  NOT NULL AFTER `timestamp`, 
  ADD INDEX (`notify`);


-- 2015.09.06
ALTER TABLE `gioc_users`
  ADD COLUMN `notify_email` TINYINT(1) DEFAULT 1  NOT NULL AFTER `send_reg_letter`, 
  ADD INDEX (`notify_email`, `deleted`);


CREATE TABLE `gioc_chief`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pos` INT(11) NOT NULL DEFAULT 0,
  `is_active` INT(1) NOT NULL DEFAULT 1,
  `icon` VARCHAR(150) NOT NULL,
  `email` VARCHAR(500),
  `name` VARCHAR(200) NOT NULL,
  `fathername` VARCHAR(200) NOT NULL,
  `lastname` VARCHAR(200) NOT NULL,
  `role` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`is_active`),
  INDEX (`pos`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

-- тестовые данные, но всё же
INSERT INTO `gioc_chief` (`id`, `pos`, `is_active`, `icon`, `email`, `name`, `fathername`, `lastname`, `role`) VALUES
(1, 1, 1, 'chief_[1441505467.7375].png', 'test1@example.com', 'Валерій', 'Іванович', 'Місань', 'Директор'),
(2, 2, 1, 'chief_[1441504103.1964].png', 'test2@example.com', 'Андрій', 'Анатолійович', 'Щербина', 'Перший заступник директора'),
(3, 3, 0, 'chief_[1441504134.7582].png', 'test3@example.com', 'Ірина', 'Андріївна', 'Юрченко', 'Заступник директора з виробництва та збуту'),
(4, 4, 0, 'chief_[1441504168.8432].png', 'test4@example.com', 'Олександр', 'Анатолійович', 'Клименко', 'Заступник директора з інформаційних технологій'),
(5, 5, 1, 'chief_[1441504218.163].png', 'test5@example.com', 'Михайло', 'Юрійович', 'Балясніков', 'Головний інженер');



ALTER TABLE `gioc_feedback`
  ADD COLUMN `to` INT(11) DEFAULT 0  NOT NULL AFTER `id`;


CREATE TABLE `gioc_useful_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(2000) NOT NULL,
  `title` varchar(2000) DEFAULT NULL,
  `target` enum('_self','_blank') NOT NULL DEFAULT '_blank',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `is_active_2` (`is_active`,`pos`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


INSERT INTO `gioc_useful_links` (`id`, `link`, `title`, `target`, `is_active`, `pos`) VALUES
(1, '#', 'Номери екстрених, аварійних та довідково-інформаційних служб', '_self', 1, 1),
(2, '#', 'Районні в місті Києві державні адміністрації', '_self', 1, 2),
(3, '#', 'Департамент житлово-комунальної інфраструктури', '_self', 1, 3),
(4, '#', 'Департамент соціальної політики', '_self', 1, 4);



-- 2015.09.09
-- добавляем поля для связки платежа с объектом.
-- Используем связку city_id + flat_id, чтоб данные об оплате сохранились даже если человек удалит объект из профиля, а потом добавит.
ALTER TABLE `gioc_payment`
  ADD COLUMN `city_id` INT(11) NULL  COMMENT 'for komdebt' AFTER `type`,
  ADD COLUMN `flat_id` INT(11) NULL  COMMENT 'for komdebt' AFTER `city_id`, 
  ADD INDEX (`user_id`, `type`, `city_id`, `flat_id`);



-- 2015.09.10
ALTER TABLE `gioc_news`
  DROP COLUMN `old_site_url`;


-- 2015.09.11
-- страница foruser теперь в ВД

INSERT INTO `gioc_text` (`id`, `variable`, `text`) VALUES
(7, 'FORUSER_CABINET', '<div class="block block--personal-cabinet">\r\n    <div class="subblock-left">\r\n        <h2>Особистий кабiнет</h2>\r\n    </div>\r\n    <div class="subblock-right">\r\n        <div class="in">\r\n            <ul>\r\n                <li><img src="{SITE_URL}/pic/pages/foruser/personal-cabinet/icon-1-1.png" alt="" /><a href="#"><b>Переглянути нарахування</b></a></li>\r\n                <li><img src="{SITE_URL}/pic/pages/foruser/personal-cabinet/icon-1-2.png" alt="" /><a href="#"><b>Погасити заборгованість</b></a></li>\r\n                <li class="or">а також</li>\r\n                <li class="li-small"><img src="{SITE_URL}/pic/pages/foruser/personal-cabinet/icon-2-1.png" alt="" /><a href="#">Переглянути нарахування</a></li>\r\n                <li class="li-small"><img src="{SITE_URL}/pic/pages/foruser/personal-cabinet/icon-2-2.png" alt="" /><a href="#">Погасити заборгованість</a></li>\r\n                <li class="li-small"><img src="{SITE_URL}/pic/pages/foruser/personal-cabinet/icon-2-3.png" alt="" /><a href="#">Погасити заборгованість</a></li>\r\n            </ul>\r\n        </div>\r\n        <div class="more">\r\n            <a href="#">Переглянути повний перелiк послуг></a>\r\n        </div>\r\n    </div>\r\n</div>'),
(8, 'FORUSER_TERMINAL', '<div class="block block--terminals">\r\n    <div class="subblock-left">\r\n    <h2>Термiнали для<br/>сплати<br/>послуг ЖКГ</h2>\r\n    </div>\r\n    <div class="subblock-right">\r\n        <div class="in">\r\n            <ul>\r\n                <li><a href="#"><b>ТОВ «Фінансова компанія<br/>"Контрактовий Дім" »</b></a></li>\r\n                <li><a href="#"><b>Термінали АБ "Укргазбанк"</b></a></li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>'),
(9, 'FORUSER_BANKS', '<div class="block block--banks">\r\n    <div class="subblock-left">\r\n        <h2>Банки та фiнансовi установи</h2>\r\n    </div>\r\n    <div class="subblock-right">\r\n        <div class="in">\r\n            <ul>\r\n                <li><img src="{SITE_URL}/pic/pages/foruser/banks/icon-1-1.png" alt="" /><a href="{SITE_URL}/foruser/banks/#service-komdebt"><b>Приймають платежі за ЖКП</b></a></li>\r\n                <li><img src="{SITE_URL}/pic/pages/foruser/banks/icon-1-1.png" alt="" /><a href="{SITE_URL}/foruser/banks/#service-heating"><b>Приймають платежі за ЦО </b></a></li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>'),
(10, 'FORUSER_CALCS', '<div class="block block--calculators">\r\n    <div class="subblock-left">\r\n        <h2>Онлайн<br/>калькулятори</h2>\r\n    </div>\r\n    <div class="subblock-right">\r\n        <div class="in">\r\n            <ul>\r\n                <li><a href="{SITE_URL}/calc-subsidies/"><span><img src="{SITE_URL}/pic/pages/foruser/calculators/icon-1-1.png" alt="" /></span><span><b>Орієнтовний розрахунок субсидій</b></span></a></li>\r\n                <li><a href="{SITE_URL}/calc-devices/"><span><img src="{SITE_URL}/pic/pages/foruser/calculators/icon-1-1.png" alt="" /></span><span><b>Розрахунок за показаннями квартирних приладів обліку </b></span></a></li>\r\n            </ul>\r\n        </div>\r\n    </div>\r\n</div>'),
(11, 'FORUSER_LINKS', '<div class="block block--links">\r\n <div class="subblock-left">\r\n   <h2>Кориснi посилання</h2>\r\n  </div>\r\n  <div class="subblock-right">\r\n    <div class="in">\r\n      <ul>\r\n        <li><a href="#">Адреси ЖЕКів</a></li>\r\n       <li><a href="#">Районні в місті Києві державні адміністрації</a></li>\r\n       <li><a href="#">Департамент житлово-комунальної інфраструктури </a></li>\r\n        <li><a href="#">Департамент соціальної політики</a></li>\r\n        <li><a href="#">Номери екстрених, аварійних та довідково-інформаційних служб</a></li>\r\n       <li><a href="#">Головне управління освіти і науки м. Києва</a></li>\r\n       <li><a href="#">Головне управління охорони здоров''я та медичного забезпечення м. Києва</a></li>\r\n      </ul>\r\n   </div>\r\n  </div>\r\n</div>'),
(12, 'FORUSER_NEWS', '<h2 class="big-subtitle green">Рекомендуємо ознайомитись</h2>\r\n<div class="news-list">\r\n  <div class="news-item first">\r\n   <h2 class="title">Про внесення змiн до норм споживання</h2>\r\n   <div class="date">15 травня</div>\r\n   <div class="announce">\r\n      Кабінет Міністрів України постановив про внесення змін до норм споживання природного газу населенням у разі відсутності газових лічильників. Постанова КМУ від 29.04.2015 № 237\r\n   </div>\r\n    <div class="details"><a href="#">детальнiше...</a></div>\r\n  </div>\r\n  <div class="news-item">\r\n   <h2 class="title">Куди звертатися для отримання субсидій?</h2>\r\n    <div class="date">12 квiтня</div>\r\n   <div class="announce">\r\n      Процедура отримання субсидій здійснюється в районних управліннях праці та соціального захисту. На дошках оголошень під''їздах житлових будинків розміщено про адреси управлінь та їх контактні телефони.\r\n    </div>\r\n    <div class="details"><a href="#">детальнiше...</a></div>\r\n  </div>\r\n</div>');



-- 2015.09.15
ALTER TABLE `gioc_hot_news`
  DROP COLUMN `type`;

ALTER TABLE `gioc_hot_news`
  ADD COLUMN `type` ENUM('index_slide','sidebar_banner') DEFAULT 'index_slide' NOT NULL AFTER `id`, 
  ADD INDEX (`type`, `is_active`),
  ADD INDEX (`pos`);

INSERT INTO `gioc_hot_news` (`type`, `img_filename`, `link`, `pos`, `is_active`) VALUES
('sidebar_banner', 'slide_[1442274354.1293].png', '{SITE_URL}/cabinet/', 2, 1),
('sidebar_banner', 'slide_[1442274796.0566].png', 'https://www.personal-account.kiev.ua/', 3, 1),
('sidebar_banner', 'slide_[1442275487.4562].png', '', 1, 1);


ALTER TABLE `gioc_hot_news`
  CHANGE `type` `type` ENUM('index_slide','partners','sidebar_banner') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'index_slide' NOT NULL;

ALTER TABLE `gioc_hot_news`
  ADD COLUMN `title` VARCHAR(500) NULL AFTER `is_active`;

INSERT INTO `gioc_hot_news` (`type`, `img_filename`, `link`, `pos`, `is_active`, `title`) VALUES
('partners', 'slide_[1442278341.5554].png', 'http://kievcity.gov.ua/', 1, 1, 'Сайт Київської міської державної адміністрації'),
('partners', 'slide_[1442278320.9032].png', 'http://info.kyivcard.com.ua/main/', 2, 1, '«Картка киянина»'),
('partners', 'slide_[1442278350.7679].png', 'http://www.municipal.kiev.ua:8080/municipal/', 3, 1, '«Ваш будинок»');


CREATE TABLE `gioc_video`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `pos` INT(11) NOT NULL DEFAULT 0,
  `type` ENUM('youtube','vimeo') NOT NULL DEFAULT 'youtube',
  `url` VARCHAR(500) NOT NULL,
  `date` DOUBLE NOT NULL,
  `title` VARCHAR(500) NOT NULL,
  `description` VARCHAR(500),
  `img_filename` VARCHAR(150),
  PRIMARY KEY (`id`),
  INDEX (`is_active`),
  INDEX (`is_active`, `pos`),
  INDEX (`type`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE `gioc_subscribers`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(250) NOT NULL,
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `subscribe` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE INDEX (`email`),
  INDEX (`subscribe`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;



INSERT INTO `gioc_social` (`key`, `link`, `title`, `target`, `is_active`, `pos`) VALUES
('twitter', '#', '', '_blank', 1, 5),
('linkedin', '#', '', '_blank', 1, 6);

-- 2015.09.16
ALTER TABLE `gioc_payment`
  ADD INDEX (`user_id`, `type`),
  ADD  INDEX `for_cron_1` (`status`, `send_payment_status_to_reports`),
  ADD  INDEX `for_cron_2` (`status`, `go_to_payment_time`);



-- 2015.09.17
ALTER TABLE `gioc_video`
  CHANGE `date` `date` DOUBLE NULL;

ALTER TABLE `gioc_users`
  ADD COLUMN `verified_email` TINYINT(1) DEFAULT 0  NOT NULL AFTER `notify_email`,
  ADD COLUMN `verified_phone` TINYINT(1) DEFAULT 0  NOT NULL AFTER `verified_email`,
  ADD COLUMN `broken_email` TINYINT(1) DEFAULT 0  NOT NULL AFTER `verified_phone`;


INSERT INTO `gioc_video` (`id`, `is_active`, `pos`, `type`, `url`, `date`, `title`, `description`, `img_filename`) VALUES
(1, 1, 1, 'youtube', 'https://www.youtube.com/watch?v=m-vCTrm_8dY', 1415311200, 'У центрі уваги.', 'Нові платіжки мають надійти киянам до 13 листопада', ''),
(2, 1, 2, 'youtube', 'https://www.youtube.com/watch?v=Img9pczIklE', 1412888400, 'У центрі уваги.', 'Чи отримають кияни вчасно квитанції за комунальні послуги?', ''),
(3, 1, 3, 'youtube', 'https://www.youtube.com/watch?v=jLB4kBHNyNQ', 1411938000, 'Громадська приймальня.', 'Як заповнити платіжку нового виду', ''),
(4, 1, 4, 'youtube', 'https://www.youtube.com/watch?v=sd1IccmSpJM', 1408395600, 'У центрі уваги.', 'За якими тарифами сплачувати комунальні послуги', ''),
(5, 1, 5, 'youtube', 'https://www.youtube.com/watch?v=zoC5bjZC3mI', 1366318800, '24-й телеканал.', 'Оплата комфорту', ''),
(6, 1, 6, 'youtube', 'https://www.youtube.com/watch?v=6mKib_R44UI', 1363125600, 'Київське віче', '', ''),
(7, 1, 7, 'youtube', 'https://www.youtube.com/watch?v=7MoeVTcT4AU', NULL, 'Телеканал «Київ»', 'Кияни - сумлінні платники за комунальні послуги', ''),
(8, 1, 8, 'youtube', 'https://www.youtube.com/watch?v=coERKQLcNuI', NULL, 'Телеканал «СТН»', 'Про картку киянина', ''),
(9, 1, 9, 'youtube', 'https://www.youtube.com/watch?v=BihTyWMX5pM', NULL, 'Картка киянин', '', '');


-- 2015.09.21
 
ALTER TABLE `gioc_users`
  ADD COLUMN `login` VARCHAR(150) NOT NULL AFTER `password_key`, 
  ADD INDEX (`deleted`, `login`);

ALTER TABLE `gioc_users`
  DROP INDEX `deleted_2`,
  ADD  INDEX `deleted_2` (`mob_phone`, `deleted`),
  DROP INDEX `deleted_3`,
  ADD  INDEX `deleted_3` (`login`, `deleted`);


-- IN ONLINE


