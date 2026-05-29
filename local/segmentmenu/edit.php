<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_segmentmenu\form\item_form;
use local_segmentmenu\item_repository;

$id = optional_param('id', 0, PARAM_INT);
$context = context_system::instance();

require_login();
require_capability('local/segmentmenu:manage', $context);

$item = $id ? item_repository::get($id) : null;
$url = new moodle_url('/local/segmentmenu/edit.php', $id ? ['id' => $id] : []);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('edititem', 'local_segmentmenu'));
$PAGE->set_heading(get_string('edititem', 'local_segmentmenu'));

$form = new item_form($url, ['item' => $item]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/segmentmenu/index.php'));
}

if ($data = $form->get_data()) {
    item_repository::save($data);
    redirect(new moodle_url('/local/segmentmenu/index.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edititem', 'local_segmentmenu'));
$form->display();
echo $OUTPUT->footer();
