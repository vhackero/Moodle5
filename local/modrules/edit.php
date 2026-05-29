<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_modrules\form\rule_form;
use local_modrules\rule_repository;

$id = optional_param('id', 0, PARAM_INT);

require_login();
require_capability('local/modrules:manage', context_system::instance());

$url = new moodle_url('/local/modrules/edit.php', $id ? ['id' => $id] : []);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(get_string('editrule', 'local_modrules'));
$PAGE->set_heading(get_string('editrule', 'local_modrules'));

$rule = $id ? rule_repository::get($id) : null;
$form = new rule_form($url, ['rule' => $rule]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/modrules/index.php'));
}

if ($data = $form->get_data()) {
    rule_repository::save($data);
    redirect(new moodle_url('/local/modrules/index.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editrule', 'local_modrules'));
$form->display();
echo $OUTPUT->footer();
