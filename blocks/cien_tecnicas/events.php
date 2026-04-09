<?php
$observers = array(
    array(
        'eventname' => '\block_cien_tecnicas\event\consult_cien_tecnicas',
        'callback' => 'block_cien_tecnicas_event_handler',
    ),
);

/**
 * Event handler for consult_cientecnicas.
 *
 * @param \core\event\base $event The event object.
 */
function block_cien_tecnicas_event_handler(\core\event\base $event) {
    // Do something when the event is triggered.
}
