<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu;

defined('MOODLE_INTERNAL') || die();

class segment_resolver {
    public static function get_current_user_segment(): string {
        global $USER;

        if (empty($USER->id)) {
            return '';
        }

        return self::get_user_segment((int)$USER->id);
    }

    public static function get_user_segment(int $userid): string {
        global $DB;

        $fieldshortname = trim((string)get_config('local_segmentmenu', 'segmentfield'));
        if ($fieldshortname === '') {
            return '';
        }

        $sql = "SELECT d.data
                  FROM {user_info_field} f
             LEFT JOIN {user_info_data} d ON d.fieldid = f.id AND d.userid = :userid
                 WHERE f.shortname = :shortname";
        $value = $DB->get_field_sql($sql, [
            'userid' => $userid,
            'shortname' => $fieldshortname,
        ]);

        return item_repository::normalise_segment($value ?: '');
    }

    public static function get_current_user_course_roles(): array {
        global $PAGE, $USER;

        if (empty($USER->id) || empty($PAGE->course->id) || (int)$PAGE->course->id === SITEID) {
            return [];
        }

        $context = \context_course::instance((int)$PAGE->course->id, IGNORE_MISSING);
        if (!$context) {
            return [];
        }

        $roles = get_user_roles($context, $USER->id, false);
        $shortnames = [];
        foreach ($roles as $role) {
            if (!empty($role->shortname)) {
                $shortnames[] = strtolower(trim($role->shortname));
            }
        }

        return array_values(array_unique(array_filter($shortnames)));
    }

    public static function get_current_courseid(): int {
        global $PAGE;

        if (empty($PAGE->course->id) || (int)$PAGE->course->id === SITEID) {
            return 0;
        }

        return (int)$PAGE->course->id;
    }

    public static function get_current_user_roles_in_courses(array $courseids): array {
        global $USER;

        if (empty($USER->id)) {
            return [];
        }

        $roles = [];
        foreach (array_unique(array_filter(array_map('intval', $courseids))) as $courseid) {
            $context = \context_course::instance($courseid, IGNORE_MISSING);
            if (!$context) {
                continue;
            }

            foreach (get_user_roles($context, $USER->id, false) as $role) {
                if (!empty($role->shortname)) {
                    $roles[] = strtolower(trim($role->shortname));
                }
            }
        }

        return array_values(array_unique(array_filter($roles)));
    }
}
