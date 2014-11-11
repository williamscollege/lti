/* 
SAVE:
	DB Creation and Maintenance Script

PROJECT:
	Signup Sheets (lti-signup-sheets)

TODO:
	schedules
	all TODO items

NOTES:


FOR TESTING ONLY:
	DROP TABLE `lms_sus_access`;
	DROP TABLE `lms_sus_openings`;
	DROP TABLE `lms_sus_sheetgroups`;
	DROP TABLE `lms_sus_sheets`;
	DROP TABLE `lms_sus_signups`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# ----------------------------
CREATE SCHEMA IF NOT EXISTS `lti_signup_sheets`;

-- USE lti_signup_sheets_TEST;
USE lti_signup_sheets;


# ----------------------------
# basic application infrastructure

CREATE TABLE IF NOT EXISTS `lms_sus_access` (
    `id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` bigint(10) unsigned default NULL,
    `updated_at` bigint(10) unsigned default NULL,
    `last_user_id` bigint(10) unsigned default NULL,
    `sheet_id` bigint(10) unsigned default NULL,
    `type` varchar(48) default NULL,
    `constraint_id` bigint(10) unsigned default NULL,
    `constraint_data` varchar(32) default NULL,
    `broadness` int(11) default NULL,
    PRIMARY KEY (`id`),
    KEY `sheet_id` (`sheet_id`),
    KEY `type` (`type`),
    KEY `constraint_id` (`constraint_id`),
    KEY `constraint_data` (`constraint_data`),
    KEY `broadness` (`broadness`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='which users can signup on which sheets';


CREATE TABLE IF NOT EXISTS `lms_sus_openings` (
    `id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` bigint(10) unsigned default NULL,
    `updated_at` bigint(10) unsigned default NULL,
    `flag_deleted` tinyint(1) unsigned default NULL,
    `last_user_id` bigint(10) unsigned default NULL,
    `sus_sheet_id` bigint(10) unsigned default NULL,
    `opening_set_id` bigint(20) unsigned default NULL,
    `name` varchar(255) default NULL,
    `description` text,
    `max_signups` mediumint(6) unsigned default NULL,
    `admin_comment` varchar(255) default NULL,
    `begin_datetime` bigint(10) unsigned default NULL,
    `end_datetime` bigint(10) unsigned default NULL,
    `location` varchar(255) default NULL,
    PRIMARY KEY (`id`),
    KEY `flag_deleted` (`flag_deleted`),
    KEY `sus_sheet_id` (`sus_sheet_id`),
    KEY `opening_set_id` (`opening_set_id`),
    KEY `begin_datetime` (`begin_datetime`),
    KEY `end_datetime` (`end_datetime`),
    KEY `location` (`location`),
    KEY `name` (`name`),
    KEY `last_user_id` (`last_user_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Places users can sign up - a single sheet may have multiple ';


CREATE TABLE IF NOT EXISTS `lms_sus_sheetgroups` (
    `id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` bigint(10) unsigned default NULL,
    `updated_at` bigint(10) unsigned default NULL,
    `flag_deleted` tinyint(1) unsigned default NULL,
    `owner_user_id` bigint(10) unsigned default NULL,
    `flag_is_default` int(1) NOT NULL default '0',
    `name` varchar(255) default NULL,
    `description` text,
    `max_g_total_user_signups` smallint(3) default NULL,
    `max_g_pending_user_signups` smallint(3) default NULL,
    PRIMARY KEY (`id`),
    KEY `flag_deleted` (`flag_deleted`),
    KEY `owner_user_id` (`owner_user_id`),
    KEY `flag_is_default` (`flag_is_default`),
    KEY `name` (`name`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='For managing collections of related sheets';

CREATE TABLE IF NOT EXISTS `lms_sus_sheets` (
    `id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` bigint(10) unsigned default NULL,
    `updated_at` bigint(10) unsigned default NULL,
    `flag_deleted` tinyint(1) unsigned default NULL,
    `owner_user_id` bigint(10) unsigned default NULL,
    `last_user_id` bigint(10) unsigned default NULL,
    `sus_sheetgroup_id` bigint(10) unsigned default NULL,
    `name` varchar(255) default NULL,
    `description` text,
    `type` varchar(32) default NULL,
    `date_opens` bigint(10) unsigned default NULL,
    `date_closes` bigint(10) unsigned default NULL,
    `max_total_user_signups` smallint(3) unsigned default NULL,
    `max_pending_user_signups` smallint(3) unsigned default NULL,
    `flag_alert_owner_change` tinyint(1) unsigned default NULL,
    `flag_alert_owner_signup` tinyint(1) unsigned default NULL,
    `flag_alert_owner_imminent` tinyint(1) unsigned default NULL,
    `flag_alert_admin_change` tinyint(1) unsigned default NULL,
    `flag_alert_admin_signup` tinyint(1) unsigned default NULL,
    `flag_alert_admin_imminent` tinyint(1) unsigned default NULL,
    `flag_private_signups` int(1) default '1',
    PRIMARY KEY (`id`),
    KEY `flag_deleted` (`flag_deleted`),
    KEY `owner_user_id` (`owner_user_id`),
    KEY `sus_sheetgroup_id` (`sus_sheetgroup_id`),
    KEY `name` (`name`),
    KEY `type` (`type`),
    KEY `date_opens` (`date_opens`),
    KEY `date_closes` (`date_closes`),
    KEY `flag_alert_owner_change` (`flag_alert_owner_change`),
    KEY `flag_alert_owner_signup` (`flag_alert_owner_signup`),
    KEY `flag_alert_owner_imminent` (`flag_alert_owner_imminent`),
    KEY `flag_alert_admin_change` (`flag_alert_admin_change`),
    KEY `flag_alert_admin_signup` (`flag_alert_admin_signup`),
    KEY `flag_alert_admin_imminent` (`flag_alert_admin_imminent`),
    KEY `flag_private_signups` (`flag_private_signups`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Contains the high-level sheet data (name, descr, etc.)';


CREATE TABLE IF NOT EXISTS `lms_sus_signups` (
    `id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` bigint(10) unsigned default NULL,
    `updated_at` bigint(10) unsigned default NULL,
    `flag_deleted` tinyint(1) unsigned default NULL,
    `last_user_id` bigint(10) unsigned default NULL,
    `sus_opening_id` bigint(10) unsigned default NULL,
    `signup_user_id` bigint(10) unsigned default NULL,
    `admin_comment` varchar(255) default NULL,
    PRIMARY KEY (`id`),
    KEY `flag_deleted` (`flag_deleted`),
    KEY `last_user_id` (`last_user_id`),
    KEY `sus_opening_id` (`sus_opening_id`),
    KEY `signup_user_id` (`signup_user_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Users signing up for openings - analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name';




#####################
# Required: The Absolute Minimalist Approach to Initial Data Population
#####################

/*
-- Values for ...
INSERT INTO
	lms_sus_access
VALUES
(741,NOW(),NOW(),123,310,'bycourse',5193,'',20),
(742,NOW(),NOW(),1054,317,'bycourse',5145,'',20),
(743,NOW(),NOW(),1054,317,'byhasaccount',0,'all',70),
(744,NOW(),NOW(),1054,317,'byrole',0,'student',60),
(745,NOW(),NOW(),1054,317,'byrole',0,'teacher',60),;

-- Values for ...
INSERT INTO
lms_sus_openings
VALUES
-- TODO
;

-- Values for ...
INSERT INTO
  lms_sus_sheetgroups
  VALUES
-- TODO
;

-- Values for ...
INSERT INTO
  lms_sus_sheets
  VALUES
 -- TODO
 ;

-- Values for ...
INSERT INTO
  lms_sus_signups
  VALUES
 -- TODO
;
*/
