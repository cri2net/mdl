

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
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

INSERT INTO `gioc_text` (`id`, `variable`, `text`) VALUES
(1, 'CONTACTS_BLOCK_CLOCK', '<h3 id="page-map-clock" class="page-subtitle border-top">Графiк роботи</h3>\r\n\r\n<div class="work-content">\r\n    <div class="line green">\r\n        <div class="col">Понеділок—Четвер</div>\r\n        <div class="col-r">8<sup>30</sup>—17<sup>30</sup></div>\r\n    </div>\r\n    <div class="line green">\r\n        <div class="col">П''ятниця</div>\r\n        <div class="col-r">8<sup>30</sup>—16<sup>15</sup></div>\r\n    </div>\r\n    <div class="line yellow">\r\n        <div class="col">Обідня перерва</div>\r\n        <div class="col-r">12<sup>30</sup>—13<sup>15</sup></div>\r\n    </div>\r\n    \r\n    <h4 class="title">Прийом "Відділом Звернень"</h4>\r\n    <div class="line green">\r\n        <div class="col">Понеділок—Четвер</div>\r\n        <div class="col-r">9<sup>00</sup>—17<sup>00</sup></div>\r\n    </div>\r\n    <div class="line green">\r\n        <div class="col">П''ятниця</div>\r\n        <div class="col-r">9<sup>00</sup>—16<sup>00</sup></div>\r\n    </div>\r\n   \r\n    <h4 class="title">Прийом керівництвом</h4>\r\n    <div class="line green">\r\n        <div class="col">Вівторок</div>\r\n        <div class="col-r">14<sup>00</sup>—17<sup>00</sup></div>\r\n    </div>\r\n    <div class="line comment">\r\n        за попереднім записом в секретаріаті: тел. +380 (44) 238-80-05\r\n    </div>\r\n</div>'),
(2, 'CONTACTS_BLOCK_PHONE', '<h3 id="page-map-phone" class="page-subtitle border-top">Телефони та електрона пошта</h3>\r\n<h4 class="title" style="margin-top:43px;">Контакт-центр</h4>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>\r\n    +38 (044) 238 80 25,\r\n    +38 (044) 238 80 27\r\n</div>\r\n<h4 class="title">Приймальня</h4>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>\r\n    +38 (044) 513-52-52,\r\n    +38 (044) 238-80-55,\r\n    +38 (044) 238-80-05\r\n</div>\r\n<div style="phone-line">\r\n    <span style="width:49px; display:inline-block; line-height:24px;">факс:</span>\r\n    +38 (044) 238-80-38,\r\n    +38 (044) 238-80-50\r\n</div>\r\n<h4 class="title">Електронна пошта</h4>\r\n<div style="phone-line">\r\n    <a class="no-decoration" href="mailto:secretary@gioc-kmda.kiev.ua" target="_blank">secretary@gioc-kmda.kiev.ua</a>\r\n</div>'),
(3, 'CONTACTS_BLOCK_MARKER', '<h3 id="page-map-marker" class="page-subtitle border-top">Адреса</h3>\r\n<h4 class="title">Поштова адреса</h4>\r\n02192, Україна, м. Київ, вул. Космічна, 12-а\r\n<div class="map-block" style="height:274px; width: 100%;">\r\n    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d634.9232534968156!2d30.622652!3d50.465441!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0000000000000000%3A0x37f7d3eaa6d2faa4!2z0JrQvtC80YPQvdCw0LvRjNC90LUg0L_RltC00L_RgNC40ZTQvNGB0YLQstC-ICLQk9C-0LvQvtCy0L3QuNC5INGW0L3RhNC-0YDQvNCw0YbRltC50L3QviAtINC-0LHRh9C40YHQu9GO0LLQsNC70YzQvdC40Lkg0YbQtdC90YLRgCI!5e0!3m2!1sru!2sua!4v1440763298928" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>\r\n</div>'),
(4, 'HEADER_PHONE', '<a class="tel" href="tel:+380442388025">(044) 238-80-25</a>'),
(5, 'HEADER_PHONE_SECOND', '<a class="tel" href="tel:+380442388027">(044) 238-80-27</a>'),
(6, 'HEADER_WORK', '<div class="line green">\r\n  <div class="col">пн—чт</div>\r\n  <div class="col-r">8:30—17:30</div>\r\n</div>\r\n<div class="line green">\r\n <div class="col">пт</div>\r\n <div class="col-r">8:30—16:15</div>\r\n</div>\r\n<div class="line yellow">\r\n  <div class="col">перерва</div>\r\n  <div class="col-r">12:30—13:15</div>\r\n</div>');


-- IN ONLINE


