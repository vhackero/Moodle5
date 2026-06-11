<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules;

defined('MOODLE_INTERNAL') || die();

class grade_exclusion_manager {
    private static $touchedcourseids = [];

    public static function sync_all(): void {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_modrules_gradeexcl')) {
            return;
        }

        $active = [];
        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_EXCLUDE_ACTIVITY) {
                continue;
            }

            foreach (self::get_matching_grade_items($rule) as $gradeitem) {
                $active[$gradeitem->id] = true;
                self::exclude_grade_item($rule, $gradeitem);
            }
        }

        self::release_stale_exclusions($active);
        self::regrade_courses(self::$touchedcourseids);
        self::$touchedcourseids = [];
    }

    public static function release_rule(int $ruleid): void {
        global $DB;

        if (!$DB->get_manager()->table_exists('local_modrules_gradeexcl')) {
            return;
        }

        $records = $DB->get_records('local_modrules_gradeexcl', ['ruleid' => $ruleid]);
        foreach ($records as $record) {
            self::release_grade_grade($record);
        }

        self::regrade_courses(self::$touchedcourseids);
        self::$touchedcourseids = [];
    }

    public static function get_excluded_cm_payload(int $courseid): array {
        $excluded = [];
        foreach (rule_repository::get_all(true) as $rule) {
            if ($rule->ruletype !== rule_repository::TYPE_EXCLUDE_ACTIVITY) {
                continue;
            }

            $modinfo = get_fast_modinfo($courseid);
            foreach ($modinfo->get_cms() as $cm) {
                if (!rule_manager::rule_matches_cm($rule, $cm)) {
                    continue;
                }

                $excluded[] = [
                    'id' => $cm->id,
                    'name' => $cm->name,
                    'modname' => $cm->modname,
                    'component' => 'mod_' . $cm->modname,
                    'url' => (new \moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]))->out(false),
                ];
            }
        }

        return $excluded;
    }

    private static function get_matching_grade_items(\stdClass $rule): array {
        global $DB;

        $gradeitems = [];
        $courseids = rule_repository::ids_from_string($rule->courseids);
        if (!$courseids) {
            $courseids = $DB->get_fieldset_select('course', 'id', 'id <> ?', [SITEID]);
        }

        foreach ($courseids as $courseid) {
            $modinfo = get_fast_modinfo((int)$courseid);
            foreach ($modinfo->get_cms() as $cm) {
                if (!rule_manager::rule_matches_cm($rule, $cm)) {
                    continue;
                }

                $items = $DB->get_records('grade_items', [
                    'courseid' => $cm->course,
                    'itemtype' => 'mod',
                    'itemmodule' => $cm->modname,
                    'iteminstance' => $cm->instance,
                ]);
                foreach ($items as $item) {
                    $gradeitems[$item->id] = $item;
                }
            }
        }

        return $gradeitems;
    }

    private static function exclude_grade_item(\stdClass $rule, \stdClass $gradeitem): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/gradelib.php');

        $grades = $DB->get_records('grade_grades', ['itemid' => $gradeitem->id]);
        $now = time();

        foreach ($grades as $grade) {
            if (!empty($grade->excluded)) {
                continue;
            }

            $grade->excluded = 1;
            $grade->timemodified = $now;
            $DB->update_record('grade_grades', $grade);
            self::$touchedcourseids[$gradeitem->courseid] = (int)$gradeitem->courseid;
            if ($item = \grade_item::fetch(['id' => $gradeitem->id])) {
                $item->force_regrading();
            }

            if (!$DB->record_exists('local_modrules_gradeexcl', ['gradegradeid' => $grade->id])) {
                $DB->insert_record('local_modrules_gradeexcl', (object) [
                    'ruleid' => $rule->id,
                    'gradeitemid' => $gradeitem->id,
                    'gradegradeid' => $grade->id,
                    'userid' => $grade->userid,
                    'timecreated' => $now,
                ]);
            }
        }
    }

    private static function release_stale_exclusions(array $active): void {
        global $DB;

        $records = $DB->get_records('local_modrules_gradeexcl');
        foreach ($records as $record) {
            if (!isset($active[$record->gradeitemid])) {
                self::release_grade_grade($record);
            }
        }
    }

    private static function release_grade_grade(\stdClass $record): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/gradelib.php');

        if ($grade = $DB->get_record('grade_grades', ['id' => $record->gradegradeid])) {
            if (!empty($grade->excluded)) {
                $grade->excluded = 0;
                $grade->timemodified = time();
                $DB->update_record('grade_grades', $grade);
                if ($gradeitem = $DB->get_record('grade_items', ['id' => $record->gradeitemid], 'id, courseid')) {
                    self::$touchedcourseids[$gradeitem->courseid] = (int)$gradeitem->courseid;
                    if ($item = \grade_item::fetch(['id' => $gradeitem->id])) {
                        $item->force_regrading();
                    }
                }
            }
        }

        $DB->delete_records('local_modrules_gradeexcl', ['id' => $record->id]);
    }

    private static function regrade_courses(array $courseids): void {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');
        if (function_exists('grade_regrade_final_grades')) {
            foreach ($courseids as $courseid) {
                grade_regrade_final_grades((int)$courseid);
            }
        }
    }
}
