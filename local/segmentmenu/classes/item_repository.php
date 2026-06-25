<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu;

defined('MOODLE_INTERNAL') || die();

class item_repository {
    private const RESTRICTION_SEGMENT = 'segment';
    private const RESTRICTION_ROLE = 'role';
    private const RESTRICTION_BOTH = 'both';
    public const ALL_OPTION = '__all__';

    public static function table_exists(): bool {
        global $DB;

        return $DB->get_manager()->table_exists('local_segmentmenu_items');
    }

    public static function get_all(bool $enabledonly = false): array {
        global $DB;

        if (!self::table_exists()) {
            return [];
        }

        $conditions = $enabledonly ? ['enabled' => 1] : null;
        return $DB->get_records('local_segmentmenu_items', $conditions, 'sortorder ASC, name ASC');
    }

    public static function get(int $id): \stdClass {
        global $DB;

        return $DB->get_record('local_segmentmenu_items', ['id' => $id], '*', MUST_EXIST);
    }

    public static function get_for_segment(?string $segment): array {
        global $DB;

        if (!self::table_exists()) {
            return [];
        }

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

    public static function get_for_user(?string $segment, array $roles, int $currentcourseid = 0): array {
        return array_values(array_filter(
            self::get_all(true),
            static function(\stdClass $item) use ($segment, $roles, $currentcourseid): bool {
                return self::matches_restrictions($item, $segment, $roles, $currentcourseid);
            }
        ));
    }

    public static function save(\stdClass $data): int {
        global $DB, $USER;

        $now = time();
        $record = (object) [
            'name' => trim($data->name),
            'url' => trim($data->url),
            'courseids' => self::normalise_courseids($data->courseids ?? ''),
            'segment' => self::normalise_segment($data->segment ?? ''),
            'courseroles' => self::normalise_roles($data->courseroles ?? ''),
            'restrictionmode' => self::normalise_restriction_mode($data->restrictionmode ?? self::RESTRICTION_SEGMENT),
            'linktarget' => self::normalise_target($data->linktarget ?? 'same'),
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

    public static function normalise_target(?string $target): string {
        return $target === 'new' ? 'new' : 'same';
    }

    public static function normalise_courseids($courseids): string {
        if (is_array($courseids)) {
            if (in_array(self::ALL_OPTION, $courseids, true)) {
                return '';
            }
            $courseids = implode(',', $courseids);
        }

        if (trim((string)$courseids) === self::ALL_OPTION) {
            return '';
        }

        $courseids = preg_split('/[\s,;]+/', trim((string)$courseids));
        $courseids = array_map('intval', array_filter($courseids, 'is_numeric'));
        $courseids = array_values(array_unique(array_filter($courseids)));

        return implode(',', $courseids);
    }

    public static function get_item_courseids(\stdClass $item): array {
        $courseids = self::normalise_courseids($item->courseids ?? '');
        return $courseids === '' ? [] : array_map('intval', explode(',', $courseids));
    }

    public static function get_course_options(): array {
        global $DB;

        $courses = $DB->get_records_select(
            'course',
            'id <> :siteid',
            ['siteid' => SITEID],
            'fullname ASC',
            'id, fullname, shortname, visible'
        );
        $options = [
            self::ALL_OPTION => get_string('allcourses', 'local_segmentmenu'),
        ];
        foreach ($courses as $course) {
            $label = format_string($course->fullname);
            if (!empty($course->shortname) && $course->shortname !== $course->fullname) {
                $label .= ' (' . format_string($course->shortname) . ')';
            }
            if (empty($course->visible)) {
                $label .= ' - ' . get_string('coursehidden', 'local_segmentmenu');
            }
            $options[$course->id] = $label;
        }

        return $options;
    }

    public static function get_course_names(array $courseids): array {
        global $DB;

        $courseids = array_values(array_unique(array_filter(array_map('intval', $courseids))));
        if (!$courseids) {
            return [];
        }

        $courses = $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname');
        $names = [];
        foreach ($courseids as $courseid) {
            if (!empty($courses[$courseid])) {
                $names[] = format_string($courses[$courseid]->fullname);
            }
        }

        return $names;
    }

    public static function get_role_options(): array {
        global $DB;

        $roles = $DB->get_records('role', null, 'sortorder ASC, name ASC, shortname ASC', 'id, name, shortname');

        $options = [
            self::ALL_OPTION => get_string('allroles', 'local_segmentmenu'),
        ];
        foreach ($roles as $role) {
            if (empty($role->shortname)) {
                continue;
            }

            $name = role_get_name($role, \context_system::instance(), ROLENAME_ORIGINAL);
            if ($name === '') {
                $name = $role->name ?: $role->shortname;
            }

            $options[strtolower($role->shortname)] = $name . ' (' . $role->shortname . ')';
        }

        return $options;
    }

    public static function get_role_names(array $roles): array {
        $roles = self::normalise_roles($roles);
        if ($roles === '') {
            return [];
        }

        $options = self::get_role_options();
        $names = [];
        foreach (explode(',', $roles) as $role) {
            $names[] = $options[$role] ?? $role;
        }

        return $names;
    }


    public static function normalise_restriction_mode(?string $mode): string {
        return in_array($mode, [
            self::RESTRICTION_SEGMENT,
            self::RESTRICTION_ROLE,
            self::RESTRICTION_BOTH,
        ], true) ? $mode : self::RESTRICTION_SEGMENT;
    }

    public static function normalise_roles($roles): string {
        if (is_array($roles)) {
            if (in_array(self::ALL_OPTION, $roles, true)) {
                return '';
            }
            $roles = implode(',', $roles);
        }

        if (trim((string)$roles) === self::ALL_OPTION) {
            return '';
        }

        $roles = preg_split('/[\s,;]+/', strtolower(trim((string)$roles)));
        $roles = array_values(array_unique(array_filter(array_map('trim', $roles))));

        return implode(',', $roles);
    }

    public static function get_item_roles(\stdClass $item): array {
        $roles = self::normalise_roles($item->courseroles ?? '');
        return $roles === '' ? [] : explode(',', $roles);
    }

    public static function get_restriction_modes(): array {
        return [
            self::RESTRICTION_SEGMENT => get_string('restrictionsegment', 'local_segmentmenu'),
            self::RESTRICTION_ROLE => get_string('restrictionrole', 'local_segmentmenu'),
            self::RESTRICTION_BOTH => get_string('restrictionboth', 'local_segmentmenu'),
        ];
    }

    public static function matches_restrictions(
        \stdClass $item,
        ?string $segment,
        array $roles,
        int $currentcourseid = 0
    ): bool {
        if (!self::matches_course_scope($item, $currentcourseid)) {
            return false;
        }

        $mode = self::normalise_restriction_mode($item->restrictionmode ?? self::RESTRICTION_SEGMENT);
        $segmentmatches = self::matches_segment($item, $segment);
        $itemcourseids = self::get_item_courseids($item);
        $matchroles = $itemcourseids && $currentcourseid > 0
            ? segment_resolver::get_current_user_roles_in_courses([$currentcourseid])
            : $roles;
        $rolematches = self::matches_roles($item, $matchroles);

        if ($mode === self::RESTRICTION_ROLE) {
            return $rolematches;
        }
        if ($mode === self::RESTRICTION_BOTH) {
            return $segmentmatches && $rolematches;
        }

        return $segmentmatches;
    }

    private static function matches_course_scope(\stdClass $item, int $currentcourseid): bool {
        $courseids = self::get_item_courseids($item);
        if (!$courseids) {
            return true;
        }

        return $currentcourseid > 0 && in_array($currentcourseid, $courseids, true);
    }

    private static function matches_segment(\stdClass $item, ?string $segment): bool {
        $itemsegment = self::normalise_segment($item->segment ?? '');
        return $itemsegment === '' || $itemsegment === self::normalise_segment($segment);
    }

    private static function matches_roles(\stdClass $item, array $roles): bool {
        $itemroles = self::get_item_roles($item);
        if (!$itemroles) {
            return true;
        }

        $roles = array_map('strtolower', array_map('trim', $roles));
        return (bool)array_intersect($itemroles, $roles);
    }

    public static function get_configured_items_for_user(?string $segment, array $roles, int $currentcourseid = 0): array {
        $segment = self::normalise_segment($segment);
        $items = [];
        $definition = (string)get_config('local_segmentmenu', 'menuitems');
        $lines = preg_split('/\r\n|\r|\n/', $definition);

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            $name = ltrim($parts[0] ?? '', '-');
            $url = $parts[1] ?? '';
            $itemsegment = self::normalise_segment($parts[2] ?? '');
            $target = self::normalise_target($parts[3] ?? 'same');
            $courseroles = self::normalise_roles($parts[4] ?? '');
            $restrictionmode = self::normalise_restriction_mode($parts[5] ?? self::RESTRICTION_SEGMENT);

            if ($name === '' || $url === '') {
                continue;
            }

            $item = (object) [
                'id' => 'config-' . $index,
                'name' => $name,
                'url' => $url,
                'courseids' => '',
                'segment' => $itemsegment,
                'courseroles' => $courseroles,
                'restrictionmode' => $restrictionmode,
                'linktarget' => $target,
                'sortorder' => $index,
                'source' => 'config',
            ];

            if (!self::matches_restrictions($item, $segment, $roles, $currentcourseid)) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    public static function get_menu_items_for_user(?string $segment, array $roles, int $currentcourseid = 0): array {
        $items = array_merge(
            self::get_configured_items_for_user($segment, $roles, $currentcourseid),
            self::get_for_user($segment, $roles, $currentcourseid)
        );

        usort($items, static function($a, $b): int {
            $sort = ((int)$a->sortorder) <=> ((int)$b->sortorder);
            if ($sort !== 0) {
                return $sort;
            }
            return strcasecmp((string)$a->name, (string)$b->name);
        });

        return $items;
    }

    public static function get_menu_items_for_segment(?string $segment): array {
        return self::get_menu_items_for_user($segment, []);
    }
}
