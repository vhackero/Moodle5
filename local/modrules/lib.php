<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function local_modrules_after_require_login($courseorid = null, $autologinguest = true, $cm = null,
        $setwantsurltome = true, $preventredirect = false): void {
    if (empty($cm) || is_siteadmin()) {
        return;
    }

    try {
        $cminfo = \cm_info::create($cm);
    } catch (\Throwable $e) {
        return;
    }

    if (\local_modrules\rule_manager::is_cm_allowed_for_user($cminfo)) {
        return;
    }

    if ($preventredirect) {
        throw new \moodle_exception('restrictedactivity', 'local_modrules');
    }

    redirect(
        new \moodle_url('/course/view.php', ['id' => $cminfo->course]),
        get_string('restrictedactivity', 'local_modrules'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}
