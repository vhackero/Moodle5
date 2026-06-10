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
 * Configurable Reports filter form for users by group and role.
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * Class userbyrolegroupuser_form
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userbyrolegroupuser_form extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition(): void {
        global $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'crformheader', get_string('filteruserbyrolegroupuser', 'block_configurable_reports'));

        $this->_customdata['compclass']->add_form_elements($mform, $this);

        $roles = $DB->get_records('role', null, 'sortorder ASC, name ASC, shortname ASC');
        $roleoptions = [];
        $systemcontext = context_system::instance();
        foreach ($roles as $role) {
            $rolename = role_get_name($role, $systemcontext, ROLENAME_ORIGINAL);
            $roleoptions[$role->id] = $rolename . ' (' . $role->shortname . ')';
        }

        $mform->addElement('select', 'roleid', get_string('role'), $roleoptions);
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);

        $this->add_action_buttons(true, get_string('add'));
    }
}
