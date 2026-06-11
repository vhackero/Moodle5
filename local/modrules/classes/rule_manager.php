<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules;

defined('MOODLE_INTERNAL') || die();

class rule_manager {
    public static function rule_matches_cm(\stdClass $rule, \cm_info $cm): bool {
        if (empty($rule->enabled) || $rule->modname !== $cm->modname) {
            return false;
        }

        $courses = rule_repository::ids_from_string($rule->courseids);
        if ($courses && !in_array((int)$cm->course, $courses, true)) {
            return false;
        }

        $namematch = trim((string)$rule->namematch);
        if ($namematch !== '' && stripos($cm->name, $namematch) === false) {
            return false;
        }

        return true;
    }

    public static function user_has_any_rule_role(\stdClass $rule, \context $context, ?int $userid = null): bool {
        global $USER;

        $roleids = rule_repository::ids_from_string($rule->roleids);
        if (!$roleids) {
            return false;
        }

        $userid = $userid ?? $USER->id;
        $assignedroles = get_user_roles($context, $userid, true);
        foreach ($assignedroles as $assignedrole) {
            if (in_array((int)$assignedrole->roleid, $roleids, true)) {
                return true;
            }
        }

        return false;
    }

    public static function can_bypass(\context $context): bool {
        return is_siteadmin();
    }

    public static function is_cm_allowed_for_user(\cm_info $cm): bool {
        $coursecontext = \context_course::instance($cm->course);
        if (self::can_bypass($coursecontext)) {
            return true;
        }

        $hasmatchingrule = false;
        $hasallowedrole = false;

        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_SHOW_BY_ROLE || !self::rule_matches_cm($rule, $cm)) {
                continue;
            }

            $hasmatchingrule = true;
            if (self::user_has_any_rule_role($rule, $coursecontext)) {
                $hasallowedrole = true;
            }
        }

        if (!$hasmatchingrule) {
            return true;
        }

        return $hasallowedrole && self::is_cm_open_by_dates($cm);
    }

    public static function should_hide_logs_for_user(\cm_info $cm): bool {
        $coursecontext = \context_course::instance($cm->course);
        if (self::can_bypass($coursecontext)) {
            return false;
        }

        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_HIDE_LOGS || !self::rule_matches_cm($rule, $cm)) {
                continue;
            }

            if (self::user_has_any_rule_role($rule, $coursecontext)) {
                return true;
            }
        }

        return false;
    }

    public static function rule_matches_course(\stdClass $rule, int $courseid): bool {
        if (empty($rule->enabled)) {
            return false;
        }

        $courses = rule_repository::ids_from_string($rule->courseids);
        if ($courses && !in_array($courseid, $courses, true)) {
            return false;
        }

        return true;
    }

    public static function should_hide_course_settings_for_user(int $courseid): bool {
        $coursecontext = \context_course::instance($courseid);
        if (self::can_bypass($coursecontext)) {
            return false;
        }

        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_HIDE_COURSE_SETTINGS || !self::rule_matches_course($rule, $courseid)) {
                continue;
            }

            if (self::user_has_any_rule_role($rule, $coursecontext)) {
                return true;
            }
        }

        return false;
    }

    public static function get_forum_maxeditingtime(int $forumid, int $courseid): int {
        global $CFG;

        $coursewide = null;
        $specific = null;

        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_FORUM_MAX_EDITING_TIME ||
                    !self::rule_matches_forum_maxeditingtime($rule, $forumid, $courseid)) {
                continue;
            }

            $config = rule_repository::decode_configdata($rule->configdata ?? '');
            $maxeditingtime = (int)($config->maxeditingtime ?? 0);
            if ($maxeditingtime < 1) {
                continue;
            }

            $forumids = rule_repository::ids_from_string($config->forumids ?? '');
            if ($forumids) {
                $specific = $maxeditingtime;
            } else {
                $coursewide = $maxeditingtime;
            }
        }

        return $specific ?? $coursewide ?? (int)$CFG->maxeditingtime;
    }

    public static function get_forum_maxeditingtime_for_cm(\cm_info $cm): int {
        if ($cm->modname !== 'forum') {
            global $CFG;
            return (int)$CFG->maxeditingtime;
        }

        return self::get_forum_maxeditingtime((int)$cm->instance, (int)$cm->course);
    }

    private static function rule_matches_forum_maxeditingtime(\stdClass $rule, int $forumid, int $courseid): bool {
        if (empty($rule->enabled) || $rule->modname !== 'forum') {
            return false;
        }

        $courses = rule_repository::ids_from_string($rule->courseids);
        if (!$courses || !in_array($courseid, $courses, true)) {
            return false;
        }

        $config = rule_repository::decode_configdata($rule->configdata ?? '');
        $forumids = rule_repository::ids_from_string($config->forumids ?? '');
        if ($forumids && !in_array($forumid, $forumids, true)) {
            return false;
        }

        return true;
    }

    public static function get_hidden_cm_payload(int $courseid): array {
        $modinfo = get_fast_modinfo($courseid);
        $hidden = [];
        $loghidden = [];

        foreach ($modinfo->get_cms() as $cm) {
            if (!self::is_cm_allowed_for_user($cm)) {
                $hidden[] = [
                    'id' => $cm->id,
                    'name' => $cm->name,
                    'modname' => $cm->modname,
                ];
            }
            if (self::should_hide_logs_for_user($cm)) {
                $loghidden[] = [
                    'id' => $cm->id,
                    'name' => $cm->name,
                    'modname' => $cm->modname,
                    'component' => 'mod_' . $cm->modname,
                    'url' => (new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]))->out(false),
                ];
            }
        }

        return [
            'activities' => $hidden,
            'logs' => $loghidden,
            'courseSettings' => self::should_hide_course_settings_for_user($courseid),
            'courseId' => $courseid,
        ];
    }

    public static function is_cm_open_by_dates(\cm_info $cm): bool {
        global $DB;

        if (!$DB->get_manager()->table_exists($cm->modname)) {
            return true;
        }

        $record = $DB->get_record($cm->modname, ['id' => $cm->instance], '*', IGNORE_MISSING);
        if (!$record) {
            return true;
        }

        $now = time();
        $openfields = ['timeopen', 'opentime', 'allowsubmissionsfromdate', 'submissionstart'];
        $closefields = ['timeclose', 'closetime', 'duedate', 'cutoffdate', 'closedate', 'submissionend'];

        foreach ($openfields as $field) {
            if (!empty($record->$field) && $now < (int)$record->$field) {
                return false;
            }
        }

        foreach ($closefields as $field) {
            if (!empty($record->$field) && $now > (int)$record->$field) {
                return false;
            }
        }

        return true;
    }
}
