<?php
namespace block_normateca_unadm\event;

defined('MOODLE_INTERNAL') || die();

class consult_normateca_unadm extends \core\event\base {

    protected function init() {
//        $this->data['objecttable'] = 'block_normateca_unadm';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('consult_normateca_unadm', 'block_normateca_unadm');
    }

    public function get_description() {
        if (isset($this->other['filtro1']) && isset($this->other['filtro2'])){
            return "El usuario con el id '{$this->userid}' realizó la busqueda con los parámetros, división '{$this->other['filtro1']}' para la carrera '{$this->other['filtro2']} con la dependencia '{$this->other['filtro3']}' y recurso '{$this->other['filtro4']}";
        }
    }
}

