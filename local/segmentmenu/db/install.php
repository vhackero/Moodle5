<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function xmldb_local_segmentmenu_install() {
    set_config('segmentfield', 'segmento', 'local_segmentmenu');
    set_config('menuposition', 'right', 'local_segmentmenu');
    return true;
}
