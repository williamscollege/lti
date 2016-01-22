/*
SAVE:		DB Creation and Maintenance Script
PROJECT:	Dashboard tables enable multiple scripts to regularly update the Williams College LMS (Canvas)

FOR TESTING ONLY:
	USE `lti_development`;

	DROP TABLE `dashboard_users`;
	DROP TABLE `dashboard_eventlogs`;
	DROP TABLE `dashboard_sis_imports_raw`;
	DROP TABLE `dashboard_sis_imports_parsed`;
	DROP TABLE `dashboard_faculty_current`;

	DELETE FROM `dashboard_users`;
	DELETE FROM `dashboard_eventlogs`;
	DELETE FROM `dashboard_sis_imports_raw`;
	DELETE FROM `dashboard_sis_imports_parsed`;
	DELETE FROM `dashboard_faculty_current`;

	SELECT * FROM `dashboard_users`;
	SELECT * FROM `dashboard_eventlogs`;
	SELECT * FROM `dashboard_sis_imports_raw`;
	SELECT * FROM `dashboard_sis_imports_parsed`;
	SELECT * FROM `dashboard_faculty_current`;
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
	`flag_is_teacher` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`flag_is_enrolled_course_ffr` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`flag_is_set_notification_preference` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`flag_is_set_avatar_image` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`flag_delete` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	INDEX `canvas_user_id` (`canvas_user_id`),
	INDEX `name` (`name`),
	INDEX `sortable_name` (`sortable_name`),
	INDEX `short_name` (`short_name`),
	INDEX `sis_user_id` (`sis_user_id`),
	INDEX `integration_id` (`integration_id`),
	INDEX `sis_login_id` (`sis_login_id`),
	INDEX `sis_import_id` (`sis_import_id`),
	INDEX `username` (`username`),
	INDEX `flag_is_teacher` (`flag_is_teacher`),
	INDEX `flag_is_enrolled_course_ffr` (`flag_is_enrolled_course_ffr`),
	INDEX `flag_is_set_notification_preference` (`flag_is_set_notification_preference`),
	INDEX `flag_is_set_avatar_image` (`flag_is_set_avatar_image`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync Canvas users and data with this dashboard table';
/* dashboard_users table is a sync of Canvas user data */
/* field 'canvas_user_id' corresponds to Canvas LMS field called 'id' */
/* field 'username' corresponds to Canvas LMS field called 'login_id' */

