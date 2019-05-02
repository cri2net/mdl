

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


CREATE TABLE `gioc_user_restore`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `hash` VARCHAR(100) NOT NULL,
  `created_at` DOUBLE NOT NULL,
  `expires_at` DOUBLE NOT NULL,
  `is_active` INT(1) NOT NULL DEFAULT 1,
  `created_by_ip` VARCHAR(50) NOT NULL,
  `created_by_user_agent_string` VARCHAR(700) NOT NULL,
  `used_at` DOUBLE,
  `used_at_ip` VARCHAR(50),
  `used_at_user_agent_string` VARCHAR(700),
  PRIMARY KEY (`id`),
  INDEX (`user_id`, `is_active`, `hash`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `gioc_user_restore`
  CHANGE `hash` `code` VARCHAR(100) CHARSET utf8 COLLATE utf8_general_ci NOT NULL;


ALTER TABLE `gioc_user_restore`
  ADD INDEX (`user_id`, `is_active`);

ALTER TABLE `gioc_user_restore`
  DROP INDEX `user_id`,
  ADD INDEX (`code`),
  ADD INDEX (`is_active`, `expires_at`);

ALTER TABLE `gioc_user_restore`
  ADD COLUMN `email` VARCHAR(300) NOT NULL AFTER `code`;


-- 2015.09.26
ALTER TABLE `gioc_users`
  ADD COLUMN `activated` TINYINT(1) DEFAULT 0  NOT NULL AFTER `send_reg_letter`;


-- 2015.09.29
ALTER TABLE `gioc_users`
  DROP INDEX `notify_email`,
  ADD  INDEX `notify_email` (`notify_email`, `broken_email`, `deleted`);

ALTER TABLE `gioc_text`
  DROP COLUMN `id`,
  CHANGE `variable` `variable` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NOT NULL,
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`variable`);


-- 2015.10.01
-- Индексы для сортировки при поиске
ALTER TABLE `gioc_news`   
  ADD INDEX (`is_actual`, `title`(50));

ALTER TABLE `gioc_pages`   
  ADD INDEX (`is_active`, `h1`(50));


-- 2015.10.02
-- перевод контента страницы ЛК для неавторизованных в БД
INSERT INTO `gioc_text` (`variable`, `text`) VALUES
('CABINET_BLOCK_1', '<div class="block block--personal-cabinet">\r\n    <div class="subblock-left">\r\n        <h2>Сплачуйте житлово-комунальні послуги</h2>\r\n        <div class="list">\r\n            <div class="item"><div class="icon time"></div> —&nbsp;&nbsp;Швидко</div>\r\n            <div class="item"><div class="icon hand"></div> —&nbsp;&nbsp;Без черг та без комісії</div>\r\n            <div class="item"><div class="icon secure"></div> —&nbsp;&nbsp;Безпечно</div>\r\n            <div class="item"><div class="icon easy"></div> —&nbsp;&nbsp;Обирайте зручний спосіб</div>\r\n        </div>\r\n    </div>\r\n    <div class="subblock-right">\r\n        <div class="in">\r\n            <a href="{SITE_URL}/cabinet/login/" class="btn green btn-login">\r\n                <span class="icon-login"></span>\r\n                Увiйти\r\n            </a>\r\n            або <a href="{SITE_URL}/cabinet/registration/">зареєструватися</a>\r\n        </div>\r\n    </div>\r\n</div>'),
('CABINET_BLOCK_2', '<div class="block block--quick-pays">\r\n    <h2>Миттєвi платежi</h2>\r\n    <a href="{SITE_URL}/cabinet/instant-payments/dai/" class="police">Cплачуйте штрафи ДАІ</a>\r\n    <a href="{SITE_URL}/cabinet/instant-payments/kindergarten/" class="baby">Сплата за дитячий садок</a>\r\n    <a href="{SITE_URL}/cabinet/instant-payments/cards/" class="cards">Переводи з карти на карту</a>\r\n  <a href="{SITE_URL}/cabinet/instant-payments/phone/" class="phone">Поповнення рахунку<br/>на мобільному</a>\r\n</div>'),
('CABINET_BLOCK_3', '<!--\r\n<div class="block block--conclusion">\r\n  <h2>Користючись нашим сервісом<br/> ви завжди отримуєте більше</h2>\r\n    <p>\r\n    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla quam velit, vulputate eu pharetra nec, mattis ac neque. Duis vulputate \r\n    commodo lectus, ac blandit elit tincidunt id. Sed rhoncus, tortor sed eleifend tristique, tortor mauris molestie elit, et lacinia ipsum quam \r\n    nec dui. Quisque nec mauris sit amet elit iaculis pretium sit amet quis magna. Aenean velit odio, elementum in tempus ut, vehicula eu diam. \r\n    Pellentesque rhoncus aliquam mattis. Ut vulputate eros sed felis sodales nec vulputate justo hendrerit. Vivamus varius pretium ligula, a \r\n    aliquam odio euismod sit amet. Quisque laoreet sem sit amet orci ullamcorper at ultricies metus viverra. Pellentesque arcu mauris, \r\n    malesuada quis ornare accumsan, blandit sed diam.\r\n    </p>\r\n</div>\r\n-->');


