<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules\task;

defined('MOODLE_INTERNAL') || die();

class sync_grade_exclusions extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('syncgradeexclusions', 'local_modrules');
    }

    public function execute(): void {
        \local_modrules\grade_exclusion_manager::sync_all();
    }
}
