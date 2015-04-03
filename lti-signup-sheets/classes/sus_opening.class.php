<?php
	require_once(dirname(__FILE__) . '/db_linked.class.php');

	class SUS_Opening extends Db_Linked {
		public static $fields = array('opening_id', 'created_at', 'updated_at', 'flag_delete', 'sheet_id', 'opening_group_id', 'name', 'description', 'max_signups', 'begin_datetime', 'end_datetime', 'location', 'admin_comment');
		public static $primaryKeyField = 'opening_id';
		public static $dbTable = 'sus_openings';
		public static $entity_type_label = 'sus_opening';

		public $signups;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			// $this->flag_workflow_published = false;
			// $this->flag_workflow_validated = false;
			$this->signups = array();
		}

		// static factory function to populate new object with desired base values
		public static function createNewOpening($sheet_id, $dbConnection) {
			return new SUS_Opening([
				'opening_id'       => 'NEW',
				'created_at'       => util_currentDateTimeString_asMySQL(),
				'updated_at'       => util_currentDateTimeString_asMySQL(),
				'flag_delete'      => FALSE,
				'sheet_id'         => $sheet_id,
				'opening_group_id' => 0,
				'name'             => '',
				'description'      => '',
				'max_signups'      => 0,
				'begin_datetime'   => util_currentDateTimeString_asMySQL(),
				'end_datetime'     => util_currentDateTimeString_asMySQL(),
				'location'         => '',
				'admin_comment'    => '',
				'DB'               => $dbConnection
			]);
		}

		public function clearCaches() {
			$this->signups = array();
		}

		/* static functions */

		public static function cmp($a, $b) {
			if ($a->begin_datetime == $b->begin_datetime) {
				if ($a->begin_datetime == $b->begin_datetime) {
					return 0;
				}
				return ($a->begin_datetime > $b->begin_datetime) ? -1 : 1;
			}
			return ($a->begin_datetime > $b->begin_datetime) ? -1 : 1;
		}

		// custom hash comparator (compares hash keys instead of object properties)
		public static function cmp_hash($a, $b) {
			if ($a['begin_datetime'] == $b['begin_datetime']) {
				if ($a['begin_datetime'] == $b['begin_datetime']) {
					return 0;
				}
				return ($a['begin_datetime'] > $b['begin_datetime']) ? -1 : 1;
			}
			return ($a['begin_datetime'] > $b['begin_datetime']) ? -1 : 1;
		}

		/* public functions */

		// cache provides data while eliminating unnecessary DB calls
		public function cacheSignups() {
			if (!$this->signups) {
				$this->loadSignups();
			}
		}

		// load explicitly calls the DB (generally called indirectly from related cache fxn)
		public function loadSignups() {
			$this->signups = [];
			$this->signups = SUS_Signup::getAllFromDb(['opening_id' => $this->opening_id], $this->dbConnection);
			usort($this->signups, 'SUS_Signup::cmp');
		}

		// mark this object as deleted as well as any lower dependent items
		public function cascadeDelete() {
			// mark opening as deleted
			$this->doDelete();

			// for this opening: fetch signups
			$this->cacheSignups();

			// mark signups as deleted
			foreach ($this->signups as $signup) {
				$signup->cascadeDelete();
			}
		}


		private function _renderHtml_BEGIN($flag_is_for_self = FALSE) {
			$rendered = '';
			$rendered .= '<div class="list-opening list-opening-id-' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" ' . $this->fieldsAsDataAttribs() . '>';
			$own_signup_class = '';
			if ($flag_is_for_self) {
				$own_signup_class = ' own-signup';
			}
			$rendered .= '<span class="opening-time-range' . $own_signup_class . '">' . date_format(new DateTime($this->begin_datetime), "h:i A") . ' - ' . date_format(new DateTime($this->end_datetime), "h:i A") . '</span>';
			$this->cacheSignups();

			$customColorClass = "text-danger";
			if (count($this->signups) < $this->max_signups || $this->max_signups == -1) {
				$customColorClass = "text-success";
			}
			$max_signups = $this->max_signups;
			if ($max_signups == -1) {
				$max_signups = "*";
			}
			$rendered .= '<span class="opening-space-usage ' . $customColorClass . '"><strong>' . '(' . count($this->signups) . '/' . htmlentities($max_signups, ENT_QUOTES, 'UTF-8') . ')</strong></span>';

			return $rendered;
		}

		private function _renderHtml_END($usersIds = [], $forceUserListing = FALSE) {
			$rendered = '';

			$this->cacheSignups();

			// display all signup users for this opening
			if ($usersIds) {

				$doUserList = $forceUserListing;

				if (!$doUserList) {
					$displayUserNames = SUS_Sheet::getOneFromDb(['sheet_id' => $this->sheet_id], $this->dbConnection);
					$doUserList       = !$displayUserNames->flag_private_signups;
				}

				if ($doUserList) {
					$signedupUsers = User::getAllFromDb(['user_id' => $usersIds], $this->dbConnection);
					if ($signedupUsers) {
						$rendered .= "<ul class=\"wms-signups\">";
						foreach ($signedupUsers as $u) {
							$rendered .= "<li>" . htmlentities($u->first_name, ENT_QUOTES, 'UTF-8') . " " . htmlentities($u->last_name, ENT_QUOTES, 'UTF-8');
							// display date signup created
							foreach ($this->signups as $signup) {
								if ($signup->signup_user_id == $u->user_id) {
									$rendered .= ' <span class="small">(' . htmlentities($u->username, ENT_QUOTES, 'UTF-8') . ', ' . util_datetimeFormatted($signup->created_at) . ')</span> ';
								}
							}
							$rendered .= "</li>";
						}
						$rendered .= "</ul>";
					}
				}
			}
			$rendered .= '</div>';

			return $rendered;
		}

		// usage: admin or managers of this sheet
		public function renderAsHtmlOpeningWithFullControls($openings_per_group_ary = []) {
			$this->cacheSignups();
			$signedupUserIdsAry = Db_Linked::arrayOfAttrValues($this->signups, 'signup_user_id');

			// show text message if opening belongs to a group (opening_group_id)
			// echo "opening_group_id=". $this->opening_group_id . ",cnt=" . $openings_per_group_ary[$this->opening_group_id] . "<br />";
			$repeating_event = "";
			$repeating_data_attr = 1; // set default value, in event that
			if (isset($openings_per_group_ary[$this->opening_group_id])) {
				$repeating_data_attr = $openings_per_group_ary[$this->opening_group_id];
				if ($openings_per_group_ary[$this->opening_group_id] > 1){
					$repeating_event = '&nbsp;<abbr title="Repeating opening" class="text-muted">repeats</abbr>';
				}
			}

			$rendered = $this->_renderHtml_BEGIN();
			$rendered .= '<a href="#" class="sus-edit-opening" data-opening-id="' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" data-toggle="modal" data-target="#modal-edit-opening" title="Edit opening"><i class="glyphicon glyphicon-wrench"></i></a>';
			$rendered .= '<a href="#" class="sus-delete-opening" data-opening-id="' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" data-count-openings-in-group-id="' . $repeating_data_attr . '"  title="Delete Opening"><i class="glyphicon glyphicon-remove"></i></a>';
			$rendered .= '<a href="#" class="sus-add-someone-to-opening" data-opening-id="' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" data-toggle="modal" data-target="#modal-edit-opening" title="Sign up"><i class="glyphicon glyphicon-plus"></i></a>' . $repeating_event . '<br />';
			$rendered .= $this->_renderHtml_END($signedupUserIdsAry, TRUE);

			return $rendered;
		}

		// usage: ordinary users with permission to signup on this sheet
		public function renderAsHtmlOpeningWithLimitedControls($UserId = 0) {
			$this->cacheSignups();
			$signedupUserIdsAry = Db_Linked::arrayOfAttrValues($this->signups, 'signup_user_id');
			$is_own_signup      = in_array($UserId, $signedupUserIdsAry);

			$rendered = $this->_renderHtml_BEGIN($is_own_signup);

			if (($this->begin_datetime >= util_currentDateTimeString_asMySQL())) {
				// FUTURE OPENINGS

				// show 'cancel signup' btn ("I am signed up for this future opening")
				if ($is_own_signup) {
					$rendered .= '<a href="#" class="sus-delete-me-from-opening" data-opening-id="' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" title="Delete my signup"><i class="glyphicon glyphicon-remove"></i>&nbsp;Cancel signup</a>';
				}
				// show 'signup' btn ("I am not signed up for this future opening")
				elseif (count($this->signups) < $this->max_signups || $this->max_signups == -1) {
					$rendered .= '<a href="#" class="sus-add-me-to-opening" data-opening-id="' . htmlentities($this->opening_id, ENT_QUOTES, 'UTF-8') . '" title="Sign me up"><i class="glyphicon glyphicon-plus"></i>&nbsp;Signup</a><br />';
				}
				// show no controls ("this future opening is already filled to capacity")
				elseif (!(count($this->signups) < $this->max_signups || $this->max_signups == -1)) {
					$rendered .= 'full capacity<br />';
				}
			}
			else {
				// PAST OPENINGS

				// show no controls ("I am signed up for this past opening: show text note")
				if ($is_own_signup) {
					$rendered .= 'I signed up';
				}
				$rendered .= '<br />';
			}

			$rendered .= $this->_renderHtml_END($signedupUserIdsAry);

			return $rendered;
		}

	}
