CREATE TABLE `company_inventory`
(
    `id`                              INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `title`                           VARCHAR(255)        NOT NULL DEFAULT '',
    `setting`                         JSON,
    `text_description`                TEXT,
    `user_id`                         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `reseller_id`                     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `industry_id`                     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_create`                     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update`                     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`                          TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `address1`                        VARCHAR(255)        DEFAULT NULL,
    `address2`                        VARCHAR(255)        DEFAULT NULL,
    `country`                         VARCHAR(64)         DEFAULT NULL,
    `state`                           VARCHAR(64)         DEFAULT NULL,
    `city`                            VARCHAR(64)         DEFAULT NULL,
    `zip_code`                        VARCHAR(16)         DEFAULT NULL,
    `phone`                           VARCHAR(16)         DEFAULT NULL,
    `website`                         VARCHAR(64)         DEFAULT NULL,
    `email`                           VARCHAR(64)         DEFAULT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `company_member`
(
    `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `company_id`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_create` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `company_id` (`company_id`),
    KEY `list` (`company_id`, `status`)
);