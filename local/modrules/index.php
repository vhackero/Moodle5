<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_modrules\rule_repository;

require_login();
require_capability('local/modrules:manage', context_system::instance());

$toggle = optional_param('toggle', 0, PARAM_INT);
if ($toggle && confirm_sesskey()) {
    rule_repository::toggle($toggle);
    redirect(new moodle_url('/local/modrules/index.php'));
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/modrules/index.php'));
$PAGE->set_title(get_string('manage', 'local_modrules'));
$PAGE->set_heading(get_string('manage', 'local_modrules'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_modrules'));

echo html_writer::div(
    html_writer::link(new moodle_url('/local/modrules/edit.php'), get_string('addrule', 'local_modrules'), ['class' => 'btn btn-primary']),
    'mb-3'
);

$rules = rule_repository::get_all();
if (!$rules) {
    echo $OUTPUT->notification(get_string('norules', 'local_modrules'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$types = rule_repository::get_rule_types();
$roles = role_fix_names(get_all_roles(), null, ROLENAME_ALIAS, true);
$table = new html_table();
$table->head = [
    get_string('name', 'local_modrules'),
    get_string('ruletype', 'local_modrules'),
    get_string('modname', 'local_modrules'),
    get_string('namematch', 'local_modrules'),
    get_string('roleids', 'local_modrules'),
    get_string('courseids', 'local_modrules'),
    get_string('status', 'local_modrules'),
    get_string('actions'),
];

foreach ($rules as $rule) {
    $rolelabels = [];
    foreach (rule_repository::ids_from_string($rule->roleids) as $roleid) {
        $rolelabels[] = $roles[$roleid] ?? $roleid;
    }

    $courselabel = get_string('allcourses', 'local_modrules');
    $courseids = rule_repository::ids_from_string($rule->courseids);
    if ($courseids) {
        $courselabel = count($courseids) . ' ' . get_string('courses');
    }

    $editurl = new moodle_url('/local/modrules/edit.php', ['id' => $rule->id]);
    $deleteurl = new moodle_url('/local/modrules/delete.php', ['id' => $rule->id]);
    $toggleurl = new moodle_url('/local/modrules/index.php', ['toggle' => $rule->id, 'sesskey' => sesskey()]);
    $actions = [
        html_writer::link($editurl, get_string('edit')),
        html_writer::link($toggleurl, $rule->enabled ? get_string('disable') : get_string('enable')),
        html_writer::link($deleteurl, get_string('delete', 'local_modrules')),
    ];

    $table->data[] = [
        s($rule->name),
        $types[$rule->ruletype] ?? s($rule->ruletype),
        s($rule->modname),
        s($rule->namematch),
        s(implode(', ', $rolelabels)),
        s($courselabel),
        $rule->enabled ? get_string('yes') : get_string('no'),
        implode(' | ', $actions),
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
