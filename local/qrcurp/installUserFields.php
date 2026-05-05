<?php

require_once(__DIR__.'/../../config.php');

use local_qrcurp\local\profile_fields_manager;

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/local/qrcurp/installUserFields.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_qrcurp'));

$configuredfields = profile_fields_manager::get_configured_shortnames();
$createdfields = profile_fields_manager::ensure_fields($configuredfields);
$missingfields = profile_fields_manager::get_missing_fields($configuredfields);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('profilefieldsstatus', 'local_qrcurp'));

if (!empty($createdfields)) {
    echo $OUTPUT->notification(get_string('profilefieldscreated', 'local_qrcurp', implode(', ', $createdfields)), \core\output\notification::NOTIFY_SUCCESS);
}

if (!empty($missingfields)) {
    echo $OUTPUT->notification(get_string('profilefieldsmissing', 'local_qrcurp', implode(', ', $missingfields)), \core\output\notification::NOTIFY_WARNING);
} else {
    echo $OUTPUT->notification(get_string('profilefieldsok', 'local_qrcurp'), \core\output\notification::NOTIFY_SUCCESS);
}

$backurl = new moodle_url('/admin/settings.php', ['section' => 'local_qrcurp']);
echo html_writer::link($backurl, get_string('back'));
echo $OUTPUT->footer();


