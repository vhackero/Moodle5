<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules;

defined('MOODLE_INTERNAL') || die();

class rule_repository {
    public const TYPE_HIDE_LOGS = 'hidelogs';
    public const TYPE_SHOW_BY_ROLE = 'showbyrole';
    public const TYPE_EXCLUDE_ACTIVITY = 'excludeactivity';
    public const TYPE_HIDE_COURSE_SETTINGS = 'hidecoursesettings';

    public static function get_rule_types(): array {
        return [
            self::TYPE_HIDE_LOGS => get_string('hidelogs', 'local_modrules'),
            self::TYPE_SHOW_BY_ROLE => get_string('showbyrole', 'local_modrules'),
            self::TYPE_EXCLUDE_ACTIVITY => get_string('excludeactivity', 'local_modrules'),
            self::TYPE_HIDE_COURSE_SETTINGS => get_string('hidecoursesettings', 'local_modrules'),
        ];
    }

    public static function get_all(bool $enabledonly = false): array {
        global $DB;

        $conditions = $enabledonly ? ['enabled' => 1] : null;
        return $DB->get_records('local_modrules_rules', $conditions, 'enabled DESC, name ASC');
    }

    public static function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record('local_modrules_rules', ['id' => $id], '*', MUST_EXIST);
    }

    public static function save(\stdClass $data): int {
        global $DB, $USER;

        $now = time();
        $modname = clean_param($data->modname, PARAM_COMPONENT);
        $namematch = trim((string)($data->namematch ?? ''));
        if ($data->ruletype === self::TYPE_HIDE_COURSE_SETTINGS) {
            $modname = 'course';
            $namematch = '';
        }

        $record = (object) [
            'name' => trim($data->name),
            'ruletype' => $data->ruletype,
            'enabled' => empty($data->enabled) ? 0 : 1,
            'modname' => $modname,
            'namematch' => $namematch,
            'roleids' => self::serialise_ids($data->roleids ?? []),
            'courseids' => self::serialise_ids($data->courseids ?? []),
            'timemodified' => $now,
            'usermodified' => $USER->id,
        ];

        if (!empty($data->id)) {
            $record->id = (int)$data->id;
            $DB->update_record('local_modrules_rules', $record);
            grade_exclusion_manager::sync_all();
            return $record->id;
        }

        $record->timecreated = $now;
        $id = $DB->insert_record('local_modrules_rules', $record);
        grade_exclusion_manager::sync_all();
        return $id;
    }

    public static function delete(int $id): void {
        global $DB;

        $DB->delete_records('local_modrules_rules', ['id' => $id]);
        grade_exclusion_manager::sync_all();
    }

    public static function toggle(int $id): void {
        global $DB, $USER;

        $record = self::get($id);
        $record->enabled = empty($record->enabled) ? 1 : 0;
        $record->timemodified = time();
        $record->usermodified = $USER->id;
        $DB->update_record('local_modrules_rules', $record);
        grade_exclusion_manager::sync_all();
    }

    public static function ids_from_string(?string $value): array {
        if (empty($value)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $value))));
    }

    private static function serialise_ids($ids): string {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        return implode(',', $ids);
    }
}
