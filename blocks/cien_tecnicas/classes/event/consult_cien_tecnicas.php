<?php
namespace block_cien_tecnicas\event;

defined('MOODLE_INTERNAL') || die();

class consult_cien_tecnicas extends \core\event\base {

    protected function init() {
//        $this->data['objecttable'] = 'block_cien_tecnicas';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('consult_cien_tecnicas', 'block_cien_tecnicas');
    }

    public function get_description() {
        if (isset($this->other['filtro']) && isset($this->other['busqueda'])){
            return "El usuario con el id '{$this->userid}' realizó la busqueda con los parámetros, filtro '{$this->other['filtro']}' con la busqueda '{$this->other['busqueda']}'";
        }
    }
}

