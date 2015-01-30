<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Opening extends Db_Linked {
		public static $fields = array('opening_id', 'created_at', 'updated_at', 'flag_delete', 'sheet_id', 'opening_set_id', 'name', 'description', 'max_signups', 'admin_comment', 'begin_datetime', 'end_datetime', 'location');
		public static $primaryKeyField = 'opening_id';
		public static $dbTable = 'sus_openings';
		public static $entity_type_label = 'sus_opening';

		public $signups;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with this object
			//			$this->flag_workflow_published = false;
			//			$this->flag_workflow_validated = false;
			$this->signups = array();
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

		public function renderAsHtmlShort() {
			$rendered = '';
			$rendered .= '<div id="list-opening-' . $this->opening_id . '" class="`list-opening`" ' . $this->fieldsAsDataAttribs() . '>';
			$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($this->begin_datetime), "h:i A") . ' - ' . date_format(new DateTime($this->end_datetime), "h:i A") . '</span>';
			$this->cacheSignups();
			$rendered .= '<span class="opening-space-usage">' . '(' . count($this->signups) . '/' . $this->max_signups . ')</span>';
			$rendered .= '</div>';

			return $rendered;
		}

		public function renderAsEditLink() {
			$rendered = '';
			// <i class="glyphicon glyphicon-wrench"></i>
			$rendered .= '<a href="#" title="Edit opening" id="edit-opening-' . $this->opening_id . '" data-opening-id="' . $this->opening_id . '" class="edit-opening-link">Edit</a>';

			return $rendered;
		}

		public function renderAsDeleteLink() {
			$rendered = '';
			// <i class="glyphicon glyphicon-remove"></i>
			$rendered .= '<a href="#" title="Delete opening" id="delete-opening-' . $this->opening_id . '" data-opening-id="' . $this->opening_id . '" class="delete-opening-link">Delete</a>';

			return $rendered;
		}

	}