ALTER TABLE `gioc_user_restore`
  ADD COLUMN `type` ENUM('restore','verify_email') DEFAULT 'restore' NULL AFTER `code`, 
  DROP INDEX `user_id_2`,
  ADD  INDEX `user_id_2` (`user_id`, `is_active`, `type`),
  DROP INDEX `code`,
  ADD  INDEX `code` (`code`, `type`);

RENAME TABLE `gioc_user_restore` TO `gioc_user_codes`;


-- 2015.10.06
ALTER TABLE `gioc_users`
  CHANGE `reg_time` `created_at` DOUBLE NOT NULL;

ALTER TABLE `gioc_user_flats`
  ADD COLUMN `plat_code` INT(11) NULL AFTER `title`,
  ADD COLUMN `auth_key` VARCHAR(30) NULL AFTER `plat_code`,
  ADD INDEX (`notify`, `user_id`);

CREATE TABLE `gioc_email_cron`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` ENUM('new','sending','complete','pause') NOT NULL DEFAULT 'new',
  `type` ENUM('invoice','newsletter','newsletter_for_subscribers') NOT NULL DEFAULT 'invoice',
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `start_user_id` INT(11) NOT NULL DEFAULT 1,
  `subject` VARCHAR(500) NOT NULL,
  `content` MEDIUMTEXT,
  PRIMARY KEY (`id`),
  INDEX (`status`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `gioc_flat`
  ADD COLUMN `need_del_after_rebuild` TINYINT(1) DEFAULT 0  NOT NULL AFTER `flat_number`, 
  ADD INDEX (`need_del_after_rebuild`);


-- IN ONLINE

-- 2015.11.02
ALTER TABLE `gioc_flat`
  ADD COLUMN `plat_code` INT(11) NULL AFTER `need_del_after_rebuild`;

CREATE TABLE `gioc_auth_code`(
  `city_id` INT(11) NOT NULL,
  `object_id` INT(11) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `plat_code` INT(11) NOT NULL,
  PRIMARY KEY (`object_id`, `city_id`, `code`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `gioc_auth_code`
  ADD COLUMN `created_at` DOUBLE NOT NULL AFTER `plat_code`;

-- 2015.11.18
ALTER TABLE `gioc_email_cron`
  ADD COLUMN `send_email` INT(11) UNSIGNED DEFAULT 0  NOT NULL AFTER `content`;

-- 2015.12.11
-- plat_code меняется от месяца к месяцу (часто), так что нет смысла его хранить.
ALTER TABLE `gioc_auth_code`
  DROP COLUMN `plat_code`;

ALTER TABLE `gioc_flat`
  DROP COLUMN `plat_code`;

ALTER TABLE `gioc_user_flats`
  DROP COLUMN `plat_code`;

-- 2015.12.19
ALTER TABLE `gioc_payment`
  CHANGE `processing` `processing` ENUM('_test_upc','mastercard','visa','webmoney','khreshchatyk') CHARSET utf8 COLLATE utf8_general_ci NULL;


-- ПРИВЯЗАННЫЕ КАРТЫ пользователя
-- type:        тип карты. Сейчас только ККК, но в будущем могут быть и другие
-- created_at:  время создания карты в БД (привязки)
-- updated_at:  время последнего обновления данных карты. Для Крещатика это означает обновление номера счёта в процессинге (будет храниться в additional)
-- pos:         позиция карты среди других карт пользователя. Можно использовать для определения "карты по умолчанию"
-- is_work:     можно ли использовать для оплаты
-- pan:         номер карты. В случае с Крещатиком, тут будет номер крещатика, а не визы
-- additional:  поле для дополнительных данных в json
-- last_verify: время последней проверки тем или иным способом, что человек действительный владелец этой карты.
CREATE TABLE `gioc_user_cards`(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `type` ENUM('khreshchatyk') NOT NULL DEFAULT 'khreshchatyk',
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `pos` INT(11) NOT NULL,
  `is_work` TINYINT(1) NOT NULL DEFAULT 1,
  `pan` VARCHAR(30) NOT NULL,
  `additional` TEXT COMMENT 'json',
  `last_verify` DOUBLE,
  PRIMARY KEY (`id`),
  INDEX (`user_id`, `type`, `is_work`, `pos`),
  INDEX (`pan`)
);

ALTER TABLE `gioc_payment`
  CHANGE `persent` `percent` DOUBLE NULL;

ALTER TABLE `gioc_payment`
  DROP INDEX `user_id_2`;

-- 2016.01.04
-- В этот индексе два поля, есть такой же индекс, но с третим полем. То есть этот индекс дублируется, удаляю его.
ALTER TABLE `gioc_page_views`
  DROP INDEX `page_type`;


-- 2016.01.05
ALTER TABLE `gioc_users`
  ADD COLUMN `deleted_processed` TINYINT(1) DEFAULT 0  NOT NULL AFTER `deleted_timestamp`,
  ADD COLUMN `deleted_answer` TEXT NULL AFTER `deleted_processed`,
  ADD COLUMN `deleted_answer_time` DOUBLE NULL AFTER `deleted_answer`, 
  DROP INDEX `deleted`,
  ADD  INDEX `deleted` (`deleted`, `deleted_processed`);

-- 2016.01.13
ALTER TABLE `gioc_site`.`gioc_email_cron`
  ADD COLUMN `additional` TEXT NULL  COMMENT 'json' AFTER `send_email`;

-- 2016.01.25
ALTER TABLE `gioc_feedback`
  ADD COLUMN `answer_need_send` TINYINT(1) DEFAULT 0  NOT NULL  COMMENT 'for cron task' AFTER `answer_time`;

-- 2016.01.28
CREATE TABLE `gioc_email_cron_part`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cron_id` INT(11) NOT NULL,
  `status` ENUM('new','sending','complete','pause') NOT NULL DEFAULT 'new',
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `start_user_id` INT(11) NOT NULL,
  `finish_user_id` INT(11) NOT NULL,
  `send_email` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX (`status`),
  INDEX (`cron_id`)
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `gioc_email_cron`
  DROP COLUMN `start_user_id`;

