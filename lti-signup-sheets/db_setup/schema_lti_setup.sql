/*
SAVE:		DB Creation and Maintenance Script
PROJECT:	LTI setup
NOTES:		This LTI table can be setup just one time and can accommodate multiple LTI projects

	USE `lti_development`;

	DROP TABLE `lti_consumer`;
	DROP TABLE `lti_context`;
	DROP TABLE `lti_nonce`;
	DROP TABLE `lti_share_key`;
	DROP TABLE `lti_user`;

	DELETE FROM `lti_consumer`;
	DELETE FROM `lti_context`;
	DELETE FROM `lti_nonce`;
	DELETE FROM `lti_share_key`;
	DELETE FROM `lti_user`;

	SELECT * FROM `lti_consumer`;
	SELECT * FROM `lti_context`;
	SELECT * FROM `lti_nonce`;
	SELECT * FROM `lti_share_key`;
	SELECT * FROM `lti_user`;
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
# IMPORTANT: For local workstation testing, create web user and enter [DB_NAME, DB_USER, DB_PASS] credentials into "institution.cfg.php" file.
# ----------------------------
-- CREATE USER 'some_dev_username'@'localhost' IDENTIFIED BY 'some_pass_phrase';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON lti_development.* TO 'some_dev_username'@'localhost';
-- /* CAREFUL!: DROP USER 'some_dev_username'@'localhost'; */

# ----------------------------
# setup database tables
# ----------------------------

CREATE TABLE IF NOT EXISTS `lti_consumer` (
	`consumer_key` varchar(50) NOT NULL,
	`name` varchar(45) NOT NULL,
	`secret` varchar(32) NOT NULL,
	`lti_version` varchar(12) DEFAULT NULL,
	`consumer_name` varchar(255) DEFAULT NULL,
	`consumer_version` varchar(255) DEFAULT NULL,
	`consumer_guid` varchar(255) DEFAULT NULL,
	`css_path` varchar(255) DEFAULT NULL,
	`protected` tinyint(1) NOT NULL,
	`enabled` tinyint(1) NOT NULL,
	`enable_from` datetime DEFAULT NULL,
	`enable_until` datetime DEFAULT NULL,
	`last_access` date DEFAULT NULL,
	`created` datetime NOT NULL,
	`updated` datetime NOT NULL,
	PRIMARY KEY (consumer_key)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='lti_consumer';

CREATE TABLE IF NOT EXISTS `lti_context` (
	`consumer_key` varchar(50) NOT NULL,
	`context_id` varchar(50) NOT NULL,
	`lti_context_id` varchar(50) DEFAULT NULL,
	`lti_resource_id` varchar(50) DEFAULT NULL,
	`title` varchar(255) NOT NULL,
	`settings` text,
	`primary_consumer_key` varchar(50) DEFAULT NULL,
	`primary_context_id` varchar(50) DEFAULT NULL,
	`share_approved` tinyint(1) DEFAULT NULL,
	`created` datetime NOT NULL,
	`updated` datetime NOT NULL,
	PRIMARY KEY (consumer_key, context_id)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='lti_context';

CREATE TABLE IF NOT EXISTS `lti_user` (
	`consumer_key` varchar(50) NOT NULL,
	`context_id` varchar(50) NOT NULL,
	`user_id` varchar(50) NOT NULL,
	`lti_result_sourcedid` varchar(255) NOT NULL,
	`created` datetime NOT NULL,
	`updated` datetime NOT NULL,
	PRIMARY KEY (consumer_key, context_id, user_id)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='lti_user';

CREATE TABLE IF NOT EXISTS `lti_nonce` (
	`consumer_key` varchar(50) NOT NULL,
	`value` varchar(32) NOT NULL,
	`expires` datetime NOT NULL,
	PRIMARY KEY (consumer_key, value)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='lti_nonce';

CREATE TABLE IF NOT EXISTS `lti_share_key` (
	`share_key_id` varchar(32) NOT NULL,
	`primary_consumer_key` varchar(50) NOT NULL,
	`primary_context_id` varchar(50) NOT NULL,
	`auto_approve` tinyint(1) NOT NULL,
	`expires` datetime NOT NULL,
	PRIMARY KEY (share_key_id)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='lti_share_key';

