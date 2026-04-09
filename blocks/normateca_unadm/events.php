<?php
$observers = array(
    array(
        'eventname' => '\block_normateca_unadm\event\consult_normateca_unadm',
        'callback' => 'block_normateca_unadm_event_handler',
    ),
);

/**
 * Event handler for consult_cientecnicas.
 *
 * @param \core\event\base $event The event object.
 */
function block_normateca_unadm_event_handler(\core\event\base $event) {
    // Do something when the event is triggered.
}
