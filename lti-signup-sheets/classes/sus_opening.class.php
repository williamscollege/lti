<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class SUS_Opening extends Db_Linked {
		public static $fields = array('opening_id', 'created_at', 'updated_at', 'flag_delete', 'sheet_id', 'opening_group_id', 'name', 'description', 'max_signups', 'admin_comment', 'begin_datetime', 'end_datetime', 'location');
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

		// factory function
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
					'admin_comment'    => '',
					'begin_datetime'   => util_currentDateTimeString_asMySQL(),
					'end_datetime'     => util_currentDateTimeString_asMySQL(),
					'location'         => '',
					'DB'               => $dbConnection]
			);
		}

		public function clearCaches() {
			$this->signups = array();
		}

		/* static functions */

		// TODO - resort cmp to list Date DESC, and Times ASC
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

		public function renderAsHtmlShortWithControls() {
			$rendered = '';
			$rendered .= '<div id="list-opening-id-' . $this->opening_id . '" class="list-opening" ' . $this->fieldsAsDataAttribs() . '>';
			$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($this->begin_datetime), "h:i A") . ' - ' . date_format(new DateTime($this->end_datetime), "h:i A") . '</span>';
			$this->cacheSignups();
			$customColorClass = " text-danger ";
			if (count($this->signups) < $this->max_signups) {
				$customColorClass = " text-success ";
			}
			$rendered .= '<span class="opening-space-usage ' . $customColorClass . '"><strong>' . '(' . count($this->signups) . '/' . $this->max_signups . ')</strong></span>';
			$rendered .= '<a href="#" class="sus-edit-opening" data-toggle="modal" data-target="#modal-edit-opening" title="Edit opening"><i class="glyphicon glyphicon-wrench"></i></a>';
			$rendered .= '<a href="#" class="sus-delete-opening" title="Delete opening"><i class="glyphicon glyphicon-remove"></i></a>';
			$rendered .= '<a href="#" class="sus-add-someone-to-opening" data-toggle="modal" data-target="#modal-edit-opening" title="Add someone to opening"><i class="glyphicon glyphicon-plus"></i></a>';
			$rendered .= '</div>';

			return $rendered;
		}

		public function renderAsHtmlShort() {
			$rendered = '';
			$rendered .= '<div id="list-opening-id-' . $this->opening_id . '" class="list-opening" ' . $this->fieldsAsDataAttribs() . '>';
			$rendered .= '<span class="opening-time-range">' . date_format(new DateTime($this->begin_datetime), "h:i A") . ' - ' . date_format(new DateTime($this->end_datetime), "h:i A") . '</span>';
			$this->cacheSignups();
			$rendered .= '<span class="opening-space-usage">' . '(' . count($this->signups) . '/' . $this->max_signups . ')</span>';
			$rendered .= '</div>';

			return $rendered;
		}


	}
