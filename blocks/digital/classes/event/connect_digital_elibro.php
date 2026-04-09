<?php
namespace block_digital\event;

defined('MOODLE_INTERNAL') || die();

class connect_digital_elibro extends \core\event\base {

    protected function init() {
//        $this->data['objecttable'] = 'block_cien_tecnicas';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('connect_digital_elibro', 'block_digital');
    }

    public function get_description() {
        if (isset($this->other['message'])){
            return $this->other['message'].' realizado por el usuario '.$this->userid;
        }
    }
}

