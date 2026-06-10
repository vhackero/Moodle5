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
 * Configurable Reports filter for users by group and role.
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/blocks/configurable_reports/plugin.class.php');

/**
 * Class plugin_userbyrolegroupuser
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_userbyrolegroupuser extends plugin_base {

    /**
     * Init.
     *
     * @return void
     */
    public function init(): void {
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('filteruserbyrolegroupuser', 'block_configurable_reports');
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
            return get_string('filteruserbyrolegroupuser_summary_norole', 'block_configurable_reports');
        }

        $rolename = role_get_name($role, context_system::instance(), ROLENAME_ORIGINAL);

        return get_string('filteruserbyrolegroupuser_summary', 'block_configurable_reports', $rolename);
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
        $userid = optional_param($paramname, 0, PARAM_INT);

        if (!$userid) {
            return $finalelements;
        }

        if (preg_match("/%%FILTER_USERBYROLEGROUPUSER:([^%]+)%%/i", $finalelements, $output)) {
            $replace = ' AND ' . $output[1] . ' = ' . $userid . ' ';

            return str_replace('%%FILTER_USERBYROLEGROUPUSER:' . $output[1] . '%%', $replace, $finalelements);
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
            foreach ($records as $userid => $data) {
                $options[$userid] = get_string('filteruserbyrolegroupuser_option', 'block_configurable_reports', $data);
            }
        }

        $label = get_string('filteruserbyrolegroupuser', 'block_configurable_reports');
        if ($roleid && $role = $DB->get_record('role', ['id' => $roleid])) {
            $label = get_string(
                'filteruserbyrolegroupuser_select',
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
        return 'filter_userbyrolegroupuser' . ($roleid ? '_' . $roleid : '');
    }

    /**
     * Get users that have the configured role and belong to at least one group in the current course.
     *
     * @param int $roleid
     * @return array
     */
    private function get_users_by_group_for_role(int $roleid): array {
        global $COURSE, $DB;

        $sql = "SELECT gm.id AS membershipid,
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
              ORDER BY u.lastname ASC, u.firstname ASC, g.name ASC";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'courseid' => $COURSE->id,
            'roleid' => $roleid,
        ];

        $users = [];
        foreach ($DB->get_records_sql($sql, $params) as $record) {
            if (!isset($users[$record->id])) {
                $users[$record->id] = (object) [
                    'user' => fullname($record),
                    'groups' => [],
                ];
            }

            $users[$record->id]->groups[] = format_string($record->groupname);
        }

        foreach ($users as $user) {
            $user->groups = implode(', ', array_unique($user->groups));
        }

        return $users;
    }
}
