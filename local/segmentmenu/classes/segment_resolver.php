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
}
