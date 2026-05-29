<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/modrules:manage', context_system::instance())) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_modrules_manage',
        get_string('manage', 'local_modrules'),
        new moodle_url('/local/modrules/index.php'),
        'local/modrules:manage'
    ));
}
