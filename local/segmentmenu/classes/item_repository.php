<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu;

defined('MOODLE_INTERNAL') || die();

class item_repository {
    public static function get_all(bool $enabledonly = false): array {
        global $DB;

        $conditions = $enabledonly ? ['enabled' => 1] : null;
        return $DB->get_records('local_segmentmenu_items', $conditions, 'sortorder ASC, name ASC');
    }

    public static function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record('local_segmentmenu_items', ['id' => $id], '*', MUST_EXIST);
    }

    public static function get_for_segment(?string $segment): array {
        global $DB;

        $segment = self::normalise_segment($segment);
        if ($segment === '') {
            return $DB->get_records_select(
                'local_segmentmenu_items',
                "enabled = 1 AND (" . $DB->sql_compare_text('segment') . " = '' OR segment IS NULL)",
                [],
                'sortorder ASC, name ASC'
            );
        }

        return $DB->get_records_select(
            'local_segmentmenu_items',
            "enabled = 1 AND (" . $DB->sql_compare_text('segment') . " = :segment OR " .
                $DB->sql_compare_text('segment') . " = '' OR segment IS NULL)",
            ['segment' => $segment],
            'sortorder ASC, name ASC'
        );
    }

    public static function save(\stdClass $data): int {
        global $DB, $USER;

        $now = time();
        $record = (object) [
            'name' => trim($data->name),
            'url' => trim($data->url),
            'segment' => self::normalise_segment($data->segment ?? ''),
            'enabled' => empty($data->enabled) ? 0 : 1,
            'sortorder' => (int)($data->sortorder ?? 0),
            'timemodified' => $now,
            'usermodified' => $USER->id,
        ];

        if (!empty($data->id)) {
            $record->id = (int)$data->id;
            $DB->update_record('local_segmentmenu_items', $record);
            return $record->id;
        }

        $record->timecreated = $now;
        return $DB->insert_record('local_segmentmenu_items', $record);
    }

    public static function delete(int $id): void {
        global $DB;

        $DB->delete_records('local_segmentmenu_items', ['id' => $id]);
    }

    public static function toggle(int $id): void {
        global $DB, $USER;

        $record = self::get($id);
        $record->enabled = empty($record->enabled) ? 1 : 0;
        $record->timemodified = time();
        $record->usermodified = $USER->id;
        $DB->update_record('local_segmentmenu_items', $record);
    }

    public static function normalise_segment(?string $segment): string {
        return trim((string)$segment);
    }
}
