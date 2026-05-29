<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_segmentmenu\item_repository;

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$context = context_system::instance();

require_login();
require_capability('local/segmentmenu:manage', $context);
require_sesskey();

$item = item_repository::get($id);
$returnurl = new moodle_url('/local/segmentmenu/index.php');
$deleteurl = new moodle_url('/local/segmentmenu/delete.php', [
    'id' => $id,
    'confirm' => 1,
    'sesskey' => sesskey(),
]);

if ($confirm) {
    item_repository::delete($id);
    redirect($returnurl);
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/segmentmenu/delete.php', ['id' => $id, 'sesskey' => sesskey()]));
$PAGE->set_title(get_string('delete', 'local_segmentmenu'));
$PAGE->set_heading(get_string('delete', 'local_segmentmenu'));

echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('confirmdelete', 'local_segmentmenu', $item->name), $deleteurl, $returnurl);
echo $OUTPUT->footer();
