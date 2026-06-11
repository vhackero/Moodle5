<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules;

defined('MOODLE_INTERNAL') || die();

class hook_callbacks {
    private static $headersprocessed = false;
    private static $headprocessed = false;

    public static function before_http_headers($hook = null): void {
        global $PAGE;

        if (self::$headersprocessed) {
            return;
        }
        self::$headersprocessed = true;

        self::redirect_hidden_course_settings();

        $cm = self::get_page_cm();
        if (!$cm) {
            return;
        }

        if ($cm->modname === 'forum') {
            $GLOBALS['CFG']->maxeditingtime = rule_manager::get_forum_maxeditingtime_for_cm($cm);
        }

        if (rule_manager::is_cm_allowed_for_user($cm)) {
            return;
        }

        $courseurl = new \moodle_url('/course/view.php', ['id' => $cm->course]);
        redirect($courseurl, get_string('restrictedactivity', 'local_modrules'), null, \core\output\notification::NOTIFY_WARNING);
    }

    public static function before_standard_head($hook = null): string {
        global $PAGE;

        if (self::$headprocessed) {
            return '';
        }
        self::$headprocessed = true;

        if (empty($PAGE->course->id) || $PAGE->course->id == SITEID) {
            return '';
        }

        $payload = rule_manager::get_hidden_cm_payload((int)$PAGE->course->id);
        $path = $PAGE->url->get_path(false);
        if (!in_array($path, ['/report/log/index.php', '/report/log/user.php', '/report/loglive/index.php', '/report/loglive/loglive_ajax.php'], true)) {
            $payload['logs'] = [];
        }
        $payload['excluded'] = [];
        if (!is_siteadmin() && (strpos($path, '/grade/') === 0 || strpos($path, '/report/stats/') === 0)) {
            $payload['excluded'] = grade_exclusion_manager::get_excluded_cm_payload((int)$PAGE->course->id);
        }
        if (empty($payload['activities']) && empty($payload['logs']) && empty($payload['excluded']) && empty($payload['courseSettings'])) {
            return '';
        }

        $json = json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $script = <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    var payload = {$json};
    function hideNode(node) {
        if (node) {
            node.hidden = true;
            node.setAttribute('aria-hidden', 'true');
        }
    }
    function hideRestrictedActivities() {
        (payload.activities || []).forEach(function(cm) {
            hideNode(document.getElementById('module-' + cm.id));
            hideNode(document.getElementById('course-index-cm-' + cm.id));
            document.querySelectorAll('[data-for="cm"][data-id="' + cm.id + '"], [data-for="cmitem"][data-id="' + cm.id + '"]').forEach(hideNode);
            document.querySelectorAll('a[href*="/mod/' + cm.modname + '/view.php?id=' + cm.id + '"]').forEach(function(link) {
                hideNode(link.closest('#course-index-cm-' + cm.id) || link.closest('[data-for="cm"]') ||
                    link.closest('[data-for="cmitem"]') || link.closest('li.activity') || link.closest('.activity') ||
                    link.closest('[role="treeitem"]') || link.closest('.list-group-item') || link.closest('.nav-item') || link);
            });
        });
    }
    hideRestrictedActivities();
    if (payload.activities && payload.activities.length && window.MutationObserver) {
        var observer = new MutationObserver(hideRestrictedActivities);
        observer.observe(document.body, {childList: true, subtree: true});
    }
    (payload.logs || []).forEach(function(cm) {
        var names = [cm.name, cm.component, cm.url, '/mod/' + cm.modname + '/view.php?id=' + cm.id];
        document.querySelectorAll('table tbody tr').forEach(function(row) {
            var text = (row.textContent || '').toLowerCase();
            var html = (row.innerHTML || '').toLowerCase();
            var hasLink = !!row.querySelector('a[href*="/mod/' + cm.modname + '/view.php?id=' + cm.id + '"]');
            var hasMatch = names.some(function(value) {
                value = (value || '').toLowerCase();
                return value && (text.indexOf(value) !== -1 || html.indexOf(value) !== -1);
            });
            if (hasLink || hasMatch) {
                hideNode(row);
            }
        });
    });
    (payload.excluded || []).forEach(function(cm) {
        var names = [cm.name, cm.component, cm.url, '/mod/' + cm.modname + '/view.php?id=' + cm.id];
        document.querySelectorAll('table tbody tr, .gradeitemheader, .user-grade, .path-grade .cell').forEach(function(node) {
            var text = (node.textContent || '').toLowerCase();
            var html = (node.innerHTML || '').toLowerCase();
            var hasLink = !!node.querySelector('a[href*="/mod/' + cm.modname + '/view.php?id=' + cm.id + '"]');
            var hasMatch = names.some(function(value) {
                value = (value || '').toLowerCase();
                return value && (text.indexOf(value) !== -1 || html.indexOf(value) !== -1);
            });
            if (hasLink || hasMatch) {
                hideNode(node.closest('tr') || node);
            }
        });
    });
    function hideCourseSettings() {
        if (!payload.courseSettings || !payload.courseId) {
            return;
        }
        var selectors = [
            'a[href*="/course/edit.php?id=' + payload.courseId + '"]',
            'a[href*="/course/edit.php?"][href*="id=' + payload.courseId + '"]'
        ];
        document.querySelectorAll(selectors.join(',')).forEach(function(link) {
            hideNode(link.closest('li') || link.closest('.nav-item') || link.closest('.list-group-item') ||
                link.closest('[role="treeitem"]') || link.closest('[data-key]') || link);
        });
    }
    hideCourseSettings();
    if (payload.courseSettings && window.MutationObserver) {
        var settingsObserver = new MutationObserver(hideCourseSettings);
        settingsObserver.observe(document.body, {childList: true, subtree: true});
    }
});
</script>
HTML;
        if ($hook && method_exists($hook, 'add_html')) {
            $hook->add_html($script);
            return '';
        }

        return $script;
    }

    private static function redirect_hidden_course_settings(): void {
        global $PAGE;

        if ($PAGE->url->get_path(false) !== '/course/edit.php') {
            return;
        }

        $courseid = optional_param('id', 0, PARAM_INT);
        if (!$courseid || !rule_manager::should_hide_course_settings_for_user($courseid)) {
            return;
        }

        $courseurl = new \moodle_url('/course/view.php', ['id' => $courseid]);
        redirect($courseurl, get_string('restrictedcoursesettings', 'local_modrules'), null, \core\output\notification::NOTIFY_WARNING);
    }

    private static function get_page_cm(): ?\cm_info {
        global $PAGE;

        if (isset($PAGE->cm) && $PAGE->cm instanceof \cm_info) {
            return $PAGE->cm;
        }

        if (isset($PAGE->cm) && is_object($PAGE->cm) && !empty($PAGE->cm->id)) {
            try {
                return \cm_info::create($PAGE->cm);
            } catch (\Throwable $e) {
                // Fall back to the id parameter below.
            }
        }

        $path = $PAGE->url->get_path(false);
        $cmid = strpos($path, '/mod/') === 0 ? optional_param('id', 0, PARAM_INT) : 0;
        if ($cmid && $cm = get_coursemodule_from_id(null, $cmid, 0, false, IGNORE_MISSING)) {
            try {
                return \cm_info::create($cm);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