ALTER TABLE `gioc_email_cron`
  ADD COLUMN `plain_text` MEDIUMTEXT NULL AFTER `content`;

ALTER TABLE `gioc_subscribers`
  ADD COLUMN `broken_email` TINYINT(1) DEFAULT 0  NOT NULL AFTER `subscribe`, 
  DROP INDEX `subscribe`,
  ADD  INDEX `subscribe` (`subscribe`, `broken_email`);

-- 2016.02.17
ALTER TABLE `gioc_payment`
  CHANGE `processing` `processing` ENUM('_test_upc','mastercard','visa','webmoney','khreshchatyk','tas') CHARSET utf8 COLLATE utf8_general_ci NULL;

-- 2016.02.21
ALTER TABLE `gioc_payment`
  CHANGE `status` `status` ENUM('new','success','error','reverse','timeout') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'new' NOT NULL;

-- 2016.02.26
ALTER TABLE `gioc_payment`
  CHANGE `processing` `processing` ENUM('_test_upc','mastercard','visa','webmoney','khreshchatyk','tas','oschad') CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `gioc_users`
  DROP COLUMN `oauth_uid`, 
  DROP COLUMN `oauth_provider`, 
  DROP COLUMN `userpic`, 
  DROP COLUMN `username`, 
  DROP COLUMN `screen_name`, 
  ADD COLUMN `max_objects` INT(11) DEFAULT 4  NOT NULL AFTER `deleted_answer_time`;


