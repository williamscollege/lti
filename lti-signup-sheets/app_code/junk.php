<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('my_sheets'));
	require_once('../app_head.php');
?>

<a href="#" class="addOpeningLink" data-toggle="modal" data-target="#modal-edit-opening" title="Create openings"><i class="glyphicon glyphicon-plus"></i></a>
<div data-location="" data-end_datetime="2015-03-12 11:15:06" data-begin_datetime="2015-02-12 11:15:06" data-admin_comment="" data-max_signups="1" data-description="Opening 710, Sheet 610, Sheetgroup 510" data-name="Opening 710" data-opening_group_id="0" data-sheet_id="610" data-flag_delete="0" data-updated_at="2015-02-12 11:15:06" data-created_at="2015-02-12 11:15:06" data-opening_id="710" class="list-opening" id="list-opening-id-710">
	<span class="opening-time-range">11:15 AM - 11:15 AM</span><span class="opening-space-usage  text-danger "><strong>(2/1)</strong></span>
	<a title="Edit opening" data-target="#modal-edit-opening" data-toggle="modal" class="sus-edit-opening" href="#"><i class="glyphicon glyphicon-wrench"></i></a>
	<a title="Delete opening" class="sus-delete-opening" href="#"><i class="glyphicon glyphicon-remove"></i></a>
	<a title="Add someone to opening" data-target="#modal-edit-opening" data-toggle="modal" class="sus-add-someone-to-opening" href="#"><i class="glyphicon glyphicon-plus"></i></a>
</div>




<script type="text/javascript" src="../js/calendar.js"></script>