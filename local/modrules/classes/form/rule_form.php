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

        $mform->addElement('duration', 'maxeditingtimeminutes', get_string('maxeditingtimeminutes', 'local_modrules'), [
            'optional' => false,
            'defaultunit' => MINSECS,
            'units' => [
                MINSECS,
                HOURSECS,
                DAYSECS,
            ],
        ]);
        $mform->addHelpButton('maxeditingtimeminutes', 'maxeditingtimeminutes', 'local_modrules');
        $mform->setDefault('maxeditingtimeminutes', MINSECS);
        $mform->hideIf('maxeditingtimeminutes', 'ruletype', 'neq', rule_repository::TYPE_FORUM_MAX_EDITING_TIME);

        $forumoptions = [];
        $sql = "SELECT f.id,
                       f.name,
                       c.fullname,
                       c.shortname
                  FROM {forum} f
                  JOIN {course} c ON c.id = f.course
                 WHERE c.id <> :siteid
              ORDER BY c.sortorder ASC, f.name ASC";
        foreach ($DB->get_records_sql($sql, ['siteid' => SITEID]) as $forum) {
            $forumoptions[$forum->id] = format_string($forum->fullname) . ' / ' . format_string($forum->name) .
                ' (' . s($forum->shortname) . ')';
        }
        $mform->addElement('autocomplete', 'forumids', get_string('forumids', 'local_modrules'), $forumoptions,
            ['multiple' => true]);
        $mform->addHelpButton('forumids', 'forumids', 'local_modrules');
        $mform->hideIf('forumids', 'ruletype', 'neq', rule_repository::TYPE_FORUM_MAX_EDITING_TIME);

        $this->add_action_buttons(true);

        if ($rule) {
            $data = clone $rule;
            $data->roleids = rule_repository::ids_from_string($rule->roleids);
            $data->courseids = rule_repository::ids_from_string($rule->courseids);
            $configdata = rule_repository::decode_configdata($rule->configdata ?? '');
            if ($rule->ruletype === rule_repository::TYPE_FORUM_MAX_EDITING_TIME) {
                $data->maxeditingtimeminutes = (int)($configdata->maxeditingtime ?? MINSECS);
                $data->forumids = rule_repository::ids_from_string($configdata->forumids ?? '');
            }
            $this->set_data($data);
        }
    }

    public function validation($data, $files): array {
        global $DB;

        $errors = parent::validation($data, $files);

        $norolesrequired = [
            rule_repository::TYPE_EXCLUDE_ACTIVITY,
            rule_repository::TYPE_FORUM_MAX_EDITING_TIME,
        ];
        if (!in_array($data['ruletype'], $norolesrequired, true) && empty($data['roleids'])) {
            $errors['roleids'] = get_string('roleidsrequired', 'local_modrules');
        }

        if ($data['ruletype'] === rule_repository::TYPE_FORUM_MAX_EDITING_TIME) {
            if (empty($data['courseids'])) {
                $errors['courseids'] = get_string('courseidsrequired', 'local_modrules');
            }
            if (empty($data['maxeditingtimeminutes']) || (int)$data['maxeditingtimeminutes'] < 1) {
                $errors['maxeditingtimeminutes'] = get_string('maxeditingtimepositive', 'local_modrules');
            }
            if (!empty($data['forumids']) && !empty($data['courseids'])) {
                [$insql, $params] = $DB->get_in_or_equal(array_map('intval', $data['forumids']), SQL_PARAMS_NAMED);
                $forums = $DB->get_records_select('forum', "id {$insql}", $params, '', 'id, course');
                $courseids = array_map('intval', $data['courseids']);
                foreach ($forums as $forum) {
                    if (!in_array((int)$forum->course, $courseids, true)) {
                        $errors['forumids'] = get_string('forumidsoutsidecourses', 'local_modrules');
                        break;
                    }
                }
            }
        }

        return $errors;
    }
}
