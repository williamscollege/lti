/* 
SAVE:
	DB Creation and Maintenance Script

PROJECT:
	Signup Sheets (lti-signup-sheets)

NOTES:
	For testing, create 'dblinktest' table by executing: "db_setup/testing_schema.sql"

FOR TESTING ONLY:
	USE lti_signup_sheets_test;

	DROP TABLE `lms_users`;
	DROP TABLE `lms_terms`;
	DROP TABLE `lms_enrollments`;
	DROP TABLE `lms_courses`;

	DROP TABLE `sus_access`;
	DROP TABLE `sus_openings`;
	DROP TABLE `sus_sheetgroups`;
	DROP TABLE `sus_sheets`;
	DROP TABLE `sus_signups`;

	DROP TABLE `roles`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# ----------------------------
CREATE SCHEMA IF NOT EXISTS `lti_signup_sheets_test`;
USE lti_signup_sheets_test;

-- CREATE SCHEMA IF NOT EXISTS `lti_signup_sheets`;
-- USE lti_signup_sheets;

# ----------------------------
# basic application infrastructure
# ----------------------------

CREATE TABLE IF NOT EXISTS `lms_users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `first_name` VARCHAR(255) NULL,
    `last_name` VARCHAR(255) NULL,
    `screen_name` VARCHAR(255) NULL,
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    `flag_is_system_admin` BIT(1) NOT NULL DEFAULT 0,
    `flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';
/* field 'username' corresponds to Canvas LMS field called 'login_id' */

