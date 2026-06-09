<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class item_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $item = $this->_customdata['item'] ?? null;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('itemname', 'local_segmentmenu'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $courseelement = $mform->addElement(
            'select',
            'courseids',
            get_string('courses', 'local_segmentmenu'),
            \local_segmentmenu\item_repository::get_course_options(),
            ['multiple' => 'multiple', 'size' => 12]
        );
        $courseelement->setMultiple(true);
        $mform->setType('courseids', PARAM_RAW);
        $mform->setDefault('courseids', [\local_segmentmenu\item_repository::ALL_OPTION]);
        $mform->addHelpButton('courseids', 'courses', 'local_segmentmenu');

        $mform->addElement('text', 'url', get_string('url', 'local_segmentmenu'), ['size' => 80]);
        $mform->setType('url', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('url', 'url', 'local_segmentmenu');

        $mform->addElement('text', 'segment', get_string('segment', 'local_segmentmenu'), ['size' => 40]);
        $mform->setType('segment', PARAM_TEXT);
        $mform->addHelpButton('segment', 'segment', 'local_segmentmenu');

        $roleelement = $mform->addElement(
            'select',
            'courseroles',
            get_string('courseroles', 'local_segmentmenu'),
            \local_segmentmenu\item_repository::get_role_options(),
            ['multiple' => 'multiple', 'size' => 8]
        );
        $roleelement->setMultiple(true);
        $mform->setType('courseroles', PARAM_RAW);
        $mform->setDefault('courseroles', [\local_segmentmenu\item_repository::ALL_OPTION]);
        $mform->addHelpButton('courseroles', 'courseroles', 'local_segmentmenu');

        $mform->addElement(
            'select',
            'restrictionmode',
            get_string('restrictionmode', 'local_segmentmenu'),
            \local_segmentmenu\item_repository::get_restriction_modes()
        );
        $mform->setDefault('restrictionmode', 'segment');
        $mform->addHelpButton('restrictionmode', 'restrictionmode', 'local_segmentmenu');

        $mform->addElement('select', 'linktarget', get_string('openin', 'local_segmentmenu'), [
            'same' => get_string('openinsame', 'local_segmentmenu'),
            'new' => get_string('openinnew', 'local_segmentmenu'),
        ]);
        $mform->setDefault('linktarget', 'same');

        $mform->addElement('text', 'sortorder', get_string('sortorder', 'local_segmentmenu'), ['size' => 10]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_segmentmenu'));
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons(true);

        if ($item) {
            $item = clone($item);
            $item->courseids = \local_segmentmenu\item_repository::get_item_courseids($item);
            if (!$item->courseids) {
                $item->courseids = [\local_segmentmenu\item_repository::ALL_OPTION];
            }
            $item->courseroles = \local_segmentmenu\item_repository::get_item_roles($item);
            if (!$item->courseroles) {
                $item->courseroles = [\local_segmentmenu\item_repository::ALL_OPTION];
            }
            $this->set_data($item);
        }
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        $courseids = \local_segmentmenu\item_repository::normalise_courseids($data['courseids'] ?? '');
        if (empty($data['url'])) {
            $errors['url'] = get_string('required');
        }
        if (!empty($data['url']) && !preg_match('~^https?://~i', $data['url']) && strpos($data['url'], '/') !== 0) {
            $errors['url'] = get_string('invalidurl', 'error');
        }
        if ($courseids !== '') {
            global $DB;

            $ids = array_map('intval', explode(',', $courseids));
            [$insql, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $found = $DB->get_fieldset_select('course', 'id', "id {$insql} AND id <> :siteid", $params + ['siteid' => SITEID]);
            $missing = array_diff($ids, array_map('intval', $found));
            if ($missing) {
                $errors['courseids'] = get_string('invalidcourses', 'local_segmentmenu', implode(', ', $missing));
            }
        }
        if (!in_array($data['linktarget'] ?? 'same', ['same', 'new'], true)) {
            $errors['linktarget'] = get_string('invaliddata', 'error');
        }
        if (!in_array($data['restrictionmode'] ?? 'segment', ['segment', 'role', 'both'], true)) {
            $errors['restrictionmode'] = get_string('invaliddata', 'error');
        }

        $roles = \local_segmentmenu\item_repository::normalise_roles($data['courseroles'] ?? '');
        if ($roles !== '') {
            $available = array_keys(\local_segmentmenu\item_repository::get_role_options());
            $missing = array_diff(explode(',', $roles), array_map('strtolower', $available));
            if ($missing) {
                $errors['courseroles'] = get_string('invalidroles', 'local_segmentmenu', implode(', ', $missing));
            }
        }

        return $errors;
    }
}
