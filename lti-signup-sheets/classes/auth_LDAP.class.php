<?php
	require_once dirname(__FILE__) . '/auth_base.class.php';

	class Auth_LDAP extends Auth_Base {

        public $ldap_link;
        public $user_info_attrs = [AUTH_LDAP_USERNAME_ATTR_LABEL, AUTH_LDAP_USER_DN_ATTR_LABEL,AUTH_LDAP_FIRSTNAME_ATTR_LABEL, AUTH_LDAP_MIDDLEINITIALS_ATTR_LABEL,
            AUTH_LDAP_LASTNAME_ATTR_LABEL, AUTH_LDAP_FULLNAME_ATTR_LABEL, AUTH_LDAP_EMAIL_ATTR_LABEL, AUTH_LDAP_GROUPMEMBERSHIP_ATTR_LABEL];

		public function authenticate($user, $pass) {
			# check authentication of test user (default condition for testing)
			if (parent::authenticate($user, $pass)) {
				return TRUE;
			}

			# check authentication against LDAP server
			# [run this fxn checkLDAP which utilizes the $AUTH object]
			if ($this->checkLDAP($user, $pass, AUTH_SERVER)) {
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

		public function getInstGroupsFromAuthSource($username) {
			$group_names = parent::getInstGroupsFromAuthSource($username);
			if (count($group_names) > 0) {
				return $group_names;
			}

			//        echo "TODO: implement fetching of group names<br/>\n";
		}

        public function connectToLDAP() {
            $this->ldap_link = ldap_connect(AUTH_SERVER,AUTH_PORT);
            if (! $this->ldap_link) {
                $this->msg = "Could not connect to the LDAP server (AUTH_SERVER)." . ldap_error($this->ldap_link);
                ldap_close($this->ldap_link);
                return false;
            }
            ldap_set_option($this->ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);
            return true;
        }


        public function convertAuthInfoToUserDataStructure($authEntry) {
            $mi = (array_key_exists(AUTH_LDAP_MIDDLEINITIALS_ATTR_LABEL,$authEntry)) ? (' '.$authEntry[AUTH_LDAP_MIDDLEINITIALS_ATTR_LABEL][0]) : '';
            $res = [
                'username'=> array_key_exists(AUTH_LDAP_USERNAME_ATTR_LABEL,$authEntry) ? $authEntry[AUTH_LDAP_USERNAME_ATTR_LABEL][0] : 'no username from auth system search',
                'fname'=> array_key_exists(AUTH_LDAP_FIRSTNAME_ATTR_LABEL,$authEntry) ? $authEntry[AUTH_LDAP_FIRSTNAME_ATTR_LABEL][0] : 'no first name from auth system search',
                'lname'=> array_key_exists(AUTH_LDAP_LASTNAME_ATTR_LABEL,$authEntry) ? $authEntry[AUTH_LDAP_LASTNAME_ATTR_LABEL][0] : 'no last name from auth system search',
                'sortname'=> (array_key_exists(AUTH_LDAP_FIRSTNAME_ATTR_LABEL,$authEntry) && array_key_exists(AUTH_LDAP_LASTNAME_ATTR_LABEL,$authEntry)) ? ($authEntry[AUTH_LDAP_LASTNAME_ATTR_LABEL][0].', '.$authEntry[AUTH_LDAP_FIRSTNAME_ATTR_LABEL][0].$mi) : 'no sortname created from auth system search',
                'email'=> array_key_exists(AUTH_LDAP_EMAIL_ATTR_LABEL,$authEntry) ? $authEntry[AUTH_LDAP_EMAIL_ATTR_LABEL][0] : ($authEntry[AUTH_LDAP_USERNAME_ATTR_LABEL][0].'@'.INSTITUTION_DOMAIN),
                'inst_group_data' => $authEntry[AUTH_LDAP_GROUPMEMBERSHIP_ATTR_LABEL],
                'auth_identifier' => $authEntry[AUTH_LDAP_USER_DN_ATTR_LABEL]
            ];

            return $res;
        }

        public function findOneUserByUsername($username) {
            $discard_chars = array(",", ".", "-", "*");
            $cleanedUsername = str_replace($discard_chars, '', $username);
            if (!$cleanedUsername) {
                $this->msg = "Username empty after discarding invalid characters";
                return FALSE;
            }

            $filter = AUTH_LDAP_USERNAME_ATTR_LABEL.'='.$cleanedUsername;

            $search_results = $this->doLDAPSearch($filter,$this->user_info_attrs);

            if (! $search_results) {
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

        public function findAllUsersBySearchTerm($searchTerm) {
            $discard_chars = array(",", ".", "-", "*");
            $cleanedSearchTerm = str_replace($discard_chars, '', $searchTerm);
            $is_two_part_search_term = false;
            $term_parts = [];
            if (strpos($cleanedSearchTerm,' ') > 0) {
                $term_parts = explode(' ',$cleanedSearchTerm);
                $is_two_part_search_term = true;
            }

            $filter = "(|(".AUTH_LDAP_USERNAME_ATTR_LABEL."=*" . $cleanedSearchTerm . "*)(".AUTH_LDAP_FIRSTNAME_ATTR_LABEL."=*" . $cleanedSearchTerm . "*)(".AUTH_LDAP_LASTNAME_ATTR_LABEL."=*" . $cleanedSearchTerm . "*))";
            if ($is_two_part_search_term) {
                $filter = "(&(".AUTH_LDAP_FIRSTNAME_ATTR_LABEL."=*" . $term_parts[0] . "*)(".AUTH_LDAP_LASTNAME_ATTR_LABEL."=*" . $term_parts[1] . "*))";
            }

            $search_results = $this->doLDAPSearch($filter,$this->user_info_attrs);

            if (! $search_results) {
                return FALSE;
            }

            unset($search_results['count']);

            // this statement makes sure that invalid entries are excluded
            $search_results = array_filter($search_results,function($e){
                return
                    array_key_exists(AUTH_LDAP_USERNAME_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_USER_DN_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_FIRSTNAME_ATTR_LABEL,$e)
//                    && array_key_exists(AUTH_LDAP_MIDDLEINITIALS_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_LASTNAME_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_FULLNAME_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_EMAIL_ATTR_LABEL,$e)
                    && array_key_exists(AUTH_LDAP_GROUPMEMBERSHIP_ATTR_LABEL,$e)
                    ;
            });

            return array_map(function($e) {
                        return $this->convertAuthInfoToUserDataStructure($e);
                    }, $search_results);
        }

        public function doLDAPSearch($filterString,$attrList=[]) {
            if (! $this->connectToLDAP()) {
                return FALSE;
            }

            $res_id = 0;
            if ($attrList) {
                $res_id = @ldap_search($this->ldap_link,AUTH_LDAP_SEARCH_DN,$filterString,$attrList);
            }
            else {
                $res_id = @ldap_search($this->ldap_link,AUTH_LDAP_SEARCH_DN,$filterString);
            }
            if (! $res_id) {
                $this->msg = "No records found for $filterString";
                ldap_close($this->ldap_link);
                return false;
            }

            $res = ldap_get_entries($this->ldap_link, $res_id);

            ldap_close($this->ldap_link);

            return $res;
        }

        // TAKES: a username, a password
        // RETURNS: true if the username and password matches an LDAP entry (i.e. has relevatn data and can bind), false otherwise
		public function checkLDAP($user = "", $pass = "", $ldap_server = AUTH_SERVER) {

			if (!$user) {
				$this->msg = "No username specified.";
				return FALSE;
			}

			if (!$pass) {
				$this->msg = "No password specified.";
				return FALSE;
			}

            $found_user = $this->findOneUserByUsername($user);

            if (! $found_user) {
                return FALSE;
            }

            // try to Sign in NOTE: this is the actual auth check!
            $this->connectToLDAP();
//            echo $found_user[AUTH_LDAP_USER_DN_ATTR_LABEL];
            $authed_ldap_link = ldap_bind($this->ldap_link, $found_user['auth_identifier'], $pass);
            ldap_close($this->ldap_link);
            if ($authed_ldap_link == FALSE) {
                $this->msg = "The username and password don't match."; //: $user_dn";
                return FALSE;
            }
            ldap_close($authed_ldap_link);


//            echo '<pre>';
//            print_r($found_user);
//            echo '</pre>';
//            exit;

            // auth check passed, so populate the user data
            $this->username = $found_user['username'];
            $this->fname = $found_user['fname'];
            $this->lname = $found_user['lname'];
            $this->sortname = $found_user['sortname'];
            $this->email = $found_user['email'];

            $this->inst_groups = [];
//            echo '<pre>';
//            print_r($found_user[AUTH_LDAP_GROUPMEMBERSHIP_ATTR_LABEL]);
//            echo '</pre>';
//            exit;
			$group_finder_pattern = '/cn=((Everyone|Jesup|[A-Z]{4}-[0-9]{3}|\\d\\dstudents)[^\\,]*)/'; // match only desired groups, exclude all others
            foreach ($found_user['inst_group_data'] as $g) {
				if (preg_match($group_finder_pattern, $g, $matches)) { // ensure no empty items
					array_push($this->inst_groups, $matches[1]);
				}
			}

			// append the position (STUDENT, FACULTY, STAFF, OTHER), as this is another kind of institutional group we want to know about
			if (preg_match("/ou=(\\w+),/", $found_user['auth_identifier'], $matches)) {
                array_push($this->inst_groups, $matches[1]);
			}
            else {
    			array_push($this->inst_groups, "OTHER");
            }

			return TRUE;
		}
	}
