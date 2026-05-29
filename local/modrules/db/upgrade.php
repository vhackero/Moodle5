<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function xmldb_local_modrules_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026052201) {
        $table = new xmldb_table('local_modrules_gradeexcl');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('gradeitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('gradegradeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('ruleid', XMLDB_INDEX_NOTUNIQUE, ['ruleid']);
        $table->add_index('gradegradeid', XMLDB_INDEX_UNIQUE, ['gradegradeid']);
        $table->add_index('gradeitemid', XMLDB_INDEX_NOTUNIQUE, ['gradeitemid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026052201, 'local', 'modrules');
    }

    if ($oldversion < 2026052202) {
        upgrade_plugin_savepoint(true, 2026052202, 'local', 'modrules');
    }

    return true;
}
