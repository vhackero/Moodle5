<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function local_segmentmenu_before_standard_top_of_body_html(): string {
    return \local_segmentmenu\hook_callbacks::before_standard_top_of_body();
}
