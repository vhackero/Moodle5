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

        $mform->addElement('text', 'url', get_string('url', 'local_segmentmenu'), ['size' => 80]);
        $mform->setType('url', PARAM_RAW_TRIMMED);
        $mform->addRule('url', null, 'required', null, 'client');
        $mform->addHelpButton('url', 'url', 'local_segmentmenu');

        $mform->addElement('text', 'segment', get_string('segment', 'local_segmentmenu'), ['size' => 40]);
        $mform->setType('segment', PARAM_TEXT);
        $mform->addHelpButton('segment', 'segment', 'local_segmentmenu');

        $mform->addElement('text', 'sortorder', get_string('sortorder', 'local_segmentmenu'), ['size' => 10]);
        $mform->setType('sortorder', PARAM_INT);
        $mform->setDefault('sortorder', 0);

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_segmentmenu'));
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons(true);

        if ($item) {
            $this->set_data($item);
        }
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (!empty($data['url']) && !preg_match('~^https?://~i', $data['url']) && strpos($data['url'], '/') !== 0) {
            $errors['url'] = get_string('invalidurl', 'error');
        }

        return $errors;
    }
}
