<?php
	require_once(dirname(__FILE__) . '/auth_base.class.php');

	class Auth_LTI extends Auth_Base {

		public $lti_link;
		public $user_info_attrs = [AUTH_LTI_USERNAME, AUTH_LTI_NONCE, AUTH_LTI_ROLES];

		public function authenticate($user, $nonce) {
			# check authentication of test user (default condition for testing)
			// TODO - remove this test?
			if (parent::authenticate($user, $nonce)) {
				return TRUE;
			}

			# check authentication against LTI database
			if ($this->checkLTI($user, $nonce)) {
				# passes authentication
				return TRUE;
			}
			else {
				# fails authentication
				//                echo $this->msg;
				//                exit;
				return FALSE;
			}
		}

		public function connectToLTI() {
			$this->lti_link = new PDO(LTI_DB_NAME, LTI_DB_USERNAME, LTI_DB_PASSWORD);

			if (!$this->lti_link) {
				$this->msg = "Could not connect to the LTI database.";
				return FALSE;
			}
			return TRUE;
		}


		public function convertAuthInfoToUserDataStructure($authEntry) {
			$mi  = (array_key_exists(AUTH_LTI_MIDDLEINITIALS_ATTR_LABEL, $authEntry)) ? (' ' . $authEntry[AUTH_LTI_MIDDLEINITIALS_ATTR_LABEL][0]) : '';
			$res = [
				'username'        => array_key_exists(AUTH_LTI_USERNAME, $authEntry) ? $authEntry[AUTH_LTI_USERNAME][0] : 'no username from auth system search',
				'nonce'           => array_key_exists(AUTH_LTI_NONCE, $authEntry) ? $authEntry[AUTH_LTI_NONCE][0] : 'no nonce from auth system search',
				'roles'           => array_key_exists(AUTH_LTI_ROLES, $authEntry) ? $authEntry[AUTH_LTI_ROLES][0] : 'no roles from auth system search'
			];

			return $res;
		}

		public function findOneUserByUsername($username) {
			$discard_chars   = array(",", ".", "-", "*");
			$cleanedUsername = str_replace($discard_chars, '', $username);
			if (!$cleanedUsername) {
				$this->msg = "Username empty after discarding invalid characters";
				return FALSE;
			}

			$filter = AUTH_LTI_USERNAME_ATTR_LABEL . '=' . $cleanedUsername;

//			$search_results = $this->doLTISearch($filter, $this->user_info_attrs);

			if (!$search_results) {
				return FALSE;
			}
			if ($search_results['count'] == 0) {
				$this->msg = "User record could not be fetched: no (count == 0) data in search results";
				return FALSE;
			}
			elseif ($search_results['count'] > 1) {
				$this->msg = "User record appears more than once - invalid";
				return FALSE;
			}
			return $this->convertAuthInfoToUserDataStructure($search_results[0]);
		}

		// TAKES: a username, a nonce
		// RETURNS: true if the username and nonce matches an LTI entry (i.e. has relevant data and can bind), false otherwise
		public function checkLTI($user = "", $nonce = "") {

			if (!$user) {
				$this->msg = "No username specified.";
				return FALSE;
			}

			if (!$nonce) {
				$this->msg = "No password specified.";
				return FALSE;
			}

			$found_user = $this->findOneUserByUsername($user);

			if (!$found_user) {
				return FALSE;
			}

			// try to Sign in (well, match credentials): this is the actual auth check!
			$this->connectToLTI();
			//            echo $found_user[AUTH_LTI_USER_DN_ATTR_LABEL];
			$authed_lti_link = lti_bind($this->lti_link, $found_user['auth_identifier'], $nonce);
			lti_close($this->lti_link);
			if ($authed_lti_link == FALSE) {
				$this->msg = "The username and password don't match."; //: $user_dn";
				return FALSE;
			}
			lti_close($authed_lti_link);


			//            echo '<pre>';
			//            print_r($found_user);
			//            echo '</pre>';
			//            exit;

			// auth check passed, so populate the user data
			$this->username = $found_user['username'];
			$this->fname    = $found_user['fname'];
			$this->lname    = $found_user['lname'];
			$this->sortname = $found_user['sortname'];
			$this->email    = $found_user['email'];

			return TRUE;
		}

//		public function findAllUsersBySearchTerm($searchTerm) {
//			$discard_chars           = array(",", ".", "-", "*");
//			$cleanedSearchTerm       = str_replace($discard_chars, '', $searchTerm);
//			$is_two_part_search_term = FALSE;
//			$term_parts              = [];
//			if (strpos($cleanedSearchTerm, ' ') > 0) {
//				$term_parts              = explode(' ', $cleanedSearchTerm);
//				$is_two_part_search_term = TRUE;
//			}
//
//			$filter = "(|(" . AUTH_LTI_USERNAME_ATTR_LABEL . "=*" . $cleanedSearchTerm . "*)(" . AUTH_LTI_FIRSTNAME_ATTR_LABEL . "=*" . $cleanedSearchTerm . "*)(" . AUTH_LTI_LASTNAME_ATTR_LABEL . "=*" . $cleanedSearchTerm . "*))";
//			if ($is_two_part_search_term) {
//				$filter = "(&(" . AUTH_LTI_FIRSTNAME_ATTR_LABEL . "=*" . $term_parts[0] . "*)(" . AUTH_LTI_LASTNAME_ATTR_LABEL . "=*" . $term_parts[1] . "*))";
//			}
//
//			//$search_results = $this->doLTISearch($filter, $this->user_info_attrs);
//
//			if (!$search_results) {
//				return FALSE;
//			}
//
//			unset($search_results['count']);
//
//			// this statement makes sure that invalid entries are excluded
//			$search_results = array_filter($search_results, function ($e) {
//				return
//					array_key_exists(AUTH_LTI_USERNAME_ATTR_LABEL, $e)
//					&& array_key_exists(AUTH_LTI_USER_DN_ATTR_LABEL, $e)
//					&& array_key_exists(AUTH_LTI_FIRSTNAME_ATTR_LABEL, $e)
//					//                    && array_key_exists(AUTH_LTI_MIDDLEINITIALS_ATTR_LABEL,$e)
//					&& array_key_exists(AUTH_LTI_LASTNAME_ATTR_LABEL, $e)
//					&& array_key_exists(AUTH_LTI_FULLNAME_ATTR_LABEL, $e)
//					&& array_key_exists(AUTH_LTI_EMAIL_ATTR_LABEL, $e)
//					&& array_key_exists(AUTH_LTI_GROUPMEMBERSHIP_ATTR_LABEL, $e);
//			});
//
//			return array_map(function ($e) {
//				return $this->convertAuthInfoToUserDataStructure($e);
//			}, $search_results);
//		}

	}
