/*
SAVE:		DB Creation and Maintenance Script
PROJECT:	Dashboard tables enable multiple scripts to regularly update the Williams College LMS (Canvas)

FOR TESTING ONLY:
	USE `lti_development`;

	DROP TABLE `dashboard_users`;
	DROP TABLE `dashboard_eventlogs`;

	DELETE FROM `dashboard_users`;
	DELETE FROM `dashboard_eventlogs`;

	SELECT * FROM `dashboard_users`;
	SELECT * FROM `dashboard_eventlogs`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to create and run this script against
# ----------------------------
# Database for Development work
CREATE SCHEMA IF NOT EXISTS `lti_development`;
USE `lti_development`;

# Database for live (production) work
--  CREATE SCHEMA IF NOT EXISTS `lti_live`;
-- USE `lti_live`;

# ----------------------------
# setup database tables
# ----------------------------

CREATE TABLE IF NOT EXISTS `dashboard_users` (
	`dash_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`canvas_user_id` INT NOT NULL DEFAULT 0,
	`name` VARCHAR(255) NULL DEFAULT '',
	`sortable_name` VARCHAR(255) NULL DEFAULT '',
	`short_name` VARCHAR(255) NULL DEFAULT '',
	`sis_user_id` INT NULL DEFAULT 0,
	`integration_id` INT NULL DEFAULT 0,
	`sis_login_id` VARCHAR(255) NULL DEFAULT '',
	`sis_import_id` INT NOT NULL DEFAULT 0,
	`username` VARCHAR(255) NULL DEFAULT '',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_is_set_avatar_image` tinyint(1) unsigned NOT NULL default 0,
	`flag_is_set_notification_preference` tinyint(1) unsigned NOT NULL default 0,
	`flag_delete` tinyint(1) unsigned NOT NULL default 0,
	INDEX `canvas_user_id` (`canvas_user_id`),
	INDEX `name` (`name`),
	INDEX `sortable_name` (`sortable_name`),
	INDEX `short_name` (`short_name`),
	INDEX `sis_user_id` (`sis_user_id`),
	INDEX `integration_id` (`integration_id`),
	INDEX `sis_login_id` (`sis_login_id`),
	INDEX `sis_import_id` (`sis_import_id`),
	INDEX `username` (`username`),
	INDEX `flag_is_set_avatar_image` (`flag_is_set_avatar_image`),
	INDEX `flag_is_set_notification_preference` (`flag_is_set_notification_preference`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync Canvas users and data with this dashboard table';
/* dashboard_users table is a sync of Canvas user data */
/* field 'canvas_user_id' corresponds to Canvas LMS field called 'id' */
/* field 'username' corresponds to Canvas LMS field called 'login_id' */

CREATE TABLE IF NOT EXISTS `dashboard_eventlogs` (
	`eventlog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`event_action` VARCHAR(255) NULL,
	`event_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`event_log_filepath` VARCHAR(255) NULL,
	`event_action_filepath` VARCHAR(255) NULL,
	`num_items` INT NULL DEFAULT 0,
	`num_changes` INT NULL DEFAULT 0,
	`num_errors` INT NULL DEFAULT 0,
	`event_dataset` VARCHAR(2000) NULL,
	`flag_success` tinyint(1) unsigned NOT NULL default 0,
	`flag_cron_job` tinyint(1) unsigned NOT NULL default 0,
	INDEX `eventlog_id` (`eventlog_id`),
	INDEX `event_action` (`event_action`),
	INDEX `event_datetime` (`event_datetime`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Event logs maintain an audit of site actions';

-- Set Initial Data
UPDATE `dashboard_users` SET `flag_is_set_notification_preference` = 1 WHERE canvas_user_id <= 6540605;


