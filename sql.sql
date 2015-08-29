

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


-- IN ONLINE


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


