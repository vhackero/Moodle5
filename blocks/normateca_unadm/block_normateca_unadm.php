<?php
defined('MOODLE_INTERNAL') || die();

class block_normateca_unadm extends block_base {

    public function init(): void {
        $this->title = ucfirst(get_string('pluginname', 'block_normateca_unadm'));
    }

    public function get_content(): stdClass {
        global $PAGE, $COURSE,$CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Datos que se pasan al template
        $data = [
            'courseid' => $COURSE->id,
            'wwwroot'  => $PAGE->url->get_host(),
            'title'    => $this->title,
        ];

        // Cargar JS AMD
        $PAGE->requires->js_call_amd(
            'block_normateca_unadm/normateca',
            'init',
            [$COURSE->id,$CFG->wwwroot]
        );

        // Renderizar template
        $this->content->text = $PAGE->get_renderer('core')
            ->render_from_template('block_normateca_unadm/block', $data);

        $this->content->footer = '';

        return $this->content;
    }

    public function instance_allow_config(): bool {
        return true;
    }

    public function instance_allow_multiple(): bool {
        return true;
    }

    public function has_config(): bool {
        return true;
    }
}