-- 2016.03.10
ALTER TABLE `gioc_payment`
  CHANGE `reports_data` `reports_data` MEDIUMTEXT CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `gioc_payment`
  DROP COLUMN `reports_data`;

-- 2016.03.16
ALTER TABLE `gioc_payment`
  DROP COLUMN `percent`, 
  DROP INDEX `type`;

-- 2016.03.24
DROP TABLE gioc_news_images;

-- 2016.04.20
ALTER TABLE `gioc_feedback`
  ADD COLUMN `address` VARCHAR(500) NULL AFTER `subject`;

-- 2016.06.08
ALTER TABLE `gioc_payment`   
  CHANGE `processing` `processing` ENUM('_test_upc','mastercard','visa','webmoney','khreshchatyk','tas','oschad','oschad_mycard') CHARSET utf8 COLLATE utf8_general_ci NULL;

-- 2016.18.19
ALTER TABLE `gioc_payment`
  ADD COLUMN `trancode` VARCHAR(10) NULL AFTER `flat_id`;

-- 2016.09.07
ALTER TABLE `gioc_page_views`
  DROP INDEX `page_type_2`,
  ADD  INDEX `page_type_2` (`page_type`, `page_id`);

-- 2016.09.19
ALTER TABLE `gioc_payment`
  CHANGE `processing` `processing` ENUM('_test_upc','mastercard','visa','webmoney','khreshchatyk','tas','oschad','oschad_mycard','oschadbank') CHARSET utf8 COLLATE utf8_general_ci NULL;

-- 2016.09.22
CREATE TABLE `gioc_page_views_user_agents`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `created_at` DOUBLE NOT NULL,
  `updated_at` DOUBLE NOT NULL,
  `views` INT(11) NOT NULL DEFAULT 0,
  `user_agent_string` VARCHAR(2000),
  PRIMARY KEY (`id`),
  INDEX (`user_agent_string`(30))
) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `gioc_page_views`
  ADD COLUMN `user_agent_id` INT(11) NOT NULL AFTER `user_agent_string`;

ALTER TABLE `gioc_page_views`
  DROP COLUMN `user_agent_string`;

-- ============================= CSK

-- 2016.09.27
DROP TABLE gioc_auth_code;
DROP TABLE gioc_chief;
DROP TABLE gioc_feedback;
DROP TABLE gioc_hot_news;
DROP TABLE gioc_menus;
DROP TABLE gioc_news;
DROP TABLE gioc_pages;
DROP TABLE gioc_pages_links;
DROP TABLE gioc_page_views;
DROP TABLE gioc_page_views_user_agents;
DROP TABLE gioc_social;
DROP TABLE gioc_subscribers;
DROP TABLE gioc_text_errors;
DROP TABLE gioc_useful_links;
DROP TABLE gioc_user_cards;
DROP TABLE gioc_video;

DROP TABLE gioc_cities;
DROP TABLE gioc_streets;
DROP TABLE gioc_flat;
DROP TABLE gioc_houses;

RENAME TABLE `gioc_email_cron` TO `cks_email_cron`; 
RENAME TABLE `gioc_email_cron_part` TO `cks_email_cron_part`; 
RENAME TABLE `gioc_payment` TO `cks_payment`; 
RENAME TABLE `gioc_text` TO `cks_text`; 
RENAME TABLE `gioc_payment_services` TO `cks_payment_services`; 
RENAME TABLE `gioc_user_codes` TO `cks_user_codes`; 
RENAME TABLE `gioc_user_flats` TO `cks_user_flats`; 
RENAME TABLE `gioc_users` TO `cks_users`; 


