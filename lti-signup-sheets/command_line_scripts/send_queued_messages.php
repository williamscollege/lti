<?php
require_once('cl_head.php');

/*
 * this script sends all queued messages that are ready to go
 */

$ready_qms = QueuedMessage::fetchMessagesReadyForDelivery($DB);

foreach ($ready_qms as $qm) {
    $qm->attemptDelivery();
}