CREATE TABLE IF NOT EXISTS `dashboard_eventlogs` (
	`eventlog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`event_action` VARCHAR(255) DEFAULT NULL,
	`event_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`event_log_filepath` VARCHAR(255) DEFAULT NULL,
	`event_action_filepath` VARCHAR(255) DEFAULT NULL,
	`num_items` INT NULL DEFAULT 0,
	`num_adds` INT NULL DEFAULT 0,
	`num_edits` INT NULL DEFAULT 0,
	`num_removes` INT NULL DEFAULT 0,
	`num_skips` INT NULL DEFAULT 0,
	`num_errors` INT NULL DEFAULT 0,
	`event_dataset_brief` varchar(255) DEFAULT NULL,
	`event_dataset_full` varchar(2000) DEFAULT NULL,
	`flag_success` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	`flag_cron_job` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
	INDEX `eventlog_id` (`eventlog_id`),
	INDEX `event_action` (`event_action`),
	INDEX `event_datetime` (`event_datetime`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Event logs maintain an audit of site actions';

CREATE TABLE IF NOT EXISTS `dashboard_sis_imports_raw` (
	`raw_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`cronjob_datetime` TIMESTAMP NULL,
	`created_at` TIMESTAMP NULL,
	`ended_at` TIMESTAMP NULL,
	`file_prep_status` VARCHAR(2500) NULL DEFAULT '',
	`curl_return_code` VARCHAR(1000) NULL DEFAULT '',
	`curl_import_id` INT NOT NULL DEFAULT 0,
	`curl_final_import_status` VARCHAR(4000) NULL DEFAULT '',
	INDEX `curl_import_id` (`curl_import_id`),
	INDEX `created_at` (`created_at`),
	INDEX `ended_at` (`ended_at`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Log curl call and import results with Instructure Canvas LMS';
/* dashboard_sis_imports_raw table logs curl call and import results with Instructure Canvas LMS */

CREATE TABLE IF NOT EXISTS `dashboard_sis_imports_parsed` (
	`parsed_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`cronjob_datetime` TIMESTAMP NULL,
	`created_at` TIMESTAMP NULL,
	`started_at` TIMESTAMP NULL,
	`ended_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL,
	`progress` INT NOT NULL DEFAULT 0,
	`id` INT NOT NULL DEFAULT 0,
	`workflow_state` VARCHAR(100) NULL DEFAULT '',
	`data_import_type` VARCHAR(100) NULL DEFAULT '',
	`data_supplied_batches` VARCHAR(255) NULL DEFAULT '',
	`data_counts_accounts` INT NOT NULL DEFAULT 0,
	`data_counts_terms` INT NOT NULL DEFAULT 0,
	`data_counts_abstract_courses` INT NOT NULL DEFAULT 0,
	`data_counts_courses` INT NOT NULL DEFAULT 0,
	`data_counts_sections` INT NOT NULL DEFAULT 0,
	`data_counts_xlists` INT NOT NULL DEFAULT 0,
	`data_counts_users` INT NOT NULL DEFAULT 0,
	`data_counts_enrollments` INT NOT NULL DEFAULT 0,
	`data_counts_groups` INT NOT NULL DEFAULT 0,
	`data_counts_group_memberships` INT NOT NULL DEFAULT 0,
	`data_counts_grade_publishing_results` INT NOT NULL DEFAULT 0,
	`batch_mode` VARCHAR(100) NULL DEFAULT '',
	`batch_mode_term_id` INT NOT NULL DEFAULT 0,
	`override_sis_stickiness` VARCHAR(100) NULL DEFAULT '',
	`add_sis_stickiness` VARCHAR(100) NULL DEFAULT '',
	`clear_sis_stickiness` VARCHAR(100) NULL DEFAULT '',
	`diffing_data_set_identifier` VARCHAR(100) NULL DEFAULT '',
	`diffed_against_import_id` VARCHAR(100) NULL DEFAULT '',
	`processing_warnings` VARCHAR(6500) NULL DEFAULT '',
	INDEX `created_at` (`created_at`),
	INDEX `ended_at` (`ended_at`),
	INDEX `updated_at` (`updated_at`),
	INDEX `progress` (`progress`),
	INDEX `id` (`id`),
	INDEX `workflow_state` (`workflow_state`),
	INDEX `data_counts_terms` (`data_counts_terms`),
	INDEX `data_counts_courses` (`data_counts_courses`),
	INDEX `data_counts_sections` (`data_counts_sections`),
	INDEX `data_counts_xlists` (`data_counts_xlists`),
	INDEX `data_counts_users` (`data_counts_users`),
	INDEX `data_counts_enrollments` (`data_counts_enrollments`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Log sanitized import results from curl call with Instructure Canvas LMS';
/* dashboard_sis_imports_parsed table logs sanitized import results from curl call with Instructure Canvas LMS */

CREATE TABLE IF NOT EXISTS `dashboard_faculty_current` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`wms_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
	`username` VARCHAR(255) NULL DEFAULT '',
	`first_name` VARCHAR(255) NULL DEFAULT '',
	`last_name` VARCHAR(255) NULL DEFAULT '',
	`email` VARCHAR(255) NULL DEFAULT '',
	`created_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX `wms_user_id` (`wms_user_id`),
	INDEX `username` (`username`),
	INDEX `first_name` (`first_name`),
	INDEX `last_name` (`last_name`),
	INDEX `email` (`email`),
	INDEX `created_datetime` (`created_datetime`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Maintain current list of faculty according to institutional records';
/* dashboard_faculty_current table maintains a current list of faculty according to institutional records */

-- Set Initial Data
UPDATE `dashboard_users` SET `flag_is_set_notification_preference` = 1 WHERE canvas_user_id <= 6540605;


