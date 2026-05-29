<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');

use local_segmentmenu\item_repository;

require_login();
require_capability('local/segmentmenu:manage', context_system::instance());

$toggle = optional_param('toggle', 0, PARAM_INT);
if ($toggle && confirm_sesskey()) {
    item_repository::toggle($toggle);
    redirect(new moodle_url('/local/segmentmenu/index.php'));
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/segmentmenu/index.php'));
$PAGE->set_title(get_string('manage', 'local_segmentmenu'));
$PAGE->set_heading(get_string('manage', 'local_segmentmenu'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_segmentmenu'));

echo html_writer::div(
    html_writer::link(new moodle_url('/local/segmentmenu/edit.php'), get_string('additem', 'local_segmentmenu'), [
        'class' => 'btn btn-primary',
    ]),
    'mb-3'
);

$segmentfield = trim((string)get_config('local_segmentmenu', 'segmentfield'));
echo $OUTPUT->notification(get_string('segmentfield', 'local_segmentmenu') . ': ' . s($segmentfield ?: '-'), 'info');

$items = item_repository::get_all();
if (!$items) {
    echo $OUTPUT->notification(get_string('noitems', 'local_segmentmenu'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->head = [
    get_string('itemname', 'local_segmentmenu'),
    get_string('url', 'local_segmentmenu'),
    get_string('segment', 'local_segmentmenu'),
    get_string('sortorder', 'local_segmentmenu'),
    get_string('status', 'local_segmentmenu'),
    get_string('actions'),
];

foreach ($items as $item) {
    $editurl = new moodle_url('/local/segmentmenu/edit.php', ['id' => $item->id]);
    $deleteurl = new moodle_url('/local/segmentmenu/delete.php', ['id' => $item->id, 'sesskey' => sesskey()]);
    $toggleurl = new moodle_url('/local/segmentmenu/index.php', ['toggle' => $item->id, 'sesskey' => sesskey()]);
    $actions = [
        html_writer::link($editurl, get_string('edit')),
        html_writer::link($toggleurl, $item->enabled ? get_string('disable') : get_string('enable')),
        html_writer::link($deleteurl, get_string('delete', 'local_segmentmenu')),
    ];

    $table->data[] = [
        s($item->name),
        html_writer::link(new moodle_url($item->url), s($item->url)),
        $item->segment === '' ? get_string('all') : s($item->segment),
        (int)$item->sortorder,
        $item->enabled ? get_string('yes') : get_string('no'),
        implode(' | ', $actions),
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
