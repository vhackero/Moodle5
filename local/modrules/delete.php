<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_modrules\rule_repository;

$id = required_param('id', PARAM_INT);

require_login();
require_capability('local/modrules:manage', context_system::instance());

$rule = rule_repository::get($id);
$url = new moodle_url('/local/modrules/delete.php', ['id' => $id, 'sesskey' => sesskey()]);
$returnurl = new moodle_url('/local/modrules/index.php');

if (optional_param('confirm', 0, PARAM_BOOL)) {
    require_sesskey();
    rule_repository::delete($id);
    redirect($returnurl);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(get_string('delete', 'local_modrules'));
$PAGE->set_heading(get_string('delete', 'local_modrules'));

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('confirmdelete', 'local_modrules', format_string($rule->name)),
    new moodle_url($url, ['confirm' => 1]),
    $returnurl
);
echo $OUTPUT->footer();