CREATE TABLE IF NOT EXISTS `lms_terms` (
    `term_id` VARCHAR(255) NULL,
    `name` VARCHAR(255) NULL,
    `start_date` TIMESTAMP,
    `end_date` TIMESTAMP,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `lms_enrollments` (
    `course_id` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `role` VARCHAR(255) NULL,
    `section_id` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `lms_courses` (
    `course_id` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(255) NOT NULL,
    `long_name` VARCHAR(255) NOT NULL,
    `account_id` VARCHAR(255) NULL,
    `term_id` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `sus_access` (
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

CREATE TABLE IF NOT EXISTS `sus_openings` (
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

CREATE TABLE IF NOT EXISTS `sus_sheetgroups` (
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

CREATE TABLE IF NOT EXISTS `sus_sheets` (
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

CREATE TABLE IF NOT EXISTS `sus_signups` (
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

CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `priority` INT NOT NULL,
    `name` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';
/* priority: Highest admin role is priority = 1; lowest anonymous/guest priority is > 1 */


/*
CREATE TABLE IF NOT EXISTS `user_role_links` (
    `user_role_link_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    `last_user_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='determines allowable actions within the digitalfieldnotebooks system';
*//* FK: users.user_id *//*
CREATE TABLE IF NOT EXISTS `actions` (
    `action_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `ordering` DECIMAL(10 , 5 ),
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='actions that users can take - together with roles are used to define permissions';

CREATE TABLE IF NOT EXISTS `role_action_target_links` (
    `role_action_target_link_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    `last_user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `action_id` INT NOT NULL,
    `target_type` VARCHAR(255) NOT NULL,
    `target_id` INT NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='';
*/
/* This is single inheritance table, meaning it is a linking table that links dependant upon the value of target_type */
/* FK: roles.role_id */
/* FK: actions.action_id */
/* FK: target_id: this is the FK that will link this roles record with objects to which permissions are being granted (value is 0 for global permissions) */
/* NOTE: action permissions are hardcoded into the application - a fully fleshed out action control system is outside the scope of this project */


# ----------------------------
# Required: The Absolute Minimalist Approach to Initial Data Population
# ----------------------------

# Required constant values for roles table
INSERT INTO
	roles
VALUES
(1,10,'teacher',0),
(2,15,'student',0),
(3,20,'user',0),
(4,30,'public',0);

/*
# Required constant values for actions table
INSERT INTO
actions
VALUES
(1,'view',1,0),
(2,'edit',2,0),
(3,'update',3,0),
(4,'create',4,0),
(5,'delete',5,0),
(6,'publish',6,0),
(7,'verify',7,0),
(8,'list',8,0);

# Required constant values for role_action_target_links table (managers can do everything)
# 		public static $fields = array('role_action_target_link_id', 'created_at', 'updated_at', 'last_user_id', 'role_id', 'action_id', 'target_type', 'target_id', 'flag_delete');
INSERT INTO
  role_action_target_links
  VALUES
  (1,NOW(),NOW(),0,1,1,'global_notebook',0,0),
  (2,NOW(),NOW(),0,1,2,'global_notebook',0,0),
  (3,NOW(),NOW(),0,1,3,'global_notebook',0,0),
  (4,NOW(),NOW(),0,1,4,'global_notebook',0,0),
  (5,NOW(),NOW(),0,1,5,'global_notebook',0,0),
  (6,NOW(),NOW(),0,1,6,'global_notebook',0,0),
  (7,NOW(),NOW(),0,1,7,'global_notebook',0,0),
  (8,NOW(),NOW(),0,1,1,'global_metadata',0,0),
  (9,NOW(),NOW(),0,1,2,'global_metadata',0,0),
  (10,NOW(),NOW(),0,1,3,'global_metadata',0,0),
  (11,NOW(),NOW(),0,1,4,'global_metadata',0,0),
  (12,NOW(),NOW(),0,1,5,'global_metadata',0,0),
  (13,NOW(),NOW(),0,1,6,'global_metadata',0,0),
  (14,NOW(),NOW(),0,1,7,'global_metadata',0,0),
  (15,NOW(),NOW(),0,1,1,'global_plant',0,0),
  (16,NOW(),NOW(),0,1,2,'global_plant',0,0),
  (17,NOW(),NOW(),0,1,3,'global_plant',0,0),
  (18,NOW(),NOW(),0,1,4,'global_plant',0,0),
  (19,NOW(),NOW(),0,1,5,'global_plant',0,0),
  (20,NOW(),NOW(),0,1,6,'global_plant',0,0),
  (21,NOW(),NOW(),0,1,7,'global_plant',0,0),
  (22,NOW(),NOW(),0,1,1,'global_specimen',0,0),
  (23,NOW(),NOW(),0,1,2,'global_specimen',0,0),
  (24,NOW(),NOW(),0,1,3,'global_specimen',0,0),
  (25,NOW(),NOW(),0,1,4,'global_specimen',0,0),
  (26,NOW(),NOW(),0,1,5,'global_specimen',0,0),
  (27,NOW(),NOW(),0,1,6,'global_specimen',0,0),
  (28,NOW(),NOW(),0,1,7,'global_specimen',0,0),
  (29,NOW(),NOW(),0,1,8,'global_notebook',0,0),
  (30,NOW(),NOW(),0,1,8,'global_metadata',0,0),
  (31,NOW(),NOW(),0,1,8,'global_plant',0,0),
  (32,NOW(),NOW(),0,1,8,'global_specimen',0,0),
  (33,NOW(),NOW(),0,2,8,'global_notebook',0,0),
  (34,NOW(),NOW(),0,2,8,'global_metadata',0,0),
  (35,NOW(),NOW(),0,2,8,'global_plant',0,0),
  (36,NOW(),NOW(),0,2,8,'global_specimen',0,0),
  (37,NOW(),NOW(),0,3,8,'global_notebook',0,0),
  (38,NOW(),NOW(),0,3,8,'global_metadata',0,0),
  (39,NOW(),NOW(),0,3,8,'global_plant',0,0),
  (40,NOW(),NOW(),0,3,8,'global_specimen',0,0),
  (41,NOW(),NOW(),0,4,8,'global_notebook',0,0),
  (42,NOW(),NOW(),0,4,8,'global_metadata',0,0),
  (43,NOW(),NOW(),0,4,8,'global_plant',0,0),
  (44,NOW(),NOW(),0,4,8,'global_specimen',0,0)
;

# a canonical public user
INSERT INTO
  users
  VALUES
 (1,NOW(),NOW(),'canonical_public','reserved_public_user',0,0,0);

INSERT INTO
  user_role_links
  VALUES
  (1,NOW(),NOW(),0,1,4);

*/
