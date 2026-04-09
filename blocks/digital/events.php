<?php
$observers = array(
    array(
        'eventname' => '\block_digital\event\connect_digital_elibro',
        'callback' => 'block_digital_event_handler',
    ),
);

/**
 * Event handler for consult_cientecnicas.
 *
 * @param \core\event\base $event The event object.
 */
function block_digital_event_handler(\core\event\base $event) {
    // Do something when the event is triggered.
}
