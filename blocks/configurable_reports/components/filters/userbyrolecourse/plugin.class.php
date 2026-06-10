<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Configurable Reports filter for users grouped by group and role.
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/configurable_reports/plugin.class.php');

/**
 * Class plugin_userbyrolecourse
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_userbyrolecourse extends plugin_base {

    /**
     * Init.
     *
     * @return void
     */
    public function init(): void {
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('filteruserbyrolecourse', 'block_configurable_reports');
        $this->reporttypes = ['sql'];
    }

    /**
     * Summary.
     *
     * @param object $data
     * @return string
     */
    public function summary(object $data): string {
        global $DB;

        if (empty($data->roleid) || !$role = $DB->get_record('role', ['id' => (int) $data->roleid])) {
            return get_string('filteruserbyrolecourse_summary_norole', 'block_configurable_reports');
        }

        $rolename = role_get_name($role, context_system::instance(), ROLENAME_ORIGINAL);

        return get_string('filteruserbyrolecourse_summary', 'block_configurable_reports', $rolename);
    }

    /**
     * Execute the SQL filter.
     *
     * @param string $finalelements
     * @param bool|object $formdata
     * @return string
     */
    public function execute($finalelements, $formdata = false): string {
        $roleid = (is_object($formdata) && !empty($formdata->roleid)) ? (int) $formdata->roleid : 0;
        $paramname = $this->get_filter_param_name($roleid);
        $groupid = optional_param($paramname, 0, PARAM_INT);

        if (!$groupid) {
            return $finalelements;
        }

        if (preg_match("/%%FILTER_USERBYROLEGROUP:([^%]+)%%/i", $finalelements, $output)) {
            $replace = ' AND ' . $output[1] . ' = ' . $groupid . ' ';

            return str_replace('%%FILTER_USERBYROLEGROUP:' . $output[1] . '%%', $replace, $finalelements);
        }

        if (preg_match("/%%FILTER_USERBYROLECOURSE:([^%]+)%%/i", $finalelements, $output)) {
            $replace = ' AND ' . $output[1] . ' = ' . $groupid . ' ';

            return str_replace('%%FILTER_USERBYROLECOURSE:' . $output[1] . '%%', $replace, $finalelements);
        }

        return $finalelements;
    }

    /**
     * Print filter.
     *
     * @param MoodleQuickForm $mform
     * @param bool|object $formdata
     * @return void
     */
    public function print_filter(MoodleQuickForm $mform, $formdata = false): void {
        global $DB;

        $roleid = (is_object($formdata) && !empty($formdata->roleid)) ? (int) $formdata->roleid : 0;
        $paramname = $this->get_filter_param_name($roleid);
        $options = [0 => get_string('filter_all', 'block_configurable_reports')];

        if ($roleid) {
            $records = $this->get_users_by_group_for_role($roleid);
            foreach ($records as $groupid => $data) {
                $options[$groupid] = get_string('filteruserbyrolecourse_option', 'block_configurable_reports', $data);
            }
        }

        $label = get_string('filteruserbyrolecourse', 'block_configurable_reports');
        if ($roleid && $role = $DB->get_record('role', ['id' => $roleid])) {
            $label = get_string(
                'filteruserbyrolecourse_select',
                'block_configurable_reports',
                role_get_name($role, context_system::instance(), ROLENAME_ORIGINAL)
            );
        }

        $mform->addElement('select', $paramname, $label, $options);
        $mform->setType($paramname, PARAM_INT);
    }

    /**
     * Build a request parameter name for the configured role.
     *
     * @param int $roleid
     * @return string
     */
    private function get_filter_param_name(int $roleid): string {
        return 'filter_userbyrolecourse' . ($roleid ? '_' . $roleid : '');
    }

    /**
     * Get groups with users that have the configured role in the current course context.
     *
     * @param int $roleid
     * @return array
     */
    private function get_users_by_group_for_role(int $roleid): array {
        global $COURSE, $DB;

        $sql = "SELECT gm.id AS membershipid,
                       g.id AS groupid,
                       g.name AS groupname,
                       u.id,
                       u.firstname,
                       u.lastname,
                       u.firstnamephonetic,
                       u.lastnamephonetic,
                       u.middlename,
                       u.alternatename
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ctx.id = ra.contextid
                       AND ctx.contextlevel = :contextlevel
                       AND ctx.instanceid = :courseid
                  JOIN {user} u ON u.id = ra.userid
                  JOIN {groups_members} gm ON gm.userid = u.id
                  JOIN {groups} g ON g.id = gm.groupid
                       AND g.courseid = ctx.instanceid
                 WHERE ra.roleid = :roleid
                       AND u.deleted = 0
              ORDER BY g.name ASC, u.lastname ASC, u.firstname ASC";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $COURSE->id,
            'roleid' => $roleid,
        ];

        $groups = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            if (isset($groups[$record->groupid])) {
                continue;
            }

            $groups[$record->groupid] = (object) [
                'user' => fullname($record),
                'group' => format_string($record->groupname),
            ];
        }

        return $groups;
    }
}
