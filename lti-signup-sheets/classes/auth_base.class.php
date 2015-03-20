<?php

	class Auth_Base {

		# define attributes of object
		# authenticate
		public $username;
		public $email;
		public $fname;
		public $lname;
		public $sortname;

		public $msg;
		public $debug;

		public static $TEST_USERNAME = TESTINGUSER;
		public static $TEST_EMAIL = 'jbond@institution.edu';
		public static $TEST_FNAME = 'James';
		public static $TEST_LNAME = 'Bond';
		public static $TEST_SORTNAME = 'Bond, James';


		// TAKES: this function takes two parameters, a username and a password, both strings
		// DOES: checks the username and password against an authentication source (details implemented by subclass); if the authentication checks out, the various attributes of this object are populated with the appropriate user data
		// RETURNS: true if the user was authenticated, false otherwise
		public function authenticate($username, $pass) {
			$this->username = $username;
			$this->email    = '';
			$this->fname    = '';
			$this->lname    = '';

			$this->msg   = '';
			$this->debug = '';

			//			echo "<pre>";
			//			echo "authenticating...\n";
			//			echo 'user='.$username."\n";
			//			echo 'TESTINGUSER='.TESTINGUSER."\n";
			//			echo 'pass='.$pass."\n";
			//			echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";
			//			echo "</pre>";

			if (($username == TESTINGUSER) && ($pass == TESTINGPASSWORD)) {
				$this->email    = self::$TEST_EMAIL;
				$this->fname    = self::$TEST_FNAME;
				$this->lname    = self::$TEST_LNAME;
				$this->sortname = self::$TEST_SORTNAME;
				return TRUE;
			}
		}

		// TAKES: this function takes one parameters, a username as a string
		// RETURNS: a array of strings, each string the name of an institutional group
		public function getInstGroupsFromAuthSource($username) {
			echo "TODO: implement group names for testing user<br/>\n";
		}

		// TAKES: a string that is someone's username
		// RETURNS: a data structure containing info about that user, fetched from the auth source
		//        'username'
		//        'email'
		//        'fname'
		//        'lname'
		//        'sortname'
		//        'inst_group_data'
		//        'auth_identifier'
		public function findOneUserByUsername($username) {
			echo "You must override findOneUserByUsername in your auth class<br/>\n";
		}

		// TAKES: a string that is a search term - either with out spaces, or with a single space
		// RETURNS: an array of data structures containing info about the users that have data that matches the search term, fetched from the auth source
		//        'username'
		//        'email'
		//        'fname'
		//        'lname'
		//        'sortname'
		//        'inst_group_data'
		//        'auth_identifier'
		public function findAllUsersBySearchTerm($searchTerm) {
			echo "You must override findAllUsersBySearchTerm in your auth class<br/>\n";
		}

		// TAKES: an auth source data set / entry for a single user
		// RETURNS: the common structure for user data, populated with the relevant info from the auth entry
		public function convertAuthInfoToUserDataStructure($authEntry) {
			echo "You must override convertAuthInfoToUserDataStructure in your auth class<br/>\n";
			$res = [
				'username'        => '',
				'email'           => '',
				'fname'           => '',
				'lname'           => '',
				'sortname'        => '',
				'inst_group_data' => '',
				'auth_identifier' => ''
			];
			return $res;
		}
	}
