<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

function local_modrules_before_http_headers(): void {
    \local_modrules\hook_callbacks::before_http_headers();
}

function local_modrules_before_standard_html_head(): string {
    return \local_modrules\hook_callbacks::before_standard_head();
}

function local_modrules_after_require_login($courseorid = null, $autologinguest = true, $cm = null,
        $setwantsurltome = true, $preventredirect = false): void {
    if (empty($cm)) {
        $cm = local_modrules_get_forum_cm_from_request();
    }

    if (empty($cm)) {
        return;
    }

    try {
        $cminfo = \cm_info::create($cm);
    } catch (\Throwable $e) {
        return;
    }

    if ($cminfo->modname === 'forum') {
        $GLOBALS['CFG']->maxeditingtime = \local_modrules\rule_manager::get_forum_maxeditingtime_for_cm($cminfo);
    }

    if (is_siteadmin()) {
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

function local_modrules_get_forum_cm_from_request(): ?\stdClass {
    global $DB, $PAGE;

    if ($PAGE->url->get_path(false) !== '/mod/forum/post.php') {
        return null;
    }

    $forumid = optional_param('forum', 0, PARAM_INT);
    if (!$forumid) {
        $postid = optional_param('reply', 0, PARAM_INT) ?: optional_param('edit', 0, PARAM_INT) ?:
            optional_param('delete', 0, PARAM_INT) ?: optional_param('prune', 0, PARAM_INT);
        if ($postid) {
            $sql = "SELECT d.forum
                      FROM {forum_posts} p
                      JOIN {forum_discussions} d ON d.id = p.discussion
                     WHERE p.id = :postid";
            $forumid = (int)$DB->get_field_sql($sql, ['postid' => $postid]);
        }
    }

    if (!$forumid) {
        return null;
    }

    $forum = $DB->get_record('forum', ['id' => $forumid], 'id, course', IGNORE_MISSING);
    if (!$forum) {
        return null;
    }

    return get_coursemodule_from_instance('forum', $forum->id, $forum->course, false, IGNORE_MISSING) ?: null;
}