CREATE TABLE IF NOT EXISTS `cks_cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ua` varchar(500) NOT NULL,
  `name_ru` varchar(500) NOT NULL,
  `name_en` varchar(500) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `pos` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB AUTO_INCREMENT=448 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cks_flats` (
  `city_id` int(11) NOT NULL,
  `street_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `flat_number` varchar(20) NOT NULL,
  `need_del_after_rebuild` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`),
  KEY `need_del_after_rebuild` (`need_del_after_rebuild`),
  KEY `city_id` (`city_id`),
  KEY `street_id` (`street_id`),
  KEY `house_id` (`house_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cks_houses` (
  `house_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `street_id` int(11) NOT NULL,
  `house_number` varchar(20) NOT NULL,
  PRIMARY KEY (`house_id`),
  KEY `city_id_2` (`city_id`),
  KEY `street_id` (`street_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cks_streets` (
  `street_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name_ua` varchar(500) DEFAULT NULL,
  `name_ru` varchar(500) DEFAULT NULL,
  `name_en` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`street_id`),
  KEY `city_id` (`city_id`),
  KEY `name_ru` (`name_ru`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2016.09.28
ALTER TABLE `cks_user_flats`
  CHANGE `timestamp` `created_at` DOUBLE NOT NULL;

-- 2016.09.29
ALTER TABLE `cks_payment`
  CHANGE `type` `type` ENUM('gai','kinders','komdebt','cks') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'komdebt'  NOT NULL;

ALTER TABLE `cks_payment`
  CHANGE `processing` `processing` ENUM('mastercard','visa','webmoney','khreshchatyk','tas','oschad_mycard','oschadbank') CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `cks_payment`
  CHANGE `processing` `processing` ENUM('mastercard','visa','webmoney','tas','oschad_mycard','oschadbank') CHARSET utf8 COLLATE utf8_general_ci NULL;

-- 2016.10.12
ALTER TABLE `cks_payment`
  CHANGE `processing` `processing` ENUM('mastercard','visa','webmoney','tas','oschad','oschad_mycard','oschadbank') CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `cks_payment`
  AUTO_INCREMENT=1000000001;

-- 2016.10.18
ALTER TABLE `cks_payment`
  CHANGE `type` `type` ENUM('gai','kinders','komdebt','cks','budget') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'komdebt'  NOT NULL;

-- 2016.10.23
ALTER TABLE `cks_payment`
  CHANGE `processing` `processing` ENUM('mastercard','visa','webmoney','tas','oschad','oschad_mycard','oschadbank','marfin') CHARSET utf8 COLLATE utf8_general_ci NULL;

-- 2016.10.24
ALTER TABLE `cks_user_flats`
  ADD COLUMN `plat_code` VARCHAR(100) NULL AFTER `auth_key`;

ALTER TABLE `cks_user_flats`
  DROP INDEX `flat`,
  ADD  UNIQUE INDEX `flat` (`user_id`, `flat_id`, `city_id`, `plat_code`);

-- 2017.03.01
ALTER TABLE `cks_payment`
  DROP COLUMN `reports_num_kvit`;

ALTER TABLE `cks_payment`
  CHANGE `reports_id_pack` `reports_id_pack` INT(11) NULL,
  CHANGE `reports_id_plat_klient` `reports_id_plat_klient` INT(11) NULL;

ALTER TABLE `cks_payment`
  CHANGE `type` `type` ENUM('gai','kinders','komdebt','cks','budget','direct') CHARSET utf8 COLLATE utf8_general_ci DEFAULT 'komdebt'  NOT NULL;

-- 2017.04.25
CREATE TABLE IF NOT EXISTS `cks_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_actual` tinyint(1) NOT NULL DEFAULT '1',
  `title` varchar(1000) NOT NULL,
  `created_at` double NOT NULL,
  `updated_at` double NOT NULL,
  `announce` text NOT NULL,
  `text` mediumtext,
  `views` int(11) NOT NULL DEFAULT '0' COMMENT 'count views',
  `seo_title` varchar(500) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `seo_keywords` varchar(500) DEFAULT NULL,
  `old_site_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_actual`),
  KEY `created_at` (`created_at`),
  KEY `is_actual_2` (`is_actual`,`title`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2017.05.05
CREATE TABLE IF NOT EXISTS `cks_news_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `is_main` tinyint(1) NOT NULL,
  `pos` int(11) NOT NULL,
  `filename` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2017.10.09
CREATE TABLE IF NOT EXISTS `cks_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `fathername` varchar(255) NOT NULL,
  `timestamp` double NOT NULL,
  `ip` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `text` text NOT NULL,
  `status` enum('new','spam','deleted','processed') NOT NULL DEFAULT 'new',
  `answer` text,
  `answer_time` double DEFAULT NULL,
  `answer_need_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'for cron task',
  `answerer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2017.11.09
ALTER TABLE `cks_flats`
  CHANGE `street_id` `street_id` BIGINT(20) NOT NULL,
  CHANGE `house_id` `house_id` BIGINT(20) NOT NULL,
  CHANGE `object_id` `object_id` BIGINT(20) NOT NULL;

ALTER TABLE `cks_houses`
  CHANGE `street_id` `street_id` BIGINT(20) NOT NULL,
  CHANGE `house_id` `house_id` BIGINT(20) NOT NULL;

ALTER TABLE `cks_streets`
  CHANGE `street_id` `street_id` BIGINT(20) NOT NULL;


-- 2017.11.19
CREATE TABLE IF NOT EXISTS `cks_flats_pin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_flat` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `created_at` double NOT NULL,
  `pin` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2017.12.07
ALTER TABLE `cks_payment`
  CHANGE `flat_id` `flat_id` BIGINT(20) NULL   COMMENT 'for komdebt';

ALTER TABLE `cks_flats_pin`
  CHANGE `id_flat` `id_flat` BIGINT(20) NOT NULL;

ALTER TABLE `cks_user_flats`
  CHANGE `flat_id` `flat_id` BIGINT(20) NOT NULL;

-- 2018.01.09
RENAME TABLE `cks_cities` TO `kmda_cities`;
RENAME TABLE `cks_dict_companies` TO `kmda_dict_companies`;
RENAME TABLE `cks_dict_feedback_themes` TO `kmda_dict_feedback_themes`;
RENAME TABLE `cks_dict_regions` TO `kmda_dict_regions`;
RENAME TABLE `cks_email_cron_part` TO `kmda_email_cron_part`;
RENAME TABLE `cks_email_cron` TO `kmda_email_cron`;
RENAME TABLE `cks_feedback` TO `kmda_feedback`;
RENAME TABLE `cks_flats_pin` TO `kmda_flats_pin`;
RENAME TABLE `cks_flats` TO `kmda_flats`;
RENAME TABLE `cks_houses` TO `kmda_houses`;
RENAME TABLE `cks_news_images` TO `kmda_news_images`;
RENAME TABLE `cks_news` TO `kmda_news`;
RENAME TABLE `cks_page_views_user_agents` TO `kmda_page_views_user_agents`;
RENAME TABLE `cks_page_views` TO `kmda_page_views`;
RENAME TABLE `cks_pages_links` TO `kmda_pages_links`;
RENAME TABLE `cks_pages` TO `kmda_pages`;
RENAME TABLE `cks_payment_services` TO `kmda_payment_services`;
RENAME TABLE `cks_payment` TO `kmda_payment`;
RENAME TABLE `cks_service_centers` TO `kmda_service_centers`;
RENAME TABLE `cks_streets` TO `kmda_streets`;
RENAME TABLE `cks_text` TO `kmda_text`;
RENAME TABLE `cks_useful_links` TO `kmda_useful_links`;
RENAME TABLE `cks_user_codes` TO `kmda_user_codes`;
RENAME TABLE `cks_user_flats` TO `kmda_user_flats`;
RENAME TABLE `cks_users` TO `kmda_users`;

-- 2018.01.12
DROP TABLE `kmda_news`;
DROP TABLE `kmda_news_images`;
DROP TABLE `kmda_feedback`;

-- 2018.01.19
ALTER TABLE `kmda_users`
  ADD COLUMN `openid_id` INT(11) NULL AFTER `max_objects`,
  ADD COLUMN `openid_data` TEXT NULL AFTER `openid_id`, 
  ADD INDEX (`openid_id`, `deleted`);

-- 2018.01.31
DROP TABLE kmda_service_centers;

-- 2018.02.12
ALTER TABLE `kmda_payment` AUTO_INCREMENT=1200000000;

-- 2018.06.16
CREATE TABLE `kmda_auth_code` (
  `object_id` bigint(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `created_at` double NOT NULL,
  PRIMARY KEY (`object_id`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2019.04.24
ALTER TABLE `kmda_payment`
  CHANGE `processing` `processing` ENUM('mastercard','visa','webmoney','tas','oschad','oschad_mycard','oschadbank','marfin','psp') CHARSET utf8 COLLATE utf8_general_ci NULL;

