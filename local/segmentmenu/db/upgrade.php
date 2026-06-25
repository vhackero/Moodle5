<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function xmldb_local_segmentmenu_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_segmentmenu_items');

    if (!$dbman->table_exists($table)) {
        $dbman->install_from_xmldb_file($CFG->dirroot . '/local/segmentmenu/db/install.xml');
    }

    if ($oldversion < 2026052900) {
        $field = new xmldb_field('linktarget', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, 'same', 'segment');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        if (get_config('local_segmentmenu', 'menuposition') === false) {
            set_config('menuposition', 'right', 'local_segmentmenu');
        }

        upgrade_plugin_savepoint(true, 2026052900, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060400) {
        $field = new xmldb_field('courseroles', XMLDB_TYPE_TEXT, null, null, null, null, null, 'segment');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field(
            'restrictionmode',
            XMLDB_TYPE_CHAR,
            '16',
            null,
            XMLDB_NOTNULL,
            null,
            'segment',
            'courseroles'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $index = new xmldb_index('restrictionenabled', XMLDB_INDEX_NOTUNIQUE, ['restrictionmode', 'enabled']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026060400, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060500) {
        $field = new xmldb_field('courseids', XMLDB_TYPE_TEXT, null, null, null, null, null, 'url');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026060500, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060800) {
        upgrade_plugin_savepoint(true, 2026060800, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060801) {
        upgrade_plugin_savepoint(true, 2026060801, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060802) {
        upgrade_plugin_savepoint(true, 2026060802, 'local', 'segmentmenu');
    }

    if ($oldversion < 2026060803) {
        upgrade_plugin_savepoint(true, 2026060803, 'local', 'segmentmenu');
    }

    return true;
}
