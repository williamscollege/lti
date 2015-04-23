/* 
PROJECT:	Signup Sheets (lti-signup-sheets)
SQL DESCR:	This creates a table that is used by the testing suites to verify database connection functionality and to test the DB linking class
NOTES:		This is for development only! You do not need this on the production server (though it shouldn't hurt anything to have it there)

FOR TESTING ONLY:
	DROP TABLE `dblinktest`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# You must first use your schema_signup_sheets.sql to create test `signup_sheets_test_suite` database
# Then also add the table `dblinktest` below.
# ----------------------------

USE `signup_sheets_test_suite`;

# ----------------------------
# IMPORTANT: For local workstation testing, create web user and enter [DB_NAME, DB_USER, DB_PASS] credentials into "institution.cfg.php" file.
# ----------------------------
-- CREATE USER 'some_test_username'@'localhost' IDENTIFIED BY 'some_pass_phrase';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON signup_sheets_test_suite.* TO 'some_test_username'@'localhost';
-- /* CAREFUL!: DROP USER 'some_test_username'@'localhost'; */

# ----------------------------
# setup database tables
# ----------------------------

CREATE TABLE IF NOT EXISTS `dblinktest` (
    `dblinktest_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `charfield` VARCHAR(255) NULL,
    `intfield` INT NOT NULL,
    `flagfield` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Used for testing based DB-link class';

