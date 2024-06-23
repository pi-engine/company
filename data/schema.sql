CREATE TABLE IF NOT EXISTS `company_inventory`
(
    `id`               INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)        NOT NULL DEFAULT '',
    `setting`          JSON,
    `text_description` TEXT,
    `user_id`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `package_id`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `reseller_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `industry_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '1',
    `time_create`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `time_update`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
    `status`           TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
    `address_1`        VARCHAR(255)                 DEFAULT NULL,
    `address_2`        VARCHAR(255)                 DEFAULT NULL,
    `country`          VARCHAR(64)                  DEFAULT NULL,
    `state`            VARCHAR(64)                  DEFAULT NULL,
    `city`             VARCHAR(64)                  DEFAULT NULL,
    `zip_code`         VARCHAR(16)                  DEFAULT NULL,
    `phone`            VARCHAR(16)                  DEFAULT NULL,
    `website`          VARCHAR(64)                  DEFAULT NULL,
    `email`            VARCHAR(64)                  DEFAULT NULL,
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
    `is_default`  TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
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

# default roles for manage company RBAC
/*
 INSERT INTO `role_resource` (`id`, `name`, `title`, `section`, `status`) VALUES
(1, 'member', 'Member', 'api', 1),
(2, 'admin', 'Admin', 'admin', 1),
(3, 'companyadmin', 'Admin', 'api', 1),
(4, 'companysuperuser', 'Super User', 'api', 1),
(5, 'companyassessmentmanager', 'Assessment Manager', 'api', 1),
(6, 'companycompliancemanager', 'Compliance Manager', 'api', 1),
(7, 'companyriskmanager', 'Risk Manager', 'api', 1),
(8, 'companyauditmanager', 'Audit Manager', 'api', 1),
(9, 'companycomplianceofficer', 'Compliance Officer', 'api', 1),
(10, 'companyauditor', 'Auditor', 'api', 1),
(11, 'companymember', 'Team Member', 'api', 1),
(12, 'companyviewer', 'Vewer', 'api', 1),
(13, 'companyexternal', 'External Member', 'api', 1);
 */