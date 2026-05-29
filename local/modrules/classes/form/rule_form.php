<?php
// This file is part of Moodle - http://moodle.org/

namespace local_modrules\form;

use local_modrules\rule_repository;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class rule_form extends \moodleform {
    protected function definition(): void {
        global $DB;

        $mform = $this->_form;
        $rule = $this->_customdata['rule'] ?? null;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_modrules'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('select', 'ruletype', get_string('ruletype', 'local_modrules'), rule_repository::get_rule_types());
        $mform->addRule('ruletype', null, 'required', null, 'client');

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_modrules'));
        $mform->setDefault('enabled', 1);

        $moduleoptions = [
            'course' => get_string('coursesettingsarea', 'local_modrules'),
        ];
        $modules = $DB->get_records('modules', ['visible' => 1], 'name ASC', 'id, name');
        foreach ($modules as $module) {
            $moduleoptions[$module->name] = $module->name;
        }
        $mform->addElement('select', 'modname', get_string('modname', 'local_modrules'), $moduleoptions);
        $mform->addHelpButton('modname', 'modname', 'local_modrules');
        $mform->addRule('modname', null, 'required', null, 'client');

        $mform->addElement('text', 'namematch', get_string('namematch', 'local_modrules'), ['size' => 60]);
        $mform->setType('namematch', PARAM_TEXT);
        $mform->addHelpButton('namematch', 'namematch', 'local_modrules');

        $roleoptions = role_fix_names(get_all_roles(), null, ROLENAME_ALIAS, true);
        $mform->addElement('autocomplete', 'roleids', get_string('roleids', 'local_modrules'), $roleoptions, ['multiple' => true]);
        $mform->addHelpButton('roleids', 'roleids', 'local_modrules');

        $courseoptions = [];
        $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname');
        foreach ($courses as $course) {
            if ((int)$course->id === SITEID) {
                continue;
            }
            $courseoptions[$course->id] = format_string($course->fullname) . ' (' . s($course->shortname) . ')';
        }
        $mform->addElement('autocomplete', 'courseids', get_string('courseids', 'local_modrules'), $courseoptions, ['multiple' => true]);
        $mform->addHelpButton('courseids', 'courseids', 'local_modrules');

        $this->add_action_buttons(true);

        if ($rule) {
            $data = clone $rule;
            $data->roleids = rule_repository::ids_from_string($rule->roleids);
            $data->courseids = rule_repository::ids_from_string($rule->courseids);
            $this->set_data($data);
        }
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if ($data['ruletype'] !== rule_repository::TYPE_EXCLUDE_ACTIVITY && empty($data['roleids'])) {
            $errors['roleids'] = get_string('roleidsrequired', 'local_modrules');
        }

        return $errors;
    }
}
