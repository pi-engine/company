CREATE TABLE IF NOT EXISTS `company_inventory`
(
	`id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
	`slug`        VARCHAR(255)                 DEFAULT NULL,
	`title`       VARCHAR(255)        NOT NULL DEFAULT '',
	`user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`package_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`reseller_id` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`industry_id` INT(10) UNSIGNED    NOT NULL DEFAULT '1',
	`time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`setting`     JSON,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `company_member`
(
	`id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
	`company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`is_default`  TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `company_id` (`company_id`),
	KEY `list` (`company_id`, `status`)
);

CREATE TABLE IF NOT EXISTS `company_package`
(
	`id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
	`title`       VARCHAR(255)        NOT NULL DEFAULT '',
	`status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`information` JSON,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `company_team_inventory`
(
	`id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
	`title`       VARCHAR(255)        NOT NULL DEFAULT '',
	`company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`information` JSON,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `company_team_member`
(
	`id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
	`company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`team_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
	`status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`team_role`   VARCHAR(255)        NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `company_id` (`company_id`),
	KEY `list` (`company_id`, `status`)
);